<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_edit_form_displays_correct_initial_values()
    {
        // ユーザーを作成してログイン
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'profile_image_path' => 'profile.jpg',
            'postal_code' => '123-4567',
            'address' => '東京都新宿区',
        ]);
        $this->actingAs($user);

        // プロフィール編集画面へGETリクエスト
        $response = $this->get(route('profile.edit'));

        // ステータスコードは200（正常表示）
        $response->assertStatus(200);

        // フォームに初期値が含まれていることを確認
        $response->assertSee('value="テストユーザー"', false);      // 名前
        $response->assertSee('value="123-4567"', false);            // 郵便番号
        $response->assertSee('value="東京都新宿区"', false);         // 住所
        $response->assertSee('profile.jpg');                        // プロフィール画像のパス

        // 他に確認したい値があればここに追加
    }
}
