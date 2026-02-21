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
        $tableNames = config('permission.table_names');
        
        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->text('description')->nullable()->after('guard_name');
            $table->boolean('is_active')->default(true)->after('description');
            $table->foreignId('created_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        
        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['description', 'is_active', 'created_by', 'deleted_at']);
        });
    }
};
