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
        Schema::create('preventive_maintenance_executions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->date('scheduled_date');
            $table->dateTime('actual_start_time')->nullable();
            $table->dateTime('actual_end_time')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('performed_by')->nullable(); // user_id
            $table->text('findings')->nullable();
            $table->text('actions_taken')->nullable();
            $table->text('notes')->nullable();
            $table->json('checklist')->nullable(); // JSON untuk checklist items
            $table->decimal('cost', 15, 2)->nullable();
            $table->string('photo_before')->nullable();
            $table->string('photo_after')->nullable();
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('preventive_maintenance_schedules')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['schedule_id', 'status']);
            $table->index('scheduled_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preventive_maintenance_executions');
    }
};
