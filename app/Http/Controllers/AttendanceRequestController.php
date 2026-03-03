<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceRequestController extends Controller
{
    /**
     * 勤怠修正申請一覧（一般ユーザー）
     */
    public function index(Request $request)
    {
        // 念のため保険（auth middleware 前提でも入れてOK）
        if (! Auth::check()) {
            abort(403);
        }

        $user = Auth::user();

        /*
         |------------------------------------------
         | 対象月の取得（YYYY-MM）
         |------------------------------------------
         */
        $month = $request->query('month');

        try {
            $currentMonth = $month
                ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
                : now()->startOfMonth();
        } catch (\Exception $e) {
            // 不正な month が来た場合の保険
            $currentMonth = now()->startOfMonth();
        }

        $start = $currentMonth->copy()->startOfMonth();
        $end = $currentMonth->copy()->endOfMonth();

        /*
         |------------------------------------------
         | 勤怠取得
         |------------------------------------------
         */
        $attendances = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', '>=', $start)
            ->whereDate('work_date', '<=', $end)
            ->orderBy('work_date')
            ->get()
            ->keyBy(fn ($attendance) => $attendance->work_date->toDateString()
            );

        /*
         |------------------------------------------
         | View へ渡す
         |------------------------------------------
         */
        return view('attendance.request_list', [
            'currentMonth' => $currentMonth, // Carbon インスタンス
            'attendances' => $attendances,
        ]);
    }
}
