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
        Schema::create('obra_social_medicine', function (Blueprint $table) {
            $table->id();
            // Relaciones
            $table->foreignId('obra_social_id')->constrained('obras_sociales')->onDelete('cascade');
            $table->foreignId('medicine_id')->constrained('medicines')->onDelete('cascade');
            
            // El porcentaje de descuento (Ej: 40.00 para un 40%)
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            
            $table->timestamps();

            // Evitamos que un mismo medicamento esté duplicado para la misma Obra Social
            $table->unique(['obra_social_id', 'medicine_id'], 'os_medicine_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obra_social_medicine');
    }
};
