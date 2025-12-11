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
            $table->string('kode_room')->nullable()->after('idMachine');
        });
        
        Schema::table('activities', function (Blueprint $table) {
            $table->string('kode_room')->nullable()->after('date');
        });
        
        Schema::table('downtime_erp2', function (Blueprint $table) {
            $table->string('kode_room')->nullable()->after('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_erp', function (Blueprint $table) {
            $table->dropColumn('kode_room');
        });
        
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('kode_room');
        });
        
        Schema::table('downtime_erp2', function (Blueprint $table) {
            $table->dropColumn('kode_room');
        });
    }
};
