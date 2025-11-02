<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modelo Jugador
 * 
 * Representa a un jugador del equipo deportivo
 */
class Jugador extends Model
{
    protected $primaryKey = 'id_jugador';
    
    protected $table = 'jugadores';
    
    public $timestamps = true;
    
    protected $fillable = [
        'nombre_jugador',
        'dni',
        'fecha_nacimiento',
        'dorsal',
        'talla_camiseta',
        'talla_pantalon',
        'talla_medias',
        'mode',
        'saldo_jugador',
    ];
    
    protected $casts = [
        'saldo_jugador' => 'decimal:2',
    ];

    /**
     * Un jugador tiene muchos pagos
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(PagoJugador::class, 'id_jugador', 'id_jugador');
    }

    /**
     * Un jugador pertenece a muchos gastos a través de la tabla pivote gasto_jugador
     */
    public function gastos(): BelongsToMany
    {
        return $this->belongsToMany(Gasto::class, 'gasto_jugador', 'id_jugador', 'id_gasto')
                    ->withPivot('importe_asignado')
                    ->withTimestamps();
    }

    /**
     * Calcular el saldo del jugador basado en gastos y pagos
     * 
     * @return float
     */
    public function calcularSaldo(): float
    {
        $totalGastos = $this->gastos()->sum('importe_asignado');
        $totalPagos = $this->pagos()->sum('importe_pago');
        return $totalGastos - $totalPagos;
    }

    /**
     * Actualizar el saldo del jugador
     */
    public function actualizarSaldo(): void
    {
        $this->saldo_jugador = (string) $this->calcularSaldo();
        $this->save();
    }
}
