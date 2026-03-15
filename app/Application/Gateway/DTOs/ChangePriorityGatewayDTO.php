<?php

declare(strict_types=1);

namespace App\Application\Gateway\DTOs;

class ChangePriorityGatewayDTO
{
    public string $id;
    public int $priority;

    public function __construct(string $id, int $priority)
    {
        $this->id = $id;
        $this->priority = $priority;
    }
}
