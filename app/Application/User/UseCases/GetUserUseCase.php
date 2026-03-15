<?php

declare(strict_types=1);

namespace App\Application\User\UseCases;

use App\Domain\User\Service\UserService;

class GetUserUseCase
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function execute(string $id)
    {
        return $this->userService->getUser($id);
    }
}
