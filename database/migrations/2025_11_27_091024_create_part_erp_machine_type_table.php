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
        Schema::create('part_erp_machine_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_erp_id')->constrained('part_erp')->onDelete('cascade');
            $table->foreignId('machine_type_id')->constrained('machine_types')->onDelete('cascade');
            $table->timestamps();
            
            // Ensure unique combination
            $table->unique(['part_erp_id', 'machine_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_erp_machine_type');
    }
};
