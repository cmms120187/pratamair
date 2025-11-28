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
        Schema::table('machine_erp', function (Blueprint $table) {
            $table->unsignedBigInteger('machine_type_id')->nullable()->after('type_name');
            $table->foreign('machine_type_id')->references('id')->on('machine_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_erp', function (Blueprint $table) {
            $table->dropForeign(['machine_type_id']);
            $table->dropColumn('machine_type_id');
        });
    }
};
