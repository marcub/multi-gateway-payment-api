<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use InvalidArgumentException;

final class UserId
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);

        if (empty($normalized) || $normalized === '') {
            throw new InvalidArgumentException("User ID cannot be empty");
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
