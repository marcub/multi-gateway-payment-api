<?php

declare(strict_types=1);

namespace App\Domain\Transaction\ValueObjects;

use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Transaction\Exceptions\TransactionItemException;

final class TransactionItem
{
    public function __construct(
        private ProductId $productId,
        private int $quantity,
        private int $unitAmount
    ) {
        if ($quantity < 1) {
            throw TransactionItemException::invalidQuantity();
        }

        if ($unitAmount <= 0) {
            throw TransactionItemException::invalidAmount();
        }
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitAmount(): int
    {
        return $this->unitAmount;
    }

    public function getSubtotal(): int
    {
        return $this->quantity * $this->unitAmount;
    }
}