@extends(Auth::user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>申請一覧</h1>

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
                    <td class="border border-gray-300 p-2 capitalize">{{ $request->status }}</td>
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
@endsection
