<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            // ID_Producto (PK) (FK) - Relación 1:1 con Producto [cite: 558]
            $table->foreignId('product_id')->primary()->constrained('products')->onDelete('cascade');
            
            // Atributos definidos en el Modelo Relacional (Pág. 18) [cite: 555]
            $table->integer('stock_minimo')->default(0);      // Stock_Minimo [cite: 561]
            $table->integer('stock_maximo')->nullable();     // Stock_Máximo [cite: 571]
            $table->integer('cantidad_actual')->default(0);  // Cantidad_Actual [cite: 573]
            $table->timestamp('fecha_actualización')->nullable(); // Fecha_Actualización [cite: 572]
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};