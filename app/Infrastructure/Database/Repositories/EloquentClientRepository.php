<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repositories;

use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Domain\Client\Entities\Client;
use App\Domain\Shared\Email;
use App\Domain\Client\ValueObjects\ClientId;
use App\Infrastructure\Database\Eloquent\Client as EloquentClient;

class EloquentClientRepository implements ClientRepositoryInterface
{

    public function findById(string $id): ?Client
    {
        $eloquentClient = EloquentClient::find($id);

        if (!$eloquentClient) {
            return null;
        }

        return new Client(
            id: new ClientId($eloquentClient->id),
            name: $eloquentClient->name,
            email: new Email($eloquentClient->email),
            createdAt: $eloquentClient->created_at->toDateTimeImmutable(),
            updatedAt: $eloquentClient->updated_at->toDateTimeImmutable()
        );
    }

    public function findAll(): array
    {
        $eloquentClients = EloquentClient::all();

        return $eloquentClients->map(function ($eloquentClient) {
            return new Client(
                id: new ClientId($eloquentClient->id),
                name: $eloquentClient->name,
                email: new Email($eloquentClient->email),
                createdAt: $eloquentClient->created_at->toDateTimeImmutable(),
                updatedAt: $eloquentClient->updated_at->toDateTimeImmutable()
            );
        })->toArray();
    }

}
