<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangeShippingAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_変更した住所が購入画面に反映される()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        // 正しいセッションキーに修正
        $response = $this->withSession([
            'purchase_address' => [
                'postal_code' => '123-4567',
                'address' => '東京都渋谷区テスト1-2-3',
                'building' => 'テストビル201'
            ]
        ])->get(route('purchase.show', ['item_id' => $item->id]));

        $response->assertStatus(200);
        $response->assertSee('〒 123-4567'); // ※全角「〒」がある前提で
        $response->assertSee('東京都渋谷区テスト1-2-3');
        $response->assertSee('テストビル201');
    }

    public function test_購入時にセッションの住所がpurchasesテーブルに登録される()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        // セッションに保存（ビューなどで使用）
        $this->withSession([
            'purchase_address' => [
                'postal_code' => '987-6543',
                'address' => '大阪府大阪市中央区テスト通り4-5-6',
                'building' => 'サンプルタワー10F'
            ]
        ]);

        // バリデーションを通すため、POSTにも同じ内容を明示的に送信
        $this->post(route('purchase.complete', ['item_id' => $item->id]), [
            'payment_method' => 'カード支払い',
            'shipping_postal_code' => '987-6543',
            'shipping_address' => '大阪府大阪市中央区テスト通り4-5-6',
            'shipping_building' => 'サンプルタワー10F',
        ])->assertStatus(302); // リダイレクト確認

        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'shipping_postal_code' => '987-6543',
            'shipping_address' => '大阪府大阪市中央区テスト通り4-5-6',
            'shipping_building' => 'サンプルタワー10F',
        ]);
    }

}
