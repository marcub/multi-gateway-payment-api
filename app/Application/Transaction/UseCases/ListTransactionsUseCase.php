<?php

declare(strict_types=1);

namespace App\Application\Transaction\UseCases;

use App\Domain\Transaction\Service\TransactionService;

class ListTransactionsUseCase
{
    public function __construct(private TransactionService $transactionService)
    {
    }

    public function execute(): array
    {
        return $this->transactionService->listTransactions();
    }
}