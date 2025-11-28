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
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plant_id');
            $table->unsignedBigInteger('process_id');
            $table->unsignedBigInteger('line_id');
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('model_id');
            $table->string('idMachine')->unique();
            $table->string('serial_number')->nullable();
            $table->integer('tahun_production')->nullable();
            $table->string('no_document')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();

            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
            $table->foreign('process_id')->references('id')->on('processes')->onDelete('cascade');
            $table->foreign('line_id')->references('id')->on('lines')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('machine_types')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
            $table->foreign('model_id')->references('id')->on('models')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
