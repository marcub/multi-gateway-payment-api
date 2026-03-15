<?php

declare(strict_types=1);

namespace App\Application\Transaction\UseCases;

use App\Application\Transaction\DTOs\ChargeTransactionDTO;
use App\Application\Transaction\DTOs\ChargeTransactionItemDTO;
use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Transaction\Service\TransactionService;
use App\Domain\Transaction\ValueObjects\TransactionItem;


class ChargeTransactionUseCase
{
    public function __construct(private TransactionService $transactionService)
    {
    }

    public function execute(ChargeTransactionDTO $dto)
    {
        $items = array_map(
            fn (ChargeTransactionItemDTO $item): TransactionItem => new TransactionItem(
                new ProductId($item->productId),
                $item->quantity,
                $item->unitAmount
            ),
            $dto->items
        );

        return $this->transactionService->charge(
            $dto->clientId,
            $dto->cardNumber,
            $dto->cvv,
            $items
        );
    }
}