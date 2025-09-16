@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>{{ $user->name }} さんの月次勤怠</h1>

    {{-- 月移動 --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('admin.staff.attendance', ['id' => $user->id, 'month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}">←前月</a>
        
        <div>
            <input type="month" value="{{ $currentMonth->format('Y-m') }}"
                onchange="location.href='{{ route('admin.staff.attendance', ['id' => $user->id]) }}?month='+this.value">
        </div>
        
        <a href="{{ route('admin.staff.attendance', ['id' => $user->id, 'month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}">翌月→</a>
    </div>

    {{-- 月次勤怠テーブル --}}
    <table class="table table-bordered text-center">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>実働時間</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dates as $date)
                @php
                    $key = $date->format('Y-m-d');
                    $att = $attendances->get($key);

                    // Carbon に変換（文字列の場合でも対応）
                    $clockIn = $att && $att->clock_in ? \Carbon\Carbon::parse($att->clock_in) : null;
                    $clockOut = $att && $att->clock_out ? \Carbon\Carbon::parse($att->clock_out) : null;

                    // 休憩時間は秒数で
                    $break = $att && isset($breakSeconds[$att->id]) ? $breakSeconds[$att->id] : 0;

                    // 実働時間の計算
                    $hours = $minutes = 0;
                    if ($clockIn && $clockOut) {
                        $workSeconds = $clockOut->diffInSeconds($clockIn);
                        $actualSeconds = max(0, $workSeconds - $break);
                        $hours = floor($actualSeconds / 3600);
                        $minutes = floor(($actualSeconds % 3600) / 60);
                    }
                @endphp
                <tr>
                    <td>{{ $date->format('m/d') }} ({{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }})</td>
                    <td>{{ $clockIn ? $clockIn->format('H:i') : '-' }}</td>
                    <td>{{ $clockOut ? $clockOut->format('H:i') : '-' }}</td>
                    <td>{{ $break ? sprintf('%d:%02d', floor($break / 3600), floor(($break % 3600) / 60)) : '-' }}</td>
                    <td>{{ ($clockIn && $clockOut) ? sprintf('%d:%02d', $hours, $minutes) : '-' }}</td>
                    <td>
                        @if ($date->isFuture())
                            -
                        @else
                            <a href="{{ route('attendance.detail', ['date' => $date->format('Y-m-d')]) }}" class="btn btn-sm btn-primary">
                                詳細
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="button-area">
        <form action="{{ route('admin.staff.attendance.csv', ['id' => $user->id]) }}" method="GET">
            <input type="hidden" name="month" value="{{ $currentMonth->format('Y-m') }}">
            <button type="submit" class="fix mt-5">
                CSV出力
            </button>
        </form>
    </div>
</div>
@endsection
