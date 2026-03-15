<?php

declare(strict_types=1);

namespace App\Application\Client\UseCases;

use App\Domain\Client\Service\ClientService;

class GetClientUseCase
{
    private $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function execute(string $id)
    {
        return $this->clientService->getClient($id);
    }
}
