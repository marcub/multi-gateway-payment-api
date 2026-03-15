<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Database\Eloquent\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        User::insert([
            [
                'id'         => Str::uuid(),
                'email'      => 'admin@example.com',
                'password'   => Hash::make('admin12345'),
                'role'       => 'admin',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => Str::uuid(),
                'email'      => 'manager@example.com',
                'password'   => Hash::make('manager12345'),
                'role'       => 'manager',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => Str::uuid(),
                'email'      => 'finance@example.com',
                'password'   => Hash::make('finance12345'),
                'role'       => 'finance',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => Str::uuid(),
                'email'      => 'user@example.com',
                'password'   => Hash::make('user12345'),
                'role'       => 'user',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}