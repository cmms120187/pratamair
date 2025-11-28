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
        Schema::create('predictive_maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_id');
            $table->unsignedBigInteger('maintenance_point_id')->nullable();
            $table->unsignedBigInteger('standard_id'); // Reference to standards table
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('frequency_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom']);
            $table->integer('frequency_value')->default(1); // e.g., every 2 weeks, every 3 months
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('preferred_time')->nullable();
            $table->integer('estimated_duration')->nullable(); // in minutes
            $table->enum('status', ['active', 'inactive', 'completed', 'cancelled'])->default('active');
            $table->unsignedBigInteger('assigned_to')->nullable(); // user_id
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('machine_id')->references('id')->on('machines')->onDelete('cascade');
            $table->foreign('maintenance_point_id')->references('id')->on('maintenance_points')->onDelete('set null');
            $table->foreign('standard_id')->references('id')->on('standards')->onDelete('restrict');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->index(['machine_id', 'status']);
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictive_maintenance_schedules');
    }
};
