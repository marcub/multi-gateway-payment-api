<?php

declare(strict_types=1);

namespace App\Application\Client\UseCases;

use App\Domain\Client\Service\ClientService;

class ListClientsUseCase
{
    private $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function execute(): array
    {
        return $this->clientService->listClients();
    }
}
