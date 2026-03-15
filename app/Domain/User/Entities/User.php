<?php

declare(strict_types=1);

namespace App\Domain\User\Entities;

use App\Domain\Shared\Email;
use App\Domain\User\ValueObjects\Role;
use App\Domain\User\ValueObjects\UserId;

use DateTimeImmutable;

class User
{

    public function __construct(
        private UserId $id,
        private Email $email,
        private string $hashedPassword,
        private Role $role,
        private bool $isActive,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function setEmail(Email $email): void
    {
        $this->email = $email;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): void
    {
        $this->role = $role;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getHashedPassword(): string
    {
        return $this->hashedPassword;
    }

    public function setHashedPassword(string $hashedPassword): void
    {
        $this->hashedPassword = $hashedPassword;
    }
}
