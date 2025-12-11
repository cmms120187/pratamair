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
        Schema::create('mutasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_erp_id'); // Mesin yang dipindahkan
            $table->unsignedBigInteger('old_room_erp_id')->nullable(); // Room lama
            $table->unsignedBigInteger('new_room_erp_id'); // Room baru
            $table->date('date'); // Tanggal mutasi
            $table->string('reason')->nullable(); // Alasan mutasi
            $table->text('description')->nullable(); // Deskripsi
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('machine_erp_id')->references('id')->on('machine_erp')->onDelete('cascade');
            $table->foreign('old_room_erp_id')->references('id')->on('room_erp')->onDelete('set null');
            $table->foreign('new_room_erp_id')->references('id')->on('room_erp')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi');
    }
};
