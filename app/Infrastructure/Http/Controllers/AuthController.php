<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\User\DTOs\RegisterUserDTO;
use App\Application\User\UseCases\RegisterUserUseCase;
use App\Application\User\DTOs\AuthenticateUserDTO;
use App\Application\User\UseCases\AuthenticateUserUseCase;
use App\Infrastructure\Database\Eloquent\User as EloquentUser;
use App\Infrastructure\Http\Requests\RegisterUserRequest;
use App\Infrastructure\Http\Requests\AuthenticateUserRequest;
use App\Infrastructure\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController
{
    private $registerUserUseCase;
    private $authenticateUserUseCase;

    public function __construct(RegisterUserUseCase $registerUserUseCase, AuthenticateUserUseCase $authenticateUserUseCase)
    {
        $this->registerUserUseCase = $registerUserUseCase;
        $this->authenticateUserUseCase = $authenticateUserUseCase;
    }

    public function register(RegisterUserRequest $request)
    {
        $dto = new RegisterUserDTO(
            $request->input('email'),
            $request->input('password'),
            $request->input('role')
        );

        $user = $this->registerUserUseCase->execute($dto);

        $data = [
            'email' => (string) $user->getEmail(),
            'role' => $user->getRole()->value,
            'is_active' => $user->getIsActive(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s')
        ];

        return ApiResponse::success($data, 'User registered successfully', Response::HTTP_CREATED);
    }

    public function login(AuthenticateUserRequest $request)
    {
        $dto = new AuthenticateUserDTO(
            $request->input('email'),
            $request->input('password')
        );

        $user = $this->authenticateUserUseCase->execute($dto);

        $eloquentUser = EloquentUser::where('email', (string) $user->getEmail())->first();
        $token = $eloquentUser->createToken($user->getRole()->value . '-token')->plainTextToken;

        $data = [
            'token' => $token,
            'role'  => $user->getRole()->value
        ];

        return ApiResponse::success($data, 'Login successful');
    }

    public function logout(Request $request)
    {
        $request->user()?->tokens()?->delete();
        return ApiResponse::success([], 'Logout successful');
    }

}
