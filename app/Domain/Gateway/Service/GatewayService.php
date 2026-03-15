<?php

declare(strict_types=1);

namespace App\Domain\Gateway\Service;

use App\Domain\Gateway\Repositories\GatewayRepositoryInterface;
use App\Domain\Gateway\Exceptions\GatewayException;
use DateTimeImmutable;

class GatewayService
{
    private GatewayRepositoryInterface $gatewayRepository;
    
    public function __construct(GatewayRepositoryInterface $gatewayRepository)
    {
        $this->gatewayRepository = $gatewayRepository;
    }

    public function activateGateway(string $id): void
    {
        $gateway = $this->gatewayRepository->findById($id);

        if (!$gateway) {
            throw GatewayException::notFound();
        }

        if ($gateway->getIsActive()) {
            throw GatewayException::alreadyActive();
        }

        $gateway->setIsActive(true);
        $gateway->setUpdatedAt(new DateTimeImmutable());

        $this->gatewayRepository->save($gateway);
    }

    public function deactivateGateway(string $id): void
    {
        $gateway = $this->gatewayRepository->findById($id);

        if (!$gateway) {
            throw GatewayException::notFound();
        }

        if (!$gateway->getIsActive()) {
            throw GatewayException::alreadyInactive();
        }

        $gateway->setIsActive(false);
        $gateway->setUpdatedAt(new DateTimeImmutable());

        $this->gatewayRepository->save($gateway);
    }

    public function changePriority(string $id, int $priority) : void
    {
        $gateway = $this->gatewayRepository->findById($id);

        if (!$gateway) {
            throw GatewayException::notFound();
        }

        if ($priority < 1) {
            throw GatewayException::invalidPriority();
        }

        $gateway->setPriority($priority);
        $gateway->setUpdatedAt(new DateTimeImmutable());

        $this->gatewayRepository->save($gateway);
    }
}
