<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_comprobante')->default('VENTA-POS');
            $table->timestamp('fecha_emision');
            $table->decimal('total', 10, 2);
            $table->string('estado')->default('PAGADO');
            $table->foreignId('user_id')->constrained(); // credencial (FK) [cite: 508]
            $table->foreignId('cliente_id')->nullable()->constrained('clients'); // ID_Cliente (FK) [cite: 509]
            $table->foreignId('medio_pago_id')->constrained('medio_pagos'); 
            $table->timestamps();
});

        Schema::create('factura_detalles', function (Blueprint $table) {
            $table->id(); // ID_Detalle (PK)
            $table->integer('cantidad'); // cantidad
            $table->decimal('precio_unitario', 10, 2); // precio_unitario
            $table->decimal('descuento', 10, 2)->default(0); // descuento
            $table->foreignId('factura_id')->constrained('facturas')->onDelete('cascade'); // ID_Factura (FK)
            $table->foreignId('product_id')->constrained('products'); // ID_Producto (FK)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_detalles'); 
        Schema::dropIfExists('facturas');
    }
};
