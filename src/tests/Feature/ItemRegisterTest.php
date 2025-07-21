<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_listing_saves_data_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // カテゴリを必ず作成する
        \App\Models\Category::factory()->create(['id' => 1]);

        $formData = [
            'category_id' => [1],  // 配列にすることがポイント
            'condition' => '新品',
            'item_name' => 'テスト商品',
            'brand' => 'サンプルブランド',
            'description' => 'これはテスト用の商品説明です。',
            'price' => 5000,
        ];

        $response = $this->post(route('item.store'), $formData);

        // リダイレクト先を文字列で確認
        $response->assertRedirect('/mypage');

        $this->assertDatabaseHas('items', [
            'user_id' => $user->id,
            'category_id' => '1', // コントローラでimplodeしているので文字列
            'condition' => '新品',
            'item_name' => 'テスト商品',
            'description' => 'これはテスト用の商品説明です。',
            'price' => 5000,
        ]);
    }

}
