<?php

namespace Database\Factories;

use App\Models\BreakTime;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(), // 自動でAttendanceを作成
            'break_start' => $this->faker->time('H:i:s'),
            'break_end' => null, // デフォルトは休憩中
        ];
    }

    /**
     * 休憩終了済みにする
     */
    public function ended()
    {
        return $this->state(function (array $attributes) {
            return [
                'break_end' => $this->faker->time('H:i:s'),
            ];
        });
    }
}
