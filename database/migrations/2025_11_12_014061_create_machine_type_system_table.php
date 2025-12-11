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
        Schema::create('machine_type_system', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_type_id');
            $table->unsignedBigInteger('system_id');
            $table->timestamps();

            $table->foreign('machine_type_id')->references('id')->on('machine_types')->onDelete('cascade');
            $table->foreign('system_id')->references('id')->on('systems')->onDelete('cascade');
            $table->unique(['machine_type_id', 'system_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_type_system');
    }
};

