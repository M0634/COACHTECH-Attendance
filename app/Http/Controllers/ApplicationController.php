<?php

namespace App\Http\Controllers;

use App\Models\AttendanceFixRequest;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $pendingRequests = AttendanceFixRequest::where('requested_by', $userId)
            ->whereNull('approved_at')
            ->latest()
            ->get();

        $approvedRequests = AttendanceFixRequest::where('requested_by', $userId)
            ->whereNotNull('approved_at')
            ->latest()
            ->get();

        return view('stamp_correction_request.list', compact(
            'pendingRequests',
            'approvedRequests'
        ));
    }
}
