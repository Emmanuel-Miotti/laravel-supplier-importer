<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();

            $table->string('reference');
            $table->string('brand');
            $table->string('ean')->nullable();
            $table->text('description')->nullable();
            $table->string('dimensions')->nullable();
            $table->string('family')->nullable();
            $table->string('subfamily')->nullable();

            $table->timestamps();

            // Un proveedor no puede tener dos productos con la misma referencia
            $table->unique(['supplier_id', 'reference']);

            // Índice para buscar por marca query más común de la API
            $table->index('brand');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
