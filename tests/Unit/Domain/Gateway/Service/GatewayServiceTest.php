<?php

namespace Tests\Unit\Domain\Gateway\Service;

use App\Domain\Gateway\Entities\Gateway;
use App\Domain\Gateway\Exceptions\GatewayException;
use App\Domain\Gateway\Repositories\GatewayRepositoryInterface;
use App\Domain\Gateway\Service\GatewayService;
use App\Domain\Gateway\ValueObjects\GatewayId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class GatewayServiceTest extends TestCase
{ 
    private function makeGateway(
        string $id = '11111111-1111-1111-1111-111111111111',
        string $name = 'Test Gateway',
        bool $isActive = true,
        int $priority = 1,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ): Gateway {
        $createdAt = $createdAt ?? new DateTimeImmutable('2020-01-01 00:00:00');
        $updatedAt = $updatedAt ?? new DateTimeImmutable('2020-01-01 00:00:00');

        return new Gateway(
            new GatewayId($id),
            $name,
            $isActive,
            $priority,
            $createdAt,
            $updatedAt
        );
    }

    public function test_activate_gateway_throws_not_found_when_gateway_does_not_exist(): void
    {
        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with('non-existent-id')
            ->willReturn(null);
        $gatewayRepository->expects($this->never())->method('save');

        $gatewayService = new GatewayService($gatewayRepository);

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Gateway not found.');

        $gatewayService->activateGateway('non-existent-id');
    }

    public function test_activate_gateway_throws_already_active_when_gateway_is_active(): void
    {
        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with('11111111-1111-1111-1111-111111111111')
            ->willReturn($this->makeGateway(isActive: true));
        $gatewayRepository->expects($this->never())->method('save');

        $gatewayService = new GatewayService($gatewayRepository);

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Gateway is already active.');

        $gatewayService->activateGateway('11111111-1111-1111-1111-111111111111');
    }

    public function test_activate_gateway_saves_when_gateway_is_inactive(): void
    {
        $gateway = $this->makeGateway(isActive: false);

        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with('11111111-1111-1111-1111-111111111111')
            ->willReturn($gateway);
        $gatewayRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Gateway $savedGateway): bool {
                return $savedGateway->getIsActive() === true;
            }));


        $gatewayService = new GatewayService($gatewayRepository);

        $gatewayService->activateGateway('11111111-1111-1111-1111-111111111111');
    }

    public function test_deactivate_gateway_throws_not_found_when_gateway_does_not_exist(): void
    {
        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with('non-existent-id')
            ->willReturn(null);
        $gatewayRepository->expects($this->never())->method('save');

        $gatewayService = new GatewayService($gatewayRepository);

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Gateway not found.');

        $gatewayService->deactivateGateway('non-existent-id');
    }

    public function test_deactivate_gateway_throws_already_inactive_when_gateway_is_inactive(): void
    {
        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with('11111111-1111-1111-1111-111111111111')
            ->willReturn($this->makeGateway(isActive: false));
        $gatewayRepository->expects($this->never())->method('save');

        $gatewayService = new GatewayService($gatewayRepository);

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Gateway is already inactive.');

        $gatewayService->deactivateGateway('11111111-1111-1111-1111-111111111111');
    }

    public function test_deactivate_gateway_saves_when_gateway_is_active(): void
    {
        $gateway = $this->makeGateway(isActive: true);

        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with('11111111-1111-1111-1111-111111111111')
            ->willReturn($gateway);
        $gatewayRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Gateway $savedGateway): bool {
                return $savedGateway->getIsActive() === false;
            }));

        $gatewayService = new GatewayService($gatewayRepository);

        $gatewayService->deactivateGateway('11111111-1111-1111-1111-111111111111');
    }

    public function test_change_priority_throws_not_found_when_gateway_does_not_exist(): void
    {
        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with('non-existent-id')
            ->willReturn(null);
        $gatewayRepository->expects($this->never())->method('save');

        $gatewayService = new GatewayService($gatewayRepository);

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Gateway not found.');

        $gatewayService->changePriority('non-existent-id', 1);
    }

    public function test_change_priority_throws_invalid_priority_when_priority_is_less_than_one(): void
    {
        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with('11111111-1111-1111-1111-111111111111')
            ->willReturn($this->makeGateway());
        $gatewayRepository->expects($this->never())->method('save');

        $gatewayService = new GatewayService($gatewayRepository);

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Invalid priority.');

        $gatewayService->changePriority('11111111-1111-1111-1111-111111111111', 0);
    }

    public function test_change_priority_saves_when_priority_is_valid(): void
    {
        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with('11111111-1111-1111-1111-111111111111')
            ->willReturn($this->makeGateway());
        $gatewayRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Gateway $savedGateway): bool {
                return $savedGateway->getPriority() === 2;
            }));

        $gatewayService = new GatewayService($gatewayRepository);

        $gatewayService->changePriority('11111111-1111-1111-1111-111111111111', 2);
    }

}
