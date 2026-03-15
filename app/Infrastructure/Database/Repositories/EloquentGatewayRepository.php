<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repositories;

use App\Domain\Gateway\Repositories\GatewayRepositoryInterface;
use App\Domain\Gateway\Entities\Gateway;
use App\Domain\Gateway\ValueObjects\GatewayId;
use App\Infrastructure\Database\Eloquent\Gateway as EloquentGateway;

class EloquentGatewayRepository implements GatewayRepositoryInterface
{
    public function save(Gateway $gateway): void
    {
        EloquentGateway::updateOrCreate(
            ['id' => (string)$gateway->getId()],
            [
                'name' => $gateway->getName(),
                'priority' => $gateway->getPriority(),
                'is_active' => $gateway->getIsActive(),
                'created_at' => $gateway->getCreatedAt(),
                'updated_at' => $gateway->getUpdatedAt()
            ]
        );
    }

    public function findById(string $id): ?Gateway
    {
        $eloquentGateway = EloquentGateway::find($id);

        if (!$eloquentGateway) {
            return null;
        }

        return new Gateway(
            id: new GatewayId($eloquentGateway->id),
            name: $eloquentGateway->name,
            priority: $eloquentGateway->priority,
            isActive: $eloquentGateway->is_active,
            createdAt: $eloquentGateway->created_at->toDateTimeImmutable(),
            updatedAt: $eloquentGateway->updated_at->toDateTimeImmutable()
        );
    }

}
