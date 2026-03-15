<?php

declare(strict_types=1);

namespace App\Application\User\UseCases;

use App\Domain\User\Service\UserService;
use App\Domain\User\Entities\User;
use App\Application\User\DTOs\AuthenticateUserDTO;

class AuthenticateUserUseCase
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function execute(AuthenticateUserDTO $dto): User
    {
        return $this->userService->authenticateUser($dto->email, $dto->password);
    }
}
