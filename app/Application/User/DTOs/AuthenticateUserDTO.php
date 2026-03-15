<?php

declare(strict_types=1);

namespace App\Application\User\DTOs;

class AuthenticateUserDTO
{
    public string $email;
    public string $password;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
}
