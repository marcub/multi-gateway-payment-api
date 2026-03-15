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
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Infrastructure\Database\Repositories\EloquentTransactionRepository;
use App\Infrastructure\Gateway\Gateway1Client;
use App\Infrastructure\Gateway\Gateway2Client;
use App\Domain\Transaction\Support\GatewayClientRegistry;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(GatewayRepositoryInterface::class, EloquentGatewayRepository::class);
        $this->app->bind(ClientRepositoryInterface::class, EloquentClientRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, EloquentTransactionRepository::class);

        $this->app->singleton(GatewayClientRegistry::class, function ($app) {
            return new GatewayClientRegistry([
                $app->make(Gateway1Client::class),
                $app->make(Gateway2Client::class),
            ]);
        });
    }
}
