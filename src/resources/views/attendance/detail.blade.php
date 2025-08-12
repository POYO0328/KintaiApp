@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
    <h2 class="text-lg font-bold mb-4">勤怠詳細</h2>

    <form action="{{ route('attendance.update', $date) }}" method="POST">
        <form>
        @csrf
        @method('PUT')

        <!-- 名前 -->
        <div class="mb-4 flex">
            <label class="w-24 font-semibold">名前</label>
            <div>{{ Auth::user()->name }}</div>
        </div>

        <!-- 日付 -->
        <div class="mb-4 flex">
            <label class="w-24 font-semibold">日付</label>
            <div>{{ \Carbon\Carbon::parse($date)->format('Y年n月j日') }}</div>
        </div>

        <!-- 出勤・退勤 -->
        <div class="mb-4 flex items-center">
            <label class="w-24 font-semibold">出勤・退勤</label>
            <input type="time" name="clock_in" value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}" class="border rounded px-2 py-1">
            <span class="mx-2">〜</span>
            <input type="time" name="clock_out" value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}" class="border rounded px-2 py-1">
        </div>

        @php
    $break = $breaks->first(); // 最初の休憩データ（なければnull）
@endphp

<!-- 休憩 -->
<div class="mb-4 flex items-center">
    <label class="w-24 font-semibold">休憩</label>
    <input type="time" name="break_start" value="{{ $break ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}" class="border rounded px-2 py-1">
    <span class="mx-2">〜</span>
    <input type="time" name="break_end" value="{{ $break ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}" class="border rounded px-2 py-1">
</div>

        <!-- 備考 -->
        <div class="mb-4">
            <label class="w-24 font-semibold block mb-1">備考</label>
            <textarea name="reason" rows="2" class="border rounded px-2 py-1 w-full">{{ $attendance->reason ?? '' }}</textarea>
        </div>

        <!-- 修正ボタン -->
        @if (isset($revision) && $revision->status === 'pending')
            <p class="text-red-600 mb-4">*承認待ちのため、修正できません</p>
        @else
            <div class="text-right">
                <button type="submit" class="bg-black text-white px-4 py-2 rounded">
                    修正
                </button>
            </div>
        @endif

    </form>
</div>
@endsection
