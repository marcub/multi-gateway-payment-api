<?php

declare(strict_types=1);

namespace App\Domain\Transaction\Support;

use App\Domain\Gateway\Entities\Gateway;
use App\Domain\Transaction\Contracts\GatewayClientInterface;
use App\Domain\Transaction\Exceptions\TransactionException;

class GatewayClientRegistry
{
    public function __construct(private array $clients)
    {
    }

    public function forGateway(Gateway $gateway): GatewayClientInterface
    {
        foreach ($this->clients as $client) {
            if ($client->supports($gateway->getName())) {
                return $client;
            }
        }

        throw TransactionException::gatewayClientNotFound($gateway->getName());
    }
}