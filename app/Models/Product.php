<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'supplier_id',
        'reference',
        'brand',
        'ean',
        'description',
        'dimensions',
        'family',
        'subfamily',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class)->orderBy('min_quantity');
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(ProductTax::class);
    }

    
   // Busco el precio que corresponda al tramo de cantidad solicitado  
    public function priceForQuantity(int $quantity): ?float
    {
        return $this->prices()
            ->where('min_quantity', '<=', $quantity)
            ->orderBy('min_quantity', 'desc')
            ->value('price');
    }
}
