<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_ユーザーがログアウトボタンを押すとログアウトできる()
    {
        // 1. ユーザーにログインをする（ログイン状態を再現）
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. ログアウトボタンを押す（POST /logout を実行）
        $response = $this->post('/logout');


        $response->assertRedirect('/');

        // ログアウト状態である（ゲスト状態になっている）
        $this->assertGuest();
    }
}
