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
        Schema::create('gasto_jugador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_gasto')->constrained('gastos', 'id_gasto')->cascadeOnDelete();
            $table->foreignId('id_jugador')->constrained('jugadores', 'id_jugador')->cascadeOnDelete();
            $table->decimal('importe_asignado', 10, 2);
            $table->timestamps();
            
            // Evitar duplicados: un jugador no puede tener el mismo gasto asignado dos veces
            $table->unique(['id_gasto', 'id_jugador']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gasto_jugador');
    }
};
