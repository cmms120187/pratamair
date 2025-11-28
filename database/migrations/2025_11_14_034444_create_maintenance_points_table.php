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
        Schema::create('maintenance_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_type_id');
            $table->enum('category', ['autonomous', 'preventive', 'predictive']);
            $table->enum('frequency_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom'])->nullable();
            $table->integer('frequency_value')->default(1);
            $table->string('name');
            $table->text('instruction')->nullable();
            $table->string('photo')->nullable();
            $table->integer('sequence')->default(0);
            $table->timestamps();

            $table->foreign('machine_type_id')->references('id')->on('machine_types')->onDelete('cascade');
            $table->index(['machine_type_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_points');
    }
};
