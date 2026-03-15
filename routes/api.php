<?php

use App\Infrastructure\Http\Controllers\AuthController;
use App\Infrastructure\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:admin')->group(function () {
    });

    // ADMIN + MANAGER — gerenciam usuários
    Route::middleware('role:admin,manager')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::patch('/users/{id}', [UserController::class, 'update']);
        Route::post('/users/{id}/deactivate', [UserController::class, 'deactivate']);
        Route::post('/users/{id}/activate', [UserController::class, 'activate']);
    });

    // ADMIN + MANAGER + FINANCE — gerenciam produtos
    Route::middleware('role:admin,manager,finance')->group(function () {
    });

    // ADMIN + FINANCE — reembolso
    Route::middleware('role:admin,finance')->group(function () {
    });
});
