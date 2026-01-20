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
        Schema::table('machine_types', function (Blueprint $table) {
            $table->unsignedBigInteger('photo_id')->nullable()->after('photo');
            $table->foreign('photo_id')->references('id')->on('photos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_types', function (Blueprint $table) {
            $table->dropForeign(['photo_id']);
            $table->dropColumn('photo_id');
        });
    }
};
