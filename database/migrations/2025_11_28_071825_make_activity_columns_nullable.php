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
        Schema::table('activities', function (Blueprint $table) {
            $table->string('plant')->nullable()->change();
            $table->string('process')->nullable()->change();
            $table->string('line')->nullable()->change();
            $table->string('room_name')->nullable()->change();
            $table->string('id_mekanik')->nullable()->change();
            $table->string('nama_mekanik')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('plant')->nullable(false)->change();
            $table->string('process')->nullable(false)->change();
            $table->string('line')->nullable(false)->change();
            $table->string('room_name')->nullable(false)->change();
            $table->string('id_mekanik')->nullable(false)->change();
            $table->string('nama_mekanik')->nullable(false)->change();
        });
    }
};
