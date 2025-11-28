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
        Schema::create('machine_erp', function (Blueprint $table) {
            $table->id();
            $table->string('idMachine');
            $table->string('plant_name')->nullable(); // Manual input, bukan foreign key
            $table->string('process_name')->nullable(); // Manual input, bukan foreign key
            $table->string('line_name')->nullable(); // Manual input, bukan foreign key
            $table->string('room_name')->nullable(); // Manual input, bukan foreign key
            $table->string('type_name')->nullable(); // Manual input, bukan foreign key
            $table->string('brand_name')->nullable(); // Manual input, bukan foreign key
            $table->string('model_name')->nullable(); // Manual input, bukan foreign key
            $table->string('serial_number')->nullable();
            $table->integer('tahun_production')->nullable();
            $table->string('no_document')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_erp');
    }
};
