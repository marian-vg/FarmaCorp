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
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->foreignId('producto_id')->primary()->constrained('productos')->cascadeOnDelete();
            $table->string('nivel')->nullable();
            $table->text('prospecto')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->boolean('es_psicotropico')->default(false);
            $table->unsignedBigInteger('grupo_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicamentos');
    }
};
