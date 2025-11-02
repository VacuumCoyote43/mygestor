<?php

namespace App\Http\Controllers;

use App\Models\Jugador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JugadorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jugadores = Jugador::orderBy('nombre_jugador')->paginate(15);
        
        // Actualizar saldos antes de mostrar
        foreach ($jugadores as $jugador) {
            $jugador->actualizarSaldo();
        }
        
        return view('jugadores.index', compact('jugadores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('jugadores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_jugador' => 'required|string|max:255',
            'dni' => 'nullable|string|max:15',
            'fecha_nacimiento' => 'nullable|date',
            'dorsal' => 'nullable|integer|min:1',
            'talla_camiseta' => 'nullable|string|max:5',
            'talla_pantalon' => 'nullable|string|max:5',
            'talla_medias' => 'nullable|string|max:5',
            'mode' => 'nullable|string|max:20',
            'saldo_jugador' => 'nullable|numeric|min:0',
        ]);

        // Establecer mode como 'manual' si no se proporciona (creación manual)
        if (!isset($validated['mode'])) {
            $validated['mode'] = 'manual';
        }

        Jugador::create($validated);

        return redirect()->route('jugadores.index')
            ->with('success', 'Jugador creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $jugador = Jugador::findOrFail($id);
        $jugador->actualizarSaldo();
        
        $pagos = $jugador->pagos()->orderBy('fecha_pago', 'desc')->paginate(10);
        $gastos = $jugador->gastos()->with('proveedor')->orderBy('fecha_gasto', 'desc')->paginate(10);

        return view('jugadores.show', compact('jugador', 'pagos', 'gastos'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $jugador = Jugador::findOrFail($id);
        return view('jugadores.edit', compact('jugador'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $jugador = Jugador::findOrFail($id);

        $validated = $request->validate([
            'nombre_jugador' => 'required|string|max:255',
            'dni' => 'nullable|string|max:15',
            'fecha_nacimiento' => 'nullable|date',
            'dorsal' => 'nullable|integer|min:1',
            'talla_camiseta' => 'nullable|string|max:5',
            'talla_pantalon' => 'nullable|string|max:5',
            'talla_medias' => 'nullable|string|max:5',
            'mode' => 'nullable|string|max:20',
        ]);

        $jugador->update($validated);

        return redirect()->route('jugadores.index')
            ->with('success', 'Jugador actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $jugador = Jugador::findOrFail($id);
        
        // Verificar si tiene gastos o pagos asociados
        if ($jugador->gastos()->count() > 0 || $jugador->pagos()->count() > 0) {
            return redirect()->route('jugadores.index')
                ->with('error', 'No se puede eliminar el jugador porque tiene gastos o pagos asociados.');
        }

        $jugador->delete();

        return redirect()->route('jugadores.index')
            ->with('success', 'Jugador eliminado exitosamente.');
    }
}