<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function test_admin_can_view_all_attendance_for_today()
    {
        // 管理者ユーザー作成
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'is_admin' => 1,
        ]);

        // 一般ユーザーと勤怠データ作成
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $today = now()->toDateString();

        Attendance::factory()->create([
            'user_id' => $user1->id,
            'clock_in' => "$today 09:00:00",
            'clock_out' => "$today 18:00:00",
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'clock_in' => "$today 10:00:00",
            'clock_out' => "$today 19:00:00",
        ]);

        // 管理者でログイン
        $response = $this->actingAs($admin)
                         ->get('/admin/attendance/list');

        $response->assertStatus(200);

        // 勤怠情報が表示されているか確認
        $response->assertSee($user1->name)
                 ->assertSee('09:00')
                 ->assertSee('18:00');

        $response->assertSee($user2->name)
                 ->assertSee('10:00')
                 ->assertSee('19:00');
    }

    /** @test */
    public function test_today_date_is_displayed_on_attendance_list()
    {
        // 管理者ユーザー作成
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'is_admin' => 1,
        ]);

        // 管理者でログインして勤怠一覧画面を表示
        $response = $this->actingAs($admin)
                         ->get('/admin/attendance/list');

        $response->assertStatus(200);

        // 今日の日付を取得（例: 2025-09-17）
        $today = now()->format('Y-m-d');

        // 画面に今日の日付が表示されているか確認
        $response->assertSee($today);
    }
}
