<?php

declare(strict_types=1);

namespace App\Domain\Product\Exceptions;

use DomainException;

class ProductException extends DomainException
{
    public static function alreadyExists(): self
    {
        return new self('Product already exists with the provided SKU.', 409);
    }

    public static function invalidAmount(): self
    {
        return new self('Invalid amount provided.', 422);
    }

    public static function invalidName(): self
    {
        return new self('Invalid name provided.', 422);
    }

    public static function notFound(): self
    {
        return new self('Product not found.', 404);
    }

    public static function nothingToUpdate(): self
    {
        return new self('No valid fields provided for update.', 422);
    }
}