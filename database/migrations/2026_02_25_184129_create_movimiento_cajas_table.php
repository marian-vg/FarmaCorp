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
        Schema::create('movimiento_cajas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_movimiento');
            $table->decimal('monto', 10, 2);
            $table->dateTime('fecha_movimiento');
            $table->foreignId('id_medio_pago')->nullable()->constrained('medio_pagos');
            $table->foreignId('id_caja')->constrained('cajas');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_cajas');
    }
};
