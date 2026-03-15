<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\Product\UseCases\ListProductsUseCase;
use App\Application\Product\UseCases\UpdateProductUseCase;
use App\Application\Product\UseCases\CreateProductUseCase;
use App\Application\Product\UseCases\DeleteProductUseCase;
use App\Application\Product\UseCases\GetProductUseCase;
use App\Application\Product\DTOs\UpdateProductDTO;
use App\Application\Product\DTOs\CreateProductDTO;
use App\Infrastructure\Http\Requests\UpdateProductRequest;
use App\Infrastructure\Http\Requests\CreateProductRequest;
use App\Infrastructure\Http\Responses\ApiResponse;
use Illuminate\Http\Response;

class ProductController
{
    private $listProductsUseCase;
    private $updateProductUseCase;
    private $getProductUseCase;
    private $createProductUseCase;
    private $deleteProductUseCase;

    public function __construct(ListProductsUseCase $listProductsUseCase, UpdateProductUseCase $updateProductUseCase, GetProductUseCase $getProductUseCase, CreateProductUseCase $createProductUseCase, DeleteProductUseCase $deleteProductUseCase)
    {
        $this->listProductsUseCase = $listProductsUseCase;
        $this->updateProductUseCase = $updateProductUseCase;
        $this->getProductUseCase = $getProductUseCase;
        $this->createProductUseCase = $createProductUseCase;
        $this->deleteProductUseCase = $deleteProductUseCase;
    }

    public function index()
    {
        $products = $this->listProductsUseCase->execute();

        $data = array_map(function ($product) {
            return [
                'id' => (string) $product->getId(),
                'sku' => (string) $product->getSku(),
                'name' => $product->getName(),
                'amount' => $product->getAmount(),
                'created_at' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $product->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $products);

        return ApiResponse::success($data, 'Products retrieved successfully');
    }

    public function show(string $id)
    {
        $product = $this->getProductUseCase->execute($id);

        $data = [
            'id' => (string) $product->getId(),
            'sku' => (string) $product->getSku(),
            'name' => $product->getName(),
            'amount' => $product->getAmount(),
            'created_at' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $product->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        return ApiResponse::success($data, 'Product retrieved successfully');
    }

    public function create(CreateProductRequest $request)
    {
        $dto = new CreateProductDTO(
            $request->input('sku'),
            $request->input('name'),
            $request->input('amount')
        );

        $product = $this->createProductUseCase->execute($dto);

        $data = [
            'sku' => (string) $product->getSku(),
            'name' => $product->getName(),
            'amount' => $product->getAmount(),
            'created_at' => $product->getCreatedAt()->format('Y-m-d H:i:s')
        ];

        return ApiResponse::success($data, 'Product created successfully', Response::HTTP_CREATED);
    }

    public function update(UpdateProductRequest $request, string $id)
    {
        $dto = new UpdateProductDTO(
            $id,
            $request->input('sku'),
            $request->input('name'),
            $request->input('amount')
        );

        $product = $this->updateProductUseCase->execute($dto);

        $data = [
            'sku' => (string) $product->getSku(),
            'name' => $product->getName(),
            'amount' => $product->getAmount(),
            'created_at' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $product->getUpdatedAt()->format('Y-m-d H:i:s')
        ];

        return ApiResponse::success($data, 'Product updated successfully', Response::HTTP_OK);
    }

    public function delete(string $id)
    {
        $this->deleteProductUseCase->execute($id);

        return ApiResponse::success([], 'Product deleted successfully', Response::HTTP_OK);
    }
}
