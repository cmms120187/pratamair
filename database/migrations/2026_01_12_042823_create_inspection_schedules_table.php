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
        Schema::create('inspection_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_erp_id');
            $table->unsignedBigInteger('template_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'custom'])->default('daily');
            $table->integer('frequency_value')->default(1); // e.g., every 2 weeks
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('preferred_time')->nullable();
            $table->integer('estimated_duration')->nullable(); // in minutes
            $table->enum('status', ['active', 'inactive', 'completed', 'cancelled'])->default('active');
            $table->unsignedBigInteger('assigned_to')->nullable(); // user_id
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('machine_erp_id')->references('id')->on('machine_erp')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('inspection_templates')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->index(['machine_erp_id', 'status']);
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_schedules');
    }
};
