<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Proveedor
 * 
 * Representa a un proveedor de servicios o productos
 */
class Proveedor extends Model
{
    protected $primaryKey = 'id_proveedor';
    
    protected $table = 'proveedores';
    
    public $timestamps = true;
    
    protected $fillable = [
        'nombre_proveedor',
        'tipo_proveedor',
        'saldo_proveedor',
    ];
    
    protected $casts = [
        'saldo_proveedor' => 'decimal:2',
    ];

    /**
     * Un proveedor tiene muchos gastos
     */
    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class, 'proveedor_id', 'id_proveedor');
    }

    /**
     * Calcular el saldo del proveedor basado en gastos
     * 
     * @return float
     */
    public function calcularSaldo(): float
    {
        return $this->gastos()->sum('importe_total_gasto');
    }

    /**
     * Actualizar el saldo del proveedor
     */
    public function actualizarSaldo(): void
    {
        $this->saldo_proveedor = (string) $this->calcularSaldo();
        $this->save();
    }
}
