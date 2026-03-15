<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Database\Eloquent\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    public const CLIENT1_ID = 'bbbbbbbb-0000-4000-b000-000000000001';
    public const CLIENT2_ID = 'bbbbbbbb-0000-4000-b000-000000000002';
    public const CLIENT3_ID = 'bbbbbbbb-0000-4000-b000-000000000003';

    public function run(): void
    {
        $now = now();

        Client::insert([
            [
                'id'         => self::CLIENT1_ID,
                'name'       => 'João Silva',
                'email'      => 'joao.silva@example.com',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => self::CLIENT2_ID,
                'name'       => 'Maria Oliveira',
                'email'      => 'maria.oliveira@example.com',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => self::CLIENT3_ID,
                'name'       => 'Carlos Mendes',
                'email'      => 'carlos.mendes@example.com',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}