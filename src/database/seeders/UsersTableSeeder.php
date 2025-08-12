<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => '山田 太郎',
                'email' => 'yamada@example.com',
                'is_admin' => 1,
            ],
            [
                'name' => '佐藤 花子',
                'email' => 'sato@example.com',
            ],
            [
                'name' => '鈴木 次郎',
                'email' => 'suzuki@example.com',
            ],
            [
                'name' => '田中 美咲',
                'email' => 'tanaka@example.com',
            ],
            [
                'name' => '高橋 健太',
                'email' => 'takahashi@example.com',
            ]
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make('password'), // 全員パスワードは 'password'
                'is_admin' => $user['is_admin'] ?? 0,
            ]);
        }
    }
}
