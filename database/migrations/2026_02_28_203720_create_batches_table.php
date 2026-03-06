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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained(
                table: 'medicines', indexName: 'batches_medicine_id_foreign', column: 'product_id'
            )->cascadeOnDelete();
            $table->string('batch_number');
            $table->date('expiration_date');
            $table->integer('initial_quantity');
            $table->integer('current_quantity');
            $table->integer('minimum_stock')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
