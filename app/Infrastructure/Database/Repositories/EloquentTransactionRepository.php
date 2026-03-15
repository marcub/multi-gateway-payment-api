<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repositories;

use App\Domain\Client\ValueObjects\ClientId;
use App\Domain\Gateway\ValueObjects\GatewayId;
use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Transaction\ValueObjects\TransactionId;
use App\Domain\Transaction\ValueObjects\TransactionItem;
use App\Domain\Transaction\ValueObjects\TransactionStatus;
use App\Infrastructure\Database\Eloquent\Transaction as EloquentTransaction;
use App\Infrastructure\Database\Eloquent\TransactionItem as EloquentTransactionItem;
use Illuminate\Support\Str;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function save(Transaction $transaction): void
    {
        EloquentTransaction::updateOrCreate(
            ['id' => (string) $transaction->getId()],
            [
                'id' => (string) $transaction->getId(),
                'client_id' => (string) $transaction->getClientId(),
                'gateway_id' => $transaction->getGatewayId() ? (string) $transaction->getGatewayId() : null,
                'external_id' => $transaction->getExternalId(),
                'status' => $transaction->getStatus()->value,
                'amount' => $transaction->getAmount(),
                'card_last_numbers' => $transaction->getCardLastNumbers(),
                'created_at' => $transaction->getCreatedAt(),
                'updated_at' => $transaction->getUpdatedAt(),
            ]
        );

        EloquentTransactionItem::where('transaction_id', (string) $transaction->getId())->delete();

        foreach ($transaction->getItems() as $item) {
            EloquentTransactionItem::create([
                'id'             => (string) Str::uuid(),
                'transaction_id' => (string) $transaction->getId(),
                'product_id'     => (string) $item->getProductId(),
                'quantity'       => $item->getQuantity(),
                'unit_amount'    => $item->getUnitAmount(),
            ]);
        }
    }

    public function findById(string $id): ?Transaction
    {
        $eloquentTransaction = EloquentTransaction::find($id);

        if (!$eloquentTransaction) {
            return null;
        }

        return $this->mapToDomain($eloquentTransaction);
    }

    public function findAll(): array
    {
        return EloquentTransaction::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (EloquentTransaction $eloquentTransaction): Transaction => $this->mapToDomain($eloquentTransaction))
            ->toArray();
    }

    private function mapToDomain(EloquentTransaction $eloquentTransaction): Transaction
    {
        $items = $eloquentTransaction->items->map(
            fn (EloquentTransactionItem $item): TransactionItem => new TransactionItem(
                new ProductId($item->product_id),
                (int) $item->quantity,
                (int) $item->unit_amount
            )
        )->toArray();

        return new Transaction(
            new TransactionId($eloquentTransaction->id),
            new ClientId($eloquentTransaction->client_id),
            $eloquentTransaction->gateway_id !== null ? new GatewayId($eloquentTransaction->gateway_id) : null,
            $eloquentTransaction->external_id,
            TransactionStatus::from($eloquentTransaction->status),
            (int) $eloquentTransaction->amount,
            $eloquentTransaction->card_last_numbers,
            $items,
            $eloquentTransaction->created_at->toDateTimeImmutable(),
            $eloquentTransaction->updated_at->toDateTimeImmutable()
        );
    }
}