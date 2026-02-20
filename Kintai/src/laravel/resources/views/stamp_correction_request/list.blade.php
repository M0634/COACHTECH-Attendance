@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request_list.css') }}">
@endsection

@section('content')
<div class="application-container">

    <h1 class="page-title">申請一覧</h1>

    {{-- タブ --}}
    <div class="tab-menu">
        <button class="tab-button active" data-tab="pending">承認待ち</button>
        <button class="tab-button" data-tab="approved">承認済み</button>
    </div>

    {{-- 承認待ち --}}
    <div class="tab-content active" id="pending">
        <table class="application-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pendingRequests as $request)
                    <tr>
                        <td class="status pending">承認待ち</td>
                        <td>{{ optional($request->user)->name }}</td>
                        <td>{{ optional($request->attendance)->work_date?->format('Y-m-d') }}</td>
                        <td>{{ $request->remark }}</td>
                        <td>{{ optional($request->created_at)->format('Y-m-d H:i') }}</td>
                        <td>
                            @if($request->attendance_id)
                                <a href="{{ route('attendance.show', $request->attendance_id) }}" class="detail-link">
                                    詳細
                                </a>
                            @else
                                <span class="text-muted">詳細なし</span>
                            @endif
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="6" class="empty">承認待ちの申請はありません</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 承認済み --}}
    <div class="tab-content" id="approved">
        <table class="application-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($approvedRequests as $request)
                <tr>
                    <td class="status approved">承認済み</td>
                    <td>{{ optional($request->user)->name }}</td>
                    <td>{{ optional($request->attendance)->work_date?->format('Y-m-d') }}</td>
                    <td>{{ $request->remark }}</td>
                    <td>{{ optional($request->created_at)->format('Y-m-d H:i') }}</td>
                    <td>
                        @if($request->attendance_id)
                            <a href="{{ route('attendance.show', $request->attendance_id) }}" class="detail-link">
                                詳細
                            </a>
                        @else
                            <span class="text-muted">詳細なし</span>
                        @endif
                    </td>
                </tr>
                @empty

                    <tr>
                        <td colspan="6" class="empty">承認済みの申請はありません</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection

@section('js')
<script>
document.querySelectorAll('.tab-button').forEach(button => {
    button.addEventListener('click', () => {
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

        button.classList.add('active');
        document.getElementById(button.dataset.tab).classList.add('active');
    });
});
</script>
@endsection
