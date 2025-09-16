@extends(Auth::user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>申請一覧</h1>

    {{-- タブ切り替え --}}
    <div class="tab-container flex mb-3">
        <span id="tab-pending" class="tab active">承認待ち</span>
        <span id="tab-approved" class="tab">承認済み</span>
    </div>

    {{-- 承認待ち --}}
    <div id="table-pending">
        @if ($pendingRequests->isEmpty())
            <p>現在、承認待ちの修正申請はありません。</p>
        @else
        <table class="table table-bordered text-center">
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
                @foreach ($pendingRequests as $request)
                    <tr>
                        <td class="border border-gray-300 p-2">承認待ち</td>
                        <td class="border border-gray-300 p-2">{{ $request->user->name ?? '-' }}</td>
                        <td class="border border-gray-300 p-2">
                            {{ \Carbon\Carbon::parse($request->work_date)->format('Y年n月j日') }}
                        </td>
                        <td class="border border-gray-300 p-2">{{ $request->reason ?? '-' }}</td>
                        <td class="border border-gray-300 p-2">
                            {{ \Carbon\Carbon::parse($request->created_at)->format('Y年n月j日 H:i') }}
                        </td>
                        <td class="border border-gray-300 p-2 text-center">
                            @if (Auth::user()->is_admin)
                                <a href="{{ route('admin.attendance.approve', $request->id) }}"
                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">
                                    詳細
                                </a>
                            @else
                                <a href="{{ route('attendance.detail', ['date' => $request->work_date]) }}"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">
                                    詳細
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- 承認済み --}}
    <div id="table-approved" style="display:none;">
        @if ($approvedRequests->isEmpty())
            <p>承認済みの修正申請はありません。</p>
        @else
        <table class="table table-bordered text-center">
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
                @foreach ($approvedRequests as $request)
                    <tr>
                        <td class="border border-gray-300 p-2">承認済み</td>
                        <td class="border border-gray-300 p-2">{{ $request->user->name ?? '-' }}</td>
                        <td class="border border-gray-300 p-2">
                            {{ \Carbon\Carbon::parse($request->work_date)->format('Y年n月j日') }}
                        </td>
                        <td class="border border-gray-300 p-2">{{ $request->reason ?? '-' }}</td>
                        <td class="border border-gray-300 p-2">
                            {{ \Carbon\Carbon::parse($request->created_at)->format('Y年n月j日 H:i') }}
                        </td>
                        <td class="border border-gray-300 p-2 text-center">
                            @if (Auth::user()->is_admin)
                                <a href="{{ route('admin.attendance.approve', $request->id) }}"
                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">
                                    詳細
                                </a>
                            @else
                                <a href="{{ route('attendance.detail', ['date' => $request->work_date]) }}"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">
                                    詳細
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabPending = document.getElementById('tab-pending');
    const tabApproved = document.getElementById('tab-approved');
    const tablePending = document.getElementById('table-pending');
    const tableApproved = document.getElementById('table-approved');

    function activateTab(tab) {
        // 全タブをリセット
        [tabPending, tabApproved].forEach(t => t.classList.remove('active'));

        // 選択タブに active 追加
        tab.classList.add('active');

        // テーブル切り替え
        if (tab === tabPending) {
            tablePending.style.display = 'block';
            tableApproved.style.display = 'none';
        } else {
            tablePending.style.display = 'none';
            tableApproved.style.display = 'block';
        }
    }

    // 初期状態
    activateTab(tabPending);

    // クリック時の切替
    tabPending.addEventListener('click', () => activateTab(tabPending));
    tabApproved.addEventListener('click', () => activateTab(tabApproved));
});
</script>
@endpush

