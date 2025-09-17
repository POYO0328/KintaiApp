<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class UserListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自分の勤怠情報が全て表示される()
    {
        // 一般ユーザー作成
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email_verified_at' => now(),
        ]);

        // 他のユーザー作成
        $otherUser = User::factory()->create([
            'name' => '他ユーザー',
            'email_verified_at' => now(),
        ]);

        // 勤怠データを作成
        $myAttendance1 = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $myAttendance2 = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::yesterday()->toDateString(),
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
        ]);

        // 他ユーザーの勤怠
        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.list')); // 勤怠一覧ページのルート

        $response->assertStatus(200);

        // 自分の勤怠情報は表示される
        $response->assertSee('09:00');
        $response->assertSee('09:30');

        // 他ユーザーの勤怠情報は表示されない
        $response->assertDontSee('08:00');
    }

    /** @test */
    public function 勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $user = User::factory()->create([
            'name' => 'テスト太郎',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.list')); // 勤怠一覧ページのルート

        $response->assertStatus(200);

        // 現在の月を YYYY-MM 形式で取得
        $currentMonth = now()->format('Y-m');
        
        // input[type="month"] の value に現在の月が入っていることを確認
        $response->assertSee('value="'.$currentMonth.'"', false);
    }

    /** @test */
    public function 勤怠一覧画面で前月ボタンを押すと前月の情報が表示される()
    {
        $user = User::factory()->create([
            'name' => 'テスト太郎',
        ]);

        $this->actingAs($user);

        $currentMonth = now();
        $prevMonth = $currentMonth->copy()->subMonth();

        // 前月の勤怠データを作成
        $attendancePrevMonth = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $prevMonth->copy()->startOfMonth()->toDateString(),
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
        ]);

        // 前月のURLにアクセス
        $response = $this->get(route('attendance.list', ['month' => $prevMonth->format('Y-m')]));

        $response->assertStatus(200);

        // 前月の勤怠データが表示されていることを確認
        $response->assertSee('08:30');
    }


    /** @test */
    public function 勤怠一覧画面で翌月ボタンを押すと翌月の情報が表示される()
    {
        $user = User::factory()->create([
            'name' => 'テスト太郎',
        ]);

        $this->actingAs($user);

        $currentMonth = now();
        $nextMonth = $currentMonth->copy()->addMonth();

        // 翌月の勤怠データを作成
        $attendanceNextMonth = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $nextMonth->copy()->startOfMonth()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 翌月のURLにアクセス
        $response = $this->get(route('attendance.list', ['month' => $nextMonth->format('Y-m')]));

        $response->assertStatus(200);

        // 翌月の勤怠データが表示されていることを確認
        $response->assertSee('09:00');
    }

    /** @test */
    public function 勤怠一覧画面の詳細ボタンを押すとその日の勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create([
            'name' => 'テスト太郎',
        ]);

        $this->actingAs($user);

        // 今日の勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 勤怠一覧ページにアクセス
        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);

        // 詳細ボタンのリンク先を確認
        $detailUrl = route('attendance.detail', ['date' => $attendance->work_date]);
        $response->assertSee($detailUrl, false);

        // 実際にそのリンク先にアクセスして詳細画面が表示されることを確認
        $detailResponse = $this->get($detailUrl);
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('勤怠詳細');
        $detailResponse->assertSee('09:00');
    }
}
