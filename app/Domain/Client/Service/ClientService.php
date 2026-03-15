<?php

declare(strict_types=1);

namespace App\Domain\Client\Service;

use App\Domain\Client\Entities\Client;
use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Domain\Client\Exceptions\ClientException;

class ClientService
{
    private ClientRepositoryInterface $clientRepository;
    
    public function __construct(ClientRepositoryInterface $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function getClient(string $id): Client
    {
        $client  = $this->clientRepository->findById($id);

        if (!$client) {
            throw ClientException::notFound();
        }

        return $client;
    }

    public function listClients(): array
    {
        return $this->clientRepository->findAll();
    }

}
