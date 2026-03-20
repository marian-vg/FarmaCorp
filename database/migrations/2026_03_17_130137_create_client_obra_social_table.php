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
    Schema::create('client_obra_social', function (Blueprint $table) {
        $table->id();
        // Relaciones
        $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
        $table->foreignId('obra_social_id')->constrained('obras_sociales')->onDelete('cascade');
        
        // Datos del carnet del cliente
        $table->string('affiliate_number')->nullable(); // Número de carnet/socio
        
        $table->timestamps();

        // Un cliente puede tener varias obras sociales, pero no repetidas
        $table->unique(['client_id', 'obra_social_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_obra_social');
    }
};
