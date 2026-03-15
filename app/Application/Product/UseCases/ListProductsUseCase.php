<?php

declare(strict_types=1);

namespace App\Application\Product\UseCases;

use App\Domain\Product\Service\ProductService;

class ListProductsUseCase
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function execute(): array
    {
        return $this->productService->listProducts();
    }
}
