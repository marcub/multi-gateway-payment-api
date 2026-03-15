<?php

declare(strict_types=1);

namespace App\Domain\Transaction\Contracts;

interface GatewayClientInterface
{
    public function supports(string $gatewayName): bool;
    public function charge(array $payload): array;
    public function refund(string $externalId): void;
}