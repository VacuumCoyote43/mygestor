<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\Jugador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para gestionar el reparto automático de gastos entre jugadores
 */
class GastoRepartoController extends Controller
{
    /**
     * Repartir un gasto de forma equitativa entre todos los jugadores activos
     * 
     * @param int $id_gasto ID del gasto a repartir
     * @return \Illuminate\Http\JsonResponse
     */
    public function repartirEquitativo($id_gasto)
    {
        try {
            DB::beginTransaction();

            $gasto = Gasto::findOrFail($id_gasto);
            $jugadores = Jugador::all();

            if ($jugadores->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay jugadores registrados en el sistema.'
                ], 400);
            }

            // Calcular importe por jugador
            $importePorJugador = $gasto->importe_total_gasto / $jugadores->count();

            // Limpiar asignaciones anteriores
            $gasto->jugadores()->detach();

            // Asignar el gasto a todos los jugadores
            $asignaciones = [];
            foreach ($jugadores as $jugador) {
                $gasto->jugadores()->attach($jugador->id_jugador, [
                    'importe_asignado' => $importePorJugador
                ]);

                // Actualizar saldo del jugador
                $jugador->actualizarSaldo();

                $asignaciones[] = [
                    'jugador_id' => $jugador->id_jugador,
                    'nombre' => $jugador->nombre_jugador,
                    'importe' => $importePorJugador
                ];
            }

            // Actualizar tipo de reparto en el gasto (si existe el campo)
            if (DB::getSchemaBuilder()->hasColumn('gastos', 'tipo_reparto')) {
                $gasto->update(['tipo_reparto' => 'equitativo']);
            }

