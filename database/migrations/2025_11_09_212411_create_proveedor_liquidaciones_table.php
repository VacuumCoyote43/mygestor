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
        Schema::create('proveedor_liquidaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proveedor_id');
            $table->decimal('monto', 10, 2);
            $table->date('fecha_pago')->default(now());
            $table->string('metodo_pago')->nullable(); // Transferencia, efectivo, bizum, etc.
            $table->text('nota')->nullable();
            $table->timestamps();

            $table->foreign('proveedor_id')->references('id_proveedor')->on('proveedores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedor_liquidaciones');
    }
};
