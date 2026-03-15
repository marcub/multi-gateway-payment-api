<?php

namespace Tests\Feature\Transaction;

use App\Infrastructure\Database\Eloquent\User as EloquentUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TransactionFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $overrides = []): EloquentUser
    {
        return EloquentUser::create(array_merge([
            'id' => Str::uuid()->toString(),
            'email' => 'test@test.com',
            'password' => password_hash('correct-password', PASSWORD_BCRYPT),
            'role' => 'admin',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    private function login(string $email): string
    {
        return $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'correct-password',
        ])->json('data.token');
    }

    public function test_unauthenticated_user_cannot_access_transaction_endpoints(): void
    {
        $this->getJson('/api/transactions')->assertStatus(401);

        $this->postJson('/api/transactions', [
            'client_id' => Str::uuid()->toString(),
            'card_number' => '5569000000006063',
            'cvv' => '010',
            'items' => [
                [
                    'product_id' => Str::uuid()->toString(),
                    'quantity' => 1,
                    'unit_amount' => 1000,
                ],
            ],
        ])->assertStatus(401);

        $this->postJson('/api/transactions/' . Str::uuid()->toString() . '/refund')
            ->assertStatus(401);
    }

    public function test_role_user_cannot_charge_transaction(): void
    {
        $this->createUser([
            'email' => 'user@test.com',
            'role' => 'user',
        ]);

        $token = $this->login('user@test.com');

        $this->postJson('/api/transactions', [
            'client_id' => Str::uuid()->toString(),
            'card_number' => '5569000000006063',
            'cvv' => '010',
            'items' => [
                [
                    'product_id' => Str::uuid()->toString(),
                    'quantity' => 1,
                    'unit_amount' => 1000,
                ],
            ],
        ], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(403);
    }

    public function test_admin_gets_422_on_invalid_charge_payload(): void
    {
        $this->createUser([
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        $token = $this->login('admin@test.com');

        $this->postJson('/api/transactions', [
            // payload inválido de propósito
            'client_id' => 'not-uuid',
            'items' => [],
        ], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(422);
    }

    public function test_finance_can_access_refund_route_and_gets_404_when_transaction_does_not_exist(): void
    {
        $this->createUser([
            'email' => 'finance@test.com',
            'role' => 'finance',
        ]);

        $token = $this->login('finance@test.com');

        $this->postJson('/api/transactions/' . Str::uuid()->toString() . '/refund', [], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(404);
    }
}