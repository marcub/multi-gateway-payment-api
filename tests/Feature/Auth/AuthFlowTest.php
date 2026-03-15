<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Database\Eloquent\User as EloquentUser;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $overrides = []): EloquentUser
    {
        return EloquentUser::create(array_merge([
            'id' => Str::uuid()->toString(),
            'email' => 'test@test.com',
            'password' => password_hash('correct-password', PASSWORD_BCRYPT),
            'role' => 'user',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_login_returns_token_with_valid_credentials(): void
    {
        $this->createUser(['email' => 'teste@test.com']);

        $response = $this->postJson('/api/login', [
            'email'    => 'teste@test.com',
            'password' => 'correct-password',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'role'],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.role', 'user');
    }

    public function test_login_returns_401_with_wrong_password(): void
    {
        $this->createUser(['email' => 'teste@test.com']);

        $response = $this->postJson('/api/login', [
            'email'    => 'teste@test.com',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertStatus(401)
            ->assertJsonStructure([
                'success',
                'message'
            ])
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid credentials.');
    }

    public function test_login_returns_403_when_user_is_inactive(): void
    {
        $this->createUser(['email' => 'teste@test.com', 'is_active' => false]);

        $response = $this->postJson('/api/login', [
            'email'    => 'teste@test.com',
            'password' => 'correct-password',
        ]);

        $response
            ->assertStatus(403)
            ->assertJsonStructure([
                'success',
                'message'
            ])
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'User is inactive.');
    }

    public function test_logout_revokes_token(): void
    {
        $this->createUser(['email' => 'teste@test.com']);

        $token = $this->postJson('/api/login', [
            'email'    => 'teste@test.com',
            'password' => 'correct-password',
        ])->json('data.token');

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->postJson('/api/logout', [], ['Authorization' => 'Bearer ' . $token])
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Logout successful');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_protected_route_returns_401_without_token(): void
    {
        $this->postJson('/api/logout')
            ->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_user_role_cannot_access_manager_routes(): void
    {
        $user = $this->createUser(['email' => 'user@test.com', 'role' => 'user']);

        $token = $this->postJson('/api/login', [
            'email'    => 'user@test.com',
            'password' => 'correct-password',
        ])->json('data.token');

        $this->getJson('/api/users', ['Authorization' => 'Bearer ' . $token])
            ->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthorized');
    }
}
