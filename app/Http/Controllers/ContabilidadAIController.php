<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContabilidadAIController extends Controller
{
    /**
     * Muestra el dashboard de contabilidad con análisis y predicciones
     */
    public function index()
    {
        // Totales generales
        $totalIngresos = DB::table('pagos_jugadores')->sum('importe_pago') ?? 0;
        $totalGastos = DB::table('gastos')->sum('importe_total_gasto') ?? 0;
        $saldoTotal = $totalIngresos - $totalGastos;

        // Deuda promedio (saldo positivo = deuda)
        $promedioDeuda = DB::table('jugadores')
            ->where('saldo_jugador', '>', 0)
            ->avg('saldo_jugador') ?? 0;

        // Total de jugadores con deuda
        $jugadoresConDeuda = DB::table('jugadores')
            ->where('saldo_jugador', '>', 0)
            ->count();

        // Datos mensuales (últimos 12 meses)
        $meses = collect(range(0, 11))->map(function ($i) {
            $fecha = Carbon::now()->subMonths($i);
            $fechaStr = $fecha->format('Y-m');
            $nombreMes = $fecha->locale('es')->translatedFormat('F Y');
            
            $ingresos = DB::table('pagos_jugadores')
                ->whereRaw("DATE_FORMAT(fecha_pago, '%Y-%m') = ?", [$fechaStr])
                ->sum('importe_pago') ?? 0;
            
            $gastos = DB::table('gastos')
                ->whereRaw("DATE_FORMAT(fecha_gasto, '%Y-%m') = ?", [$fechaStr])
                ->sum('importe_total_gasto') ?? 0;
            
            return [
                'mes' => $fechaStr,
                'nombre' => ucfirst($nombreMes),
                'ingresos' => (float) $ingresos,
                'gastos' => (float) $gastos,
                'saldo' => (float) ($ingresos - $gastos),
            ];
        })->reverse()->values();

        // Predicciones simples con regresión lineal
        $ingresosVals = $meses->pluck('ingresos')->values()->all();
        $gastosVals = $meses->pluck('gastos')->values()->all();
        
        $prediccionIngresos = $this->prediccionLineal($ingresosVals);
        $prediccionGastos = $this->prediccionLineal($gastosVals);
        
        // Calcular porcentaje de cambio esperado
        $ultimoIngreso = end($ingresosVals) ?: 1;
        $ultimoGasto = end($gastosVals) ?: 1;
        
        $variacionIngresos = $ultimoIngreso > 0 
            ? (($prediccionIngresos - $ultimoIngreso) / $ultimoIngreso) * 100 
            : 0;
        
        $variacionGastos = $ultimoGasto > 0 
            ? (($prediccionGastos - $ultimoGasto) / $ultimoGasto) * 100 
            : 0;

        // Alertas
        $alertas = [];
        
        // Alerta: Gastos del último mes superan la media + 20%
        if (count($gastosVals) > 0) {
            $mediaGastos = collect($gastosVals)->avg();
            $ultimoGastoMes = end($gastosVals);
            
            if ($mediaGastos > 0 && $ultimoGastoMes > ($mediaGastos * 1.2)) {
                $porcentaje = (($ultimoGastoMes - $mediaGastos) / $mediaGastos) * 100;
                $alertas[] = [
                    'tipo' => 'warning',
                    'mensaje' => "⚠️ Los gastos del último mes superan la media en un " . number_format($porcentaje, 1) . "%",
                    'icono' => 'exclamation-triangle'
                ];
            }
        }

        // Alerta: Jugadores con deuda alta
        if ($promedioDeuda > 0) {
            $umbral = $promedioDeuda * 1.3;
            $jugadoresConDeudaAlta = DB::table('jugadores')
                ->where('saldo_jugador', '>', $umbral)
                ->count();
            
            if ($jugadoresConDeudaAlta > 0) {
                $alertas[] = [
                    'tipo' => 'danger',
                    'mensaje' => "⚠️ Hay {$jugadoresConDeudaAlta} jugador(es) con deuda superior al promedio + 30%",
                    'icono' => 'exclamation-circle'
                ];
            }
        }

        // Alerta: Saldo total negativo
        if ($saldoTotal < 0) {
            $alertas[] = [
                'tipo' => 'danger',
                'mensaje' => "⚠️ El saldo total del equipo es negativo: €" . number_format(abs($saldoTotal), 2, ',', '.'),
                'icono' => 'exclamation-circle'
            ];
        }

        // Estadísticas adicionales
        $mesActual = Carbon::now()->format('Y-m');
        $ingresosMesActual = DB::table('pagos_jugadores')
            ->whereRaw("DATE_FORMAT(fecha_pago, '%Y-%m') = ?", [$mesActual])
            ->sum('importe_pago') ?? 0;
        
        $gastosMesActual = DB::table('gastos')
            ->whereRaw("DATE_FORMAT(fecha_gasto, '%Y-%m') = ?", [$mesActual])
            ->sum('importe_total_gasto') ?? 0;

        return view('dashboard.contable_ai', compact(
            'totalIngresos',
            'totalGastos',
            'saldoTotal',
            'promedioDeuda',
            'jugadoresConDeuda',
            'meses',
            'prediccionIngresos',
            'prediccionGastos',
            'variacionIngresos',
            'variacionGastos',
            'alertas',
            'ingresosMesActual',
            'gastosMesActual'
        ));
    }

    /**
     * Predicción lineal simple para el próximo mes
     * 
     * @param array $datos Array de valores históricos
     * @return float Valor predicho
     */
    private function prediccionLineal(array $datos): float
    {
        $n = count($datos);
        
        // Si hay menos de 2 datos, retornar el último o 0
        if ($n < 2) {
            return $n === 1 ? (float) $datos[0] : 0;
        }

        // Filtrar valores válidos
        $datos = array_filter($datos, function($val) {
            return is_numeric($val) && $val >= 0;
        });

        if (count($datos) < 2) {
            return count($datos) === 1 ? (float) end($datos) : 0;
        }

        // Reindexar array
        $datos = array_values($datos);
        $n = count($datos);

        // Calcular regresión lineal: y = b0 + b1*x
        $x = range(1, $n);
        $mediaX = array_sum($x) / $n;
        $mediaY = array_sum($datos) / $n;

        $num = 0;
        $den = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $num += ($x[$i] - $mediaX) * ($datos[$i] - $mediaY);
            $den += pow(($x[$i] - $mediaX), 2);
        }

        // Evitar división por cero
        if ($den == 0) {
            return $mediaY;
        }

        $b1 = $num / $den; // Pendiente
        $b0 = $mediaY - $b1 * $mediaX; // Intercepto

        // Predecir para el siguiente período (n + 1)
        $prediccion = $b0 + $b1 * ($n + 1);
        
        // No permitir predicciones negativas
        return max(0, $prediccion);
    }
}

