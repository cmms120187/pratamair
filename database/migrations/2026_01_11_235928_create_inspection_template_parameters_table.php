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
        Schema::create('inspection_template_parameters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inspection_template_id');
            $table->string('parameter_name');
            $table->string('unit')->nullable();
            $table->decimal('min_value', 15, 4)->nullable();
            $table->decimal('max_value', 15, 4)->nullable();
            $table->integer('sequence')->default(0);
            $table->text('instruction')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();

            $table->foreign('inspection_template_id')->references('id')->on('inspection_templates')->onDelete('cascade');
            $table->index('inspection_template_id');
            $table->index('sequence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_template_parameters');
    }
};
