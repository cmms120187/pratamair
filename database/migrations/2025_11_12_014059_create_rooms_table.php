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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->unsignedBigInteger('plant_id')->nullable();
            $table->unsignedBigInteger('line_id')->nullable();
            $table->unsignedBigInteger('process_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
            $table->foreign('line_id')->references('id')->on('lines')->onDelete('cascade');
            $table->foreign('process_id')->references('id')->on('processes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
