<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\Proveedor;
use App\Models\Jugador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GastoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gastos = Gasto::with('proveedor', 'jugadores')
            ->orderBy('fecha_gasto', 'desc')
            ->paginate(15);
        
        return view('gastos.index', compact('gastos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $proveedores = Proveedor::orderBy('nombre_proveedor')->get();
        $jugadores = Jugador::orderBy('nombre_jugador')->get();
        
        return view('gastos.create', compact('proveedores', 'jugadores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_gasto' => 'required|string|max:255',
            'fecha_gasto' => 'required|date',
            'proveedor_id' => 'nullable|exists:proveedores,id_proveedor',
            'importe_total_gasto' => 'required|numeric|min:0.01',
            'descripcion_gasto' => 'nullable|string',
            'jugadores' => 'nullable|array',
            'jugadores.*.id' => 'required|exists:jugadores,id_jugador',
            'jugadores.*.importe' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Crear el gasto
            $gasto = Gasto::create([
                'tipo_gasto' => $validated['tipo_gasto'],
                'fecha_gasto' => $validated['fecha_gasto'],
                'proveedor_id' => $validated['proveedor_id'] ?? null,
                'importe_total_gasto' => $validated['importe_total_gasto'],
                'descripcion_gasto' => $validated['descripcion_gasto'] ?? null,
            ]);

            // Asignar gastos a jugadores si se proporcionaron
            if (isset($validated['jugadores']) && !empty($validated['jugadores'])) {
                $jugadoresData = [];
                foreach ($validated['jugadores'] as $jugadorData) {
                    $jugadoresData[$jugadorData['id']] = [
                        'importe_asignado' => $jugadorData['importe']
                    ];
                }
                $gasto->jugadores()->attach($jugadoresData);

                // Actualizar saldos de jugadores
                foreach ($gasto->jugadores as $jugador) {
                    $jugador->actualizarSaldo();
                }
            }

            // Actualizar saldo del proveedor si existe
            if ($gasto->proveedor) {
                $gasto->proveedor->actualizarSaldo();
            }

            DB::commit();

            return redirect()->route('gastos.index')
                ->with('success', 'Gasto creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el gasto: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $gasto = Gasto::with('proveedor', 'jugadores')->findOrFail($id);
        return view('gastos.show', compact('gasto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $gasto = Gasto::with('jugadores')->findOrFail($id);
        $proveedores = Proveedor::orderBy('nombre_proveedor')->get();
        $jugadores = Jugador::orderBy('nombre_jugador')->get();
        
        return view('gastos.edit', compact('gasto', 'proveedores', 'jugadores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $gasto = Gasto::findOrFail($id);

        $validated = $request->validate([
            'tipo_gasto' => 'required|string|max:255',
            'fecha_gasto' => 'required|date',
            'proveedor_id' => 'nullable|exists:proveedores,id_proveedor',
            'importe_total_gasto' => 'required|numeric|min:0.01',
            'descripcion_gasto' => 'nullable|string',
            'jugadores' => 'nullable|array',
            'jugadores.*.id' => 'required|exists:jugadores,id_jugador',
            'jugadores.*.importe' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Guardar jugadores anteriores para actualizar sus saldos
            $jugadoresAnteriores = $gasto->jugadores->pluck('id_jugador')->toArray();

            // Actualizar el gasto
            $gasto->update([
                'tipo_gasto' => $validated['tipo_gasto'],
                'fecha_gasto' => $validated['fecha_gasto'],
                'proveedor_id' => $validated['proveedor_id'] ?? null,
                'importe_total_gasto' => $validated['importe_total_gasto'],
                'descripcion_gasto' => $validated['descripcion_gasto'] ?? null,
            ]);

            // Actualizar asignaciones a jugadores
            if (isset($validated['jugadores']) && !empty($validated['jugadores'])) {
                $jugadoresData = [];
                foreach ($validated['jugadores'] as $jugadorData) {
                    $jugadoresData[$jugadorData['id']] = [
                        'importe_asignado' => $jugadorData['importe']
                    ];
                }
                $gasto->jugadores()->sync($jugadoresData);
            } else {
                $gasto->jugadores()->detach();
            }

            // Actualizar saldos de todos los jugadores afectados
            $todosJugadores = array_unique(array_merge(
                $jugadoresAnteriores,
                isset($validated['jugadores']) ? array_column($validated['jugadores'], 'id') : []
            ));
            
            foreach ($todosJugadores as $jugadorId) {
                $jugador = Jugador::find($jugadorId);
                if ($jugador) {
                    $jugador->actualizarSaldo();
                }
            }

            // Actualizar saldo del proveedor
            if ($gasto->proveedor) {
                $gasto->proveedor->actualizarSaldo();
            }

            DB::commit();

            return redirect()->route('gastos.index')
                ->with('success', 'Gasto actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el gasto: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $gasto = Gasto::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Guardar jugadores afectados para actualizar sus saldos
            $jugadoresAfectados = $gasto->jugadores->pluck('id_jugador')->toArray();
            
            // Eliminar el gasto (esto eliminará también las relaciones en gasto_jugador por cascade)
            $gasto->delete();

            // Actualizar saldos de jugadores afectados
            foreach ($jugadoresAfectados as $jugadorId) {
                $jugador = Jugador::find($jugadorId);
                if ($jugador) {
                    $jugador->actualizarSaldo();
                }
            }

            DB::commit();

            return redirect()->route('gastos.index')
                ->with('success', 'Gasto eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al eliminar el gasto: ' . $e->getMessage());
        }
    }
}