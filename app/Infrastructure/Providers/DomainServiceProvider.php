<?php

namespace App\Infrastructure\Providers;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\Gateway\Repositories\GatewayRepositoryInterface;
use App\Infrastructure\Database\Repositories\EloquentGatewayRepository;
use App\Infrastructure\Database\Repositories\EloquentUserRepository;
use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Infrastructure\Database\Repositories\EloquentClientRepository;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Database\Repositories\EloquentProductRepository;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(GatewayRepositoryInterface::class, EloquentGatewayRepository::class);
        $this->app->bind(ClientRepositoryInterface::class, EloquentClientRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
    }
}
