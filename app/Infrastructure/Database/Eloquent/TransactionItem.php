<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Eloquent;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $table = 'transaction_items';

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'transaction_id',
        'product_id',
        'quantity',
        'unit_amount',
    ];
}