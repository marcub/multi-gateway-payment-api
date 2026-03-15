<?php

declare(strict_types=1);

namespace App\Application\Transaction\DTOs;

class ChargeTransactionItemDTO
{
    public function __construct(
        public string $productId,
        public int $quantity,
        public int $unitAmount
    ) {
    }
}