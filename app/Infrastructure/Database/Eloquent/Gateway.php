<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Eloquent;

use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{

    protected $table = 'gateways';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'priority',
        'is_active',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = false;
    
}
