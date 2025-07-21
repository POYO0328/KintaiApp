<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_profile_data_can_be_retrieved()
    {
        // ユーザー作成
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'profile_image_path' => 'profile.jpg',  // もしカラムあるなら
        ]);

        // ユーザーが出品した商品を3つ作成
        $items = Item::factory()->count(3)->create(['user_id' => $user->id]);

        // ユーザーが購入した商品を2つ作成（別ユーザーの出品商品を購入）
        $otherUser = User::factory()->create();
        $purchasedItems = Item::factory()->count(2)->create(['user_id' => $otherUser->id]);

        foreach ($purchasedItems as $item) {
            Purchase::factory()->create([
                'user_id' => $user->id,
                'item_id' => $item->id,
                'payment_method' => 'カード支払い',
                'shipping_postal_code' => '123-4567',
                'shipping_address' => '西新宿1-1-1',
                'shipping_building' => '新宿ビル101号',
            ]);
        }

        // 実際にUserモデルから情報を取得してテスト

        // プロフィール画像、名前の確認
        $this->assertEquals('テストユーザー', $user->name);
        $this->assertEquals('profile.jpg', $user->profile_image_path);

        // 出品商品数が3であること
        $this->assertCount(3, $user->items);

        // 購入した商品の数が2であること
        $this->assertCount(2, $user->purchasedItems);
    }
}

