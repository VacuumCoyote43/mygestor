<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProveedorLiquidacion extends Model
{
    use HasFactory;

    protected $table = 'proveedor_liquidaciones';

    protected $fillable = [
        'proveedor_id',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'nota',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto' => 'decimal:2',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id', 'id_proveedor');
    }
}
