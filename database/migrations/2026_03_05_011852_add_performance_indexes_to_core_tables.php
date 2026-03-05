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
        Schema::table('medicines', function (Blueprint $table) {
            $table->index('group_id', 'idx_medicines_group_id');
            $table->index('is_psychotropic', 'idx_medicines_is_psychotropic');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->index('medicine_id', 'idx_batches_medicine_id');
            $table->index('expiration_date', 'idx_batches_expiration_date');
            $table->index('current_quantity', 'idx_batches_current_quantity');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index('batch_id', 'idx_sm_batch_id');
            $table->index('user_id', 'idx_sm_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropIndex('idx_medicines_group_id');
            $table->dropIndex('idx_medicines_is_psychotropic');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropIndex('idx_batches_medicine_id');
            $table->dropIndex('idx_batches_expiration_date');
            $table->dropIndex('idx_batches_current_quantity');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('idx_sm_batch_id');
            $table->dropIndex('idx_sm_user_id');
        });
    }
};
