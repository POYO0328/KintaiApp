@extends(Auth::user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>勤怠詳細</h1>

    @php
        // 一般ユーザーかつ承認待ち/承認済みなら入力を無効化
        $isDisabled = isset($revision)
            && in_array($revision->status, ['pending', 'approved'])
            && !auth()->user()->is_admin;
    @endphp

    <form action="{{ route('attendance.update', $date) }}" method="POST">
        <div class="max-w-2xl bg-white p-6 rounded-lg shadow">
            @csrf
            @method('PUT')

            @if (auth()->user()->is_admin)
                <input type="hidden" name="user_id" value="{{ $user->id }}">
            @endif

            <!-- エラー一括表示 -->
            @if ($errors->any())
                <div class="error-messages">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 名前 -->
            <div class="mb-4 flex">
                <label class="subtitle">名前</label>
                <div class="ml-180 font-bold">{{ $user->name }}</div>
            </div>

            <!-- 日付 -->
            <div class="mb-4 flex">
                <label class="subtitle">日付</label>
                <div class="ml-180 font-bold">{{ \Carbon\Carbon::parse($date)->format('Y年n月j日') }}</div>
            </div>

            <!-- 出勤・退勤 -->
            <div class="mb-4 flex items-center">
                <label class="subtitle">出勤・退勤</label>
                <input type="time" class="ml-180 border rounded px-2 py-1"
                    name="clock_in"
                    value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
                    {{ $isDisabled ? 'disabled' : '' }}>
                <span class="mx-2">〜</span>
                <input type="time" class="border rounded px-2 py-1"
                    name="clock_out"
                    value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}"
                    {{ $isDisabled ? 'disabled' : '' }}>
            </div>

            <!-- 休憩 -->
            <div class="mb-4">
                @php
                    $breaks = $breaks ?? [];
                    $count = $isDisabled ? count($breaks) : count($breaks) + 1;
                @endphp

                @for ($i = 0; $i < $count; $i++)
                    <div class="flex items-center mb-4">
                        <label class="subtitle">休憩{{ $i+1 }}</label>
                        <input type="time" class="ml-180 border rounded px-2 py-1"
                            name="breaks[{{ $i }}][break_start]"
                            value="{{ old("breaks.$i.break_start", isset($breaks[$i]) ? \Carbon\Carbon::parse($breaks[$i]->break_start)->format('H:i') : '') }}"
                            {{ $isDisabled ? 'disabled' : '' }}>
                        <span class="mx-2">〜</span>
                        <input type="time" class="border rounded px-2 py-1"
                            name="breaks[{{ $i }}][break_end]"
                            value="{{ old("breaks.$i.break_end", isset($breaks[$i]) ? \Carbon\Carbon::parse($breaks[$i]->break_end)->format('H:i') : '') }}"
                            {{ $isDisabled ? 'disabled' : '' }}>
                    </div>
                @endfor
            </div>

            <!-- 備考 -->
            <div class="flex items-center">
                <label class="subtitle">備考</label>
                <textarea name="reason" rows="2" class="border rounded px-2 py-1 w-full"
                    {{ $isDisabled ? 'disabled' : '' }}>{{ old('reason', $attendance->reason ?? '') }}</textarea>
            </div>
        </div>

        <!-- 修正ボタン -->
        @if (isset($revision) && $revision->status === 'pending' && !auth()->user()->is_admin)
            <p class="text-red-600 mb-4">*承認待ちのため、修正できません</p>
        @elseif (isset($revision) && $revision->status === 'approved' && !auth()->user()->is_admin)
            <p class="text-red-600 mb-4">*承認済みのため、修正できません</p>
        @else
            <div class="button-area">
                <button type="submit" class="fix">修正</button>
            </div>
        @endif
    </form>

</div>
@endsection
