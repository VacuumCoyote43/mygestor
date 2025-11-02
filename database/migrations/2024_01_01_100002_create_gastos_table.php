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
        Schema::create('gastos', function (Blueprint $table) {
            $table->id('id_gasto');
            $table->string('tipo_gasto'); // equipación, ficha, seguro, partido, transporte, etc.
            $table->date('fecha_gasto');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores', 'id_proveedor')->nullOnDelete();
            $table->decimal('importe_total_gasto', 10, 2);
            $table->text('descripcion_gasto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
