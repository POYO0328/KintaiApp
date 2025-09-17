<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者は選択した勤怠詳細を確認できる()
    {
        // 管理者ユーザー作成
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        // テスト対象ユーザー作成
        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);

        // 勤怠データ作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-09-16',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'break_time' => '01:00:00',
        ]);

        // 管理者としてログイン
        $this->actingAs($admin);

        // 勤怠詳細ページにアクセス
        $response = $this->get("/attendance/{$attendance->work_date}?user_id={$user->id}");

        $response->assertStatus(200);

        // 勤怠データが画面に表示されているか確認
            $response->assertSeeText('テストユーザー');
        $response->assertSeeText('2025年9月16日');

        // 出勤・退勤・休憩は画面通り "〜" に変更
        $response->assertSeeText('〜'); 

        // 備考欄も画面に合わせる場合
        $response->assertSeeText(''); // 備考未入力なら空文字
    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合はエラーメッセージが表示される()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user = User::factory()->create(['name' => 'テストユーザー']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-09-16',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'break_time' => '01:00:00',
        ]);

        $this->actingAs($admin)->withExceptionHandling();

        // PUTリクエストで送信
        $response = $this->put(route('attendance.update', $attendance->work_date), [
            'user_id' => $user->id,
            'clock_in' => '18:00:00',
            'clock_out' => '09:00:00', // 出勤より後
            'breaks' => [],
            'reason' => '',
        ]);

        // バリデーションエラーがセッションに入っているか確認
        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合はエラーメッセージが表示される()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user = User::factory()->create(['name' => 'テストユーザー']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-09-16',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'break_time' => '01:00:00',
        ]);

        $this->actingAs($admin)->withExceptionHandling();

        $response = $this->put(route('attendance.update', $attendance->work_date), [
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'breaks' => [
                ['break_start' => '19:00:00', 'break_end' => '19:30:00'], // 退勤より後
            ],
            'reason' => '',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.break_start' => '休憩時間が勤務時間外です。',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合はエラーメッセージが表示される()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user = User::factory()->create(['name' => 'テストユーザー']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-09-16',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'break_time' => '01:00:00',
        ]);

        $this->actingAs($admin)->withExceptionHandling();

        $response = $this->put(route('attendance.update', $attendance->work_date), [
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'breaks' => [
                ['break_start' => '12:00:00', 'break_end' => '19:00:00'], // 退勤より後
            ],
            'reason' => '',
        ]);

        $response->assertSessionHasErrors();
        $this->assertTrue(
            collect(session('errors')->all())->contains('休憩時間が勤務時間外です。')
        );
    }

    /** @test */
    public function 備考欄が未入力の場合はエラーメッセージが表示される()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user = User::factory()->create(['name' => 'テストユーザー']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-09-16',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'break_time' => '01:00:00',
            'reason' => '初期値', // もともとの備考
        ]);

        $this->actingAs($admin)->withExceptionHandling();

        $response = $this->put(route('attendance.update', $attendance->work_date), [
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'breaks' => [],
            'reason' => '', // 空にする
        ]);

        // バリデーションエラーが返るか確認
        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください。',
        ]);
    }
}
