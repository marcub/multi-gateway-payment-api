<?php

declare(strict_types=1);

namespace App\Domain\Transaction\Service;

use App\Domain\Client\Entities\Client;
use App\Domain\Client\Exceptions\ClientException;
use App\Domain\Client\Repositories\ClientRepositoryInterface;
use App\Domain\Client\ValueObjects\ClientId;
use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Gateway\Repositories\GatewayRepositoryInterface;
use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Exceptions\TransactionException;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Transaction\Support\GatewayClientRegistry;
use App\Domain\Transaction\ValueObjects\TransactionId;
use App\Domain\Transaction\ValueObjects\TransactionItem;
use App\Domain\Transaction\ValueObjects\TransactionStatus;
use DateTimeImmutable;
use Illuminate\Support\Str;
use Throwable;

class TransactionService
{
    private TransactionRepositoryInterface $transactionRepository;
    private GatewayRepositoryInterface $gatewayRepository;
    private GatewayClientRegistry $gatewayClientRegistry;
    private ClientRepositoryInterface $clientRepository;

    public function __construct(TransactionRepositoryInterface $transactionRepository, GatewayRepositoryInterface $gatewayRepository, GatewayClientRegistry $gatewayClientRegistry, ClientRepositoryInterface $clientRepository) 
    {
        $this->transactionRepository = $transactionRepository;
        $this->gatewayRepository = $gatewayRepository;
        $this->gatewayClientRegistry = $gatewayClientRegistry;
        $this->clientRepository = $clientRepository;
    }

    public function charge(string $clientId, string $cardNumber, string $cvv, array $items): Transaction
    {
        $clientIdVO = new ClientId($clientId);

        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw ClientException::notFound();
        }

        $gateways = $this->gatewayRepository->findAllActive();
        if ($gateways === []) {
            throw TransactionException::noActiveGateway();
        }

        $amount = $this->calculateTotal($items);
        $cardLastNumbers = substr($cardNumber, -4);
        $lastException = null;

        foreach ($gateways as $gateway) {
            try {
                $gatewayClient = $this->gatewayClientRegistry->forGateway($gateway);

                $response = $gatewayClient->charge([
                    'amount' => $amount,
                    'name' => $client->getName(),
                    'email' => (string) $client->getEmail(),
                    'cardNumber' => $cardNumber,
                    'cvv' => $cvv,
                ]);

                $externalId = $response['external_id'] ?? null;

                if (!is_string($externalId) || trim($externalId) === '') {
                    throw TransactionException::missingExternalId();
                }

                $now = new DateTimeImmutable();

                $transaction = new Transaction(
                    new TransactionId((string) Str::uuid()),
                    $clientIdVO,
                    $gateway->getId(),
                    $externalId,
                    TransactionStatus::PAID,
                    $amount,
                    $cardLastNumbers,
                    $items,
                    $now,
                    $now
                );

                $this->transactionRepository->save($transaction);

                return $transaction;
            } catch (Throwable $e) {
                $lastException = $e;
            }
        }

        $transaction = new Transaction(
            new TransactionId((string) Str::uuid()),
            $clientIdVO,
            null,
            null,
            TransactionStatus::FAILED,
            $amount,
            substr($cardNumber, -4),
            $items,
            new DateTimeImmutable(),
            new DateTimeImmutable()
        );

        $this->transactionRepository->save($transaction);

        throw TransactionException::allGatewaysFailed($lastException);
    }

    public function refund(string $transactionId): Transaction
    {
        $transaction = $this->transactionRepository->findById($transactionId);

        if (!$transaction) {
            throw TransactionException::notFound();
        }

        $gateway = $this->gatewayRepository->findById((string) $transaction->getGatewayId());

        if (!$gateway) {
            throw TransactionException::gatewayNotFound();
        }

        $externalId = $transaction->getExternalId();

        if ($externalId === null || trim($externalId) === '') {
            throw TransactionException::missingExternalId();
        }

        $client = $this->gatewayClientRegistry->forGateway($gateway);
        $client->refund($externalId);

        $transaction->setStatus(TransactionStatus::REFUNDED);
        $transaction->setUpdatedAt(new DateTimeImmutable());
        $this->transactionRepository->save($transaction);

        return $transaction;
    }

    public function getTransaction(string $id): Transaction
    {
        $transaction = $this->transactionRepository->findById($id);

        if (!$transaction) {
            throw TransactionException::notFound();
        }

        return $transaction;
    }

    public function listTransactions(): array
    {
        return $this->transactionRepository->findAll();
    }

    private function calculateTotal(array $items): int
    {
        return array_reduce($items, function ($carry, TransactionItem $item) {
            return $carry + $item->getSubtotal();
        }, 0);
    }
}