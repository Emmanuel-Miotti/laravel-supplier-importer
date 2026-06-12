<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ProductPrice extends Model
{
     protected $fillable = [
        'product_id',
        'min_quantity',
        'price',
    ];

    protected $casts = [
        'price'        => 'float',
        'min_quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
