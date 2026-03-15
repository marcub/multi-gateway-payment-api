<?php

declare(strict_types=1);

namespace App\Domain\Gateway\Exceptions;

use DomainException;

class GatewayException extends DomainException
{
    public static function notFound(): self
    {
        return new self('Gateway not found.', 404);
    }

    public static function alreadyActive(): self
    {
        return new self('Gateway is already active.', 422);
    }

    public static function alreadyInactive(): self
    {
        return new self('Gateway is already inactive.', 422);
    }

    public static function invalidPriority(): self
    {
        return new self('Invalid priority.', 422);
    }
}