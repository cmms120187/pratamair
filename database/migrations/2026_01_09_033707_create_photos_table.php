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
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->string('original_filename'); // Nama file asli saat upload
            $table->string('stored_filename'); // Nama file yang disimpan di storage
            $table->string('file_path'); // Path relatif di storage (misal: machine-erp/abc123.webp)
            $table->string('file_size')->nullable(); // Ukuran file dalam bytes
            $table->string('mime_type'); // image/jpeg, image/png, image/webp
            $table->integer('width')->nullable(); // Lebar gambar dalam pixel
            $table->integer('height')->nullable(); // Tinggi gambar dalam pixel
            $table->string('related_type')->nullable(); // Tipe relasi: machine_erp, machine_type, model, user, dll
            $table->unsignedBigInteger('related_id')->nullable(); // ID dari tabel terkait
            $table->text('description')->nullable(); // Deskripsi photo (opsional)
            $table->unsignedBigInteger('uploaded_by')->nullable(); // User yang upload
            $table->timestamps();
            
            // Indexes
            $table->index(['related_type', 'related_id']);
            $table->index('uploaded_by');
            
            // Foreign key untuk uploaded_by
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
