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
        Schema::create('medio_pagos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo_medio');
            $table->decimal('recargo', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medio_pagos');
    }
};
