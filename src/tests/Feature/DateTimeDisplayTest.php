<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class DateTimeDisplayTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function initial_datetime_is_rendered_correctly()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $now = Carbon::now();
        $expectedTime = $now->format('H:i:s');
        $daysOfWeek = ['日', '月', '火', '水', '木', '金', '土'];
        $expectedDate = $now->format('Y年n月j日') . '(' . $daysOfWeek[$now->dayOfWeek] . ')';

        $response = $this->get(route('attendance'));

        // Blade 初期表示の確認
        $response->assertSeeText($expectedTime); // current-time div
        $response->assertSeeText($expectedDate); // datetime div
    }
}
