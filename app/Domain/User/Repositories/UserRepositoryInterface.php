<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;
use App\Domain\Shared\Email;

interface UserRepositoryInterface
{
    public function save(User $user): void;
    public function findById(string $id): ?User;
    public function findByEmail(Email $email): ?User;
    public function findAll(): array;
}
 