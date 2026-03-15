<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Database\Eloquent\Product;
use Illuminate\Database\Seeder;;

class ProductSeeder extends Seeder
{
    public const PRODUCT1_ID = 'cccccccc-0000-4000-c000-000000000001';
    public const PRODUCT2_ID = 'cccccccc-0000-4000-c000-000000000002';
    public const PRODUCT3_ID = 'cccccccc-0000-4000-c000-000000000003';
    public const PRODUCT4_ID = 'cccccccc-0000-4000-c000-000000000004';
    public const PRODUCT5_ID = 'cccccccc-0000-4000-c000-000000000005';

    public function run(): void
    {
        $now = now();

        Product::insert([
            [
                'id'         => self::PRODUCT1_ID,
                'sku'        => 'CAIXA-BASIC',
                'name'       => 'Caixa de Som',
                'amount'     => 9990,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => self::PRODUCT2_ID,
                'sku'        => 'CADERNO-PRO',
                'name'       => 'Caderno Pro',
                'amount'     => 4990,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => self::PRODUCT3_ID,
                'sku'        => 'TELA-PORTATIL',
                'name'       => 'Tela portátil',
                'amount'     => 14990,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => self::PRODUCT4_ID,
                'sku'        => 'CAIXA-PAPEL',
                'name'       => 'Caixa de Papel',
                'amount'     => 2990,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => self::PRODUCT5_ID,
                'sku'        => 'GARRAFA-TERM',
                'name'       => 'Garrafa Térmica',
                'amount'     => 29990,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}