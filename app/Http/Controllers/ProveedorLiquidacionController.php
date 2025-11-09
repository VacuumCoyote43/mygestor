<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\ProveedorLiquidacion;
use Illuminate\Http\Request;

class ProveedorLiquidacionController extends Controller
{
    public function index(string $proveedor)
    {
        $proveedor = Proveedor::findOrFail($proveedor);
        $liquidaciones = $proveedor->liquidaciones()->latest()->get();
        return view('proveedores.liquidaciones.index', compact('proveedor', 'liquidaciones'));
    }

    public function create(string $proveedor)
    {
        $proveedor = Proveedor::findOrFail($proveedor);
        return view('proveedores.liquidaciones.create', compact('proveedor'));
    }

    public function store(Request $request, string $proveedor)
    {
        $proveedor = Proveedor::findOrFail($proveedor);
        
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'nullable|string|max:50',
            'nota' => 'nullable|string|max:500',
        ]);

        ProveedorLiquidacion::create([
            'proveedor_id' => $proveedor->id_proveedor,
            'monto' => $request->monto,
            'fecha_pago' => $request->fecha_pago,
            'metodo_pago' => $request->metodo_pago,
            'nota' => $request->nota,
        ]);

        return redirect()
            ->route('proveedores.liquidaciones.index', $proveedor->id_proveedor)
            ->with('success', 'Liquidación registrada correctamente.');
    }
}
