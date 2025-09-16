<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_is_required_for_login()
    {
        // ユーザー作成
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        // メールアドレス未入力でログインを試す
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // バリデーションエラーを確認
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    // /** @test */
    // public function password_is_required_for_login()
    // {
    //     // 1. ユーザーを作成（パスワードは設定するがテストでは使わない）
    //     $user = User::factory()->create([
    //         'password' => bcrypt('password123'),
    //     ]);

    //     // 2. パスワードを空にしてログイン試行
    //     $response = $this->post('/login', [
    //         'email' => $user->email,
    //         'password' => '', // パスワード未入力
    //     ]);

    //     // 3. バリデーションエラー確認
    //     $response->assertSessionHasErrors([
    //         'email' => 'パスワードを入力してください',
    //     ]);
    // }
}
