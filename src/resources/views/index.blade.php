@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="attendance__container">

    {{-- 勤怠状態表示 --}}
    <div class="attendance__status">
        @if ($attendance_status === 0)
            勤 務 外
        @elseif ($attendance_status === 1)
            出 勤 中
        @elseif ($attendance_status === 2)
            休 憩 中
        @elseif ($attendance_status === 3)
            休 憩 戻
        @elseif ($attendance_status === 4)
            退 勤 済 
        @else
            状 態 不 明
        @endif
    </div>

    <div id="datetime" class="text-lg font-bold" style="font-size:1.2em; margin-bottom:15px;">
      <!-- リアルタイムで日付・曜日・時間を表示 -->
    </div>

    {{-- リアルタイム時計表示 --}}
    <div id="current-time" style="font-weight:bold; font-size:2em; margin-bottom:40px;">
      {{ now()->format('H:i:s') }}
    </div>

    {{-- 出退勤ボタン --}}
    <div class="attendance__buttons">
        @if ($attendance_status === 0)
            {{-- 出勤前 --}}
            <form method="POST" action="{{ route('attendance.clockIn') }}" style="display:inline-block;">
                @csrf
                <button type="submit" class="start_buttons">出勤</button>
            </form>
        @elseif ($attendance_status === 1)
            {{-- 出勤後・休憩前 --}}
            <form method="POST" action="{{ route('attendance.breakStart') }}" style="display:inline-block;">
                @csrf
                <button type="submit" class="break_start_buttons">休憩入</button>
            </form>
            <form method="POST" action="{{ route('attendance.clockOut') }}" style="display:inline-block;">
                @csrf
                <button type="submit" class="end_buttons">退勤</button>
            </form>
        @elseif ($attendance_status === 2)
            {{-- 休憩中 --}}
            <form method="POST" action="{{ route('attendance.breakEnd') }}" style="display:inline-block;">
                @csrf
                <button type="submit" class="break_end_buttons">休憩戻</button>
            </form>
        @elseif ($attendance_status === 3)
            {{-- 休憩終了後 --}}
            <form method="POST" action="{{ route('attendance.breakStart') }}" style="display:inline-block;">
                @csrf
                <button type="submit" class="break_start_buttons">休憩入</button>
            </form>
            <form method="POST" action="{{ route('attendance.clockOut') }}" style="display:inline-block;">
                @csrf
                <button type="submit" class="end_buttons">退勤</button>
            </form>
        @elseif ($attendance_status === 4)
            {{-- 勤務終了 --}}
            <p class="mt-3">お疲れ様でした。
            </p>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  function updateTime() {
    const now = new Date();
    document.getElementById('current-time').textContent = now.toLocaleTimeString();
  }
  setInterval(updateTime, 1000);
  updateTime();
});
</script>

<script>
  function updateDateTime() {
    const now = new Date();

    const daysOfWeek = ['日', '月', '火', '水', '木', '金', '土'];
    const year = now.getFullYear();
    const month = now.getMonth() + 1;
    const date = now.getDate();
    const day = daysOfWeek[now.getDay()];
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    const dateTimeString = `${year}年${month}月${date}日(${day})`;
    document.getElementById('datetime').textContent = dateTimeString;
  }

  setInterval(updateDateTime, 1000);
  updateDateTime(); // 初期表示
</script>

@endpush
