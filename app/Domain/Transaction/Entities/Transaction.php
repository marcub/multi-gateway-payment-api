<?php

declare(strict_types=1);

namespace App\Domain\Transaction\Entities;

use App\Domain\Transaction\ValueObjects\TransactionId;
use App\Domain\Client\ValueObjects\ClientId;
use App\Domain\Gateway\ValueObjects\GatewayId;
use App\Domain\Transaction\ValueObjects\TransactionStatus;
use App\Domain\Transaction\ValueObjects\TransactionItem;
use App\Domain\Transaction\Exceptions\TransactionException;

use DateTimeImmutable;

class Transaction
{

    public function __construct(
        private TransactionId $id,
        private ClientId $clientId,
        private ?GatewayId $gatewayId,
        private ?string $externalId,
        private TransactionStatus $status,
        private int $amount,
        private string $cardLastNumbers,
        private array $items,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {
        if ($amount <= 0) {
            throw TransactionException::invalidAmount();
        }

        if (!preg_match('/^\d{4}$/', $cardLastNumbers)) {
            throw TransactionException::invalidCardLastNumbers();
        }

        if ($items === []) {
            throw TransactionException::emptyItems();
        }

        foreach ($items as $item) {
            if (!$item instanceof TransactionItem) {
                throw TransactionException::invalidItemsType();
            }
        }

        if ($this->calculateItemsTotal() !== $amount) {
            throw TransactionException::amountMismatch();
        }
    }

    public function getId(): TransactionId
    {
        return $this->id;
    }

    public function getClientId(): ClientId
    {
        return $this->clientId;
    }

    public function getGatewayId(): ?GatewayId
    {
        return $this->gatewayId;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function getStatus(): TransactionStatus
    {
        return $this->status;
    }

    public function setStatus(TransactionStatus $status): void
    {
        $this->status = $status;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCardLastNumbers(): string
    {
        return $this->cardLastNumbers;
    }

    /**
     * @return TransactionItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    private function calculateItemsTotal(): int
    {
        return array_reduce($this->items, function ($carry, TransactionItem $item) {
            return $carry + $item->getSubtotal();
        }, 0);
    }

}
