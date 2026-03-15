<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\Transaction\DTOs\ChargeTransactionDTO;
use App\Application\Transaction\DTOs\ChargeTransactionItemDTO;
use App\Application\Transaction\UseCases\ChargeTransactionUseCase;
use App\Application\Transaction\UseCases\GetTransactionUseCase;
use App\Application\Transaction\UseCases\ListTransactionsUseCase;
use App\Application\Transaction\UseCases\RefundTransactionUseCase;
use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\ValueObjects\TransactionItem;
use App\Infrastructure\Http\Requests\ChargeTransactionRequest;
use App\Infrastructure\Http\Responses\ApiResponse;
use Illuminate\Http\Response;

class TransactionController
{
    private ListTransactionsUseCase $listTransactionsUseCase;
    private GetTransactionUseCase $getTransactionUseCase;
    private ChargeTransactionUseCase $chargeTransactionUseCase;
    private RefundTransactionUseCase $refundTransactionUseCase;

    public function __construct(ListTransactionsUseCase $listTransactionsUseCase, GetTransactionUseCase $getTransactionUseCase, ChargeTransactionUseCase $chargeTransactionUseCase, RefundTransactionUseCase $refundTransactionUseCase) 
    {
        $this->listTransactionsUseCase = $listTransactionsUseCase;
        $this->getTransactionUseCase = $getTransactionUseCase;
        $this->chargeTransactionUseCase = $chargeTransactionUseCase;
        $this->refundTransactionUseCase = $refundTransactionUseCase;
    }

    public function index()
    {
        $transactions = $this->listTransactionsUseCase->execute();

        $data = array_map([$this, 'mapTransaction'], $transactions);

        return ApiResponse::success($data, 'Transactions retrieved successfully');
    }

    public function show(string $id)
    {
        $transaction = $this->getTransactionUseCase->execute($id);

        $data = $this->mapTransaction($transaction);

        return ApiResponse::success($data, 'Transaction retrieved successfully');
    }

    public function charge(ChargeTransactionRequest $request)
    {
        $items = array_map(
            fn (array $item): ChargeTransactionItemDTO => new ChargeTransactionItemDTO(
                $item['product_id'],
                $item['quantity'],
                $item['unit_amount']
            ),
            $request->input('items')
        );

        $dto = new ChargeTransactionDTO(
            $request->input('client_id'),
            $request->input('card_number'),
            $request->input('cvv'),
            $items
        );

        $transaction = $this->chargeTransactionUseCase->execute($dto);

        return ApiResponse::success(
            $this->mapTransaction($transaction),
            'Transaction charged successfully',
            Response::HTTP_CREATED
        );
    }

    public function refund(string $id)
    {
        $transaction = $this->refundTransactionUseCase->execute($id);

        return ApiResponse::success(
            $this->mapTransaction($transaction),
            'Transaction refunded successfully'
        );
    }

    private function mapTransaction(Transaction $transaction): array
    {
        return [
            'id' => (string) $transaction->getId(),
            'client_id' => (string) $transaction->getClientId(),
            'gateway_id' => $transaction->getGatewayId() ? (string) $transaction->getGatewayId() : null,
            'external_id' => $transaction->getExternalId(),
            'status' => $transaction->getStatus()->value,
            'amount' => $transaction->getAmount(),
            'card_last_numbers' => $transaction->getCardLastNumbers(),
            'items' => array_map(
                fn (TransactionItem $item): array => [
                    'product_id' => (string) $item->getProductId(),
                    'quantity' => $item->getQuantity(),
                    'unit_amount' => $item->getUnitAmount(),
                    'subtotal' => $item->getSubtotal(),
                ],
                $transaction->getItems()
            ),
            'created_at' => $transaction->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $transaction->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}