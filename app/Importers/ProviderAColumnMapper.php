<?php

namespace App\Importers;

// Proveedor A — usa nombres en español
class ProviderAColumnMapper extends BaseColumnMapper
{
    public function providerName(): string
    {
        return 'Provider A';
    }

    public function pricesIncludeTax(): bool
    {
        return false;
    }

    protected function aliases(): array
    {
        return [
            'reference'   => ['referencia', 'ref', 'codigo proveedor'],
            'brand'       => ['marca', 'brand'],
            'ean'         => ['ean', 'codigo ean'],
            'description' => ['descripcion', 'descripción', 'detalle'],
            'dimensions'  => ['dimensiones', 'medidas'],
            'family'      => ['familia', 'categoria'],
            'subfamily'   => ['subfamilia', 'subcategoria'],
            'price'       => ['precio', 'precio unitario'],
            'min_quantity' => ['cantidad minima', 'cantidad mínima'],
        ];
    }
}