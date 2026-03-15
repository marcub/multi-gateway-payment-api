<?php

declare(strict_types=1);

namespace App\Application\Gateway\UseCases;

use App\Domain\Gateway\Service\GatewayService;

class DeactivateGatewayUseCase
{
    private $gatewayService;

    public function __construct(GatewayService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    public function execute(string $id): void
    {
        $this->gatewayService->deactivateGateway($id);
    }
}