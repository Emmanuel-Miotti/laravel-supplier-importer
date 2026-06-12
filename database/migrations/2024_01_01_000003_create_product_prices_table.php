<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Cantidad mínima para acceder a este precio
            $table->unsignedInteger('min_quantity')->default(1);

            // Precio en la moneda del proveedor
            $table->decimal('price', 12, 4);

            $table->timestamps();

            // Un producto no puede tener dos tramos con la misma cantidad mínima
            $table->unique(['product_id', 'min_quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
