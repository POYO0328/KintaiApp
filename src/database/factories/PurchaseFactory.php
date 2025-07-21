<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'item_id' => Item::factory(),
            'payment_method' => $this->faker->randomElement(['コンビニ支払い', 'カード支払い']),
            'shipping_postal_code' => '123-4567',
            'shipping_address' => '西新宿1-1-1',
            'shipping_building' => '新宿ビル101号',
        ];
    }
}
