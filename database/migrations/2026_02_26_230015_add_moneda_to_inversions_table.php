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
        Schema::table('inversions', function (Blueprint $table) {
            $table->string('moneda', 5)->default('ARS')->after('activo'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inversions', function (Blueprint $table) {
            $table->dropColumn('moneda');
        });
    }
};
