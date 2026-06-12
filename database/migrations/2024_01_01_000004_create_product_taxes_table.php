<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Código de país ISO 3166-1 alpha-2: 'UY'
            $table->string('country_code', 2);

            //Unidad a la que aplica el impuesto: 'unit', 'kg', 'litre'
            $table->string('unit_type')->default('unit');

            // Tasa como decimal: 0.21 = 21%, 0.10 = 10%
            $table->decimal('rate', 6, 4);

            $table->timestamps();

            //Un producto no puede tener dos impuestos para el mismo país + unidad
            $table->unique(['product_id', 'country_code', 'unit_type']);

            // Índice para buscar todos los impuestos de un producto por país
            $table->index(['product_id', 'country_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_taxes');
    }
};
