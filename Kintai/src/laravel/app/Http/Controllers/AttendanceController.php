<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceFixRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * 当日の勤怠表示
     */
    public function index()
    {
        $attendance = Attendance::with([
            'breaks',
            'latestPendingFix.breaks',
            'latestApprovedFix.breaks',
        ])
        ->where('user_id', auth()->id())
        ->whereDate('work_date', Carbon::today())
        ->first();

        return view('attendance.index', compact('attendance'));
    }

    /**
     * 月次勤怠一覧
     */
    public function monthly(Request $request)
    {
        // ✅ month パラメータ安全処理
        $monthParam = $request->get('month');

        try {
            $currentMonth = $monthParam
                ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
                : now()->startOfMonth();
        } catch (\Exception $e) {
            $currentMonth = now()->startOfMonth();
        }

        $userId = auth()->id();

        /*
        |--------------------------------------------------------------------------
        | 1. Attendance 取得
        |--------------------------------------------------------------------------
        */
        $attendances = Attendance::where('user_id', $userId)
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->with([
                'breaks',
                'latestPendingFix.breaks',
                'latestApprovedFix.breaks'
            ])
            ->get()
            ->keyBy(fn ($a) => $a->work_date->toDateString());

        /*
        |--------------------------------------------------------------------------
        | 2. Attendance が無い日でも break がある場合は仮生成
        |--------------------------------------------------------------------------
        */
        $breaksOnly = AttendanceBreak::whereHas('attendance', function ($q) use ($userId, $currentMonth) {
                $q->where('user_id', $userId)
                ->whereYear('work_date', $currentMonth->year)
                ->whereMonth('work_date', $currentMonth->month);
            })
            ->with('attendance')
            ->get()
            ->groupBy(fn ($b) => $b->attendance->work_date->toDateString());

        foreach ($breaksOnly as $date => $breaksGroup) {

            if (! isset($attendances[$date])) {

                $fakeAttendance = new Attendance([
                    'user_id'    => $userId,
                    'work_date'  => Carbon::parse($date),
                    'started_at' => null,
                    'ended_at'   => null,
                ]);

                // breaks を手動セット（アクセサ用）
                $fakeAttendance->setRelation('breaks', $breaksGroup);

                $attendances[$date] = $fakeAttendance;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. 日付順ソート
        |--------------------------------------------------------------------------
        */
        $attendances = $attendances->sortKeys();

        return view('attendance_list', [
            'attendances'  => $attendances,
            'currentMonth' => $currentMonth,
        ]);
    }


    /**
     * 出勤 / 休憩入 / 休憩戻
     */
    public function updateStatus()
    {
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', $today)
            ->first();

        // ① 出勤（まだ勤怠がない）
        if (! $attendance) {
            Attendance::create([
                'user_id' => auth()->id(),
                'work_date' => $today,
                'status' => Attendance::STATUS_WORKING,
                'started_at' => now(),
            ]);

            return back();
        }

        // ② 出勤中 → 休憩入
        if ($attendance->status === Attendance::STATUS_WORKING) {
            $attendance->update([
                'status' => Attendance::STATUS_BREAK,
            ]);

            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'start_time' => now(),
            ]);

            return back();
        }

        // ③ 休憩中 → 休憩戻
        if ($attendance->status === Attendance::STATUS_BREAK) {
            $attendance->update([
                'status' => Attendance::STATUS_WORKING,
            ]);

            $attendance->breaks()
                ->whereNull('end_time')
                ->latest()
                ->first()
                ?->update([
                    'end_time' => now(),
                ]);

            return back();
        }

        return back();
    }

    /**
     * 退勤
     */
    public function finish()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', Carbon::today())
            ->firstOrFail();

        if ($attendance->status === Attendance::STATUS_FINISHED) {
            return back();
        }

        $attendance->update([
            'status' => Attendance::STATUS_FINISHED,
            'ended_at' => now(),
        ]);

        return back()->with('message', 'お疲れ様でした。');
    }

    /**
     * 勤怠修正申請
     */
    public function requestFix(Request $request, Attendance $attendance)
    {
        if (auth()->user()->role === 'admin') {
            abort(403);
        }

        if ($attendance->request_status === Attendance::REQUEST_PENDING) {
            abort(403);
        }

        $request->validate([
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'breaks.*.start' => 'nullable|date_format:H:i',
            'breaks.*.end' => 'nullable|date_format:H:i',
        ]);

        DB::transaction(function () use ($request, $attendance) {

            $fixRequest = AttendanceFixRequest::create([
                'attendance_id' => $attendance->id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'remark' => $request->remark,
                'requested_by' => auth()->id(),
            ]);

            foreach ($request->breaks ?? [] as $break) {
                if (empty($break['start']) && empty($break['end'])) {
                    continue;
                }

                $fixRequest->breaks()->create([
                    'start_time' => $break['start'],
                    'end_time' => $break['end'],
                ]);
            }

            $attendance->update([
                'request_status' => Attendance::REQUEST_PENDING,
            ]);
        });

        return back()->with('message', '修正申請を送信しました');
    }

    /**
     * 勤怠詳細
     */
    public function show(Attendance $attendance)
    {
        if (
            auth()->user()->role !== 'admin' &&
            $attendance->user_id !== auth()->id()
        ) {
            abort(403);
        }

        $attendance->load([
            'user',
            'breaks' => fn ($q) => $q->orderBy('start_time'),
            'fixRequests',
            'latestPendingFix.breaks',
            'latestApprovedFix.breaks',
        ]);

        return view('attendance.detail', [
            'attendance' => $attendance,
            'isAdmin' => auth()->user()->role === 'admin',
        ]);
    }
}
