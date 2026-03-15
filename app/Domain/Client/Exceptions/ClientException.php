<?php

declare(strict_types=1);

namespace App\Domain\Client\Exceptions;

use DomainException;

class ClientException extends DomainException
{
    public static function notFound(): self
    {
        return new self('Client not found.', 404);
    }
}