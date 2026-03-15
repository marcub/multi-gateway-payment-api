<?php

namespace Database\Seeders;

use App\Infrastructure\Database\Eloquent\Transaction;
use App\Infrastructure\Database\Eloquent\TransactionItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $now   = now();
        $t1_id = 'dddddddd-0000-4000-d000-000000000001';
        $t2_id = 'dddddddd-0000-4000-d000-000000000002';
        $t3_id = 'dddddddd-0000-4000-d000-000000000003';
        $t4_id = 'dddddddd-0000-4000-d000-000000000004';

        Transaction::create([
            'id'               => $t1_id,
            'client_id'        => ClientSeeder::CLIENT1_ID,
            'gateway_id'       => GatewaySeeder::GATEWAY1_ID,
            'external_id'      => 'ext-gw1-001',
            'status'           => 'paid',
            'amount'           => 24970,
            'card_last_numbers'=> '4242',
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
        TransactionItem::insert([
            [
                'id'             => Str::uuid(),
                'transaction_id' => $t1_id,
                'product_id'     => ProductSeeder::PRODUCT1_ID,
                'quantity'       => 2,
                'unit_amount'    => 9990,
            ],
            [
                'id'             => Str::uuid(),
                'transaction_id' => $t1_id,
                'product_id'     => ProductSeeder::PRODUCT2_ID,
                'quantity'       => 1,
                'unit_amount'    => 4990,
            ],
        ]);

        Transaction::create([
            'id'               => $t2_id,
            'client_id'        => ClientSeeder::CLIENT2_ID,
            'gateway_id'       => GatewaySeeder::GATEWAY2_ID,
            'external_id'      => 'ext-gw2-001',
            'status'           => 'paid',
            'amount'           => 14990,
            'card_last_numbers'=> '1111',
            'created_at'       => $now->copy()->subHours(2),
            'updated_at'       => $now->copy()->subHours(2),
        ]);
        TransactionItem::insert([
            [
                'id'             => Str::uuid(),
                'transaction_id' => $t2_id,
                'product_id'     => ProductSeeder::PRODUCT3_ID,
                'quantity'       => 1,
                'unit_amount'    => 14990,
            ],
        ]);

        Transaction::create([
            'id'               => $t3_id,
            'client_id'        => ClientSeeder::CLIENT3_ID,
            'gateway_id'       => null,
            'external_id'      => null,
            'status'           => 'failed',
            'amount'           => 8970,
            'card_last_numbers'=> '9999',
            'created_at'       => $now->copy()->subHours(5),
            'updated_at'       => $now->copy()->subHours(5),
        ]);
        TransactionItem::insert([
            [
                'id'             => Str::uuid(),
                'transaction_id' => $t3_id,
                'product_id'     => ProductSeeder::PRODUCT4_ID,
                'quantity'       => 3,
                'unit_amount'    => 2990,
            ],
        ]);

        Transaction::create([
            'id'               => $t4_id,
            'client_id'        => ClientSeeder::CLIENT1_ID,
            'gateway_id'       => GatewaySeeder::GATEWAY1_ID,
            'external_id'      => 'ext-gw1-002',
            'status'           => 'refunded',
            'amount'           => 29990,
            'card_last_numbers'=> '5678',
            'created_at'       => $now->copy()->subDay(),
            'updated_at'       => $now->copy()->subHours(1),
        ]);
        TransactionItem::insert([
            [
                'id'             => Str::uuid(),
                'transaction_id' => $t4_id,
                'product_id'     => ProductSeeder::PRODUCT5_ID,
                'quantity'       => 1,
                'unit_amount'    => 29990,
            ],
        ]);
    }
}