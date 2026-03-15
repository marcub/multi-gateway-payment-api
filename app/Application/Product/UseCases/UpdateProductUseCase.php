<?php

declare(strict_types=1);

namespace App\Application\Product\UseCases;

use App\Domain\Product\Service\ProductService;
use App\Domain\Product\Entities\Product;
use App\Application\Product\DTOs\UpdateProductDTO;

class UpdateProductUseCase
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function execute(UpdateProductDTO $dto): Product
    {
        return $this->productService->updateProduct($dto->id, $dto->sku, $dto->name, $dto->amount);
    }
}