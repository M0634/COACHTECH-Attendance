@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
@php
use Carbon\Carbon;

/** 修正申請（未承認） */
$fixRequest = $attendance->latestPendingFix ?? null;
$isPending  = !is_null($fixRequest);

/** 承認済みか */
$isApproved = $attendance->fixRequests?->whereNotNull('approved_at')->isNotEmpty() ?? false;

/** 表示する休憩（申請中があればそちら優先） */
$displayBreaks =
    ($fixRequest && $fixRequest->breaks?->isNotEmpty())
        ? $fixRequest->breaks
        : ($attendance->breaks ?? collect());

$workDate = $attendance->work_date instanceof Carbon
    ? $attendance->work_date->format('Y年n月j日')
    : Carbon::parse($attendance->work_date)->format('Y年n月j日');
@endphp

<div class="attendance-detail">
    <h2 class="page-title">勤怠詳細</h2>

    {{-- エラー --}}
    @if ($errors->any())
        <ul class="error-messages">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    {{-- =========================
        管理者：承認フォーム
        一般：修正申請フォーム
    ========================= --}}
    @if($isAdmin)
        @if($fixRequest)
            <form method="POST"
                action="{{ route('admin.stamp_correction_requests.approve', $fixRequest->id) }}">
                @csrf
        @endif
    @else
        @if(!$isPending)
            <form method="POST"
                action="{{ route('attendance.request-fix', $attendance->id) }}">
                @csrf
                @method('PUT')
        @endif
    @endif

    <table class="detail-table">
        <tr>
            <th>名前</th>
            <td>{{ optional($attendance->user)->name ?? '不明' }}</td>
        </tr>

        <tr>
            <th>日付</th>
            <td>{{ $workDate }}</td>
        </tr>

        {{-- 出勤・退勤 --}}
        <tr>
            <th>出勤・退勤</th>
            <td>
                <input type="time"
                    name="start_time"
                    value="{{ old('start_time', $attendance->display_start_time) }}"
                    {{ (!$isAdmin && $isPending) ? 'disabled' : '' }}>


                〜

                <input type="time"
                    name="end_time"
                    value="{{ old('end_time', $attendance->display_end_time) }}"
                    {{ (!$isAdmin && $isPending) ? 'disabled' : '' }}>

            </td>
        </tr>

        {{-- 休憩 --}}
        @foreach($displayBreaks as $index => $break)
        <tr>
            <th>休憩{{ $index + 1 }}</th>
            <td>
                <input type="time"
                    name="breaks[{{ $index }}][start]"
                    value="{{ old("breaks.$index.start",
                        $break->start_time ? Carbon::parse($break->start_time)->format('H:i') : '') }}"
                    {{ (!$isAdmin && $isPending) ? 'disabled' : '' }}>

                〜

                <input type="time"
                    name="breaks[{{ $index }}][end]"
                    value="{{ old("breaks.$index.end",
                        $break->end_time ? Carbon::parse($break->end_time)->format('H:i') : '') }}"
                    {{ (!$isAdmin && $isPending) ? 'disabled' : '' }}>
            </td>
        </tr>
        @endforeach

        @php
            $nextIndex = $displayBreaks->count();
        @endphp

        {{-- 追加休憩 --}}
        <tr>
            <th>休憩{{ $nextIndex + 1 }}</th>
            <td>
                <input type="time"
                    name="breaks[{{ $nextIndex }}][start]"
                    {{ (!$isAdmin && $isPending) ? 'disabled' : '' }}>

                〜

                <input type="time"
                    name="breaks[{{ $nextIndex }}][end]"
                    {{ (!$isAdmin && $isPending) ? 'disabled' : '' }}>
            </td>
        </tr>

    </table>

    {{-- ボタン --}}
    <div class="button-area">
        @if($isAdmin)
            @if($fixRequest)
                <button type="submit" class="btn-submit approve">
                    承認する
                </button>
            @elseif($isApproved)
                <span class="btn-submit approved">✔ 承認済み</span>
            @else
                <p class="pending-message">修正申請はありません。</p>
            @endif
        @else
            @if($isPending)
                <p class="pending-message">
                    承認待ちのため修正できません。
                </p>
            @else
                <button type="submit" class="btn-submit">
                    修正申請
                </button>
            @endif
        @endif
    </div>

    {{-- フォーム閉じ --}}
    @if(
        ($isAdmin && $fixRequest) ||
        (!$isAdmin && !$isPending)
    )
        </form>
    @endif
</div>
@endsection
