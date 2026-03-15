<?php

declare(strict_types=1);

namespace App\Application\User\DTOs;

class UpdateUserDTO
{
    public string $id;
    public ?string $email;
    public ?string $password;
    public ?string $role;

    public function __construct(string $id, ?string $email, ?string $password, ?string $role)
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }
}
