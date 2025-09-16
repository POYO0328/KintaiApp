<?php

namespace Database\Factories;

use App\Models\AttendanceCorrection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceCorrectionFactory extends Factory
{
    protected $model = AttendanceCorrection::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'attendance_id' => null, // 必要に応じて Attendance ID を設定
            'work_date' => $this->faker->date(),
            'clock_in' => $this->faker->time(),
            'clock_out' => $this->faker->time(),
            'reason' => $this->faker->sentence(),
            'status' => 'pending', // デフォルトは承認待ち
        ];
    }
}
