<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_clock_out()
    {
        // ユーザー作成
        $user = User::factory()->create();

        // 勤怠（出勤中）作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status' => 1, // 出勤中
        ]);

        // 退勤リクエスト
        $response = $this->actingAs($user)
                         ->post(route('attendance.clockOut'));

        // DBの勤怠が退勤済になっているか確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'attendance_status' => 4, // 退勤済み
        ]);

        $response->assertStatus(302); // リダイレクトを想定
    }

    /** @test */
    public function clock_out_time_is_recorded_in_list()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status' => 1, // 出勤中
            'clock_in' => now()->subHours(8),
            'clock_out' => null,
        ]);

        // 退勤リクエスト
        $this->actingAs($user)
             ->post(route('attendance.clockOut'));

        // 勤怠一覧に退勤時刻が登録されているか
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'attendance_status' => 4,
        ]);

        $updatedAttendance = Attendance::find($attendance->id);
        $this->assertNotNull($updatedAttendance->clock_out);
    }
}
