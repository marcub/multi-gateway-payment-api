<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Database\Eloquent\Product as EloquentProduct;
use App\Infrastructure\Database\Eloquent\User as EloquentUser;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(array $overrides = []): EloquentProduct
    {
        return EloquentProduct::create(array_merge([
            'id' => Str::uuid()->toString(),
            'sku' => 'TEST-001',
            'name' => 'Test Product',
            'amount' => 15000,
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

    public function test_unauthenticated_user_cannot_access_products(): void
    {
        $product = $this->createProduct();

        $this->getJson("/api/products", [])->assertStatus(401);
        $this->getJson("/api/products/{$product->id}", [])->assertStatus(401);
        $this->postJson("/api/products", [
            'sku' => 'TEST-002',
            'name' => 'Another Product',
            'amount' => 20000,
        ])->assertStatus(401);
        $this->deleteJson("/api/products/{$product->id}", [])->assertStatus(401);
        $this->patchJson("/api/products/{$product->id}", [
            'name' => 'Updated Name',
            'sku' => 'Updated SKU',
            'amount' => 20000,
        ])->assertStatus(401);
    }

    public function test_role_user_cannot_access_products(): void
    {
        $product = $this->createProduct();

        $this->createUser([
            'email' => 'teste@test.com',
            'role' => 'user',
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'teste@test.com',
            'password' => 'correct-password',
        ])->json('data.token');

        $this->getJson("/api/products", [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(403);
        $this->getJson("/api/products/{$product->id}", [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(403);
        $this->postJson("/api/products", [
            'sku' => 'TEST-002',
            'name' => 'Another Product',
            'amount' => 20000,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(403);
        $this->deleteJson("/api/products/{$product->id}", [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(403);
        $this->patchJson("/api/products/{$product->id}", [
            'name' => 'Updated Name',
            'sku' => 'Updated SKU',
            'amount' => 20000,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(403);
    }

    public function test_manager_can_create_product(): void
    {
        $this->createUser([
            'email' => 'manager@test.com',
            'role' => 'manager',
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'manager@test.com',
            'password' => 'correct-password',
        ])->json('data.token');

        $this->postJson("/api/products/", 
        [
            'sku' => 'TEST-002',
            'name' => 'Another Product',
            'amount' => 20000,
        ], 
        [
            'Authorization' => 'Bearer ' . $token,
        ]
        )->assertStatus(201)->assertJsonPath('success', true);
    }

    public function test_manager_can_update_product(): void
    {
        $product = $this->createProduct();

        $this->createUser([
            'email' => 'manager@test.com',
            'role' => 'manager',
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'manager@test.com',
            'password' => 'correct-password',
        ])->json('data.token');

        $this->patchJson("/api/products/{$product->id}", 
        [
            'sku' => 'TEST-002',
            'name' => 'Another Product',
            'amount' => 20000,
        ], 
        [
            'Authorization' => 'Bearer ' . $token,
        ]
        )->assertStatus(200)->assertJsonPath('success', true);
    }

    public function test_wrong_payload_pattern(): void
    {
        $this->createUser([
            'email' => 'manager@test.com',
            'role' => 'manager',
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'manager@test.com',
            'password' => 'correct-password',
        ])->json('data.token');

        $this->postJson("/api/products/", 
        [
            'sku' => 'TEST-002',
            'name' => 'Another Product'
        ], 
        [
            'Authorization' => 'Bearer ' . $token,
        ]
        )->assertStatus(422);
    }
}
