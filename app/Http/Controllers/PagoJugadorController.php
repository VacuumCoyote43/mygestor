<?php

namespace App\Http\Controllers;

use App\Models\PagoJugador;
use App\Models\Jugador;
use Illuminate\Http\Request;

class PagoJugadorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pagos = PagoJugador::with('jugador')
            ->orderBy('fecha_pago', 'desc')
            ->paginate(15);
        
        return view('pagos.index', compact('pagos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $jugadores = Jugador::orderBy('nombre_jugador')->get();
        
        // Parámetros opcionales para pre-rellenar el formulario
        $jugadorId = $request->input('jugador_id');
        $importe = $request->input('importe');
        
        return view('pagos.create', compact('jugadores', 'jugadorId', 'importe'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_jugador' => 'required|exists:jugadores,id_jugador',
            'fecha_pago' => 'required|date',
            'importe_pago' => 'required|numeric|min:0.01',
            'concepto_pago' => 'nullable|string|max:255',
        ]);

        PagoJugador::create($validated);

        // El saldo se actualiza automáticamente en el modelo mediante eventos

        return redirect()->route('pagos.index')
            ->with('success', 'Pago registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pago = PagoJugador::with('jugador')->findOrFail($id);
        return view('pagos.show', compact('pago'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pago = PagoJugador::findOrFail($id);
        $jugadores = Jugador::orderBy('nombre_jugador')->get();
        return view('pagos.edit', compact('pago', 'jugadores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pago = PagoJugador::findOrFail($id);

        $validated = $request->validate([
            'id_jugador' => 'required|exists:jugadores,id_jugador',
            'fecha_pago' => 'required|date',
            'importe_pago' => 'required|numeric|min:0.01',
            'concepto_pago' => 'nullable|string|max:255',
        ]);

        $pago->update($validated);

        // El saldo se actualiza automáticamente en el modelo mediante eventos

        return redirect()->route('pagos.index')
            ->with('success', 'Pago actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pago = PagoJugador::findOrFail($id);
        $pago->delete();

        // El saldo se actualiza automáticamente en el modelo mediante eventos

        return redirect()->route('pagos.index')
            ->with('success', 'Pago eliminado exitosamente.');
    }
}