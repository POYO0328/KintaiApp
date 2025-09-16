<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceCorrection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PendingListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pending_corrections_are_displayed_for_admin()
    {
        // 管理者ユーザーを作成
        $admin = User::factory()->create([
            'is_admin' => 1,
            'email_verified_at' => now(),
        ]);

        // 一般ユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 承認待ちの修正申請を作成
        $correction = AttendanceCorrection::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => null,
            'work_date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'reason' => '勤務時間修正',
            'status' => 'pending',
        ]);

        // 管理者として承認待ち一覧ページにアクセス
        $response = $this->actingAs($admin)
                         ->get(route('stamp_correction_request.list'));

        // 修正申請の内容が画面に表示されるか確認
        $response->assertSeeText('勤務時間修正')
                 ->assertSeeText($user->name);
    }

    /** @test */
    public function approved_corrections_are_displayed_for_admin()
    {
        // 管理者ユーザーを作成
        $admin = User::factory()->create([
            'is_admin' => 1,
            'email_verified_at' => now(),
        ]);

        // 一般ユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 承認済みの修正申請を作成
        $correction = AttendanceCorrection::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => null,
            'work_date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'reason' => '勤務時間修正（承認済み）',
            'status' => 'approved', // 承認済み
        ]);

        // 管理者としてログインして一覧画面を確認
        $this->actingAs($admin)
            ->get(route('stamp_correction_request.list'))
            ->assertSeeText('勤務時間修正（承認済み）') // 理由が表示される
            ->assertSeeText($user->name);             // ユーザー名も表示される
    }

    /** @test */
    public function correction_details_are_displayed_correctly()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $correction = AttendanceCorrection::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => null,
            'work_date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'reason' => '勤務時間修正（詳細確認）',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.approve', $correction->id))
            ->assertSeeText($user->name)
            ->assertSeeText(today()->format('Y年n月j日'))
            ->assertSeeText('〜')  // 出勤・退勤の区切りだけチェック
            ->assertSeeText('勤務時間修正（詳細確認）');
    }

    /** @test */
    public function correction_can_be_approved_by_admin()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $correction = AttendanceCorrection::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => null,
            'work_date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'reason' => '勤務時間修正（承認処理テスト）',
            'status' => 'pending',
        ]);

        // 承認処理
        $this->actingAs($admin)
            ->put(route('admin.attendance.approveUpdate', $correction->id), [
                'action' => 'approve',
            ]);

        // DB に承認済みに更新されているか確認
        $this->assertDatabaseHas('attendance_corrections', [
            'id' => $correction->id,
            'status' => 'approved',
        ]);
    }
}
