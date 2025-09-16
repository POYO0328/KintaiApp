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

                    // 休憩を1〜4回ランダムで作成
                    $numBreaks = rand(1, 4);
                    for ($i = 0; $i < $numBreaks; $i++) {
                        $startHour = rand(10, 15);
                        $startMin  = rand(0, 59);
                        $endHour   = $startHour + rand(0, 1);
                        $endMin    = rand(0, 59);

                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start'   => $date->copy()->setTime($startHour, $startMin),
                            'break_end'     => $date->copy()->setTime($endHour, $endMin),
                        ]);
                    }

                    $daysCreated++;
                }

                $date->subDay(); // 1日前に移動
            }
        }
    }
}
