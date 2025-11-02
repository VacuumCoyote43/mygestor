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
        Schema::table('jugadores', function (Blueprint $table) {
            $table->string('dni', 15)->nullable()->after('nombre_jugador');
            $table->date('fecha_nacimiento')->nullable()->after('dni');
            $table->integer('dorsal')->nullable()->after('fecha_nacimiento');
            $table->string('talla_camiseta', 5)->nullable()->after('dorsal');
            $table->string('talla_pantalon', 5)->nullable()->after('talla_camiseta');
            $table->string('talla_medias', 5)->nullable()->after('talla_pantalon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jugadores', function (Blueprint $table) {
            $table->dropColumn([
                'dni',
                'fecha_nacimiento',
                'dorsal',
                'talla_camiseta',
                'talla_pantalon',
                'talla_medias'
            ]);
        });
    }
};
