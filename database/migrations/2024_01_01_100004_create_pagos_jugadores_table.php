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
        Schema::create('pagos_jugadores', function (Blueprint $table) {
            $table->id('id_pago');
            $table->foreignId('id_jugador')->constrained('jugadores', 'id_jugador')->cascadeOnDelete();
            $table->date('fecha_pago');
            $table->decimal('importe_pago', 10, 2);
            $table->string('concepto_pago')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_jugadores');
    }
};
