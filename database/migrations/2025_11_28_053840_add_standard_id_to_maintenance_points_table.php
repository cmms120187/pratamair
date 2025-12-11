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
        Schema::table('maintenance_points', function (Blueprint $table) {
            $table->unsignedBigInteger('standard_id')->nullable()->after('category');
            $table->foreign('standard_id')->references('id')->on('standards')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_points', function (Blueprint $table) {
            $table->dropForeign(['standard_id']);
            $table->dropColumn('standard_id');
        });
    }
};
