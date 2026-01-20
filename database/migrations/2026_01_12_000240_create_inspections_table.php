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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_erp_id');
            $table->unsignedBigInteger('template_id');
            $table->date('inspection_date');
            $table->unsignedBigInteger('performed_by');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('machine_erp_id')->references('id')->on('machine_erp')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('inspection_templates')->onDelete('restrict');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('restrict');
            $table->index('machine_erp_id');
            $table->index('template_id');
            $table->index('inspection_date');
            $table->index('performed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
