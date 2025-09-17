<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminUserListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者は全ユーザーの氏名とメールアドレスを確認できる()
    {
        // 管理者ユーザーを作成
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'is_admin' => 1,
        ]);

        // 一般ユーザーを3人作成
        $users = User::factory()->count(3)->create([
            'email_verified_at' => now(),
            'is_admin' => 0,
        ]);

        // 管理者でログイン
        $this->actingAs($admin);

        // スタッフ一覧ページにアクセス
        $response = $this->get('/admin/staff/list');

        $response->assertStatus(200);

        // 全ての一般ユーザーの氏名・メールアドレスが表示されているか確認
        foreach ($users as $user) {
            $response->assertSeeText($user->name);
            $response->assertSeeText($user->email);
        }
    }

    /** @test */
    public function 管理者はユーザーの勤怠情報を確認できる()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user = User::factory()->create(['name' => 'テストユーザー']);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-09-17',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'break_time' => null,
        ]);

        $this->actingAs($admin);

        $response = $this->get("/admin/attendance/staff/{$user->id}");
        $response->assertStatus(200);

        $response->assertSeeText('09/17 (水)');
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
    }

    /** @test */
    public function 管理者は前月の勤怠情報を確認できる()
    {
        // 管理者ユーザー作成
        $admin = User::factory()->create(['is_admin' => 1]);

        // テスト対象ユーザー作成
        $user = User::factory()->create(['name' => 'テストユーザー']);

        // 前月の勤怠データを作成（例：2025年8月）
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-08-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'break_time' => null,
        ]);

        // 管理者としてログイン
        $this->actingAs($admin);

        // 勤怠一覧ページを開く
        $response = $this->get("/admin/attendance/staff/{$user->id}");

        // 「前月」ボタンを押した場合のリクエスト
        $response = $this->get("/admin/attendance/staff/{$user->id}?month=2025-08");

        $response->assertStatus(200);

        // 前月の勤怠情報が表示されているか確認
        $response->assertSeeText('08/15');
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
    }

    /** @test */
    public function 管理者は翌月の勤怠情報を確認できる()
    {
        // 管理者ユーザー作成
        $admin = User::factory()->create(['is_admin' => 1]);

        // テスト対象ユーザー作成
        $user = User::factory()->create(['name' => 'テストユーザー']);

        // 翌月の勤怠データを作成（例：2025年10月）
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-10-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'break_time' => null,
        ]);

        // 管理者としてログイン
        $this->actingAs($admin);

        // 勤怠一覧ページを開く
        $response = $this->get("/admin/attendance/staff/{$user->id}");

        // 「翌月」ボタンを押した場合のリクエスト
        $response = $this->get("/admin/attendance/staff/{$user->id}?month=2025-10");

        $response->assertStatus(200);

        // 翌月の勤怠情報が表示されているか確認
        $response->assertSeeText('10/15');
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
    }

}
