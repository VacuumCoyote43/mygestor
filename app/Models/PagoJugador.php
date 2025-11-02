<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo PagoJugador
 * 
 * Representa un pago realizado por un jugador
 */
class PagoJugador extends Model
{
    protected $primaryKey = 'id_pago';
    
    protected $table = 'pagos_jugadores';
    
    public $timestamps = true;
    
    protected $fillable = [
        'id_jugador',
        'fecha_pago',
        'importe_pago',
        'concepto_pago',
    ];
    
    protected $casts = [
        'fecha_pago' => 'date',
        'importe_pago' => 'decimal:2',
    ];

    /**
     * Un pago pertenece a un jugador
     */
    public function jugador(): BelongsTo
    {
        return $this->belongsTo(Jugador::class, 'id_jugador', 'id_jugador');
    }

    /**
     * Actualizar el saldo del jugador después de crear/actualizar/eliminar un pago
     */
    protected static function booted(): void
    {
        static::created(function ($pago) {
            $pago->jugador->actualizarSaldo();
        });

        static::updated(function ($pago) {
            $pago->jugador->actualizarSaldo();
        });

        static::deleted(function ($pago) {
            $pago->jugador->actualizarSaldo();
        });
    }
}
