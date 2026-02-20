@extends('layouts.admin_app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
@php
use Carbon\Carbon;

/** 未承認の修正申請 */
$fixRequest = $attendance->latestPendingFix ?? null;
$isPending  = !is_null($fixRequest);

/** 承認済み履歴があるか */
$isApproved = $attendance->fixRequests?->whereNotNull('approved_at')->isNotEmpty() ?? false;

/** 表示する休憩（申請中があれば申請内容を優先） */
$displayBreaks =
    ($fixRequest && $fixRequest->breaks?->isNotEmpty())
        ? $fixRequest->breaks
        : ($attendance->breaks ?? collect());

$workDate = $attendance->work_date instanceof Carbon
    ? $attendance->work_date->format('Y年n月j日')
    : Carbon::parse($attendance->work_date)->format('Y年n月j日');

/** 管理者：承認待ちでなければ編集可 */
$canEdit = !$isPending;
@endphp

<div class="attendance-detail">
    <h2 class="page-title">勤怠詳細</h2>

    {{-- エラー表示 --}}
    @if ($errors->any())
        <ul class="error-messages">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    {{-- =========================
        フォーム開始（管理者のみ）
    ========================= --}}
    @if(!$isPending)
        <form method="POST"
              action="{{ route('admin.attendance.update', $attendance->id) }}">
            @csrf
            @method('PUT')
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
                       {{ $canEdit ? '' : 'disabled' }}>

                〜

                <input type="time"
                       name="end_time"
                       value="{{ old('end_time', $attendance->display_end_time) }}"
                       {{ $canEdit ? '' : 'disabled' }}>
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
                       {{ $canEdit ? '' : 'disabled' }}>

                〜

                <input type="time"
                       name="breaks[{{ $index }}][end]"
                       value="{{ old("breaks.$index.end",
                            $break->end_time ? Carbon::parse($break->end_time)->format('H:i') : '') }}"
                       {{ $canEdit ? '' : 'disabled' }}>
            </td>
        </tr>
        @endforeach

        {{-- 追加休憩 --}}
        @php $nextIndex = $displayBreaks->count(); @endphp
        <tr>
            <th>休憩{{ $nextIndex + 1 }}</th>
            <td>
                <input type="time"
                       name="breaks[{{ $nextIndex }}][start]"
                       {{ $canEdit ? '' : 'disabled' }}>

                〜

                <input type="time"
                       name="breaks[{{ $nextIndex }}][end]"
                       {{ $canEdit ? '' : 'disabled' }}>
            </td>
        </tr>

        {{-- 備考 --}}
        <tr>
            <th>備考</th>
            <td>
                <textarea name="remarks"
                          rows="3"
                          {{ $canEdit ? '' : 'disabled' }}>{{ old('remarks', $attendance->remarks) }}</textarea>
            </td>
        </tr>
    </table>

    {{-- ボタン --}}
    <div class="button-area">
        @if($isPending)
            <p class="pending-message">
                承認待ちのため修正はできません。
            </p>
        @else
            <button type="submit" class="btn-submit">
                修正
            </button>
        @endif
    </div>

    {{-- フォーム閉じ --}}
    @if(!$isPending)
        </form>
    @endif
</div>
@endsection
