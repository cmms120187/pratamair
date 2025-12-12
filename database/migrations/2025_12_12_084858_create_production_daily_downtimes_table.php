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
        Schema::create('production_daily_downtimes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_daily_grade_id');
            $table->string('downtime_type'); // process, quality, material, changeover, planned_maintenance, human_error, power_system, waiting_material, waiting_operator, process_adjustment, quality_inspection
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_minutes'); // Duration in minutes
            $table->text('description')->nullable();
            $table->boolean('include_oee')->default(true); // Whether to include in OEE calculation
            $table->timestamps();

            $table->foreign('production_daily_grade_id')->references('id')->on('production_daily_grades')->onDelete('cascade');
            
            // Index for faster queries
            $table->index(['production_daily_grade_id']);
            $table->index(['downtime_type']);
            $table->index(['include_oee']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_daily_downtimes');
    }
};
