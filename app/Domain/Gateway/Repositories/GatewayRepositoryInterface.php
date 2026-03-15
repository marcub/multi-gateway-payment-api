<?php

declare(strict_types=1);

namespace App\Domain\Gateway\Repositories;

use App\Domain\Gateway\Entities\Gateway;

interface GatewayRepositoryInterface
{
    public function save(Gateway $gateway): void;
    public function findById(string $id): ?Gateway;
}
 