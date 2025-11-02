<?php

namespace App\Http\Controllers;

use App\Models\Jugador;
use App\Models\Proveedor;
use App\Models\Gasto;
use App\Models\PagoJugador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard con resumen general
     * Los datos se cargan dinámicamente vía AJAX desde el método stats()
     */
    public function index()
    {
        return view('dashboard.index');
    }

    /**
     * Endpoint API para obtener estadísticas del dashboard
     * Retorna JSON con todos los datos necesarios para gráficos y visualizaciones
     */
    public function stats()
    {
        // Actualizar todos los saldos antes de calcular
        $jugadores = Jugador::all();
        foreach ($jugadores as $jugador) {
            $jugador->actualizarSaldo();
        }

        $proveedores = Proveedor::all();
        foreach ($proveedores as $proveedor) {
            $proveedor->actualizarSaldo();
        }

        // 1. Totales generales
        $totalGastos = (float) Gasto::sum('importe_total_gasto');
        $totalPagos = (float) PagoJugador::sum('importe_pago');
        
        // 2. Deuda total de jugadores (solo saldos positivos = deuda)
        $deudaJugadores = (float) Jugador::where('saldo_jugador', '>', 0)
            ->sum('saldo_jugador');
        
        // 3. Deuda con proveedores (saldo total de proveedores)
        $deudaProveedores = (float) Proveedor::sum('saldo_proveedor');

        // 4. Gastos por tipo (agrupados)
        $gastosPorTipo = Gasto::select('tipo_gasto', DB::raw('SUM(importe_total_gasto) as total'))
            ->groupBy('tipo_gasto')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'tipo' => $item->tipo_gasto,
                    'total' => (float) $item->total
                ];
            });

        // 5. Deuda por jugador (solo jugadores con deuda)
        $deudaPorJugador = Jugador::where('saldo_jugador', '>', 0)
            ->orderBy('saldo_jugador', 'desc')
            ->get()
            ->map(function ($jugador) {
                return [
                    'nombre' => $jugador->nombre_jugador,
                    'saldo' => (float) $jugador->saldo_jugador
                ];
            });

        // 6. Pagos mensuales (últimos 12 meses)
        $pagosMensuales = PagoJugador::select(
                DB::raw('YEAR(fecha_pago) as year'),
                DB::raw('MONTH(fecha_pago) as month'),
                DB::raw('SUM(importe_pago) as total')
            )
            ->where('fecha_pago', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                $fecha = \Carbon\Carbon::create($item->year, $item->month, 1);
                return [
                    'mes' => $fecha->format('Y-m'),
                    'label' => $fecha->format('M Y'),
                    'total' => (float) $item->total
                ];
            });

        // 7. Datos completos de jugadores para la tabla
        $jugadoresCompletos = Jugador::with(['gastos', 'pagos'])->get()->map(function ($jugador) {
            $totalGastos = $jugador->gastos->sum('pivot.importe_asignado');
            $totalPagos = $jugador->pagos->sum('importe_pago');
            
            return [
                'id' => $jugador->id_jugador,
                'nombre' => $jugador->nombre_jugador,
                'total_gastos' => (float) $totalGastos,
                'total_pagos' => (float) $totalPagos,
                'saldo' => (float) $jugador->saldo_jugador,
                'estado' => $jugador->saldo_jugador > 0 ? 'Debe dinero' : ($jugador->saldo_jugador < 0 ? 'A favor' : 'Al día')
            ];
        });

        return response()->json([
            'total_gastos' => $totalGastos,
            'total_pagos' => $totalPagos,
            'deuda_jugadores' => $deudaJugadores,
            'deuda_proveedores' => $deudaProveedores,
            'gastos_por_tipo' => $gastosPorTipo,
            'deuda_por_jugador' => $deudaPorJugador,
            'pagos_mensuales' => $pagosMensuales,
            'jugadores' => $jugadoresCompletos
        ]);
    }
}