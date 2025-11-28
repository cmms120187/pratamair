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
        Schema::create('machine_type_standard', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_type_id');
            $table->unsignedBigInteger('standard_id');
            $table->timestamps();

            $table->foreign('machine_type_id')->references('id')->on('machine_types')->onDelete('cascade');
            $table->foreign('standard_id')->references('id')->on('standards')->onDelete('cascade');
            $table->unique(['machine_type_id', 'standard_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_type_standard');
    }
};
