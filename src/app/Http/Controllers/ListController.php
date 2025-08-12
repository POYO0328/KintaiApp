<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;

class ListController extends Controller
{
    public function index(Request $request)
    {
        $yearMonth = $request->query('month', Carbon::now()->format('Y-m'));
        try {
            $currentMonth = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();
        } catch (\Exception $e) {
            $currentMonth = Carbon::now()->startOfMonth();
        }

        $start = $currentMonth->copy()->startOfMonth()->format('Y-m-d');
        $end   = $currentMonth->copy()->endOfMonth()->format('Y-m-d');

        // 1ヶ月の日付リスト作成
        $dates = collect();
        for ($d = $currentMonth->copy(); $d->lte($currentMonth->copy()->endOfMonth()); $d->addDay()) {
            $dates->push($d->copy());
        }

        // ユーザーの当月勤怠取得
        $attendances = Attendance::where('user_id', auth()->id())
            ->whereBetween('work_date', [$start, $end])
            ->get()
            ->keyBy(fn($item) => \Carbon\Carbon::parse($item->work_date)->format('Y-m-d'));


        // 勤怠IDリスト抽出
        $attendanceIds = $attendances->pluck('id')->toArray();

        // 該当勤怠の休憩時間をまとめて取得
        $breakTimes = BreakTime::whereIn('attendance_id', $attendanceIds)->get();

        // 勤怠ID毎に休憩秒数を合計
        $breakSeconds = [];
        foreach ($breakTimes as $break) {
            if ($break->break_start && $break->break_end) {
                $startTimestamp = Carbon::parse($break->break_start)->timestamp;
                $endTimestamp = Carbon::parse($break->break_end)->timestamp;
                $diff = max(0, $endTimestamp - $startTimestamp);
                if (!isset($breakSeconds[$break->attendance_id])) {
                    $breakSeconds[$break->attendance_id] = 0;
                }
                $breakSeconds[$break->attendance_id] += $diff;
            }
        }

        // 集計結果を hh:mm 形式に変換
        $breakTimesFormatted = [];
        foreach ($breakSeconds as $attendanceId => $seconds) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $breakTimesFormatted[$attendanceId] = sprintf('%d:%02d', $hours, $minutes);
        }


        return view('attendance.list', compact('dates', 'attendances', 'breakSeconds', 'currentMonth'));
    }
}
