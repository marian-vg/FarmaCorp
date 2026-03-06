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
        Schema::create('medicines', function (Blueprint $table) {
            $table->foreignId('product_id')->primary()->constrained('products')->cascadeOnDelete();
            $table->string('presentation_name')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('level')->nullable();
            $table->text('leaflet')->nullable();
            $table->date('expiration_date')->nullable();
            $table->boolean('is_psychotropic')->default(false);
            $table->unsignedBigInteger('group_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
