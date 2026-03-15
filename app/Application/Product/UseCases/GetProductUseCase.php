<?php

declare(strict_types=1);

namespace App\Application\Product\UseCases;

use App\Domain\Product\Service\ProductService;

class GetProductUseCase
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function execute(string $id)
    {
        return $this->productService->getProduct($id);
    }
}
