<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class UserDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面にログインユーザーの名前が表示される()
    {
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'attendance_status' => '4'
        ]);

        $this->actingAs($user);
        $response = $this->get(route('attendance.detail', ['date' => Carbon::today()->toDateString()]));

        $response->assertStatus(200);
        $response->assertSeeText('テスト太郎');
    }

    /** @test */
    public function 勤怠詳細画面に正しい日付が表示される()
    {
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email_verified_at' => now(),
        ]);

        $date = Carbon::today()->toDateString();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $date,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'attendance_status' => '1',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.detail', ['date' => $attendance->work_date]));

        $response->assertStatus(200);
        // 日付がフォーマット通りに表示されているか確認
        $response->assertSeeText(Carbon::parse($date)->format('Y年n月j日'));
    }

    /** @test */
    public function 勤怠詳細画面に正しい出勤・退勤時間が表示される()
    {
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '09:10:00',
            'attendance_status' => '4'
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.detail', ['date' => $attendance->work_date]));

        $response->assertStatus(200);

        // input の value 属性に '09:00' が含まれるかを文字列検索
        $response->assertSee('value="09:00"', false);
        $response->assertSee('value="09:10"', false);
    }

    /** @test */
    public function 勤怠詳細画面に正しい休憩時間が表示される()
    {
        $user = User::factory()->create(['name' => 'テスト太郎']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 休憩データ作成
        $attendance->breaks()->create([
            'break_start' => '12:00:00',
            'break_end' => '12:30:00',
        ]);

        $this->actingAs($user);
        $response = $this->get(route('attendance.detail', ['date' => $attendance->work_date]));

        $response->assertStatus(200);

        // HTML 内に出力されている値を文字列検索で確認
        $response->assertSee('12:00');
        $response->assertSee('12:30');
    }
}
