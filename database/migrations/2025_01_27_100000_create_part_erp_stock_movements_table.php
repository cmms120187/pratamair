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
        Schema::create('part_erp_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_erp_id')->constrained('part_erp')->onDelete('cascade');
            $table->string('type', 20)->default('in'); // in, out, adjustment
            $table->string('document_type', 10)->nullable(); // MR, PO, MO
            $table->string('document_number')->nullable();
            $table->integer('quantity'); // positive for in, negative for out
            $table->integer('balance_after')->nullable(); // stock after this movement
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_erp_stock_movements');
    }
};
