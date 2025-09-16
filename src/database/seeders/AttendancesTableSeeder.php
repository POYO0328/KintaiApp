<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者以外のユーザー
        $users = User::where('is_admin', 0)->get();

        foreach ($users as $user) {
            $daysCreated = 0;
            $date = Carbon::now(); // 今日からさかのぼる

            while ($daysCreated < 30) {
                // 土日をスキップ
                if (!$date->isWeekend()) {

                    // 出勤・退勤をランダムで作成
                    $clockIn  = $date->copy()->setTime(rand(8, 9), rand(0, 59));
                    $clockOut = $date->copy()->setTime(rand(17, 19), rand(0, 59));

                    $attendance = Attendance::create([
                        'user_id' => $user->id,
                        'work_date' => $date->toDateString(),
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'attendance_status' => 4, // 退勤済み
                    ]);

                    // 休憩を1〜4回ランダムで作成（勤務時間内・重複なし）
                    $numBreaks = rand(1, 4);
                    $breaks = [];

                    for ($i = 0; $i < $numBreaks; $i++) {
                        do {
                            // 勤務時間内でランダム開始時間
                            $startHour = rand($clockIn->hour, max($clockIn->hour, $clockOut->hour - 1));
                            $startMin  = rand(0, 59);
                            $start     = $date->copy()->setTime($startHour, $startMin);

                            // 終了時間は開始から15〜60分以内、勤務終了を超えない
                            $duration = rand(15, 60);
                            $end = $start->copy()->addMinutes($duration);
                            if ($end > $clockOut) {
                                $end = $clockOut->copy();
                            }

                            // 既存休憩との重複判定
                            $overlap = false;
                            foreach ($breaks as $b) {
                                if ($start < $b['end'] && $end > $b['start']) {
                                    $overlap = true;
                                    break;
                                }
                            }
                        } while ($overlap); // 重複ならやり直し

                        $breaks[] = ['start' => $start, 'end' => $end];

                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start'   => $start,
                            'break_end'     => $end,
                        ]);
                    }

                    $daysCreated++;
                }

                $date->subDay(); // 1日前に移動
            }
        }
    }
}
