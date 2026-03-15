<?php

declare(strict_types=1);

namespace App\Domain\Transaction\ValueObjects;

enum TransactionStatus : string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
}
