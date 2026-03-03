@extends('layouts.admin_app')

@section('title', '修正申請 承認')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
@php
use Carbon\Carbon;

/** 承認対象 */
$attendance = $fixRequest->attendance;
$user = $fixRequest->user;

$isApproved = !is_null($fixRequest->approved_at);
@endphp

<div class="attendance-detail">
    <h2 class="page-title">勤怠詳細</h2>

    <table class="detail-table">
        <tr>
            <th>申請者</th>
            <td>{{ $user->name }}</td>
        </tr>

        <tr>
            <th>対象日</th>
            <td>{{ Carbon::parse($attendance->work_date)->format('Y年n月j日') }}</td>
        </tr>

        <tr>
            <th>修正理由</th>
            <td>{{ $fixRequest->remark ?? '（記載なし）' }}</td>
        </tr>

        {{-- 出勤・退勤 --}}
        <tr>
            <th>出勤・退勤</th>
            <td>
                {{ $fixRequest->start_time ?? $attendance->start_time }}
                〜
                {{ $fixRequest->end_time ?? $attendance->end_time }}
            </td>
        </tr>

        {{-- 休憩 --}}
        @foreach($fixRequest->breaks ?? [] as $i => $break)
        <tr>
            <th>休憩{{ $i + 1 }}</th>
            <td>
                {{ $break->start_time }} 〜 {{ $break->end_time }}
            </td>
        </tr>
        @endforeach
    </table>

    {{-- 操作 --}}
    <div class="button-area">
        @if($isApproved)
            <span class="btn-submit approved">✔ 承認済み</span>
        @else
            <form method="POST"
                  action="{{ route('admin.stamp_correction_requests.approve', $fixRequest->id) }}">
                @csrf

                <button class="btn-submit approve">承認する</button>
            </form>
        @endif
    </div>
</div>
@endsection
