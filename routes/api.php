<?php

use App\Infrastructure\Http\Controllers\AuthController;
use App\Infrastructure\Http\Controllers\GatewayController;
use App\Infrastructure\Http\Controllers\UserController;
use App\Infrastructure\Http\Controllers\ClientController;
use App\Infrastructure\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/gateways/{id}/activate', [GatewayController::class, 'activate']);
        Route::post('/gateways/{id}/deactivate', [GatewayController::class, 'deactivate']);
        Route::patch('/gateways/{id}/priority', [GatewayController::class, 'changePriority']);
    });

    // ADMIN + MANAGER — gerenciam usuários
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::patch('/users/{id}', [UserController::class, 'update']);
        Route::post('/users/{id}/deactivate', [UserController::class, 'deactivate']);
        Route::post('/users/{id}/activate', [UserController::class, 'activate']);
    });

    // ADMIN + MANAGER + FINANCE — gerenciam produtos
    Route::middleware('role:admin,manager,finance')->group(function () {
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
        Route::post('/products', [ProductController::class, 'create']);
        Route::patch('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'delete']);
    });

    // ADMIN + FINANCE — reembolso
    Route::middleware('role:admin,finance')->group(function () {
    });

    Route::middleware('role:admin,user')->group(function () {
        Route::get('/clients', [ClientController::class, 'index']);
        Route::get('/clients/{id}', [ClientController::class, 'show']);
    });
});