            // Log de auditoría
            Log::info("Reparto equitativo aplicado", [
                'gasto_id' => $id_gasto,
                'importe_total' => $gasto->importe_total_gasto,
                'jugadores' => $jugadores->count(),
                'importe_por_jugador' => $importePorJugador
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gasto repartido equitativamente entre ' . $jugadores->count() . ' jugadores.',
                'data' => [
                    'tipo_reparto' => 'equitativo',
                    'importe_total' => $gasto->importe_total_gasto,
                    'numero_jugadores' => $jugadores->count(),
                    'importe_por_jugador' => $importePorJugador,
                    'asignaciones' => $asignaciones,
                    'total_asignado' => array_sum(array_column($asignaciones, 'importe'))
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en reparto equitativo', [
                'gasto_id' => $id_gasto,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al realizar el reparto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Repartir un gasto de forma personalizada entre jugadores seleccionados
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id_gasto ID del gasto a repartir
     * @return \Illuminate\Http\JsonResponse
     */
    public function repartirPersonalizado(Request $request, $id_gasto)
    {
        try {
            // Validar los datos recibidos
            $validated = $request->validate([
                'jugadores' => 'required|array|min:1',
                'jugadores.*.id_jugador' => 'required|exists:jugadores,id_jugador',
                'jugadores.*.importe' => 'required|numeric|min:0.01',
            ]);

            DB::beginTransaction();

            $gasto = Gasto::findOrFail($id_gasto);
            
            // Verificar que la suma de importes no supere el total del gasto (con tolerancia)
            $totalAsignado = array_sum(array_column($validated['jugadores'], 'importe'));
            $diferencia = abs($gasto->importe_total_gasto - $totalAsignado);
            
            if ($diferencia > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => sprintf(
                        'La suma de importes asignados (€%s) no coincide con el importe total del gasto (€%s). Diferencia: €%s',
                        number_format((float)$totalAsignado, 2, ',', '.'),
                        number_format((float)$gasto->importe_total_gasto, 2, ',', '.'),
                        number_format((float)$diferencia, 2, ',', '.')
                    )
                ], 400);
            }

            // Limpiar asignaciones anteriores
            $gasto->jugadores()->detach();

            // Aplicar las nuevas asignaciones
            $asignaciones = [];
            $jugadoresIdsAnteriores = [];

            // Guardar IDs de jugadores anteriores para actualizar sus saldos
            foreach ($gasto->jugadores as $jugador) {
                $jugadoresIdsAnteriores[] = $jugador->id_jugador;
            }

            foreach ($validated['jugadores'] as $jugadorData) {
                $jugador = Jugador::find($jugadorData['id_jugador']);
                
                $gasto->jugadores()->attach($jugador->id_jugador, [
                    'importe_asignado' => $jugadorData['importe']
                ]);

                // Actualizar saldo del jugador
                $jugador->actualizarSaldo();

                $asignaciones[] = [
                    'jugador_id' => $jugador->id_jugador,
                    'nombre' => $jugador->nombre_jugador,
                    'importe' => $jugadorData['importe']
                ];

                // Si este jugador estaba en las asignaciones anteriores, eliminarlo de la lista
                $jugadoresIdsAnteriores = array_diff($jugadoresIdsAnteriores, [$jugador->id_jugador]);
            }

            // Actualizar saldos de jugadores que fueron removidos del reparto
            foreach ($jugadoresIdsAnteriores as $jugadorId) {
                $jugador = Jugador::find($jugadorId);
                if ($jugador) {
                    $jugador->actualizarSaldo();
                }
            }

            // Actualizar tipo de reparto
            if (DB::getSchemaBuilder()->hasColumn('gastos', 'tipo_reparto')) {
                $gasto->update(['tipo_reparto' => 'personalizado']);
            }

            // Log de auditoría
            Log::info("Reparto personalizado aplicado", [
                'gasto_id' => $id_gasto,
                'importe_total' => $gasto->importe_total_gasto,
                'jugadores' => count($asignaciones),
                'asignaciones' => $asignaciones
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gasto repartido personalizadamente entre ' . count($asignaciones) . ' jugadores.',
                'data' => [
                    'tipo_reparto' => 'personalizado',
                    'importe_total' => $gasto->importe_total_gasto,
                    'numero_jugadores' => count($asignaciones),
                    'asignaciones' => $asignaciones,
                    'total_asignado' => $totalAsignado
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en reparto personalizado', [
                'gasto_id' => $id_gasto,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al realizar el reparto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Repartir un gasto según una regla específica
     * Por ahora, implementamos una versión básica que puede extenderse
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id_gasto ID del gasto a repartir
     * @return \Illuminate\Http\JsonResponse
     */
    public function repartirPorRegla(Request $request, $id_gasto)
    {
        try {
            // Validar datos de la regla
            $validated = $request->validate([
                'tipo_regla' => 'required|in:equitativo,ponderado,personalizado',
                'regla_data' => 'nullable|array',
            ]);

            DB::beginTransaction();

            $gasto = Gasto::findOrFail($id_gasto);
            $tipoRegla = $validated['tipo_regla'];

            // Limpiar asignaciones anteriores
            $gasto->jugadores()->detach();

            $asignaciones = [];

            switch ($tipoRegla) {
                case 'equitativo':
                    // Similar a repartirEquitativo pero se puede filtrar por regla
                    $jugadores = Jugador::all();
                    $importePorJugador = $gasto->importe_total_gasto / $jugadores->count();

                    foreach ($jugadores as $jugador) {
                        $gasto->jugadores()->attach($jugador->id_jugador, [
                            'importe_asignado' => $importePorJugador
                        ]);
                        $jugador->actualizarSaldo();

                        $asignaciones[] = [
                            'jugador_id' => $jugador->id_jugador,
                            'nombre' => $jugador->nombre_jugador,
                            'importe' => $importePorJugador
                        ];
                    }
                    break;

                case 'ponderado':
                    // Reparto ponderado según pesos en regla_data
                    if (!isset($validated['regla_data']['jugadores']) || empty($validated['regla_data']['jugadores'])) {
                        throw new \Exception('Se requiere especificar jugadores con sus pesos para el reparto ponderado.');
                    }

                    $jugadoresConPeso = $validated['regla_data']['jugadores'];
                    $totalPeso = array_sum(array_column($jugadoresConPeso, 'peso'));

                    if ($totalPeso <= 0) {
                        throw new \Exception('La suma de pesos debe ser mayor a cero.');
                    }

                    foreach ($jugadoresConPeso as $jugadorData) {
                        $jugador = Jugador::findOrFail($jugadorData['id_jugador']);
                        $peso = $jugadorData['peso'];
                        $importe = ($peso / $totalPeso) * $gasto->importe_total_gasto;

                        $gasto->jugadores()->attach($jugador->id_jugador, [
                            'importe_asignado' => $importe
                        ]);
                        $jugador->actualizarSaldo();

                        $asignaciones[] = [
                            'jugador_id' => $jugador->id_jugador,
                            'nombre' => $jugador->nombre_jugador,
                            'importe' => $importe,
                            'peso' => $peso
                        ];
                    }
                    break;

                default:
                    throw new \Exception('Tipo de regla no soportado: ' . $tipoRegla);
            }

            // Actualizar tipo de reparto
            if (DB::getSchemaBuilder()->hasColumn('gastos', 'tipo_reparto')) {
                $gasto->update(['tipo_reparto' => 'regla_' . $tipoRegla]);
            }

            // Log de auditoría
            Log::info("Reparto por regla aplicado", [
                'gasto_id' => $id_gasto,
                'tipo_regla' => $tipoRegla,
                'importe_total' => $gasto->importe_total_gasto,
                'jugadores' => count($asignaciones)
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gasto repartido según regla "' . $tipoRegla . '" entre ' . count($asignaciones) . ' jugadores.',
                'data' => [
                    'tipo_reparto' => 'regla_' . $tipoRegla,
                    'tipo_regla' => $tipoRegla,
                    'importe_total' => $gasto->importe_total_gasto,
                    'numero_jugadores' => count($asignaciones),
                    'asignaciones' => $asignaciones,
                    'total_asignado' => array_sum(array_column($asignaciones, 'importe'))
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en reparto por regla', [
                'gasto_id' => $id_gasto,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al realizar el reparto: ' . $e->getMessage()
            ], 500);
        }
    }
}