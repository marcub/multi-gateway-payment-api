<?php

declare(strict_types=1);

namespace App\Domain\Transaction\Repositories;

use App\Domain\Transaction\Entities\Transaction;

interface TransactionRepositoryInterface
{
    public function save(Transaction $transaction): void;
    public function findById(string $id): ?Transaction;
    public function findAll(): array;
}