<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repositories;

use App\Domain\User\Entities\User;
use App\Domain\Shared\Email;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Role;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Database\Eloquent\User as EloquentUser;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function save(User $user): void
    {
        EloquentUser::updateOrCreate(
            ['email' => (string)$user->getEmail()],
            [
                'id' => (string)$user->getId(),
                'password' => $user->getHashedPassword(),
                'role' => $user->getRole()->value,
                'is_active' => $user->getIsActive(),
                'created_at' => $user->getCreatedAt(),
                'updated_at' => $user->getUpdatedAt(),
            ]
        );
    }

    public function findById(string $id): ?User
    {
        $eloquentUser = EloquentUser::find($id);

        if (!$eloquentUser) {
            return null;
        }

        return new User(
            id: new UserId($eloquentUser->id),
            email: new Email($eloquentUser->email),
            hashedPassword: $eloquentUser->password,
            role: Role::from($eloquentUser->role),
            isActive: $eloquentUser->is_active,
            createdAt: $eloquentUser->created_at->toDateTimeImmutable(),
            updatedAt: $eloquentUser->updated_at->toDateTimeImmutable()
        );
    }

    public function findByEmail(Email $email): ?User
    {
        $eloquentUser = EloquentUser::where('email', (string)$email)->first();

        if (!$eloquentUser) {
            return null;
        }

        return new User(
            id: new UserId($eloquentUser->id),
            email: new Email($eloquentUser->email),
            hashedPassword: $eloquentUser->password,
            role: Role::from($eloquentUser->role),
            isActive: $eloquentUser->is_active,
            createdAt: $eloquentUser->created_at->toDateTimeImmutable(),
            updatedAt: $eloquentUser->updated_at->toDateTimeImmutable()
        );
    }

    public function findAll(): array
    {
        $eloquentUsers = EloquentUser::all();

        return $eloquentUsers->map(function ($eloquentUser) {
            return new User(
                id: new UserId($eloquentUser->id),
                email: new Email($eloquentUser->email),
                hashedPassword: $eloquentUser->password,
                role: Role::from($eloquentUser->role),
                isActive: $eloquentUser->is_active,
                createdAt: $eloquentUser->created_at->toDateTimeImmutable(),
                updatedAt: $eloquentUser->updated_at->toDateTimeImmutable()
            );
        })->toArray();
    }
}
