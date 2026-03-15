<?php

declare(strict_types=1);

namespace App\Domain\Product\Repositories;

use App\Domain\Product\Entities\Product;

interface ProductRepositoryInterface
{
    public function save(Product $product): void;
    public function findById(string $id): ?Product;
    public function findBySku(string $sku): ?Product;
    public function findAll(): array;
    public function delete(string $id): void;
}
 