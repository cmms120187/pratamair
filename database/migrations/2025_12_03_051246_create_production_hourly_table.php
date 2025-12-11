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
        Schema::create('production_hourly', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('line_id');
            $table->unsignedBigInteger('process_id');
            $table->date('production_date');
            $table->tinyInteger('hour')->unsigned(); // 0-23
            $table->integer('total_production')->default(0);
            $table->integer('grade_b')->default(0); // Defect Grade B
            $table->integer('grade_c')->default(0); // Defect Grade C
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('line_id')->references('id')->on('lines')->onDelete('cascade');
            $table->foreign('process_id')->references('id')->on('processes')->onDelete('cascade');
            
            // Ensure unique combination of line, process, date, and hour
            $table->unique(['line_id', 'process_id', 'production_date', 'hour'], 'unique_production_hourly');
            
            // Index for faster queries
            $table->index(['production_date', 'hour']);
            $table->index(['line_id', 'process_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_hourly');
    }
};
