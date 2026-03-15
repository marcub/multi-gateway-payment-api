<?php

namespace Tests\Unit\Domain\Product\Service;

use App\Domain\Product\Entities\Product;
use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\Exceptions\ProductException;
use App\Domain\Product\Service\ProductService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Str;

class ProductServiceTest extends TestCase
{ 
    private function makeProduct(
        ?string $id = null,
        string $sku = 'TEST-001',
        string $name = 'Test Product',
        int $amount = 15000,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ): Product {
        $id = $id ?? Str::uuid()->toString();
        $createdAt = $createdAt ?? new DateTimeImmutable('2020-01-01 00:00:00');
        $updatedAt = $updatedAt ?? new DateTimeImmutable('2020-01-01 00:00:00');

        return new Product(
            new ProductId($id),
            $sku,
            $name,
            $amount,
            $createdAt,
            $updatedAt
        );
    }

    public function test_create_product_throws_already_exists_when_sku_exists(): void
    {
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())->method('findBySku')->with('SKU-1')->willReturn($this->makeProduct(sku: 'SKU-1'));
        $repo->expects($this->never())->method('save');

        $service = new ProductService($repo);

        $this->expectException(ProductException::class);
        $this->expectExceptionMessage('Product already exists with the provided SKU.');

        $service->createProduct('SKU-1', 'Produto', 1000);
    }

    public function test_create_product_throws_invalid_amount_when_amount_is_negative(): void
    {
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())->method('findBySku')->with('SKU-1')->willReturn(null);
        $repo->expects($this->never())->method('save');

        $service = new ProductService($repo);

        $this->expectException(ProductException::class);
        $this->expectExceptionMessage('Invalid amount provided.');

        $service->createProduct('SKU-1', 'Produto', -15000);
    }

    public function test_create_product_succeeds(): void
    {
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())->method('findBySku')->with('SKU-1')->willReturn(null);
        $repo->expects($this->once())->method('save');

        $service = new ProductService($repo);

        $service->createProduct('SKU-1', 'Produto', 15000);
    }

    public function test_update_product_throws_not_found_when_missing(): void
    {
        $missingId = Str::uuid()->toString();
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())->method('findById')->with($missingId)->willReturn(null);
        $repo->expects($this->never())->method('save');

        $service = new ProductService($repo);

        $this->expectException(ProductException::class);
        $this->expectExceptionMessage('Product not found.');

        $service->updateProduct($missingId, 'SKU-2', 'Novo', 2000);
    }

    public function test_update_product_succeeds(): void
    {
        $existingId = Str::uuid()->toString();
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())->method('findById')->with($existingId)->willReturn($this->makeProduct(id: $existingId));
        $repo->expects($this->once())->method('save');

        $service = new ProductService($repo);

        $service->updateProduct($existingId, 'SKU-2', 'Novo', 2000);
    }

    public function test_delete_product_throws_not_found_when_missing(): void
    {
        $missingId = Str::uuid()->toString();
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())->method('findById')->with($missingId)->willReturn(null);
        $repo->expects($this->never())->method('save');

        $service = new ProductService($repo);

        $this->expectException(ProductException::class);
        $this->expectExceptionMessage('Product not found.');

        $service->deleteProduct($missingId);
    }

    public function test_delete_product_succeeds(): void
    {
        $existingId = Str::uuid()->toString();
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())->method('findById')->with($existingId)->willReturn($this->makeProduct(id: $existingId));
        $repo->expects($this->once())->method('delete');

        $service = new ProductService($repo);

        $service->deleteProduct($existingId);
    }

}
