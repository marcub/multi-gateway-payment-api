<?php

namespace Tests\Unit\Domain\Transaction\Service;

use App\Domain\Client\Entities\Client;
use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Domain\Client\ValueObjects\ClientId;
use App\Domain\Gateway\Entities\Gateway;
use App\Domain\Gateway\Repositories\GatewayRepositoryInterface;
use App\Domain\Gateway\ValueObjects\GatewayId;
use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Shared\Email;
use App\Domain\Transaction\Contracts\GatewayClientInterface;
use App\Domain\Transaction\Exceptions\TransactionException;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Transaction\Service\TransactionService;
use App\Domain\Transaction\Support\GatewayClientRegistry;
use App\Domain\Transaction\ValueObjects\TransactionItem;
use App\Domain\Transaction\ValueObjects\TransactionStatus;
use DateTimeImmutable;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class TransactionServiceTest extends TestCase
{
    private function makeGateway(
        ?string $id = null,
        string $name = 'Gateway 1',
        bool $isActive = true,
        int $priority = 1
    ): Gateway {
        $id = $id ?? Str::uuid()->toString();

        return new Gateway(
            new GatewayId($id),
            $name,
            $isActive,
            $priority,
            new DateTimeImmutable('2020-01-01 00:00:00'),
            new DateTimeImmutable('2020-01-01 00:00:00')
        );
    }

    private function makeClient(?string $id = null): Client
    {
        $id = $id ?? Str::uuid()->toString();

        return new Client(
            new ClientId($id),
            'Tester',
            new Email('tester@email.com'),
            new DateTimeImmutable('2020-01-01 00:00:00'),
            new DateTimeImmutable('2020-01-01 00:00:00')
        );
    }

    private function makeItems(): array
    {
        return [
            new TransactionItem(new ProductId(Str::uuid()->toString()), 1, 1000),
        ];
    }

    public function test_charge_throws_when_no_active_gateway(): void
    {
        $transactionRepo = $this->createMock(TransactionRepositoryInterface::class);
        $gatewayRepo = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRegistry = $this->createMock(GatewayClientRegistry::class);
        $clientRepo = $this->createMock(ClientRepositoryInterface::class);

        $clientRepo->expects($this->once())
            ->method('findById')
            ->willReturn($this->makeClient());

        $gatewayRepo->expects($this->once())
            ->method('findAllActive')
            ->willReturn([]);

        $transactionRepo->expects($this->never())->method('save');

        $service = new TransactionService($transactionRepo, $gatewayRepo, $gatewayRegistry, $clientRepo);

        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('No active gateway available.');

        $service->charge(
            Str::uuid()->toString(),
            '5569000000006063',
            '010',
            $this->makeItems()
        );
    }

    public function test_charge_fallbacks_to_second_gateway_and_saves_paid_transaction(): void
    {
        $gateway1 = $this->makeGateway(name: 'Gateway 1', priority: 1);
        $gateway2 = $this->makeGateway(name: 'Gateway 2', priority: 2);

        $transactionRepo = $this->createMock(TransactionRepositoryInterface::class);
        $gatewayRepo = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRegistry = $this->createMock(GatewayClientRegistry::class);
        $clientRepo = $this->createMock(ClientRepositoryInterface::class);

        $gatewayClient1 = $this->createMock(GatewayClientInterface::class);
        $gatewayClient2 = $this->createMock(GatewayClientInterface::class);

        $clientRepo->expects($this->once())
            ->method('findById')
            ->willReturn($this->makeClient());

        $gatewayRepo->expects($this->once())
            ->method('findAllActive')
            ->willReturn([$gateway1, $gateway2]);

        $gatewayRegistry->expects($this->exactly(2))
            ->method('forGateway')
            ->willReturnCallback(function (Gateway $gateway) use ($gateway1, $gatewayClient1, $gatewayClient2) {
                return (string) $gateway->getId() === (string) $gateway1->getId()
                    ? $gatewayClient1
                    : $gatewayClient2;
            });

        $gatewayClient1->expects($this->once())
            ->method('charge')
            ->willThrowException(new \RuntimeException('gateway1 down'));

        $gatewayClient2->expects($this->once())
            ->method('charge')
            ->willReturn(['external_id' => 'tx-123']);

        $transactionRepo->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($transaction) use ($gateway2) {
                return $transaction->getStatus() === TransactionStatus::PAID
                    && $transaction->getExternalId() === 'tx-123'
                    && (string) $transaction->getGatewayId() === (string) $gateway2->getId();
            }));

        $service = new TransactionService($transactionRepo, $gatewayRepo, $gatewayRegistry, $clientRepo);

        $transaction = $service->charge(
            Str::uuid()->toString(),
            '5569000000006063',
            '010',
            $this->makeItems()
        );

        $this->assertSame(TransactionStatus::PAID, $transaction->getStatus());
        $this->assertSame('tx-123', $transaction->getExternalId());
    }

    public function test_refund_throws_not_found_when_transaction_does_not_exist(): void
    {
        $transactionRepo = $this->createMock(TransactionRepositoryInterface::class);
        $gatewayRepo = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRegistry = $this->createMock(GatewayClientRegistry::class);
        $clientRepo = $this->createMock(ClientRepositoryInterface::class);

        $transactionRepo->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $service = new TransactionService($transactionRepo, $gatewayRepo, $gatewayRegistry, $clientRepo);

        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Transaction not found.');

        $service->refund(Str::uuid()->toString());
    }
}