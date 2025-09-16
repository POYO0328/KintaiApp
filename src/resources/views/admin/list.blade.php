@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>スタッフ一覧</h1>

    @if ($staffs->isEmpty())
        <p>スタッフは登録されていません。</p>
    @else
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($staffs as $staff)
                    <tr>
                        <td class="border border-gray-300 p-2">{{ $staff->name }}</td>
                        <td class="border border-gray-300 p-2">{{ $staff->email }}</td>
                        <td class="border border-gray-300 p-2 text-center">
                            <a href="{{ route('admin.staff.attendance', ['id' => $staff->id]) }}" 
                                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
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
