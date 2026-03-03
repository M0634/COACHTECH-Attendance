<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceFixRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceFixRequestController extends Controller
{
    public function index(Request $request)
    {
        // URLパラメータ取得（デフォルト pending）
        $status = $request->get('status', 'pending');

        // 未承認
        $pendingRequests = AttendanceFixRequest::with([
            'user',
            'attendance.user',
        ])
            ->whereNull('approved_at')
            ->orderBy('created_at', 'desc')
            ->get();

        // 承認済み
        $approvedRequests = AttendanceFixRequest::with([
            'user',
            'attendance.user',
        ])
            ->whereNotNull('approved_at')
            ->orderBy('approved_at', 'desc')
            ->get();

        return view('admin.attendance.fix_requests', compact(
            'pendingRequests',
            'approvedRequests',
            'status'   // ← これ追加
        ));
    }

    /**
     * 修正申請 詳細表示
     * GET admin/stamp_correction_requests/{fixRequest}
     */
    public function show(AttendanceFixRequest $fixRequest)
    {
        $fixRequest->load([
            'user',
            'attendance.user',
            'attendance.breaks',
        ]);

        return view('admin.attendance.fix_request_show', [
            'fixRequest' => $fixRequest,
            'isApproved' => ! is_null($fixRequest->approved_at),
        ]);
    }

    /**
     * 修正申請 承認処理
     * POST admin/stamp_correction_requests/{fixRequest}/approve
     */
    public function approve(AttendanceFixRequest $fixRequest)
    {
        // 二重承認防止
        if ($fixRequest->approved_at) {
            return redirect()
                ->route('admin.stamp_correction_requests.show', $fixRequest->id)
                ->with('error', 'この申請はすでに承認されています');
        }

        DB::transaction(function () use ($fixRequest) {

            $attendance = $fixRequest->attendance;

            // 勤怠データ更新
            $attendance->update([
                'started_at' => $fixRequest->start_time,
                'ended_at' => $fixRequest->end_time,
                'request_status' => Attendance::REQUEST_APPROVED,
            ]);

            // 申請を承認済みに
            $fixRequest->update([
                'approved_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.stamp_correction_requests.show', $fixRequest->id) // ★ここ変更
            ->with('message', '修正申請を承認しました');
    }
}
