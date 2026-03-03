@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance-list-container">

    <h1 class="page-title">勤怠一覧</h1>

    {{-- 月切替（/attendance/monthly に統一） --}}
    <div class="month-switch">
        <a href="{{ route('attendance.monthly', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}">
            ← 前月
        </a>

        <span class="current-month">
            {{ $currentMonth->format('Y/m') }}
        </span>

        <a href="{{ route('attendance.monthly', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}">
            翌月 →
        </a>
    </div>

    {{-- 一覧テーブル --}}
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
        @php
            $start = $currentMonth->copy()->startOfMonth();
            $end   = $currentMonth->copy()->endOfMonth();
        @endphp

        @for ($d = $start->copy(); $d->lte($end); $d->addDay())
            @php
                $key = $d->format('Y-m-d');
                $attendance = $attendances[$key] ?? null;
            @endphp

            <tr>
                {{-- 日付 --}}
                <td>
                    {{ $d->format('m/d') }}
                    （{{ ['日','月','火','水','木','金','土'][$d->dayOfWeek] }}）
                </td>

                <td>{{ optional($attendance)->display_start_time }}</td>
                <td>{{ optional($attendance)->display_end_time }}</td>
                <td>{{ optional($attendance)->display_break_time }}</td>
                <td>{{ optional($attendance)->working_time }}</td>

                {{-- 詳細 --}}
                <td>
                    @if ($attendance && $attendance->id)
                        <a href="{{ route('attendance.show', $attendance->id) }}"
                           class="detail-button">
                            詳細
                        </a>
                    @endif
                </td>
            </tr>
        @endfor
        </tbody>
    </table>

</div>
@endsection