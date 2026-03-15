<?php

declare(strict_types=1);

namespace App\Domain\Transaction\Exceptions;

use DomainException;
use Throwable;

class TransactionException extends DomainException
{
    public static function invalidAmount(): self
    {
        return new self('Invalid transaction amount.', 422);
    }

    public static function invalidCardLastNumbers(): self
    {
        return new self('Card last numbers must contain exactly 4 digits.', 422);
    }

    public static function emptyItems(): self
    {
        return new self('Transaction must contain at least one item.', 422);
    }

    public static function invalidItemsType(): self
    {
        return new self('Transaction items must be instances of TransactionItem.', 422);
    }

    public static function amountMismatch(): self
    {
        return new self('Transaction amount does not match items total.', 422);
    }

    public static function notFound(): self
    {
        return new self('Transaction not found.', 404);
    }

    public static function noActiveGateway(): self
    {
        return new self('No active gateway available.', 422);
    }

    public static function gatewayNotFound(): self
    {
        return new self('Gateway not found for this transaction.', 404);
    }

    public static function gatewayClientNotFound(string $gatewayName): self
    {
        return new self("Gateway client not found for gateway [{$gatewayName}].", 500);
    }

    public static function missingExternalId(): self
    {
        return new self('Gateway response did not return a valid external id.', 502);
    }

    public static function alreadyRefunded(): self
    {
        return new self('Transaction has already been refunded.', 422);
    }

    public static function allGatewaysFailed(?Throwable $previous = null): self
    {
        return new self('All payment gateways failed.', 502, $previous);
    }
}