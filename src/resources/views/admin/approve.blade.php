@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>申請承認画面</h1>

    <form action="{{ route('admin.attendance.approveUpdate', $attendanceCorrection->id) }}" method="POST">
        <div class="max-w-2xl bg-white p-6 rounded-lg shadow">
            @csrf
            @method('PUT')

            <!-- ユーザー名 -->
            <div class="mb-4 flex">
                <label class="subtitle">名前</label>
                <div class="ml-180 font-bold">{{ $attendanceCorrection->user->name }}</div>
            </div>

            <!-- 日付 -->
            <div class="mb-4 flex">
                <label class="subtitle">日付</label>
                <div class="ml-180 font-bold">{{ \Carbon\Carbon::parse($attendanceCorrection->work_date)->format('Y年n月j日') }}</div>
            </div>

            <!-- 出勤・退勤 -->
            <div class="mb-4 flex items-center">
                <label class="subtitle">出勤・退勤</label>
                <input type="time" class="ml-180" name="clock_in" value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}" class="border rounded px-2 py-1">
                <span class="mx-2">〜</span>
                <input type="time" name="clock_out" value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}" class="border rounded px-2 py-1">
            </div>

            @php
                $break = $breaks->first(); // 最初の休憩データ（なければnull）
            @endphp

            <!-- 休憩 -->
            <div class="mb-4 flex items-center">
                <label class="subtitle">休憩</label>
                <input type="time" class="ml-180" name="break_start" value="{{ $break ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}" class="border rounded px-2 py-1">
                <span class="mx-2">〜</span>
                <input type="time" name="break_end" value="{{ $break ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}" class="border rounded px-2 py-1">
            </div>

            <!-- 備考 -->
            <div class="flex items-center">
                <label class="subtitle">備考</label>
                <textarea name="reason" rows="2" class="border rounded px-2 py-1 w-full">{{ $attendance->reason ?? '' }}</textarea>
            </div>
        </div>

        <!-- 承認・却下ボタン -->
        <div class="button-area mt-4">
            <button type="submit" name="action" value="approve" class="approve">承認</button>
        </div>
    </form>
</div>
@endsection
