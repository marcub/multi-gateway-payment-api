<?php

declare(strict_types=1);

namespace App\Application\Transaction\UseCases;

use App\Domain\Transaction\Service\TransactionService;

class GetTransactionUseCase
{
    public function __construct(private TransactionService $transactionService)
    {
    }

    public function execute(string $id)
    {
        return $this->transactionService->getTransaction($id);
    }
}