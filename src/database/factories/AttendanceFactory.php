<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => now(),
            'clock_out' => null,
            'attendance_status' => 1,
        ];
    }
}
