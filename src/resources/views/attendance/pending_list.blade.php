@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-xl font-bold mb-6">承認待ち修正申請一覧</h2>

    @if ($pendingRequests->isEmpty())
        <p>現在、承認待ちの修正申請はありません。</p>
    @else
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr>
                    <th class="border border-gray-300 p-2">ステータス</th>
                    <th class="border border-gray-300 p-2">名前</th>
                    <th class="border border-gray-300 p-2">対象日時</th>
                    <th class="border border-gray-300 p-2">申請理由</th>
                    <th class="border border-gray-300 p-2">申請日時</th>
                    <th class="border border-gray-300 p-2">詳細</th>
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
                            <a href="{{ route('attendance.detail', ['date' => $request->work_date]) }}"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">
                                詳細
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
