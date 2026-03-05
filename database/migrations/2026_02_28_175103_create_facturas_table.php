<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id(); // ID_Factura (PK) [cite: 504]
            $table->string('tipo_comprobante')->default('VENTA-POS'); // [cite: 505]
            $table->timestamp('fecha_emision'); // [cite: 505]
            $table->decimal('total', 10, 2); // [cite: 506]
            $table->string('estado')->default('PAGADO'); // PAGADO o PENDING (RF-11) [cite: 507]
            
            // Relaciones
            $table->foreignId('user_id')->constrained(); // Credencial (FK) [cite: 508]
            $table->foreignId('cliente_id')->nullable()->constrained('clients'); // ID_Cliente (FK) [cite: 509]
            
            // EL CAMBIO CLAVE: nullable() para permitir Cuenta Corriente (RF-11)
            $table->foreignId('medio_pago_id')->nullable()->constrained('medio_pagos'); 
            
            $table->timestamps();
        });

        Schema::create('factura_detalles', function (Blueprint $table) {
            $table->id(); // ID_Detalle (PK) [cite: 526]
            $table->integer('cantidad'); // [cite: 526]
            $table->decimal('precio_unitario', 10, 2); // [cite: 527]
            $table->decimal('descuento', 10, 2)->default(0); // [cite: 527]
            $table->foreignId('factura_id')->constrained('facturas')->onDelete('cascade'); // [cite: 528]
            $table->foreignId('product_id')->constrained('products'); // [cite: 529]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_detalles'); 
        Schema::dropIfExists('facturas');
    }
};