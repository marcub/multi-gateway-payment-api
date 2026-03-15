<?php

namespace Tests\Unit\Domain\User\Service;

use App\Domain\User\Entities\User;
use App\Domain\User\Exceptions\UserException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Service\UserService;
use App\Domain\Shared\Email;
use App\Domain\User\ValueObjects\Role;
use App\Domain\User\ValueObjects\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Str;

class UserServiceTest extends TestCase
{ 
    private function makeUser(
        ?string $id = null,
        string $email = 'teste@test.com',
        string $plainPassword = 'correct-password',
        Role $role = Role::USER,
        bool $isActive = true,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ): User {
        $id = $id ?? Str::uuid()->toString();
        $createdAt = $createdAt ?? new DateTimeImmutable('2020-01-01 00:00:00');
        $updatedAt = $updatedAt ?? new DateTimeImmutable('2020-01-01 00:00:00');

        return new User(
            new UserId($id),
            new Email($email),
            password_hash($plainPassword, PASSWORD_BCRYPT),
            $role,
            $isActive,
            $createdAt,
            $updatedAt
        );
    }

    public function test_authenticate_throws_invalid_credentials_when_password_is_wrong(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->never())->method('save');
        $userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($this->isInstanceOf(Email::class))
            ->willReturn($this->makeUser(role: Role::ADMIN, isActive: true));

        $userService = new UserService($userRepository);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Invalid credentials.');

