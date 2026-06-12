<?php

namespace App\Services;

use App\Contracts\ColumnMappingContract;
use App\Importers\ColumnMapperRegistry;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use Throwable;

class ExcelImportService
{
    public function __construct(
        private readonly ColumnMapperRegistry $registry,
    ) {}

    // lo hago por separado cada fila
    public function import(string $filePath, ?string $providerHint = null): array
    {
        $spreadsheet = IOFactory::load($filePath); // detecta el tipo de archivo automáticamente lo hace  plugin phpoffice/phpspreadsheet
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['El archivo está vacío']];
        }

        // Tomo la primera fila para detectar el formato
        $headers = array_map(fn($v) => (string) ($v ?? ''), $rows[0]);
        $mapper = $this->registry->detect($headers, $providerHint);
        $mapping = $mapper->resolveFromHeader($headers);

        $this->validateRequiredColumns($mapping);

        $supplier = Supplier::firstOrCreate(['name' => $mapper->providerName()]);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        // cada fila puede romper por separado y no quiero que eso ropa toda la importación lo hace fila por fila
        DB::transaction(function () use ($rows, $mapping, $mapper, $supplier, &$imported, &$skipped, &$errors) {
            // Salteo los headers (índice 0)
            foreach (array_slice($rows, 1) as $rowIndex => $row) {
                $lineNumber = $rowIndex + 2; 
                
                try {
                    $this->processRow($row, $mapping, $mapper, $supplier->id);
                    $imported++;
                } catch (Throwable $e) {
                    $skipped++;
                    $errors[] = "Fila {$lineNumber}: " . $e->getMessage();
                }
            }
        });

        return compact('imported', 'skipped', 'errors');
    }

    //
    private function processRow(array $row, array $mapping, ColumnMappingContract $mapper, int $supplierId): void 
    {
        $reference = $this->get($row, $mapping, 'reference');
        $brand = $this->get($row, $mapping, 'brand');

        if (empty($reference) || empty($brand)) {
            throw new RuntimeException('Falta referencia o marca');
        }

        $product = Product::updateOrCreate( //evitar duplicados
            ['supplier_id' => $supplierId, 'reference' => $reference],
            [
                'brand' => $brand,
                'ean' => $this->get($row, $mapping, 'ean') ?: null,
                'description' => $this->get($row, $mapping, 'description') ?: null,
                'dimensions' => $this->get($row, $mapping, 'dimensions') ?: null,
                'family' => $this->get($row, $mapping, 'family') ?: null,
                'subfamily' => $this->get($row, $mapping, 'subfamily') ?: null,
            ]
        );

        // Limpio precios anteriores para recrearlos
        $product->prices()->delete();

        $price = $this->getFloat($row, $mapping, 'price');
        $minQty = $this->getInt($row, $mapping, 'min_quantity') ?? 1;

        if ($price !== null) {
            ProductPrice::create([
                'product_id' => $product->id,
                'min_quantity' => $minQty,
                'price' => $price,
            ]);
        }

        // si el Excel trae descuentos por cantidad
        $price2 = $this->getFloat($row, $mapping, 'price_2');
        $minQty2 = $this->getInt($row, $mapping, 'min_quantity_2');

        if ($price2 !== null && $minQty2 !== null) {
            ProductPrice::create([
                'product_id' => $product->id,
                'min_quantity' => $minQty2,
                'price' => $price2,
            ]);
        }
    }

    // los datos vienen como strings y pueden tener formatos distintos según el proveedor los normaliza
    private function get(array $row, array $mapping, string $field): string
    {
        return isset($mapping[$field]) ? trim((string) ($row[$mapping[$field]] ?? '')) : '';
    }

    private function getFloat(array $row, array $mapping, string $field): ?float
    {
        $raw = $this->get($row, $mapping, $field);
        if ($raw === '') return null;
        
        $normalized = str_replace(',', '.', $raw);
        return is_numeric($normalized) ? (float) $normalized : null;
    }

    private function getInt(array $row, array $mapping, string $field): ?int
    {
        $raw = $this->get($row, $mapping, $field);
        return is_numeric($raw) ? (int) $raw : null;
    }

    private function validateRequiredColumns(array $mapping): void
    {
        foreach (['reference', 'brand'] as $field) {
            if (!isset($mapping[$field])) {
                throw new RuntimeException("El Excel no tiene columna para '{$field}'. Revisá el mapper.");
            }
        }
    }
}