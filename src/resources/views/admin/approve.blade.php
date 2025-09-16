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
                <input type="time" class="ml-180" 
                       value="{{ $attendanceCorrection->clock_in ? \Carbon\Carbon::parse($attendanceCorrection->clock_in)->format('H:i') : '' }}" 
                       disabled>
                <span class="mx-2">〜</span>
                <input type="time" 
                       value="{{ $attendanceCorrection->clock_out ? \Carbon\Carbon::parse($attendanceCorrection->clock_out)->format('H:i') : '' }}" 
                       disabled>
            </div>

            @php
                $break = $attendanceCorrection->breaks->first(); // 修正申請側の休憩データ
            @endphp

            @php
                $breaks = $attendanceCorrection->breaks ?? collect([]);
                $count = $breaks->count(); // 既存の件数
            @endphp

            @for ($i = 0; $i < $count; $i++)
                <div class="flex items-center mb-4">
                    <label class="subtitle">休憩{{ $i + 1 }}</label>
                    <input type="time" class="ml-180 border rounded px-2 py-1"
                        value="{{ isset($breaks[$i]) ? \Carbon\Carbon::parse($breaks[$i]->break_start)->format('H:i') : '' }}"
                        disabled>
                    <span class="mx-2">〜</span>
                    <input type="time" class="border rounded px-2 py-1"
                        value="{{ isset($breaks[$i]) ? \Carbon\Carbon::parse($breaks[$i]->break_end)->format('H:i') : '' }}"
                        disabled>
                </div>
            @endfor


            <!-- 備考 -->
            <div class="flex items-center">
                <label class="subtitle">備考</label>
                <textarea rows="2" class="border rounded px-2 py-1 w-full" disabled>{{ $attendanceCorrection->reason ?? '' }}</textarea>
            </div>
        </div>

        <!-- 承認・却下ボタン -->
        <div class="button-area mt-4">
            @if ($attendanceCorrection->status === 'approved')
                <button class="approve gray cursor-not-allowed" disabled>
                    承認済み
                </button>
            @else
                <button type="submit" name="action" value="approve" class="approve">
                    承認
                </button>
            @endif
        </div>
    </form>
</div>
@endsection
