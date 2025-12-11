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
        Schema::table('reasons', function (Blueprint $table) {
            $table->unsignedBigInteger('system_id')->nullable()->after('name');
            $table->unsignedBigInteger('problem_id')->nullable()->after('system_id');
            
            $table->foreign('system_id')->references('id')->on('systems')->onDelete('set null');
            $table->foreign('problem_id')->references('id')->on('problems')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reasons', function (Blueprint $table) {
            $table->dropForeign(['system_id']);
            $table->dropForeign(['problem_id']);
            $table->dropColumn(['system_id', 'problem_id']);
        });
    }
};
