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
        Schema::table('prescriptions', function (Blueprint $table) {
            // Agregamos la matrícula del médico y la fecha de emisión de la receta física
            $table->string('doctor_license')->nullable()->after('client_id'); // Matrícula
            $table->date('prescription_date')->nullable()->after('doctor_license'); // Fecha de la receta
            $table->string('authorization_code')->nullable()->after('prescription_date'); // El AUTH-XXXX
        });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            //
        });
    }
};
