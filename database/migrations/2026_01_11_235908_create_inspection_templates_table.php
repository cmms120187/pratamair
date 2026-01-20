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
        Schema::create('inspection_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_type_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'custom'])->default('daily');
            $table->timestamps();

            $table->foreign('machine_type_id')->references('id')->on('machine_types')->onDelete('cascade');
            $table->index('machine_type_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_templates');
    }
};
