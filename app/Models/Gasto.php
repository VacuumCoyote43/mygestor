<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modelo Gasto
 * 
 * Representa un gasto del equipo
 */
class Gasto extends Model
{
    protected $primaryKey = 'id_gasto';
    
    protected $table = 'gastos';
    
    public $timestamps = true;
    
    protected $fillable = [
        'tipo_gasto',
        'fecha_gasto',
        'proveedor_id',
        'importe_total_gasto',
        'descripcion_gasto',
    ];
    
    protected $casts = [
        'fecha_gasto' => 'date',
        'importe_total_gasto' => 'decimal:2',
    ];

    /**
     * Un gasto pertenece a un proveedor
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id', 'id_proveedor');
    }

    /**
     * Un gasto pertenece a muchos jugadores a través de la tabla pivote gasto_jugador
     */
    public function jugadores(): BelongsToMany
    {
        return $this->belongsToMany(Jugador::class, 'gasto_jugador', 'id_gasto', 'id_jugador')
                    ->withPivot('importe_asignado')
                    ->withTimestamps();
    }

    /**
     * Obtener el total asignado a jugadores
     * 
     * @return float
     */
    public function getTotalAsignadoAttribute(): float
    {
        return $this->jugadores()->sum('importe_asignado');
    }

    /**
     * Verificar si el gasto está completamente repartido
     * 
     * @return bool
     */
    public function estaCompletamenteRepartido(): bool
    {
        return abs($this->importe_total_gasto - $this->total_asignado) < 0.01;
    }
}
