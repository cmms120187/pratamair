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
        Schema::create('standards', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama standar (contoh: "Standar Suhu", "Standar Tekanan", "Standar Getaran")
            $table->string('reference_type')->nullable(); // Tipe referensi (contoh: "ISO", "Factory Standard", "Custom", dll)
            $table->string('reference_code')->nullable(); // Kode referensi (contoh: "ISO 9001", "FS-001", dll)
            $table->string('reference_name')->nullable(); // Nama referensi lengkap (contoh: "ISO 9001:2015 Quality Management", "Factory Standard - Temperature Control")
            $table->string('class')->nullable(); // Class/Group untuk standar (contoh: "Machine Group 4 - Rigid Foundation")
            $table->string('unit')->nullable(); // Unit pengukuran (contoh: "°C", "bar", "mm/s")
            $table->decimal('min_value', 15, 4)->nullable(); // Nilai minimum standar
            $table->decimal('max_value', 15, 4)->nullable(); // Nilai maksimum standar
            $table->decimal('target_value', 15, 4)->nullable(); // Nilai target standar
            $table->text('description')->nullable(); // Deskripsi standar
            $table->text('keterangan')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedBigInteger('machine_type_id')->nullable(); // Optional: standar untuk jenis mesin tertentu
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('machine_type_id')->references('id')->on('machine_types')->onDelete('set null');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standards');
    }
};
