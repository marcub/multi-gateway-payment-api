<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Infrastructure\Http\Middlewares\ForceJsonRequestHeader::class);
        $middleware->group('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class
        ]);
        $middleware->alias([
            'role' => \App\Infrastructure\Http\Middlewares\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            return \App\Infrastructure\Http\Responses\ApiResponse::error(
                'Unauthenticated.',
                401
            );
        });
        
        $exceptions->render(function (\App\Domain\User\Exceptions\UserException $e, $request) {
            return \App\Infrastructure\Http\Responses\ApiResponse::error(
                $e->getMessage(),
                $e->getCode() ?: 422
            );
        });

        $exceptions->render(function (\App\Domain\Gateway\Exceptions\GatewayException $e, $request) {
            return \App\Infrastructure\Http\Responses\ApiResponse::error(
                $e->getMessage(),
                $e->getCode() ?: 422
            );
        });

        $exceptions->render(function (\App\Domain\Client\Exceptions\ClientException $e, $request) {
            return \App\Infrastructure\Http\Responses\ApiResponse::error(
                $e->getMessage(),
                $e->getCode() ?: 422
            );
        });

        $exceptions->render(function (\App\Domain\Product\Exceptions\ProductException $e, $request) {
            return \App\Infrastructure\Http\Responses\ApiResponse::error(
                $e->getMessage(),
                $e->getCode() ?: 422
            );
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            return \App\Infrastructure\Http\Responses\ApiResponse::error(
                'Validation failed.',
                422,
                $e->errors()
            );
        });

        $exceptions->render(function (\Throwable $e, $request) {
            report($e);

            if (config('app.debug')) {
                return \App\Infrastructure\Http\Responses\ApiResponse::error(
                    $e->getMessage(),
                    500
                );
            }

            return \App\Infrastructure\Http\Responses\ApiResponse::error(
                'Internal server error.',
                500
            );
        });
    })->create();
