<?php

declare(strict_types=1);

namespace App\Application\User\DTOs;

class RegisterUserDTO
{
    public string $email;
    public string $password;
    public string $role;

    public function __construct(string $email, string $password, string $role)
    {
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }
}
