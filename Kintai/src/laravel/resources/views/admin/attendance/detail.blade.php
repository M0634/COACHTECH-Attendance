@extends('layouts.admin_app')

@section('title', '勤怠一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_detail.css') }}">
@endsection

@section('content')
<div class="attendance-wrapper">

    {{-- タイトル --}}
    <h2 class="page-title">
        <span class="title-bar"></span>
        {{ $user->name }}さんの勤怠
    </h2>

    {{-- 月切り替え --}}
    <div class="month-switch">
        <a href="?month={{ $prevMonth }}" class="month-link">← 前月</a>

        <div class="current-month">
            <span class="calendar-icon">📅</span>
            {{ $currentMonth }}
        </div>

        <a href="?month={{ $nextMonth }}" class="month-link">翌月 →</a>
    </div>

    {{-- 勤怠テーブル --}}
    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('m/d') }}</td>
                    <td>{{ optional($attendance)->display_start_time }}</td>
                    <td>{{ optional($attendance)->display_end_time }}</td>
                    <td>{{ optional($attendance)->display_break_time }}</td>
                    <td>{{ optional($attendance)->working_time }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.show', $attendance->id) }}"
                        class="detail-button">
                            詳細
                        </a>


                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- CSV --}}
    <div class="csv-export">
        <a href="{{ route('admin.attendance.monthly.csv', [
                'user' => $user->id,
                'month' => $currentMonth
            ]) }}"
        class="csv-button">
            CSV出力
        </a>
    </div>





</div>
@endsection
