<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition()
    {
        return [
            'item_name' => $this->faker->word(),
            'price' => $this->faker->numberBetween(1000, 10000),
            'brand' => $this->faker->company(),
            'condition' => $this->faker->randomElement(['良好', '傷あり']),
            'description' => $this->faker->sentence(),
            'image_path' => 'images/default.png',
            'user_id' => \App\Models\User::factory(),
            'category_id' => 1, // 実際はカテゴリーが必要なら適宜変更
        ];
    }
}
