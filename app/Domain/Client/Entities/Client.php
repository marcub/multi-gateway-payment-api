<?php

declare(strict_types=1);

namespace App\Domain\Client\Entities;

use App\Domain\Client\ValueObjects\ClientId;
use App\Domain\Shared\Email;

use DateTimeImmutable;

class Client
{

    public function __construct(
        private ClientId $id,
        private string $name,
        private Email $email,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public function getId(): ClientId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }


    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

}
