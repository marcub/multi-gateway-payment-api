<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repositories;

use App\Domain\Product\Entities\Product;
use App\Domain\Shared\Email;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\ValueObjects\ProductId;
use App\Infrastructure\Database\Eloquent\Product as EloquentProduct;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function save(Product $product): void
    {
        EloquentProduct::updateOrCreate(
            ['id' => (string)$product->getId()],
            [
                'id' => (string)$product->getId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'amount' => $product->getAmount(),
                'created_at' => $product->getCreatedAt(),
                'updated_at' => $product->getUpdatedAt(),
            ]
        );
    }

    public function findById(string $id): ?Product
    {
        $eloquentProduct = EloquentProduct::find($id);

        if (!$eloquentProduct) {
            return null;
        }

        return new Product(
            id: new ProductId($eloquentProduct->id),
            sku: $eloquentProduct->sku,
            name: $eloquentProduct->name,
            amount: $eloquentProduct->amount,
            createdAt: $eloquentProduct->created_at->toDateTimeImmutable(),
            updatedAt: $eloquentProduct->updated_at->toDateTimeImmutable()
        );
    }

    public function findBySku(string $sku): ?Product
    {
        $eloquentProduct = EloquentProduct::where('sku', (string) $sku)->first();

        if (!$eloquentProduct) {
            return null;
        }

        return new Product(
            id: new ProductId($eloquentProduct->id),
            sku: $eloquentProduct->sku,
            name: $eloquentProduct->name,
            amount: $eloquentProduct->amount,
            createdAt: $eloquentProduct->created_at->toDateTimeImmutable(),
            updatedAt: $eloquentProduct->updated_at->toDateTimeImmutable()
        );
    }

    public function findAll(): array
    {
        $eloquentProducts = EloquentProduct::all();

        return $eloquentProducts->map(function ($eloquentProduct) {
            return new Product(
                id: new ProductId($eloquentProduct->id),
                sku: $eloquentProduct->sku,
                name: $eloquentProduct->name,
                amount: $eloquentProduct->amount,
                createdAt: $eloquentProduct->created_at->toDateTimeImmutable(),
                updatedAt: $eloquentProduct->updated_at->toDateTimeImmutable()
            );
        })->toArray();
    }

    public function delete(string $id): void
    {
        EloquentProduct::destroy($id);
    }
}
