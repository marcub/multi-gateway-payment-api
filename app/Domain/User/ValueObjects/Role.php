<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

enum Role : string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case FINANCE = 'finance';
    case USER = 'user';
}
