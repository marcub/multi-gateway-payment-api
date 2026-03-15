<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Database\Eloquent\Gateway as EloquentGateway;
use App\Infrastructure\Database\Eloquent\User as EloquentUser;
use Illuminate\Support\Str;
use Tests\TestCase;

class GatewayFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createGateway(array $overrides = []): EloquentGateway
    {
        return EloquentGateway::create(array_merge([
            'id' => Str::uuid()->toString(),
            'name' => 'Test Gateway',
            'is_active' => true,
            'priority' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

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

    public function test_admin_can_activate_gateway(): void
    {
        $gateway = $this->createGateway([
            'name' => 'Test Gateway',
            'is_active' => false,
        ]);

        $this->createUser([
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'correct-password',
        ])->json('data.token');

        $this->postJson("/api/gateways/{$gateway->id}/activate", [], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(200)->assertJsonPath('success', true);

        $this->assertDatabaseHas('gateways', [
            'id' => $gateway->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_change_gateway_priority(): void
    {
        $gateway = $this->createGateway();

        $this->createUser([
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'correct-password',
        ])->json('data.token');

        $this->patchJson("/api/gateways/{$gateway->id}/priority", 
        [
            'priority' => 2,
        ], 
        [
            'Authorization' => 'Bearer ' . $token,
        ]
        )->assertStatus(200)->assertJsonPath('success', true);

        $this->assertDatabaseHas('gateways', [
            'id' => $gateway->id,
            'is_active' => true,
            'priority' => 2,
        ]);
    }

    public function test_non_admin_cannot_manage_gateways(): void
    {
        $gateway = $this->createGateway([
            'name' => 'Test Gateway',
            'is_active' => false,
        ]);

        $this->createUser([
            'email' => 'admin@test.com',
            'role' => 'user',
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'correct-password',
        ])->json('data.token');

        $this->postJson("/api/gateways/{$gateway->id}/activate", [], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_manage_gateways(): void
    {
        $gateway = $this->createGateway([
            'name' => 'Test Gateway',
            'is_active' => false,
        ]);

        $this->postJson("/api/gateways/{$gateway->id}/activate", [])->assertStatus(401);
        $this->postJson("/api/gateways/{$gateway->id}/deactivate", [])->assertStatus(401);
        $this->patchJson("/api/gateways/{$gateway->id}/priority", [
            'priority' => 2,
        ])->assertStatus(401);
    }
}
