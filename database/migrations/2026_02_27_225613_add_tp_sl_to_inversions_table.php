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
            $table->decimal('take_profit_porcentaje', 5, 2)->default(10.00)->after('precio_actual');
            $table->decimal('stop_loss_porcentaje', 5, 2)->default(-5.00)->after('take_profit_porcentaje');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inversions', function (Blueprint $table) {
            $table->dropColumn(['take_profit_porcentaje', 'stop_loss_porcentaje']);
        });
    }
};
