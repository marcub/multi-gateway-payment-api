<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\Client\UseCases\ListClientsUseCase;
use App\Application\Client\UseCases\GetClientUseCase;
use App\Infrastructure\Http\Responses\ApiResponse;

class ClientController
{
    private $listClientsUseCase;
    private $getClientUseCase;

    public function __construct(ListClientsUseCase $listClientsUseCase, GetClientUseCase $getClientUseCase)
    {
        $this->listClientsUseCase = $listClientsUseCase;
        $this->getClientUseCase = $getClientUseCase;
    }

    public function index()
    {
        $clients = $this->listClientsUseCase->execute();

        $data = array_map(function ($client) {
            return [
                'id' => (string) $client->getId(),
                'name' => (string) $client->getName(),
                'email' => (string) $client->getEmail(),
                'created_at' => $client->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $client->getUpdatedAt()->format('Y-m-d H:i:s')
            ];
        }, $clients);

        return ApiResponse::success($data, 'Clients retrieved successfully');
    }

    public function show(string $id)
    {
        $client = $this->getClientUseCase->execute($id);

        $data = [
            'id' => (string) $client->getId(),
            'name' => (string) $client->getName(),
            'email' => (string) $client->getEmail(),
            'created_at' => $client->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $client->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        return ApiResponse::success($data, 'Client retrieved successfully');
    }

}
