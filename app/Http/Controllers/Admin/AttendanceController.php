<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * 勤怠一覧（管理者・日別）
     */
    public function list(Request $request)
    {
        $targetDate = $request->get('date', now()->toDateString());
        
        $attendances = Attendance::with([
                'user',
                'breaks',
                'latestPendingFix',
                'latestApprovedFix',
            ])
            ->whereDate('work_date', $targetDate)
            ->whereHas('user', function ($q) {
                $q->where('role', '!=', 'admin');
            })
            ->orderBy('user_id')
            ->get();

        return view('admin.attendance.list', [
            'attendances' => $attendances,
            'targetDate' => $targetDate,
            'prevDate' => Carbon::parse($targetDate)->subDay()->toDateString(),
            'nextDate' => Carbon::parse($targetDate)->addDay()->toDateString(),
        ]);
    }

    /**
     * スタッフ別 月次勤怠一覧（管理者）
     */
    public function monthly(User $user, Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endOfMonth   = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::with([
                'breaks',
                'latestPendingFix',
                'latestApprovedFix',
            ])
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [
                $currentMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->orderBy('work_date')
            ->get();


        return view('admin.attendance.detail', [
            'user' => $user,
            'attendances' => $attendances,
            'currentMonth' => $currentMonth->format('Y-m'),
            'prevMonth' => $currentMonth->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $currentMonth->copy()->addMonth()->format('Y-m'),
        ]);
    }

    public function show(Attendance $attendance)
    {
        $attendance->load([
            'user',
            'breaks',
            'latestPendingFix',
            'latestApprovedFix',
        ]);

        return view('admin.attendance.show', [
            'attendance' => $attendance,
        ]);
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'remarks' => ['required'],

            'breaks.*.start' => ['nullable', 'before_or_equal:end_time'],
            'breaks.*.end' => [
                'nullable',
                'after:breaks.*.start',
                'before_or_equal:end_time'
            ],
        ], [
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'remarks.required' => '備考を記入してください',
            'breaks.*.start.before_or_equal' => '休憩時間が不適切な値です',
            'breaks.*.end.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);

        $attendance->started_at = $attendance->work_date->copy()
            ->setTimeFromTimeString($request->start_time);

        $attendance->ended_at = $attendance->work_date->copy()
            ->setTimeFromTimeString($request->end_time);

        $attendance->remarks = $request->remarks;
        $attendance->save();

        // 休憩リセット
        $attendance->breaks()->delete();

        foreach ($request->breaks ?? [] as $break) {
            if (!empty($break['start']) && !empty($break['end'])) {
                $attendance->breaks()->create([
                    'start_time' => $break['start'],
                    'end_time'   => $break['end'],
                ]);
            }
        }

        return redirect()
            ->route('admin.attendance.show', $attendance)
            ->with('success', '勤怠を修正しました');
    }
    
    public function exportMonthlyCsv(User $user, Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endOfMonth   = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::with([
                'breaks',
                'latestPendingFix.breaks',
                'latestApprovedFix.breaks',
            ])
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [
                $currentMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->orderBy('work_date')
            ->get();

        $fileName = "{$user->name}_attendance_{$month}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        $callback = function () use ($attendances) {
            $handle = fopen('php://output', 'w');

            // Excel文字化け対策
            fwrite($handle, "\xEF\xBB\xBF");

            // ヘッダー
            fputcsv($handle, [
                '日付',
                '出勤',
                '退勤',
                '休憩',
                '合計'
            ]);

            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    Carbon::parse($attendance->work_date)->format('Y-m-d'),
                    $attendance->display_start_time ?? '',
                    $attendance->display_end_time ?? '',
                    $attendance->display_break_time ?? '',
                    $attendance->working_time ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }


}
