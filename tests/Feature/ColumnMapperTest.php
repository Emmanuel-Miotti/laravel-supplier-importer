<?php

namespace Tests\Feature;

use App\Importers\ColumnMapperRegistry;
use InvalidArgumentException;
use Tests\TestCase;

class ColumnMapperTest extends TestCase
{
    // Pruebo que reconozca los headers en español (Proveedor A)
    public function test_detects_provider_a_format(): void
    {
        $registry = new ColumnMapperRegistry();
        $headers = ['Referencia', 'Marca', 'Precio', 'Cantidad minima'];

        $mapper = $registry->detect($headers);

        $this->assertEquals('Provider A', $mapper->providerName());
        $this->assertFalse($mapper->pricesIncludeTax());
    }

    // Pruebo que reconozca los headers en inglés (Proveedor B)
    public function test_detects_provider_b_format(): void
    {
        $registry = new ColumnMapperRegistry();
        $headers = ['Part Number', 'Manufacturer', 'List Price', 'MOQ'];

        $mapper = $registry->detect($headers);

        $this->assertEquals('Provider B', $mapper->providerName());
        $this->assertTrue($mapper->pricesIncludeTax());
    }

    // Pruebo que tire error si le mando cualquier cosa
    public function test_throws_exception_on_unknown_format(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $registry = new ColumnMapperRegistry();
        $headers = ['Columna Random 1', 'Sarasa'];

        $registry->detect($headers);
    }
}