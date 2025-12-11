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
        Schema::create('standard_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('standard_id');
            $table->string('name'); // Nama variant (contoh: "New machine condition", "Unlimited long-term operation allowable")
            $table->decimal('min_value', 15, 4)->nullable(); // Nilai minimum untuk variant ini
            $table->decimal('max_value', 15, 4)->nullable(); // Nilai maksimum untuk variant ini
            $table->string('color')->nullable(); // Warna untuk visualisasi (contoh: "#22C55E" untuk hijau)
            $table->integer('order')->default(0); // Urutan tampilan
            $table->timestamps();

            $table->foreign('standard_id')->references('id')->on('standards')->onDelete('cascade');
            $table->index(['standard_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_variants');
    }
};

