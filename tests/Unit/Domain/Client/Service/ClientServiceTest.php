<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Client\Service;

use App\Domain\Client\Service\ClientService;
use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Domain\Client\Exceptions\ClientException;
use App\Domain\Client\Entities\Client;
use App\Domain\Client\ValueObjects\ClientId;
use App\Domain\Shared\Email;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use Illuminate\Support\Str;

class ClientServiceTest extends TestCase
{
    private function makeClient(
        ?string $id = null,
        string $name = 'Test Client',
        string $email = 'test@example.com',
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ): Client {
        $id = $id ?? Str::uuid()->toString();
        $createdAt = $createdAt ?? new DateTimeImmutable('2020-01-01 00:00:00');
        $updatedAt = $updatedAt ?? new DateTimeImmutable('2020-01-01 00:00:00');

        return new Client(
            new ClientId($id),
            $name,
            new Email($email),
            $createdAt,
            $updatedAt
        );
    }

    public function test_get_client_throws_not_found_when_client_does_not_exist(): void
    {
        $nonExistentId = Str::uuid()->toString();
        $clientRepository = $this->createMock(ClientRepositoryInterface::class);
        $clientRepository->expects($this->once())
            ->method('findById')
            ->with($nonExistentId)
            ->willReturn(null);

        $clientService = new ClientService($clientRepository);

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Client not found.');

        $clientService->getClient($nonExistentId);
    }

    public function test_get_client_returns_client_when_exists(): void
    {
        $existingId = Str::uuid()->toString();
        $client = $this->makeClient($existingId, 'Test name', 'test@example.com');

        $clientRepository = $this->createMock(ClientRepositoryInterface::class);
        $clientRepository->expects($this->once())
            ->method('findById')
            ->with($existingId)
            ->willReturn($client);

        $clientService = new ClientService($clientRepository);

        $result = $clientService->getClient($existingId);

        $this->assertSame($client, $result);
    }

    public function test_list_clients_returns_all_clients(): void
    {
        $id1 = Str::uuid()->toString();
        $id2 = Str::uuid()->toString();
        $client1 = $this->makeClient($id1, 'Client 1', 'client1@example.com');
        $client2 = $this->makeClient($id2, 'Client 2', 'client2@example.com');

        $clientRepository = $this->createMock(ClientRepositoryInterface::class);
        $clientRepository->expects($this->once())
        ->method('findAll')
        ->willReturn([$client1, $client2]);

        $clientService = new ClientService($clientRepository);

        $result = $clientService->listClients();

        $this->assertSame([$client1, $client2], $result);
    }
}