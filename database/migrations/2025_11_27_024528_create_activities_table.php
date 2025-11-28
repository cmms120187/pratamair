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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('plant');
            $table->string('process');
            $table->string('line');
            $table->string('room_name');
            $table->string('start'); // Format: hh:mm
            $table->string('stop'); // Format: hh:mm
            $table->integer('duration')->nullable(); // Duration in minutes (calculated: Stop - Start)
            $table->text('description')->nullable();
            $table->text('remarks')->nullable();
            $table->string('id_mekanik');
            $table->string('nama_mekanik');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
