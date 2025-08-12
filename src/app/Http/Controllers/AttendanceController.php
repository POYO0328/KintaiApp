<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\BreakTime;
use App\Models\AttendanceCorrection;

class AttendanceController extends Controller
{
    public function clockIn()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['clock_in' => now(), 'attendance_status' => 1]
        );

        return redirect()->back()->with('message', '出勤しました');
    }

    public function breakStart()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', Carbon::today())
            ->firstOrFail();

        // 未終了の休憩がなければ新規作成
        $existingBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->first();

        if (!$existingBreak) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => now(),
            ]);
            $attendance->update(['attendance_status' => 2]);
        }

        return redirect()->back()->with('message', '休憩開始しました');
    }

    public function breakEnd()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', Carbon::today())
            ->firstOrFail();

        // 未終了の休憩を取得して終了時間をセット
        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        if ($break) {
            $break->update([
                'break_end' => now(),
            ]);
            $attendance->update(['attendance_status' => 3]);
        }

        return redirect()->back()->with('message', '休憩終了しました');
    }

    // public function breakStart()
    // {
    //     $attendance = Attendance::where('user_id', Auth::id())
    //         ->where('work_date', Carbon::today())
    //         ->firstOrFail();

    //     $attendance->update([
    //         'break_start' => now(),
    //         'attendance_status' => 2
    //     ]);

    //     return redirect()->back()->with('message', '休憩開始しました');
    // }

    // public function breakEnd()
    // {
    //     $attendance = Attendance::where('user_id', Auth::id())
    //         ->where('work_date', Carbon::today())
    //         ->firstOrFail();

    //     $attendance->update([
    //         'break_end' => now(),
    //         'attendance_status' => 3
    //     ]);

    //     return redirect()->back()->with('message', '休憩終了しました');
    // }

    public function clockOut()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', Carbon::today())
            ->firstOrFail();

        $attendance->update([
            'clock_out' => now(),
            'attendance_status' => 4
        ]);

        return redirect()->back()->with('message', '退勤しました');
    }

    public function index()
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', Carbon::today())
            ->first();

        $attendance_status = $attendance ? $attendance->attendance_status : 0;

        return view('index', compact('attendance_status'));
    }

    public function detail($date)
    {
        $userId = auth()->id();

        // 修正申請があればそちらを優先
        $editRequest = AttendanceCorrection::with('breaks')
            ->where('user_id', $userId)
            ->where('work_date', $date)
            ->first();

        if ($editRequest) {
            $attendance = $editRequest;
            $revision = $editRequest; // 修正申請がある場合
            $breaks = $editRequest->breaks;
        } else {
            $attendance = Attendance::where('user_id', $userId)
                ->where('work_date', $date)
                ->first();
            $revision = null; // 修正申請がない場合

            if ($attendance) {
                // 本番勤怠に紐づく休憩時間を取得
                $breaks = $attendance->breaks;  // ここで Attendance モデルに breaks リレーションがある前提
            } else {
                $breaks = collect(); // どちらもなければ空のコレクション
            }
        }

        // データがない場合は空のオブジェクトを作る
        if (!$attendance) {
            $attendance = new \stdClass();
            $attendance->id = null;
            $attendance->user_id = $userId;
            $attendance->work_date = $date;
            $attendance->clock_in = null;
            $attendance->clock_out = null;
            $attendance->attendance_status = 0;
        }

        return view('attendance.detail', compact('attendance', 'revision', 'date', 'breaks'));
    }

    public function update(Request $request, $date)
    {
        $userId = auth()->id();

        // バリデーション（必要に応じて拡張してください）
        $validated = $request->validate([
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i',
            'reason' => 'nullable|string|max:1000',
        ]);

        // 本番勤怠レコードを取得
        $attendance = Attendance::where('user_id', $userId)
            ->where('work_date', $date)
            ->first();

        if (!$attendance) {
            return redirect()->back()->withErrors(['msg' => '勤怠データが見つかりません。']);
        }

        // 既に修正申請があるかチェック
        $correction = AttendanceCorrection::where('attendance_id', $attendance->id)->first();

        if ($correction) {
            // 更新
            $correction->clock_in = $validated['clock_in'] ?? null;
            $correction->clock_out = $validated['clock_out'] ?? null;
            $correction->attendance_status = 0; // 状態は承認待ちにリセットなど
            $correction->status = 'pending';
            $correction->reason = $validated['reason'] ?? null;
            $correction->save();

            // 休憩時間の更新もあればここで処理（下記参照）

        } else {
            // 新規作成
            $correction = AttendanceCorrection::create([
                'attendance_id' => $attendance->id,
                'user_id' => $userId,
                'work_date' => $date,
                'clock_in' => $validated['clock_in'] ?? null,
                'clock_out' => $validated['clock_out'] ?? null,
                'attendance_status' => 0,
                'status' => 'pending',
                'reason' => $validated['reason'] ?? null,
            ]);

            // 休憩時間の新規登録処理もここで行う
        }

        // 休憩時間の処理（break_start, break_end）も必要なら

        // 例）休憩は1件のみ、既存レコードがあれば更新、なければ新規
        $breakStart = $validated['break_start'] ?? null;
        $breakEnd = $validated['break_end'] ?? null;

        if ($breakStart && $breakEnd) {
            $break = $correction->breaks()->first();

            if ($break) {
                $break->break_start = $breakStart;
                $break->break_end = $breakEnd;
                $break->save();
            } else {
                $correction->breaks()->create([
                    'break_start' => $breakStart,
                    'break_end' => $breakEnd,
                ]);
            }
        }

        return redirect()->route('attendance.detail', ['date' => $date])
            ->with('success', '修正申請を送信しました。承認までお待ちください。');
    }

    public function pendingList()
    {
        $userId = auth()->id();

        // 自分の承認待ち申請だけ取得（必要なら並び替えも）
        $pendingRequests = AttendanceCorrection::where('user_id', $userId)
            ->where('status', 'pending')
            ->orderBy('work_date', 'desc')
            ->get();

        return view('attendance.pending_list', compact('pendingRequests'));
    }



}

