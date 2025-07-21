<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;

class LikeFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function いいねアイコンを押下するといいね登録できる()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        // post()に変更し、302リダイレクトを許容
        $response = $this->post(route('like.toggle', ['item_id' => $item->id]));

        $response->assertStatus(302);  // リダイレクト想定

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'items_id' => $item->id,
        ]);
    }

    /** @test */
    public function いいね済みのアイコンは画面で色が変わる状態で表示されることは別途フロントで確認()
    {
        // 画面での色変化はFeatureテストで直接確認困難なので
        // ここではいいね済みでDBにレコードが存在することを確認するに留める例
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        $this->post(route('like.toggle', ['item_id' => $item->id]));

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'items_id' => $item->id,
        ]);
    }

    /** @test */
    public function いいねアイコンを再度押下するといいね解除できる()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        // いいね登録
        $this->post(route('like.toggle', ['item_id' => $item->id]));

        // いいね解除
        $response = $this->post(route('like.toggle', ['item_id' => $item->id]));

        $response->assertStatus(302); // リダイレクトを想定

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'items_id' => $item->id,
        ]);
    }
}
