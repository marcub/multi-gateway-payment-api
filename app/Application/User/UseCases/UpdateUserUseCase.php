<?php

declare(strict_types=1);

namespace App\Application\User\UseCases;

use App\Domain\User\Service\UserService;
use App\Domain\User\Entities\User;
use App\Application\User\DTOs\UpdateUserDTO;

class UpdateUserUseCase
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function execute(UpdateUserDTO $dto): User
    {
        return $this->userService->updateUser($dto->id, $dto->email, $dto->password, $dto->role);
    }
}