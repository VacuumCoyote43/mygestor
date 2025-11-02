<?php

namespace App\Http\Controllers;

use App\Models\Jugador;
use App\Models\Proveedor;
use App\Models\Gasto;
use App\Models\PagoJugador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Barryvdh\DomPDF\Facade\Pdf;

class EstadisticasController extends Controller
{
    /**
     * Muestra el dashboard de estadísticas financieras
     */
    public function index(Request $request)
    {
        // Obtener fechas de filtro (por defecto último año)
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->subYear()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaFinCarbon = Carbon::parse($fechaFin)->endOfDay();

        // Guardar fechas para los filtros
        $fechasFiltro = [
            'inicio' => $fechaInicio,
            'fin' => $fechaFin
        ];

        // 1. Ingresos por jugador
        $ingresosPorJugador = PagoJugador::select(
                'jugadores.id_jugador',
                'jugadores.nombre_jugador',
                DB::raw('SUM(pagos_jugadores.importe_pago) as total')
            )
            ->join('jugadores', 'pagos_jugadores.id_jugador', '=', 'jugadores.id_jugador')
            ->whereBetween('pagos_jugadores.fecha_pago', [$fechaInicioCarbon, $fechaFinCarbon])
            ->groupBy('jugadores.id_jugador', 'jugadores.nombre_jugador')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->nombre_jugador,
                    'total' => (float) $item->total
                ];
            });

        // 2. Gastos por proveedor
        $gastosPorProveedor = Gasto::select(
                'proveedores.id_proveedor',
                'proveedores.nombre_proveedor',
                DB::raw('SUM(gastos.importe_total_gasto) as total')
            )
            ->leftJoin('proveedores', 'gastos.proveedor_id', '=', 'proveedores.id_proveedor')
            ->whereBetween('gastos.fecha_gasto', [$fechaInicioCarbon, $fechaFinCarbon])
            ->groupBy('proveedores.id_proveedor', 'proveedores.nombre_proveedor')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->nombre_proveedor ?: 'Sin proveedor',
                    'total' => (float) $item->total
                ];
            });

        // 3. Balance mensual comparado
        $balanceMensual = $this->calcularBalanceMensual($fechaInicioCarbon, $fechaFinCarbon);

        // 4. Totales del período
        $totalIngresos = PagoJugador::whereBetween('fecha_pago', [$fechaInicioCarbon, $fechaFinCarbon])
            ->sum('importe_pago') ?? 0;
        
        $totalGastos = Gasto::whereBetween('fecha_gasto', [$fechaInicioCarbon, $fechaFinCarbon])
            ->sum('importe_total_gasto') ?? 0;
        
        $saldoPeriodo = $totalIngresos - $totalGastos;

        // 5. Alertas
        $alertas = $this->generarAlertas($fechaInicioCarbon, $fechaFinCarbon);

        return view('dashboard.estadisticas', compact(
            'ingresosPorJugador',
            'gastosPorProveedor',
            'balanceMensual',
            'totalIngresos',
            'totalGastos',
            'saldoPeriodo',
            'alertas',
            'fechasFiltro'
        ));
    }

    /**
     * Calcula el balance mensual comparado
     */
    private function calcularBalanceMensual($fechaInicio, $fechaFin)
    {
        $meses = collect();
        $fechaActual = $fechaInicio->copy()->startOfMonth();

        while ($fechaActual <= $fechaFin) {
            $mes = $fechaActual->format('Y-m');
            $nombreMes = $fechaActual->locale('es')->translatedFormat('F Y');

            $ingresos = PagoJugador::whereRaw("DATE_FORMAT(fecha_pago, '%Y-%m') = ?", [$mes])
                ->sum('importe_pago') ?? 0;

            $gastos = Gasto::whereRaw("DATE_FORMAT(fecha_gasto, '%Y-%m') = ?", [$mes])
                ->sum('importe_total_gasto') ?? 0;

            $meses->push([
                'mes' => $mes,
                'nombre' => ucfirst($nombreMes),
                'ingresos' => (float) $ingresos,
                'gastos' => (float) $gastos,
                'balance' => (float) ($ingresos - $gastos)
            ]);

            $fechaActual->addMonth();
        }

        return $meses;
    }

    /**
     * Genera alertas basadas en los datos
     */
    private function generarAlertas($fechaInicio, $fechaFin)
    {
        $alertas = [];

        // Calcular promedios mensuales
        $meses = $this->calcularBalanceMensual($fechaInicio, $fechaFin);
        $promedioIngresos = $meses->avg('ingresos');
        $promedioGastos = $meses->avg('gastos');

        // Alerta: Gastos superan ingresos
        if ($promedioGastos > $promedioIngresos) {
            $alertas[] = [
                'tipo' => 'danger',
                'icono' => 'exclamation-triangle',
                'mensaje' => 'Los gastos promedio superan los ingresos promedio en el período seleccionado.'
            ];
        }

        // Alerta: Último mes con balance negativo
        $ultimoMes = $meses->last();
        if ($ultimoMes && $ultimoMes['balance'] < 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'icono' => 'exclamation-circle',
                'mensaje' => "Balance negativo en {$ultimoMes['nombre']}: €" . number_format(abs($ultimoMes['balance']), 2, ',', '.')
            ];
        }

        return $alertas;
    }

    /**
     * Exporta los datos actuales a PDF
     */
    public function exportPDF(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->subYear()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaFinCarbon = Carbon::parse($fechaFin)->endOfDay();

        // Obtener datos
        $ingresosPorJugador = PagoJugador::select(
                'jugadores.nombre_jugador',
                DB::raw('SUM(pagos_jugadores.importe_pago) as total')
            )
            ->join('jugadores', 'pagos_jugadores.id_jugador', '=', 'jugadores.id_jugador')
            ->whereBetween('pagos_jugadores.fecha_pago', [$fechaInicioCarbon, $fechaFinCarbon])
            ->groupBy('jugadores.nombre_jugador')
            ->orderBy('total', 'desc')
            ->get();

        $gastosPorProveedor = Gasto::select(
                'proveedores.nombre_proveedor',
                DB::raw('SUM(gastos.importe_total_gasto) as total')
            )
            ->leftJoin('proveedores', 'gastos.proveedor_id', '=', 'proveedores.id_proveedor')
            ->whereBetween('gastos.fecha_gasto', [$fechaInicioCarbon, $fechaFinCarbon])
            ->groupBy('proveedores.nombre_proveedor')
            ->orderBy('total', 'desc')
            ->get();

        $balanceMensual = $this->calcularBalanceMensual($fechaInicioCarbon, $fechaFinCarbon);

        $totalIngresos = PagoJugador::whereBetween('fecha_pago', [$fechaInicioCarbon, $fechaFinCarbon])
            ->sum('importe_pago') ?? 0;
        
        $totalGastos = Gasto::whereBetween('fecha_gasto', [$fechaInicioCarbon, $fechaFinCarbon])
            ->sum('importe_total_gasto') ?? 0;

        $pdf = Pdf::loadView('dashboard.estadisticas_pdf', compact(
            'ingresosPorJugador',
            'gastosPorProveedor',
            'balanceMensual',
            'totalIngresos',
            'totalGastos',
            'fechaInicio',
            'fechaFin'
        ));

        return $pdf->download('estadisticas-financieras-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Exporta los datos actuales a Excel
     */
    public function exportExcel(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->subYear()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaFinCarbon = Carbon::parse($fechaFin)->endOfDay();

        // Recopilar todos los datos
        $datos = [];

        // Ingresos por jugador
        $ingresosPorJugador = PagoJugador::select(
                'jugadores.nombre_jugador',
                DB::raw('SUM(pagos_jugadores.importe_pago) as total')
            )
            ->join('jugadores', 'pagos_jugadores.id_jugador', '=', 'jugadores.id_jugador')
            ->whereBetween('pagos_jugadores.fecha_pago', [$fechaInicioCarbon, $fechaFinCarbon])
            ->groupBy('jugadores.nombre_jugador')
            ->orderBy('total', 'desc')
            ->get();

        foreach ($ingresosPorJugador as $item) {
            $datos[] = [
                'Tipo' => 'Ingreso',
                'Concepto' => $item->nombre_jugador,
                'Importe' => (float) $item->total,
                'Fecha' => ''
            ];
        }

        // Gastos por proveedor
        $gastosPorProveedor = Gasto::select(
                'proveedores.nombre_proveedor',
                DB::raw('SUM(gastos.importe_total_gasto) as total')
            )
            ->leftJoin('proveedores', 'gastos.proveedor_id', '=', 'proveedores.id_proveedor')
            ->whereBetween('gastos.fecha_gasto', [$fechaInicioCarbon, $fechaFinCarbon])
            ->groupBy('proveedores.nombre_proveedor')
            ->orderBy('total', 'desc')
            ->get();

        foreach ($gastosPorProveedor as $item) {
            $datos[] = [
                'Tipo' => 'Gasto',
                'Concepto' => $item->nombre_proveedor ?: 'Sin proveedor',
                'Importe' => (float) $item->total,
                'Fecha' => ''
            ];
        }

        // Balance mensual
        $balanceMensual = $this->calcularBalanceMensual($fechaInicioCarbon, $fechaFinCarbon);
        foreach ($balanceMensual as $mes) {
            $datos[] = [
                'Tipo' => 'Balance Mensual',
                'Concepto' => $mes['nombre'],
                'Importe' => $mes['balance'],
                'Fecha' => $mes['mes']
            ];
        }

        return Excel::download(new class($datos) implements FromCollection, WithHeadings, WithStyles {
            private $datos;

            public function __construct($datos)
            {
                $this->datos = $datos;
            }

            public function collection()
            {
                return collect($this->datos);
            }

            public function headings(): array
            {
                return ['Tipo', 'Concepto', 'Importe', 'Fecha'];
            }

            public function styles(Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']]],
                ];
            }
        }, 'estadisticas-financieras-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Endpoint AJAX para actualizar gráficos con nuevos filtros
     */
    public function datosGraficos(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->subYear()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaFinCarbon = Carbon::parse($fechaFin)->endOfDay();

        $ingresosPorJugador = PagoJugador::select(
                'jugadores.nombre_jugador',
                DB::raw('SUM(pagos_jugadores.importe_pago) as total')
            )
            ->join('jugadores', 'pagos_jugadores.id_jugador', '=', 'jugadores.id_jugador')
            ->whereBetween('pagos_jugadores.fecha_pago', [$fechaInicioCarbon, $fechaFinCarbon])
            ->groupBy('jugadores.nombre_jugador')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->nombre_jugador,
                    'total' => (float) $item->total
                ];
            });

        $gastosPorProveedor = Gasto::select(
                'proveedores.nombre_proveedor',
                DB::raw('SUM(gastos.importe_total_gasto) as total')
            )
            ->leftJoin('proveedores', 'gastos.proveedor_id', '=', 'proveedores.id_proveedor')
            ->whereBetween('gastos.fecha_gasto', [$fechaInicioCarbon, $fechaFinCarbon])
            ->groupBy('proveedores.nombre_proveedor')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->nombre_proveedor ?: 'Sin proveedor',
                    'total' => (float) $item->total
                ];
            });

        $balanceMensual = $this->calcularBalanceMensual($fechaInicioCarbon, $fechaFinCarbon);

        return response()->json([
            'ingresosPorJugador' => $ingresosPorJugador,
            'gastosPorProveedor' => $gastosPorProveedor,
            'balanceMensual' => $balanceMensual
        ]);
    }
}

