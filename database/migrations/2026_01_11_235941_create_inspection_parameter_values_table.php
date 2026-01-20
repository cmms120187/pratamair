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
        Schema::create('inspection_parameter_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inspection_id');
            $table->unsignedBigInteger('template_parameter_id');
            $table->decimal('parameter_value', 15, 4);
            $table->enum('status', ['normal', 'warning', 'critical'])->default('normal');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('inspection_id')->references('id')->on('inspections')->onDelete('cascade');
            $table->foreign('template_parameter_id')->references('id')->on('inspection_template_parameters')->onDelete('cascade');
            $table->index('inspection_id');
            $table->index('template_parameter_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_parameter_values');
    }
};
