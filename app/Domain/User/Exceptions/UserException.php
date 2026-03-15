<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use DomainException;

class UserException extends DomainException
{
    public static function alreadyExists(): self
    {
        return new self('User already exists.', 409);
    }

    public static function invalidCredentials(): self
    {
        return new self('Invalid credentials.', 401);
    }

    public static function inactive(): self
    {
        return new self('User is inactive.', 403);
    }

    public static function invalidPassword(): self
    {
        return new self('Password must be at least 8 characters long.', 422);
    }

    public static function notFound(): self
    {
        return new self('User not found.', 404);
    }

    public static function alreadyActive(): self
    {
        return new self('User is already active.', 422);
    }

    public static function alreadyInactive(): self
    {
        return new self('User is already inactive.', 422);
    }

    public static function invalidRole(): self
    {
        return new self('Invalid role specified.', 422);
    }

    public static function nothingToUpdate(): self
    {
        return new self('No valid fields provided for update.', 422);
    }
}