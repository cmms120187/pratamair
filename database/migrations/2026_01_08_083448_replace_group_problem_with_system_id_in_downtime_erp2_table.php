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
        Schema::table('downtime_erp2', function (Blueprint $table) {
            // Drop old column if exists
            if (Schema::hasColumn('downtime_erp2', 'groupProblem')) {
                $table->dropColumn('groupProblem');
            }
            
            // Add new system_id column
            $table->unsignedBigInteger('system_id')->nullable()->after('nameCoord');
            $table->foreign('system_id')->references('id')->on('systems')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('downtime_erp2', function (Blueprint $table) {
            // Drop foreign key and column
            if (Schema::hasColumn('downtime_erp2', 'system_id')) {
                $table->dropForeign(['system_id']);
                $table->dropColumn('system_id');
            }
            
            // Restore old column
            if (!Schema::hasColumn('downtime_erp2', 'groupProblem')) {
                $table->string('groupProblem')->nullable()->after('nameCoord');
            }
        });
    }
};
