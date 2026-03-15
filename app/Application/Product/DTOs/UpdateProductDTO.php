<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

class UpdateProductDTO
{
    public string $id;
    public ?string $sku;
    public ?string $name;
    public ?int $amount;

    public function __construct(string $id, ?string $sku, ?string $name, ?int $amount)
    {
        $this->id = $id;
        $this->sku = $sku;
        $this->name = $name;
        $this->amount = $amount;
    }
}
