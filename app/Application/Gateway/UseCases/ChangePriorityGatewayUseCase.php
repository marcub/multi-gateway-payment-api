<?php

declare(strict_types=1);

namespace App\Application\Gateway\UseCases;

use App\Domain\Gateway\Service\GatewayService;
use App\Application\Gateway\DTOs\ChangePriorityGatewayDTO;

class ChangePriorityGatewayUseCase
{
    private $gatewayService;

    public function __construct(GatewayService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    public function execute(ChangePriorityGatewayDTO $dto): void
    {
        $this->gatewayService->changePriority($dto->id, $dto->priority);
    }
}