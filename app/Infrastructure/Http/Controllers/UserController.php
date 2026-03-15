<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\User\UseCases\ListUsersUseCase;
use App\Application\User\UseCases\UpdateUserUseCase;
use App\Application\User\UseCases\ActivateUserUseCase;
use App\Application\User\UseCases\DeactivateUserUseCase;
use App\Application\User\UseCases\GetUserUseCase;
use App\Application\User\DTOs\UpdateUserDTO;
use App\Infrastructure\Http\Requests\UpdateUserRequest;
use App\Infrastructure\Http\Responses\ApiResponse;
use Illuminate\Http\Response;

class UserController
{
    private $listUsersUseCase;
    private $updateUserUseCase;
    private $getUserUseCase;
    private $activateUserUseCase;
    private $deactivateUserUseCase;

    public function __construct(ListUsersUseCase $listUsersUseCase, UpdateUserUseCase $updateUserUseCase, GetUserUseCase $getUserUseCase, ActivateUserUseCase $activateUserUseCase, DeactivateUserUseCase $deactivateUserUseCase)
    {
        $this->listUsersUseCase = $listUsersUseCase;
        $this->updateUserUseCase = $updateUserUseCase;
        $this->getUserUseCase = $getUserUseCase;
        $this->activateUserUseCase = $activateUserUseCase;
        $this->deactivateUserUseCase = $deactivateUserUseCase;
    }

    public function index()
    {
        $users = $this->listUsersUseCase->execute();

        $data = array_map(function ($user) {
            return [
                'id' => (string) $user->getId(),
                'email' => (string) $user->getEmail(),
                'role' => $user->getRole(),
                'is_active' => $user->getIsActive(),
                'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $users);

        return ApiResponse::success($data, 'Users retrieved successfully');
    }

    public function show(string $id)
    {
        $user = $this->getUserUseCase->execute($id);

        $data = [
            'id' => (string) $user->getId(),
            'email' => (string) $user->getEmail(),
            'role' => $user->getRole(),
            'is_active' => $user->getIsActive(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        return ApiResponse::success($data, 'User retrieved successfully');
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        $dto = new UpdateUserDTO(
            $id,
            $request->input('email'),
            $request->input('password'),
            $request->input('role')
        );

        $user = $this->updateUserUseCase->execute($dto);

        $data = [
            'email' => (string) $user->getEmail(),
            'role' => $user->getRole()->value,
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
        ];

        return ApiResponse::success($data, 'User updated successfully', Response::HTTP_OK);
    }

    public function deactivate(string $id)
    {
        $this->deactivateUserUseCase->execute($id);

        return ApiResponse::success([], 'User deactivated successfully', Response::HTTP_OK);
    }

    public function activate(string $id)
    {
        $this->activateUserUseCase->execute($id);

        return ApiResponse::success([], 'User activated successfully', Response::HTTP_OK);
    }

}
