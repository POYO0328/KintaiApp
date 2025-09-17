<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤務外の場合は勤怠ステータスが勤務外と表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 勤怠データを作成しない → 勤務外
        $this->actingAs($user);

        $response = $this->get(route('attendance'));

        $response->assertStatus(200);
        $response->assertSeeText('勤 務 外');
    }

    /** @test */
    public function 勤務中の場合は勤怠ステータスが勤務中と表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => null,
            'attendance_status' => '1'
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance'));

        $response->assertStatus(200);
        $response->assertSeeText('出 勤 中');
    }

    /** @test */
    public function 休憩中の場合は勤怠ステータスが休憩中と表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => null,
            'attendance_status' => '2'
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance'));

        $response->assertStatus(200);
        $response->assertSeeText('休 憩 中');
    }

    /** @test */
    public function 退勤後の場合は勤怠ステータスが退勤済と表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '10:00:00',
            'attendance_status' => '4'
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance'));

        $response->assertStatus(200);
        $response->assertSeeText('退 勤 済');
    }

    // /** @test */
    // public function 休憩中の場合は勤怠ステータスが休憩中と表示される()
    // {
    //     $user = User::factory()->create();

    //     // 出勤しており、休憩中 → 休憩中
    //     Attendance::create([
    //         'user_id'   => $user->id,
    //         'work_date' => Carbon::today()->toDateString(),
    //         'clock_in'  => '09:00:00',
    //         'clock_out' => null,
    //         'on_break'  => true, // ← フラグや判定用のカラムがある場合
    //     ]);

    //     $this->actingAs($user);

    //     $response = $this->get(route('attendance.index'));

    //     $response->assertStatus(200);
    //     $response->assertSeeText('休 憩 中');
    // }
}
