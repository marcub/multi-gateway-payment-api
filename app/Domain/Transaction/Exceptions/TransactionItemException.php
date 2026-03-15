<?php

declare(strict_types=1);

namespace App\Domain\Transaction\Exceptions;

use DomainException;

class TransactionItemException extends DomainException
{

    public static function invalidAmount(): self
    {
        return new self('Invalid amount provided for transaction item.', 422);
    }

    public static function invalidQuantity(): self
    {
        return new self('Invalid quantity provided for transaction item.', 422);
    }
    
}