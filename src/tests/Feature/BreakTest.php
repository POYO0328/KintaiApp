<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function break_button_is_displayed_and_can_start_break()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status' => 1, // 出勤中
        ]);

        // 出勤画面にアクセスして「休憩入」ボタンが表示されることを確認
        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertSeeText('休憩入');

        // 休憩開始処理
        $this->actingAs($user)
            ->post(route('attendance.breakStart', ['attendance' => $attendance->id]))
            ->assertStatus(302);

        // DB に休憩開始が記録されていることを確認
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_end' => null,
        ]);

        // 出勤画面にアクセスしてステータスが「休憩中」と表示されることを確認
        $attendance->refresh();
        $this->assertEquals(2, $attendance->attendance_status);

        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertSeeText('休 憩 中');
    }


    /** @test */
    public function user_can_take_multiple_breaks()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(), // メール認証済み
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status' => 1, // 出勤中
        ]);

        // 1回目の休憩
        $this->actingAs($user)
            ->post(route('attendance.breakStart', ['attendance' => $attendance->id]))
            ->assertStatus(302);

        $attendance->refresh();
        $this->assertEquals(2, $attendance->attendance_status); // 休憩中

        $this->actingAs($user)
            ->post(route('attendance.breakEnd', ['attendance' => $attendance->id]))
            ->assertStatus(302);

        $attendance->refresh();
        $this->assertEquals(3, $attendance->attendance_status); // 休憩戻

        // 2回目の休憩開始前に「休憩入」ボタンが表示されることを確認
        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertSeeText('休憩入');

        // 2回目の休憩
        $this->actingAs($user)
            ->post(route('attendance.breakStart', ['attendance' => $attendance->id]))
            ->assertStatus(302);

        $attendance->refresh();
        $this->assertEquals(2, $attendance->attendance_status); // 休憩中

        // ボタンが「休憩戻」に変わることを確認
        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertSeeText('休憩戻');
    }

    public function test_break_end_button_functions_correctly()
    {
        // 出勤中ユーザーでログイン
        $user = User::factory()->create([
            'email_verified_at' => now(), // メール認証済み
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status' => 1, // 出勤中
        ]);
        $this->actingAs($user);

        // 休憩入
        $this->post('/attendance/break-start')
            ->assertStatus(302); // リダイレクトでもOK
        // DB から最新の出勤記録を再取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', today())
            ->first();
        $this->assertEquals(2, $attendance->attendance_status); // 2 = 休憩中

        // 休憩終了
        $this->post('/attendance/break-end')->assertStatus(302);

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', today())
            ->first();
        $this->assertEquals(3, $attendance->attendance_status); // 3 = 出勤中

        // ビュー上に休憩戻ボタンが表示されるか確認
        $response = $this->get('/attendance');
        $response->assertSeeText('休 憩 戻');
    }

    /** @test */
    public function break_can_be_taken_multiple_times_per_day()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status' => 1, // 出勤中
        ]);

        $this->actingAs($user);

        // 1回目の休憩
        $this->post('/attendance/break-start')->assertStatus(302);
        $attendance->refresh();
        $this->assertEquals(2, $attendance->attendance_status); // 休憩中

        $this->post('/attendance/break-end')->assertStatus(302);
        $attendance->refresh();
        $this->assertEquals(3, $attendance->attendance_status); // 出勤中

        // 2回目の休憩
        $this->post('/attendance/break-start')->assertStatus(302);
        $attendance->refresh();
        $this->assertEquals(2, $attendance->attendance_status); // 休憩中

        $this->post('/attendance/break-end')->assertStatus(302);
        $attendance->refresh();
        $this->assertEquals(3, $attendance->attendance_status); // 出勤中

        // 出勤画面に「休憩戻」ボタンが表示されることを確認
        $response = $this->get('/attendance');
        $response->assertSeeText('休 憩 戻');
    }

    /** @test */
    public function break_times_are_visible_on_attendance_list()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status' => 1, // 出勤中
        ]);

        $this->actingAs($user);

        // 休憩入
        $this->post('/attendance/break-start')->assertStatus(302);
        $attendance->refresh();
        $this->assertEquals(2, $attendance->attendance_status); // 休憩中

        $break = \App\Models\BreakTime::where('attendance_id', $attendance->id)->latest()->first();
        $this->assertNotNull($break->break_start);

        // 休憩戻
        $this->post('/attendance/break-end')->assertStatus(302);
        $attendance->refresh();
        $this->assertEquals(3, $attendance->attendance_status); // 出勤中

        $break->refresh();
        $this->assertNotNull($break->break_end);

        // 勤怠一覧画面で休憩時刻が表示されるか確認
        $response = $this->get('/attendance/list'); // 勤怠一覧画面のルート
        $response->assertSeeText(substr($break->break_start, 11, 5)); // HH:MM
        $response->assertSeeText(substr($break->break_end, 11, 5));   // HH:MM
    }
}
