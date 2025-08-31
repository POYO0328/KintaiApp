@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>勤怠一覧（管理者）</h1>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('admin.attendances.index', ['date' => $currentDate->copy()->subDay()->format('Y-m-d')]) }}">←前日</a>

        <div>
            <input type="date" value="{{ $currentDate->format('Y-m-d') }}"
                onchange="location.href='{{ route('admin.attendances.index') }}?date='+this.value">
        </div>

        <a href="{{ route('admin.attendances.index', ['date' => $currentDate->copy()->addDay()->format('Y-m-d')]) }}">翌日→</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                @php
                    $key = $user->id . '_' . $currentDate->format('Y-m-d');
                    $attendance = $attendances[$key] ?? null;
                    $break = $attendance ? ($breakSeconds[$attendance->id] ?? 0) : 0;
                    $workDuration = ($attendance && $attendance->clock_in && $attendance->clock_out)
                        ? $attendance->clock_out->diffInSeconds($attendance->clock_in) - $break
                        : 0;
                @endphp
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $attendance && $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}</td>
                    <td>{{ $attendance && $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}</td>
                    <td>{{ gmdate('H:i', $break) }}</td>
                    <td>{{ $workDuration > 0 ? gmdate('H:i', $workDuration) : '' }}</td>
                    <td>
                        @if($attendance)
                            <a href="{{ route('attendance.detail', ['date' => $currentDate->format('Y-m-d')]) }}">詳細</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection