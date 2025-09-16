<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function clock_in_button_is_displayed_and_can_clock_in()
    {
        // 勤務外のユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Attendance はまだ作らない（勤務外状態）
        
        // 出勤画面にアクセスして「出勤」ボタンが表示されることを確認
        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertSeeText('出勤');

        // 出勤ボタンを押す（POST）
        $this->actingAs($user)
            ->post(route('attendance.clockIn'))
            ->assertStatus(302); // リダイレクトでも OK

        // DB に出勤が記録されていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'attendance_status' => 1, // 1 = 勤務中
        ]);

        // 出勤画面にアクセスしてステータスが「勤務中」と表示されることを確認
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', today())
            ->first();

        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertSeeText('出 勤 中');
    }

    /** @test */
    public function user_can_only_clock_in_once_per_day()
    {
        // 退勤済のユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 当日分の退勤済み出勤記録を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'attendance_status' => 4, // 4 = 退勤済
            'clock_in' => now()->subHours(8),
            'clock_out' => now()->subHours(1),
        ]);

        // 出勤画面にアクセスして「出勤」ボタンが表示されないことを確認
        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertDontSeeText('出勤');
    }

    /** @test */
    public function clock_in_time_is_visible_on_attendance_list()
    {
        // 勤務外のユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        // 出勤処理
        $this->post(route('attendance.clockIn'))->assertStatus(302);

        // DB から最新の出勤記録を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', today())
            ->first();

        $this->assertNotNull($attendance->clock_in);

        // 勤怠一覧画面で出勤時刻が表示されるか確認
        $response = $this->get(route('attendance.list')); // 勤怠一覧画面のルート
        $response->assertSeeText($attendance->clock_in->format('H:i'));
    }
}
