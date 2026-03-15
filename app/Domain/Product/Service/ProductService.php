<?php

declare(strict_types=1);

namespace App\Domain\Product\Service;

use App\Domain\Product\Entities\Product;
use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\Exceptions\ProductException;
use DateTimeImmutable;
use Illuminate\Support\Str;

class ProductService
{
    private ProductRepositoryInterface $productRepository;
    
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function createProduct(string $sku, string $name, int $amount): Product
    {
        $productFound = $this->productRepository->findBySku($sku);

        if ($productFound) {
            throw ProductException::alreadyExists();
        }

        if ($amount <= 0) {
            throw ProductException::invalidAmount();
        }

        if (empty($name) || trim($name) === '') {
            throw ProductException::invalidName();
        }

        $productId = new ProductId((string) Str::uuid());

        $dateTimeNow = new DateTimeImmutable();

        $product = new Product(
            $productId,
            $sku,
            $name,
            $amount,
            $dateTimeNow,
            $dateTimeNow
        );

        $this->productRepository->save($product);

        return $product;
    }

    public function updateProduct(string $id, ?string $sku, ?string $name, ?int $amount): Product
    {
        $product  = $this->productRepository->findById($id);

        if (!$product) {
            throw ProductException::notFound();
        };

        $isUpdated = false;

        if ($sku !== null && $product->getSku() !== $sku) {
             $productFound = $this->productRepository->findBySku($sku);

            if ($productFound) {
                throw ProductException::alreadyExists();
            }

            $product->setSku($sku);
            $isUpdated = true;
        };

        if ($amount !== null && $product->getAmount() !== $amount) {
            if ($amount <= 0) {
                throw ProductException::invalidAmount();
            }

            $product->setAmount($amount);
            $isUpdated = true;
        };

        if ($name !== null && $product->getName() !== $name) {
            if (empty($name) || trim($name) === '') {
                throw ProductException::invalidName();
            }

            $product->setName($name);
            $isUpdated = true;
        };

        if ($isUpdated) {
            $dateTimeNow = new DateTimeImmutable();
            $product->setUpdatedAt($dateTimeNow);
        };

        $this->productRepository->save($product);

        return $product;
    }

    public function getProduct(string $id): Product
    {
        $product  = $this->productRepository->findById($id);

        if (!$product) {
            throw ProductException::notFound();
        };

        return $product;
    }

    public function listProducts(): array
    {
        return $this->productRepository->findAll();
    }

    public function deleteProduct(string $id): void
    {
        $product  = $this->productRepository->findById($id);

        if (!$product) {
            throw ProductException::notFound();
        };

        $this->productRepository->delete($id);
    }
}
