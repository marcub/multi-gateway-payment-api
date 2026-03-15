<?php

declare(strict_types=1);

namespace App\Application\Transaction\DTOs;

class ChargeTransactionDTO
{
    public function __construct(
        public string $clientId,
        public string $cardNumber,
        public string $cvv,
        public array $items
    ) {
    }
}