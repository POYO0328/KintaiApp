<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\BreakTime;
use App\Models\AttendanceCorrection;
use App\Models\User;

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
        }

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
        $query = AttendanceCorrection::with('user')
            ->where('status', 'pending')
            ->orderBy('work_date', 'desc');

        if (!auth()->user()->is_admin) {
            // 一般ユーザーの場合 → 自分の申請だけ
            $query->where('user_id', auth()->id());
        }
        // 管理者の場合は where('user_id', …) を付けずに全件対象

        $pendingRequests = $query->get();

        return view('attendance.pending_list', compact('pendingRequests'));
    }

    // 管理者用 一覧（全ユーザー日別勤怠）
    public function adminList(Request $request)
    {
        // デフォルトは今日
        $date = $request->get('date', now()->format('Y-m-d'));
        $currentDate = \Carbon\Carbon::parse($date);

        $users = \App\Models\User::where('is_admin', 0)->get();

        // その日の勤怠データだけ取得
        $attendancesRaw = \App\Models\Attendance::whereIn('user_id', $users->pluck('id'))
            ->whereDate('work_date', $currentDate->format('Y-m-d'))
            ->get();

        $attendances = $attendancesRaw->mapWithKeys(function ($att) {
            return [
                $att->user_id . '_' . $att->work_date->format('Y-m-d') => (object) [
                    'id' => $att->id,
                    'clock_in' => $att->clock_in ? \Carbon\Carbon::parse($att->clock_in) : null,
                    'clock_out' => $att->clock_out ? \Carbon\Carbon::parse($att->clock_out) : null,
                ]
            ];
        });

        $breakSecondsRaw = \App\Models\BreakTime::whereIn('attendance_id', $attendancesRaw->pluck('id'))->get();
        $breakSeconds = $breakSecondsRaw->groupBy('attendance_id')->mapWithKeys(function ($breaks, $attendance_id) {
            $total = $breaks->reduce(function ($carry, $b) {
                $start = $b->break_start ? \Carbon\Carbon::parse($b->break_start) : null;
                $end   = $b->break_end   ? \Carbon\Carbon::parse($b->break_end)   : null;
                return $carry + ($start && $end ? $end->diffInSeconds($start) : 0);
            }, 0);
            return [$attendance_id => $total];
        });

        return view('admin.attendances', compact('users', 'attendances', 'breakSeconds', 'currentDate'));
    }




    //スタッフ一覧
    public function staffList()
    {
        // 管理者のみアクセスさせたい場合は Gate や Middleware を利用
        $staffs = \App\Models\User::where('is_admin', 0)->get();

        return view('admin.list', compact('staffs'));
    }

    //スタッフ別月次勤怠
    public function staffMonthly($id, Request $request)
    {
        $user = \App\Models\User::findOrFail($id);

        $month = $request->get('month', now()->format('Y-m'));
        $currentMonth = \Carbon\Carbon::parse($month . '-01');

        // 月の全日付を取得
        $dates = collect();
        for ($d = $currentMonth->copy()->startOfMonth(); $d->lte($currentMonth->copy()->endOfMonth()); $d->addDay()) {
            $dates->push($d->copy());
        }

        // 勤怠取得
        $attendancesRaw = \App\Models\Attendance::where('user_id', $user->id)
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->get();

        // Carbon に変換してキー付きで整理
        $attendances = $attendancesRaw->mapWithKeys(function($att){
            return [
                $att->work_date->format('Y-m-d') => (object)[
                    'id' => $att->id,
                    'clock_in' => $att->clock_in ? \Carbon\Carbon::parse($att->clock_in) : null,
                    'clock_out' => $att->clock_out ? \Carbon\Carbon::parse($att->clock_out) : null,
                ]
            ];
        });

        // 休憩秒数も同じように取得
        $breakSecondsRaw = \App\Models\BreakTime::whereIn('attendance_id', $attendancesRaw->pluck('id'))->get();
        $breakSeconds = $breakSecondsRaw->groupBy('attendance_id')->mapWithKeys(function($breaks, $attendance_id){
            $total = $breaks->reduce(function($carry, $b){
                $start = $b->break_start ? \Carbon\Carbon::parse($b->break_start) : null;
                $end = $b->break_end ? \Carbon\Carbon::parse($b->break_end) : null;
                return $carry + ($start && $end ? $end->diffInSeconds($start) : 0);
            }, 0);
            return [$attendance_id => $total];
        });

        return view('admin.staff_monthly', compact('user', 'currentMonth', 'dates', 'attendances', 'breakSeconds'));
    }


    // 管理者用 承認画面の表示
    public function approve(AttendanceCorrection $attendanceCorrection)
    {
        $attendance = $attendanceCorrection->attendance; // 関連
        $breaks = $attendanceCorrection->breaks;        // 申請に紐づく休憩

        return view('admin.approve', compact('attendanceCorrection', 'attendance', 'breaks'));
    }

    // 管理者による申請承認・却下
    public function approveUpdate(Request $request, AttendanceCorrection $attendanceCorrection)
    {
        $action = $request->input('action'); // approve or reject

        if ($action === 'approve') {
            // 本番の Attendance を更新
            $attendance = $attendanceCorrection->attendance;
            if ($attendance) {
                $attendance->update([
                    'clock_in'  => $attendanceCorrection->clock_in,
                    'clock_out' => $attendanceCorrection->clock_out,
                    'reason'    => $attendanceCorrection->reason,
                ]);
                // 休憩も上書きするならここで
            }

            $attendanceCorrection->status = 'approved';
            $attendanceCorrection->save();

            return redirect()->route('attendance.pending_list')
                ->with('success', '申請を承認しました。');
        }

        if ($action === 'reject') {
            $attendanceCorrection->status = 'rejected';
            $attendanceCorrection->save();

            return redirect()->route('attendance.pending_list')
                ->with('success', '申請を却下しました。');
        }

        return redirect()->back()->withErrors(['msg' => '不正な操作です。']);
    }



}

