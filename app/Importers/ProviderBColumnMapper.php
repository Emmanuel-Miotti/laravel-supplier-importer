<?php

namespace App\Importers;

// Proveedor B — usa nombres en inglés, precios con impuesto incluido
class ProviderBColumnMapper extends BaseColumnMapper
{
    public function providerName(): string
    {
        return 'Provider B';
    }

    public function pricesIncludeTax(): bool
    {
        return true;
    }

    protected function aliases(): array
    {
        return [
            'reference'    => ['ref', 'part number', 'sku', 'item code'],
            'brand'        => ['brand', 'manufacturer'],
            'ean'          => ['ean', 'barcode', 'upc'],
            'description'  => ['description', 'product name'],
            'dimensions'   => ['dimensions', 'size'],
            'family'       => ['family', 'category'],
            'subfamily'    => ['subfamily', 'subcategory'],
            'price'        => ['unit price', 'price', 'list price'],
            'min_quantity' => ['min qty', 'minimum quantity', 'moq'],
            'price_2'      => ['price 10+', 'bulk price'],
            'min_quantity_2' => ['min qty 2', 'bulk min qty'],
        ];
    }
}