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
    $table->string('numero_comprobante')->unique(); // Ej: FAC-00001
    $table->decimal('total', 10, 2);
    $table->foreignId('user_id')->constrained(); // Responsable 
    $table->foreignId('medio_pago_id')->constrained('medio_pagos');
    $table->foreignId('caja_id')->constrained('cajas'); // Para saber en qué turno se vendió
    $table->timestamps();
    });

    Schema::create('factura_detalles', function (Blueprint $table) {
        $table->id();
        $table->foreignId('factura_id')->constrained()->onDelete('cascade');
        $table->foreignId('product_id')->constrained('products'); // El producto vendido 
        $table->integer('cantidad');
        $table->decimal('precio_unitario', 10, 2);
        $table->decimal('subtotal', 10, 2);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
