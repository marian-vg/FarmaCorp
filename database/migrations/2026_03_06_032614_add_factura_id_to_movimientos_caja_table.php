<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    // CAMBIAR 'movimientos_caja' por 'movimiento_cajas'
    Schema::table('movimiento_cajas', function (Blueprint $table) {
        $table->foreignId('factura_id')->nullable()->constrained('facturas')->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('movimiento_cajas', function (Blueprint $table) {
        $table->dropColumn('factura_id');
    });
}
};
