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
        Schema::table('oportunidads', function (Blueprint $table) {
            $table->boolean('es_recurrente')->default(false)->after('is_active');
            $table->timestamp('disponible_desde')->nullable()->after('es_recurrente');
            $table->decimal('mejora_porcentaje', 5, 2)->default(0)->after('precio_gatillo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oportunidads', function (Blueprint $table) {
            $table->dropColumn(['es_recurrente', 'disponible_desde', 'mejora_porcentaje']);
        });
    }
};
