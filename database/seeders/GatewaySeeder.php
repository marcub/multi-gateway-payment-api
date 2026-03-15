<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Database\Eloquent\Gateway;
use Illuminate\Database\Seeder;

class GatewaySeeder extends Seeder
{
    public const GATEWAY1_ID = 'aaaaaaaa-0000-4000-a000-000000000001';
    public const GATEWAY2_ID = 'aaaaaaaa-0000-4000-a000-000000000002';

    public function run(): void
    {
        $now = now();

        Gateway::insert([
            [
                'id'         => self::GATEWAY1_ID,
                'name'       => 'Gateway1',
                'priority'   => 1,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => self::GATEWAY2_ID,
                'name'       => 'Gateway2',
                'priority'   => 2,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}