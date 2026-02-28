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
        // Forzamos el nombre 'operaciones' desde la migración
        Schema::create('operaciones', function (Blueprint $table) {
            $table->id();
            $table->integer('iol_id')->unique(); 
            $table->string('simbolo');           
            $table->string('tipo');              
            $table->decimal('cantidad', 15, 8);
            $table->decimal('precio_unitario', 15, 2);
            $table->timestamp('fecha_ejecucion'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operaciones');
    }
};
