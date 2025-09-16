<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\BreakTime;
use App\Models\AttendanceCorrection;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Requests\AttendanceDetailRequest;

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

    public function detail(Request $request, $date)
    {
        $userId = $request->input('user_id') ?? auth()->id();
        $user = User::find($userId);
        $isAdmin = auth()->user()->is_admin;

        // 本番勤怠
        $attendanceData = Attendance::with('breaks')
            ->where('user_id', $userId)
            ->where('work_date', $date)
            ->first();

        if ($isAdmin) {
            // 管理者は常に本番勤怠
            $attendance = $attendanceData;
            $revision = null;
            $breaks = $attendance ? $attendance->breaks : collect();
        } else {
            // ユーザー用
            $editRequest = AttendanceCorrection::with('breaks')
                ->where('user_id', $userId)
                ->where('work_date', $date)
                ->first();

            if ($editRequest && $editRequest->status === 'approved' && $attendanceData) {
                // 申請は承認済み → 本番優先
                $attendance = $attendanceData;
                $revision = $editRequest;
                $breaks = $attendance->breaks;
            } elseif ($editRequest) {
                // 申請があれば申請優先
                $attendance = $editRequest;
                $revision = $editRequest;
                $breaks = $editRequest->breaks;
            } else {
                // 申請がなければ本番
                $attendance = $attendanceData;
                $revision = null;
                $breaks = $attendance ? $attendance->breaks : collect();
            }
        }

        // データがない場合はダミー
        if (!$attendance) {
            $attendance = new \stdClass();
            $attendance->id = null;
            $attendance->user_id = $userId;
            $attendance->work_date = $date;
            $attendance->clock_in = null;
            $attendance->clock_out = null;
            $attendance->attendance_status = 0;
        }

        return view('attendance.detail', compact('attendance', 'revision', 'date', 'breaks', 'user'));
    }

    public function update(AttendanceDetailRequest $request, $date)
    {
        $user = auth()->user();
        $userId = $request->input('user_id', $user->id); // 管理者は他人のIDを受け取る想定

        // バリデーション済みデータを取得
        $validated = $request->validated();

        // 本番勤怠レコードを取得
        $attendance = Attendance::where('user_id', $userId)
            ->where('work_date', $date)
            ->first();

        if ($user->is_admin) {
            // ========== 管理者：本番データを直接修正 ==========
            if (!$attendance) {
                // 本番勤怠がなければ新規作成
                $attendance = Attendance::create([
                    'user_id' => $userId,
                    'work_date' => $date,
                    'clock_in' => $validated['clock_in'] ? Carbon::parse($date.' '.$validated['clock_in']) : null,
                    'clock_out' => $validated['clock_out'] ? Carbon::parse($date.' '.$validated['clock_out']) : null,
                    'reason' => $validated['reason'] ?? null,
                    'attendance_status' => 4,
                ]);
            } else {
                // 既存レコードを更新
                $attendance->clock_in = $validated['clock_in'] ? Carbon::parse($date.' '.$validated['clock_in']) : null;
                $attendance->clock_out = $validated['clock_out'] ? Carbon::parse($date.' '.$validated['clock_out']) : null;
                $attendance->reason = $validated['reason'] ?? null;
                $attendance->attendance_status = 4;
                $attendance->save();
            }

            // // 休憩処理
            // if ($validated['break_start'] && $validated['break_end']) {
            //     $attendance->breaks()->updateOrCreate(
            //         ['attendance_id' => $attendance->id],
            //         [
            //             'break_start' => Carbon::parse($date.' '.$validated['break_start']),
            //             'break_end'   => Carbon::parse($date.' '.$validated['break_end']),
            //         ]
            //     );
            // }
            // 休憩処理
            $attendance->breaks()->delete(); // 一旦削除して再登録
            if (!empty($validated['breaks'])) {
                foreach ($validated['breaks'] as $break) {
                    if (!empty($break['break_start']) && !empty($break['break_end'])) {
                        $attendance->breaks()->create([
                            'break_start' => Carbon::parse($date.' '.$break['break_start']),
                            'break_end'   => Carbon::parse($date.' '.$break['break_end']),
                        ]);
                    }
                }
            }

            return redirect()->route('admin.attendances.index', ['date' => $date])
                ->with('success', '勤怠を修正しました（管理者権限で即時反映）');

        } else {
            // ========== ユーザー：申請テーブルに保存 ==========
            $attendanceId = $attendance?->id; // 本番勤怠がない場合は null
            $correction = AttendanceCorrection::firstOrNew([
                'attendance_id' => $attendanceId,
                'user_id' => $user->id,
                'work_date' => $date,
            ]);

            $correction->clock_in = $validated['clock_in'] ?? null;
            $correction->clock_out = $validated['clock_out'] ?? null;
            $correction->status = 'pending';
            $correction->reason = $validated['reason'] ?? null;
            $correction->save();

            // 休憩処理（Correction 側）
            $correction->breaks()->delete();
            if (!empty($validated['breaks'])) {
                foreach ($validated['breaks'] as $break) {
                    if (!empty($break['break_start']) && !empty($break['break_end'])) {
                        $correction->breaks()->create([
                            'break_start' => $break['break_start'],
                            'break_end'   => $break['break_end'],
                        ]);
                    }
                }
            }

            return redirect()->route('attendance.detail', ['date' => $date])
                ->with('success', '修正申請を送信しました。承認までお待ちください。');
        }
    }

    public function pendingList()
    {
        $baseQuery = AttendanceCorrection::with('user');

        if (!auth()->user()->is_admin) {
            $baseQuery->where('user_id', auth()->id());
        }

        $pendingRequests = (clone $baseQuery)->where('status', 'pending')->orderBy('work_date', 'desc')->get();
        $approvedRequests = (clone $baseQuery)->where('status', 'approved')->orderBy('work_date', 'desc')->get();

        return view('attendance.pending_list', compact('pendingRequests', 'approvedRequests'));
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

    // 管理者による申請承認
    public function approveUpdate(Request $request, AttendanceCorrection $attendanceCorrection)
    {
        // Attendance がなければ新規作成
        $attendance = $attendanceCorrection->attendance;
        if (!$attendance) {
            $attendance = new Attendance([
                'user_id'   => $attendanceCorrection->user_id,
                'work_date' => $attendanceCorrection->work_date,
            ]);
        }

        // 本番の Attendance を更新
        $attendance->clock_in = $attendanceCorrection->clock_in;
        $attendance->clock_out = $attendanceCorrection->clock_out;
        $attendance->reason = $attendanceCorrection->reason;
        $attendance->attendance_status = 4; // 新規・更新ともに無条件で 4
        $attendance->save();

        // 休憩を一旦削除して再登録
        $attendance->breaks()->delete();
        foreach ($attendanceCorrection->breaks as $correctionBreak) {
            $attendance->breaks()->create([
                'break_start' => $correctionBreak->break_start,
                'break_end' => $correctionBreak->break_end,
            ]);
        }

        // 申請ステータス更新
        $attendanceCorrection->status = 'approved';
        $attendanceCorrection->save();

        return back()->with('success', '申請を承認しました。');
    }

    public function exportCsv($id, Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $user = User::findOrFail($id);

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [
                \Carbon\Carbon::parse($month)->startOfMonth(),
                \Carbon\Carbon::parse($month)->endOfMonth()
            ])
            ->get();

        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function() use ($attendances, $user, $month) {
            $handle = fopen('php://output', 'w');

            // ヘッダー行
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩時間', '実働時間']);

            foreach ($attendances as $att) {
                $clockIn  = $att->clock_in ? \Carbon\Carbon::parse($att->clock_in) : null;
                $clockOut = $att->clock_out ? \Carbon\Carbon::parse($att->clock_out) : null;

                // 休憩合計（分）
                $breakMinutes = $att->breaks->sum(function($b) {
                    return \Carbon\Carbon::parse($b->break_end)->diffInMinutes(\Carbon\Carbon::parse($b->break_start));
                });

                // 実働時間（分）
                $workMinutes = 0;
                if ($clockIn && $clockOut) {
                    $workMinutes = $clockOut->diffInMinutes($clockIn) - $breakMinutes;
                    $workMinutes = max(0, $workMinutes);
                }

                // 表示用 h:mm 形式
                $breakStr = sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60);
                $workStr  = $clockIn && $clockOut ? sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60) : '';

                fputcsv($handle, [
                    \Carbon\Carbon::parse($att->work_date)->format('Y-m-d'),
                    $clockIn ? $clockIn->format('H:i') : '',
                    $clockOut ? $clockOut->format('H:i') : '',
                    $breakStr,
                    $workStr,
                ]);
            }

            fclose($handle);
        });

        $filename = "{$user->name}_{$month}_attendance.csv";
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename={$filename}");

        return $response;
    }
}

