@extends('layouts.app')

@section('title', '勤怠')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@php
    use App\Models\Attendance;
@endphp

@section('content')
<div class="attendance-container">

    {{-- =========================
        ステータス表示
    ========================= --}}
    <span class="status-badge">
        @if (!$attendance)
            勤務外
        @elseif ($attendance->status === Attendance::STATUS_WORKING)
            出勤中
        @elseif ($attendance->status === Attendance::STATUS_BREAK)
            休憩中
        @elseif ($attendance->status === Attendance::STATUS_FINISHED)
            退勤済
        @else
            勤務外
        @endif
    </span>

    {{-- 日付 --}}
    <p class="attendance-date" id="current-date"></p>

    {{-- 時刻 --}}
    <p class="attendance-time" id="current-time"></p>

    {{-- メッセージ --}}
    @if (session('message'))
        <p class="attendance-message">{{ session('message') }}</p>
    @endif

    {{-- =========================
        ボタン制御
    ========================= --}}
    @if (!$attendance)
        {{-- 出勤 --}}
        <form method="POST" action="{{ route('attendance.status') }}">
            @csrf
            <button class="attendance-button">出勤</button>
        </form>

    @elseif ($attendance->status === Attendance::STATUS_WORKING)
        {{-- 出勤中：退勤 + 休憩 --}}
        <div class="attendance-actions">
            <form method="POST" action="{{ route('attendance.finish') }}">
                @csrf
                <button class="attendance-button finish">退勤</button>
            </form>

            <form method="POST" action="{{ route('attendance.status') }}">
                @csrf
                <button class="attendance-button">休憩</button>
            </form>
        </div>

    @elseif ($attendance->status === Attendance::STATUS_BREAK)
        {{-- 休憩中：休憩戻 --}}
        <form method="POST" action="{{ route('attendance.status') }}">
            @csrf
            <button class="attendance-button">休憩戻</button>
        </form>

    @elseif ($attendance->status === Attendance::STATUS_FINISHED)
        {{-- 退勤済 --}}
        <p class="attendance-finished">本日の勤務は終了しました</p>
    @endif

</div>
@endsection

@section('js')
<script>
    function updateTime() {
        const now = new Date();

        const date =
            now.getFullYear() + '年' +
            (now.getMonth() + 1) + '月' +
            now.getDate() + '日';

        const week = ['日','月','火','水','木','金','土'][now.getDay()];

        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        document.getElementById('current-date').textContent =
            `${date}（${week}）`;

        document.getElementById('current-time').textContent =
            `${hours}:${minutes}`;
    }

    updateTime();
    setInterval(updateTime, 1000);
</script>
@endsection
