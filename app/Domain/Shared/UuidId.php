<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use InvalidArgumentException;

abstract class UuidId
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);

        if (empty($normalized) || $normalized === '') {
            throw new InvalidArgumentException("ID cannot be empty");
        }

        $this->value = $normalized;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}