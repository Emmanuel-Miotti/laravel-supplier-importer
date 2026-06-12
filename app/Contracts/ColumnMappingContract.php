<?php

namespace App\Contracts;

interface ColumnMappingContract
{

    // Mapea los títulos del Excel con las columnas que usa la base de datos
    // Retorna algo como: ['reference' => 0, 'brand' => 1]
    public function resolveFromHeader(array $headers): array;

    // Avisa si los precios del Excel ya vienen con impuestos cargados
    public function pricesIncludeTax(): bool;

    // Nombre comercial del proveedor
    public function providerName(): string;

}