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
        Schema::create('downtime_erp2', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('plant');
            $table->string('process');
            $table->string('line');
            $table->string('roomName');
            $table->string('idMachine');
            $table->string('typeMachine');
            $table->string('modelMachine');
            $table->string('brandMachine');
            $table->string('stopProduction');
            $table->string('responMechanic');
            $table->string('startProduction');
            $table->string('duration');
            $table->string('Standar_Time')->nullable();
            $table->string('problemDowntime');
            $table->string('Problem_MM')->nullable();
            $table->string('reasonDowntime');
            $table->string('actionDowtime');
            $table->string('Part')->nullable();
            $table->string('idMekanik');
            $table->string('nameMekanik');
            $table->string('idLeader');
            $table->string('nameLeader');
            $table->string('idGL')->nullable();
            $table->string('nameGL')->nullable();
            $table->string('idCoord');
            $table->string('nameCoord');
            $table->string('groupProblem');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downtime_erp2');
    }
};
