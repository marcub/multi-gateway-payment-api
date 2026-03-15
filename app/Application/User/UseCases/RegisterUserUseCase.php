<?php

declare(strict_types=1);

namespace App\Application\User\UseCases;

use App\Domain\User\Service\UserService;
use App\Domain\User\Entities\User;
use App\Application\User\DTOs\RegisterUserDTO;

class RegisterUserUseCase
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function execute(RegisterUserDTO $dto): User
    {
        return $this->userService->registerUser($dto->email, $dto->password, $dto->role);
    }
}
