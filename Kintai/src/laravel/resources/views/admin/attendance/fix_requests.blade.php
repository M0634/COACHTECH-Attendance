@extends('layouts.admin_app')

@section('title', '申請一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request_list.css') }}">
@endsection

@section('content')
<div class="application-container">

    <h1 class="page-title">申請一覧</h1>

    {{-- タブ --}}
    <div class="tab-menu">
        <button
            class="tab-button {{ $status === 'pending' ? 'active' : '' }}"
            onclick="location.href='{{ route('admin.stamp_correction_requests.index', ['status' => 'pending']) }}'">
            承認待ち
        </button>

        <button
            class="tab-button {{ $status === 'approved' ? 'active' : '' }}"
            onclick="location.href='{{ route('admin.stamp_correction_requests.index', ['status' => 'approved']) }}'">
            承認済み
        </button>
    </div>


    {{-- ========================= 承認待ち ========================= --}}
    @if($status === 'pending')
        <div class="tab-content active">
            <table class="application-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>申請者</th>
                        <th>対象日</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingRequests as $request)
                        <tr>
                            <td class="status pending">承認待ち</td>
                            <td>{{ optional($request->user)->name ?? '不明' }}</td>
                            <td>
                                {{ optional($request->attendance)->work_date
                                    ? \Carbon\Carbon::parse($request->attendance->work_date)->format('Y-m-d')
                                    : '-' }}
                            </td>
                            <td>{{ $request->remark ?? '（記載なし）' }}</td>
                            <td>{{ $request->created_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.stamp_correction_requests.show', $request->id) }}"
                                   class="detail-link">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">
                                承認待ちの申請はありません
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif


    {{-- ========================= 承認済み ========================= --}}
    @if($status === 'approved')
        <div class="tab-content active">
            <table class="application-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>申請者</th>
                        <th>対象日</th>
                        <th>申請理由</th>
                        <th>承認日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($approvedRequests as $request)
                        <tr>
                            <td class="status approved">承認済み</td>
                            <td>{{ optional($request->user)->name ?? '不明' }}</td>
                            <td>
                                {{ optional($request->attendance)->work_date
                                    ? \Carbon\Carbon::parse($request->attendance->work_date)->format('Y-m-d')
                                    : '-' }}
                            </td>
                            <td>{{ $request->remark ?? '（記載なし）' }}</td>
                            <td>{{ $request->approved_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.stamp_correction_requests.show', $request->id) }}"
                                   class="detail-link">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">
                                承認済みの申請はありません
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

</div>
@endsection
