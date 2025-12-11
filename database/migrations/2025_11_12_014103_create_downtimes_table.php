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
        Schema::create('downtimes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_id');
            $table->date('date');
            $table->dateTime('stopProduction');
            $table->dateTime('responMechanic');
            $table->dateTime('startProduction');
            $table->integer('duration');
            $table->integer('standard_time')->nullable();
            $table->unsignedBigInteger('problem_id');
            $table->unsignedBigInteger('problem_mm_id')->nullable();
            $table->unsignedBigInteger('reason_id');
            $table->unsignedBigInteger('action_id');
            $table->unsignedBigInteger('group_id');
            $table->string('part')->nullable();
            $table->unsignedBigInteger('mekanik_id');
            $table->unsignedBigInteger('leader_id');
            $table->unsignedBigInteger('coord_id');
            $table->timestamps();

            $table->foreign('machine_id')->references('id')->on('machines')->onDelete('cascade');
            $table->foreign('problem_id')->references('id')->on('problems')->onDelete('cascade');
            $table->foreign('problem_mm_id')->references('id')->on('problem_mms')->onDelete('cascade');
            $table->foreign('reason_id')->references('id')->on('reasons')->onDelete('cascade');
            $table->foreign('action_id')->references('id')->on('actions')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('mekanik_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('leader_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('coord_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downtimes');
    }
};
