<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // 管理者1名
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // 任意のパスワード
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // 一般ユーザー10名
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => "User{$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'), // 同じパスワードでもOK
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
        }
    }
}