        $userService->authenticateUser('teste@test.com', 'wrong-password');
    }

    public function test_authenticate_throws_invalid_credentials_when_user_not_found(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->never())->method('save');
        $userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($this->callback(fn (Email $e) => (string) $e === 'missing@test.com'))
            ->willReturn(null);

        $userService = new UserService($userRepository);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Invalid credentials.');

        $userService->authenticateUser('missing@test.com', 'any-password');
    }

    public function test_authenticate_throws_inactive_when_user_is_deactivated(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())
            ->method('findByEmail')
            ->willReturn($this->makeUser(isActive: false));

        $userService = new UserService($userRepository);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('User is inactive.');

        $userService->authenticateUser('teste@test.com', 'correct-password');
    }

    public function test_authenticate_returns_user_when_credentials_are_valid(): void
    {
        $user = $this->makeUser(email: 'ok@test.com', role: Role::ADMIN, isActive: true);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())
            ->method('findByEmail')
            ->willReturn($user);

        $userService = new UserService($userRepository);

        $result = $userService->authenticateUser('ok@test.com', 'correct-password');

        $this->assertSame('ok@test.com', (string) $result->getEmail());
        $this->assertSame('admin', $result->getRole()->value);
    }

    public function test_register_throws_already_exists_when_email_already_registered(): void
    {
        $existing = $this->makeUser(email: 'teste@test.com');

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($this->callback(fn (Email $e) => (string) $e === 'teste@test.com'))
            ->willReturn($existing);
        $userRepository->expects($this->never())->method('save');

        $userService = new UserService($userRepository);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('User already exists.');

        $userService->registerUser('teste@test.com', 'correct-password', 'user');
    }

    public function test_register_throws_invalid_role_when_invalid_role_is_provided(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findByEmail')->willReturn(null);
        $userRepository->expects($this->never())->method('save');

        $userService = new UserService($userRepository);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Invalid role specified.');

        $userService->registerUser('teste@test.com', 'correct-password', 'invalid-role');
    }

    public function test_register_throws_invalid_password_when_password_is_too_short(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findByEmail')->willReturn(null);
        $userRepository->expects($this->never())->method('save');

        $userService = new UserService($userRepository);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Password must be at least 8 characters long.');

        $userService->registerUser('teste@test.com', '123', 'user');
    }

    public function test_register_saves_and_returns_user_when_payload_is_valid(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())->method('findByEmail')->willReturn(null);
        $userRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        $userService = new UserService($userRepository);

        $user = $userService->registerUser('new@test.com', 'correct-password', 'manager');

        $this->assertSame('new@test.com', (string) $user->getEmail());
        $this->assertSame('manager', $user->getRole()->value);
        $this->assertTrue($user->getIsActive());
    }

    public function test_update_throws_not_found_when_user_does_not_exist(): void
    {
        $missingId = Str::uuid()->toString();
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())->method('findById')->with($missingId)->willReturn(null);
        $userRepository->expects($this->never())->method('save');

        $userService = new UserService($userRepository);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('User not found.');

        $userService->updateUser($missingId, 'a@test.com', null, null);
    }

    public function test_update_throws_nothing_to_update_when_payload_has_no_effective_changes(): void
    {
        $updateId = Str::uuid()->toString();
        $user = $this->makeUser(id: $updateId, email: 'same@test.com', plainPassword: 'same-password', role: Role::USER);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findById')->willReturn($user);
        $userRepository->expects($this->never())->method('save');

        $userService = new UserService($userRepository);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('No valid fields provided for update.');

        $userService->updateUser(
            $updateId,
            'same@test.com',
            'same-password',
            'user'
        );
    }

    public function test_update_throws_already_exists_when_new_email_is_already_used_by_another_user(): void
    {
        $currentId = Str::uuid()->toString();
        $current = $this->makeUser(
            id: $currentId,
            email: 'current@test.com'
        );

        $otherId = Str::uuid()->toString();
        $other = $this->makeUser(
            id: $otherId,
            email: 'used@test.com'
        );

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findById')->willReturn($current);
        $userRepository->method('findByEmail')->willReturn($other);
        $userRepository->expects($this->never())->method('save');

        $userService = new UserService($userRepository);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('User already exists.');

        $userService->updateUser($currentId, 'used@test.com', null, null);
    }

    public function test_update_saves_and_returns_user_when_changes_are_valid(): void
    {
        $currentId = Str::uuid()->toString();
        $current = $this->makeUser(
            id: $currentId,
            email: 'old@test.com',
            plainPassword: 'old-password',
            role: Role::USER,
            isActive: true,
            createdAt: new DateTimeImmutable('2020-01-01 00:00:00'),
            updatedAt: new DateTimeImmutable('2020-01-01 00:00:00')
        );

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findById')->willReturn($current);
        $userRepository->method('findByEmail')->willReturn(null);
        $userRepository->expects($this->once())->method('save')->with($this->isInstanceOf(User::class));

        $userService = new UserService($userRepository);

        $updated = $userService->updateUser(
            $currentId,
            'new@test.com',
            'new-password',
            'admin'
        );

        $this->assertSame('new@test.com', (string) $updated->getEmail());
        $this->assertSame('admin', $updated->getRole()->value);
        $this->assertTrue(password_verify('new-password', $updated->getHashedPassword()));
    }

    public function test_get_user_throws_not_found_when_missing(): void
    {
        $missingId = Str::uuid()->toString();
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())->method('findById')->willReturn(null);

        $userService = new UserService($userRepository);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('User not found.');

        $userService->getUser($missingId);
    }

    public function test_get_user_returns_user_when_exists(): void
    {
        $userId = Str::uuid()->toString();
        $user = $this->makeUser(id: $userId);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())->method('findById')->willReturn($user);

        $userService = new UserService($userRepository);

        $result = $userService->getUser($userId);

        $this->assertSame((string) $user->getId(), (string) $result->getId());
    }

    public function test_list_users_returns_repository_list(): void
    {
        $userId1 = Str::uuid()->toString();
        $userId2 = Str::uuid()->toString();
        $users = [
            $this->makeUser(id: $userId1),
            $this->makeUser(id: $userId2)
        ];

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())->method('findAll')->willReturn($users);

        $userService = new UserService($userRepository);

        $result = $userService->listUsers();

        $this->assertCount(2, $result);
    }

    public function test_activate_user_throws_not_found_when_missing(): void
    {
        $missingId = Str::uuid()->toString();
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findById')->willReturn(null);
        $userRepository->expects($this->never())->method('save');

        $userService = new UserService($userRepository);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('User not found.');

        $userService->activateUser($missingId);
    }

    public function test_activate_user_throws_already_active_when_user_is_active(): void
    {
        $userId = Str::uuid()->toString();
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findById')->willReturn($this->makeUser(id: $userId, isActive: true));
        $userRepository->expects($this->never())->method('save');

        $userService = new UserService($userRepository);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('User is already active.');

        $userService->activateUser($userId);
    }

    public function test_activate_user_saves_when_user_is_inactive(): void
    {
        $inactiveId = Str::uuid()->toString();
        $inactive = $this->makeUser(id: $inactiveId, isActive: false);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findById')->willReturn($inactive);
        $userRepository->expects($this->once())->method('save')->with($this->callback(function (User $u): bool {
            return $u->getIsActive() === true;
        }));

        $userService = new UserService($userRepository);

        $userService->activateUser($inactiveId);
    }

    public function test_deactivate_user_throws_not_found_when_missing(): void
    {
        $missingId = Str::uuid()->toString();
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findById')->willReturn(null);
        $userRepository->expects($this->never())->method('save');

        $userService = new UserService($userRepository);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('User not found.');

        $userService->deactivateUser($missingId);
    }

    public function test_deactivate_user_throws_already_inactive_when_user_is_inactive(): void
    {
        $userId = Str::uuid()->toString();
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findById')->willReturn($this->makeUser(id: $userId, isActive: false));
        $userRepository->expects($this->never())->method('save');

        $userService = new UserService($userRepository);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('User is already inactive.');

        $userService->deactivateUser($userId);
    }

    public function test_deactivate_user_saves_when_user_is_active(): void
    {
        $userId = Str::uuid()->toString();
        $active = $this->makeUser(id: $userId, isActive: true);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findById')->willReturn($active);
        $userRepository->expects($this->once())->method('save')->with($this->callback(function (User $u): bool {
            return $u->getIsActive() === false;
        }));

        $userService = new UserService($userRepository);

        $userService->deactivateUser($userId);
    }
}
