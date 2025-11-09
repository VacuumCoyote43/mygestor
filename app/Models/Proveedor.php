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
     * Un proveedor tiene muchas liquidaciones
     */
    public function liquidaciones(): HasMany
    {
        return $this->hasMany(ProveedorLiquidacion::class, 'proveedor_id', 'id_proveedor');
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

    /**
     * Calcular el balance del proveedor (gastos - liquidaciones)
     * Positivo = deuda pendiente, Negativo o cero = pagado o a favor
     * 
     * @return float
     */
    public function getBalanceAttribute(): float
    {
        $totalGastos = (float) $this->gastos()->sum('importe_total_gasto');
        $totalPagado = (float) $this->liquidaciones()->sum('monto');
        
        return $totalGastos - $totalPagado;
    }

    /**
     * Obtener el total de gastos del proveedor
     * 
     * @return float
     */
    public function getTotalGastosAttribute(): float
    {
        return (float) $this->gastos()->sum('importe_total_gasto');
    }

    /**
     * Obtener el total pagado en liquidaciones
     * 
     * @return float
     */
    public function getTotalPagadoAttribute(): float
    {
        return (float) $this->liquidaciones()->sum('monto');
    }
}
