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
        Schema::create('standard_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('standard_id')->nullable(); // Nullable untuk photo yang bisa digunakan oleh multiple standards
            $table->string('photo_path'); // Path ke file photo di storage
            $table->string('name')->nullable(); // Nama photo (opsional)
            $table->timestamps();

            $table->foreign('standard_id')->references('id')->on('standards')->onDelete('cascade');
            $table->index('standard_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_photos');
    }
};
