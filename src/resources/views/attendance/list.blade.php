@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>勤怠一覧</h1>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('attendance.list', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}">←前月</a>

        <div>
            カレンダー →
            <input type="month" value="{{ $currentMonth->format('Y-m') }}"
                onchange="location.href='{{ route('attendance.list') }}?month='+this.value">
        </div>

        <a href="{{ route('attendance.list', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}">翌月→</a>
    </div>

    <table class="table table-bordered text-center">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dates as $date)
                @php
                    $key = $date->format('Y-m-d');
                    $att = $attendances->get($key);
                @endphp
                <tr>
                    <td>{{ $date->format('m/d') }} ({{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }})</td>
                    <td>{{ $att && $att->clock_in ? $att->clock_in->format('H:i') : '' }}</td>
                    <td>{{ $att && $att->clock_out ? $att->clock_out->format('H:i') : '' }}</td>
                    {{--<td>{{ $att ? $att->clock_in->format('H:i:s') : '' }}</td>--}}
                    {{--<td>{{ $att ? $att->clock_out ? $att->clock_out->format('H:i:s') : '' : '' }}</td>--}}
                    <td>
                        @if ($att && isset($breakSeconds[$att->id]))
                            @php
                                $hours = floor($breakSeconds[$att->id] / 3600);
                                $minutes = floor(($breakSeconds[$att->id] % 3600) / 60);
                            @endphp
                            {{ sprintf('%d:%02d', $hours, $minutes) }}
                        @endif
                    </td>
                    <td>
                        @if ($att && isset($breakSeconds[$att->id]))
                            @php
                                // 勤務時間（秒）
                                if ($att->clock_in && $att->clock_out) {
                                    $workSeconds = $att->clock_out->diffInSeconds($att->clock_in);

                                    // 休憩時間（秒）
                                    $break = $breakSeconds[$att->id];

                                    // 実働時間（秒）
                                    $actualSeconds = max(0, $workSeconds - $break);

                                    $hours = floor($actualSeconds / 3600);
                                    $minutes = floor(($actualSeconds % 3600) / 60);
                                    echo sprintf('%d:%02d', $hours, $minutes);
                                }
                            @endphp
                        @endif
                    </td>
                    <td>
                        <td>
                            <a href="{{ route('attendance.detail', $date) }}" class="btn btn-sm btn-primary">
                                詳細
                            </a>
                        </td>
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection