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
        Schema::create('standard_standard_photo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('standard_id');
            $table->unsignedBigInteger('standard_photo_id');
            $table->timestamps();

            $table->foreign('standard_id')->references('id')->on('standards')->onDelete('cascade');
            $table->foreign('standard_photo_id')->references('id')->on('standard_photos')->onDelete('cascade');
            $table->unique(['standard_id', 'standard_photo_id']); // Prevent duplicate associations
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_standard_photo');
    }
};
