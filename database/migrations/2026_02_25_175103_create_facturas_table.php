<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_comprobante')->default('VENTA-POS');
            $table->timestamp('fecha_emision');
            $table->decimal('total', 10, 2);
            $table->string('estado')->default('PAGADO');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('cliente_id')->nullable()->constrained('clients');
            $table->foreignId('medio_pago_id')->nullable()->constrained('medio_pagos');
            $table->decimal('ajuste_global', 10, 2)->default(0);

            $table->timestamps();
        });

        Schema::create('factura_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->foreignId('factura_id')->constrained('facturas')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_detalles');
        Schema::dropIfExists('facturas');
    }
};
