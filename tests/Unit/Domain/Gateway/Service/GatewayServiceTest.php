<?php

namespace Tests\Unit\Domain\Gateway\Service;

use App\Domain\Gateway\Entities\Gateway;
use App\Domain\Gateway\Exceptions\GatewayException;
use App\Domain\Gateway\Repositories\GatewayRepositoryInterface;
use App\Domain\Gateway\Service\GatewayService;
use App\Domain\Gateway\ValueObjects\GatewayId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Str;

class GatewayServiceTest extends TestCase
{ 
    private function makeGateway(
        ?string $id = null,
        string $name = 'Test Gateway',
        bool $isActive = true,
        int $priority = 1,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ): Gateway {
        $id = $id ?? Str::uuid()->toString();
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
        $nonExistentId = Str::uuid()->toString();
        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with($nonExistentId)
            ->willReturn(null);
        $gatewayRepository->expects($this->never())->method('save');

        $gatewayService = new GatewayService($gatewayRepository);

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Gateway not found.');

        $gatewayService->activateGateway($nonExistentId);
    }

    public function test_activate_gateway_throws_already_active_when_gateway_is_active(): void
    {
        $existingId = Str::uuid()->toString();
        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with($existingId)
            ->willReturn($this->makeGateway($existingId, isActive: true));
        $gatewayRepository->expects($this->never())->method('save');

        $gatewayService = new GatewayService($gatewayRepository);

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Gateway is already active.');

        $gatewayService->activateGateway($existingId);
    }

    public function test_activate_gateway_saves_when_gateway_is_inactive(): void
    {
        $existingId = Str::uuid()->toString();
        $gateway = $this->makeGateway($existingId, isActive: false);

        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with($existingId)
            ->willReturn($gateway);
        $gatewayRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Gateway $savedGateway): bool {
                return $savedGateway->getIsActive() === true;
            }));


        $gatewayService = new GatewayService($gatewayRepository);

        $gatewayService->activateGateway($existingId);
    }

    public function test_deactivate_gateway_throws_not_found_when_gateway_does_not_exist(): void
    {
        $nonExistentId = Str::uuid()->toString();
        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with($nonExistentId)
            ->willReturn(null);
        $gatewayRepository->expects($this->never())->method('save');

        $gatewayService = new GatewayService($gatewayRepository);

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Gateway not found.');

        $gatewayService->deactivateGateway($nonExistentId);
    }

    public function test_deactivate_gateway_throws_already_inactive_when_gateway_is_inactive(): void
    {
        $nonExistentId = Str::uuid()->toString();
        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with($nonExistentId)
            ->willReturn($this->makeGateway($nonExistentId, isActive: false));
        $gatewayRepository->expects($this->never())->method('save');

        $gatewayService = new GatewayService($gatewayRepository);

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Gateway is already inactive.');

        $gatewayService->deactivateGateway($nonExistentId);
    }

    public function test_deactivate_gateway_saves_when_gateway_is_active(): void
    {
        $existingId = Str::uuid()->toString();
        $gateway = $this->makeGateway($existingId, isActive: true);

        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with($existingId)
            ->willReturn($gateway);
        $gatewayRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Gateway $savedGateway): bool {
                return $savedGateway->getIsActive() === false;
            }));

        $gatewayService = new GatewayService($gatewayRepository);

        $gatewayService->deactivateGateway($existingId);
    }

    public function test_change_priority_throws_not_found_when_gateway_does_not_exist(): void
    {
        $nonExistentId = Str::uuid()->toString();
        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with($nonExistentId)
            ->willReturn(null);
        $gatewayRepository->expects($this->never())->method('save');

        $gatewayService = new GatewayService($gatewayRepository);

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Gateway not found.');

        $gatewayService->changePriority($nonExistentId, 1);
    }

    public function test_change_priority_throws_invalid_priority_when_priority_is_less_than_one(): void
    {
        $existingId = Str::uuid()->toString();
        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with($existingId)
            ->willReturn($this->makeGateway($existingId));
        $gatewayRepository->expects($this->never())->method('save');

        $gatewayService = new GatewayService($gatewayRepository);

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Invalid priority.');

        $gatewayService->changePriority($existingId, 0);
    }

    public function test_change_priority_saves_when_priority_is_valid(): void
    {
        $existingId = Str::uuid()->toString();
        $gatewayRepository = $this->createMock(GatewayRepositoryInterface::class);
        $gatewayRepository->expects($this->once())
            ->method('findById')
            ->with($existingId)
            ->willReturn($this->makeGateway($existingId));
        $gatewayRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Gateway $savedGateway): bool {
                return $savedGateway->getPriority() === 2;
            }));

        $gatewayService = new GatewayService($gatewayRepository);

        $gatewayService->changePriority($existingId, 2);
    }

}
