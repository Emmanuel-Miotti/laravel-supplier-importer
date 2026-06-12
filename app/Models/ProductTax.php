<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTax extends Model
{
   protected $fillable = [
        'product_id',
        'country_code',
        'unit_type',
        'rate',
    ];

    protected $casts = [
        'rate' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
