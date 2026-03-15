<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

class CreateProductDTO
{
    public string $sku;
    public string $name;
    public int $amount;

    public function __construct(string $sku, string $name, int $amount)
    {
        $this->sku = $sku;
        $this->name = $name;
        $this->amount = $amount;
    }
}
