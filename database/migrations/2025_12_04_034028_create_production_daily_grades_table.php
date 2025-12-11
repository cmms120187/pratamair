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
        Schema::create('production_daily_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('line_id');
            $table->unsignedBigInteger('process_id');
            $table->date('production_date');
            $table->integer('grade_b')->default(0); // Total Grade B per hari
            $table->integer('grade_c')->default(0); // Total Grade C per hari
            $table->timestamps();

            $table->foreign('line_id')->references('id')->on('lines')->onDelete('cascade');
            $table->foreign('process_id')->references('id')->on('processes')->onDelete('cascade');
            
            // Ensure unique combination of line, process, and date
            $table->unique(['line_id', 'process_id', 'production_date'], 'unique_production_daily_grades');
            
            // Index for faster queries
            $table->index(['production_date']);
            $table->index(['line_id', 'process_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_daily_grades');
    }
};
