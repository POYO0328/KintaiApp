<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 商品名で部分一致検索ができる()
    {
        // 検索対象データの準備
        Item::factory()->create(['item_name' => 'iPhone 15']);
        Item::factory()->create(['item_name' => 'iPhone 14']);
        Item::factory()->create(['item_name' => 'MacBook Pro']);

        // 「iPhone」で検索（ルートは /）
        $response = $this->get('/?keyword=iPhone');

        // 部分一致する商品だけが表示されていることを確認
        $response->assertSee('iPhone 15');
        $response->assertSee('iPhone 14');
        $response->assertDontSee('MacBook Pro');
    }

    /** @test */
    public function 検索キーワードがマイリストでも保持されている()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create();
        $item1 = Item::factory()->create(['item_name' => 'PS5']);
        $item2 = Item::factory()->create(['item_name' => 'Nintendo Switch']);
        $item3 = Item::factory()->create(['item_name' => 'ゲーミングPC']);

        // item1, item2 をお気に入り（likes）に登録
        $user->likes()->attach([$item1->id, $item2->id]);

        // ログイン状態で、マイリスト（page=mylist）かつ「Switch」で検索
        $response = $this->actingAs($user)->get('/?page=mylist&keyword=Switch');

        // キーワードに一致する、かつマイリストにある商品だけが表示されている
        $response->assertSee('Nintendo Switch');
        $response->assertDontSee('PS5');
        $response->assertDontSee('ゲーミングPC');
    }
}
