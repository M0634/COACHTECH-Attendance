@extends('layouts.admin_app')

@section('title', '勤怠一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance-container">

    {{-- タイトル --}}
    <h1 class="page-title">
        {{ \Carbon\Carbon::parse($targetDate)->format('Y年n月j日') }} の勤怠
    </h1>
        
    {{-- 日付ナビ --}}
    <div class="date-nav">
        <a href="?date={{ $prevDate }}" class="nav-button">← 前日</a>

        <input type="date"
            value="{{ $targetDate }}"
            onchange="location.href='?date=' + this.value">

        <a href="?date={{ $nextDate }}" class="nav-button">翌日 →</a>
    </div>

    {{-- 勤怠テーブル --}}
    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>

        <tbody>
        @forelse ($attendances as $attendance)
            <tr>
                <td>{{ $attendance->user->name }}</td>

                <td>{{ $attendance->display_start_time ?? '-' }}</td>

                <td>{{ $attendance->display_end_time ?? '-' }}</td>

                <td>{{ $attendance->display_break_time ?: '-' }}</td>

                <td>{{ $attendance->working_time ?? '-' }}</td>

                <td>
                    <a href="{{ route('admin.attendance.monthly', $attendance->user->id) }}"
                    class="detail-button">
                        詳細
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="no-data">
                    勤怠データがありません
                </td>
            </tr>
        @endforelse
        </tbody>

    </table>


</div>
@endsection
