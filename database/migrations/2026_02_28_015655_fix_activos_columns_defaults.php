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
        Schema::table('activos', function (Blueprint $table) {
            // Hacemos que sean opcionales para que el seeder no explote
            $table->decimal('cantidad_total', 15, 8)->nullable()->change();
            $table->decimal('precio_actual', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activos', function (Blueprint $table) {
            $table->decimal('cantidad_total', 15, 8)->nullable(false)->change();
            $table->decimal('precio_actual', 15, 2)->nullable(false)->change();
        });
    }
};
