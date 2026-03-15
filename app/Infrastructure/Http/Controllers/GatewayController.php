<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\Gateway\UseCases\ActivateGatewayUseCase;
use App\Application\Gateway\UseCases\DeactivateGatewayUseCase;
use App\Application\Gateway\UseCases\ChangePriorityGatewayUseCase;
use App\Application\Gateway\DTOs\ChangePriorityGatewayDTO;
use App\Infrastructure\Http\Requests\ChangePriorityGatewayRequest;
use App\Infrastructure\Http\Responses\ApiResponse;
use Illuminate\Http\Response;

class GatewayController
{

    private $activateGatewayUseCase;
    private $deactivateGatewayUseCase;
    private $changePriorityGatewayUseCase;

    public function __construct(ActivateGatewayUseCase $activateGatewayUseCase, DeactivateGatewayUseCase $deactivateGatewayUseCase, ChangePriorityGatewayUseCase $changePriorityGatewayUseCase)
    {
        $this->activateGatewayUseCase = $activateGatewayUseCase;
        $this->deactivateGatewayUseCase = $deactivateGatewayUseCase;
        $this->changePriorityGatewayUseCase = $changePriorityGatewayUseCase;
    }

    public function deactivate(string $id)
    {
        $this->deactivateGatewayUseCase->execute($id);

        return ApiResponse::success([], 'Gateway deactivated successfully', Response::HTTP_OK);
    }

    public function activate(string $id)
    {
        $this->activateGatewayUseCase->execute($id);

        return ApiResponse::success([], 'Gateway activated successfully', Response::HTTP_OK);
    }

    public function changePriority(ChangePriorityGatewayRequest $request, string $id)
    {
        $dto = new ChangePriorityGatewayDTO(
            $id,
            $request->input('priority')
        );

        $this->changePriorityGatewayUseCase->execute($dto);

        return ApiResponse::success([], 'Gateway updated successfully', Response::HTTP_OK);
    }

}
