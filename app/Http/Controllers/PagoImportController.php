<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PagosImport;
use Illuminate\Support\Facades\DB;

class PagoImportController extends Controller
{
    /**
     * Muestra el formulario de importación
     */
    public function index()
    {
        return view('pagos.import');
    }

    /**
     * Procesa el archivo Excel y importa los pagos
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:2048',
        ], [
            'file.required' => 'Debe seleccionar un archivo.',
            'file.mimes' => 'El archivo debe ser de tipo .xlsx o .csv',
            'file.max' => 'El archivo no puede ser mayor a 2MB.',
        ]);

        try {
            DB::beginTransaction();

            $import = new PagosImport();
            Excel::import($import, $request->file('file'));

            $filasFallidas = method_exists($import, 'failures') ? $import->failures() : collect();
            $totalErrores = count($filasFallidas);

            DB::commit();

            // Obtener estadísticas
            $importados = $import->getImportados();
            $ignorados = $import->getIgnorados();
            $jugadoresNoEncontrados = $import->getJugadoresNoEncontrados();

            // Construir mensaje
            $mensaje = "Se importaron {$importados} pago(s) correctamente.";
            
            if ($ignorados > 0) {
                $mensaje .= " Se ignoraron {$ignorados} fila(s).";
            }

            if ($totalErrores > 0) {
                $mensaje .= " Se encontraron {$totalErrores} error(es) de validación.";
            }

            if (count($jugadoresNoEncontrados) > 0) {
                $jugadores = implode(', ', array_slice($jugadoresNoEncontrados, 0, 5));
                if (count($jugadoresNoEncontrados) > 5) {
                    $jugadores .= ' y más...';
                }
                $mensaje .= " Jugadores no encontrados: {$jugadores}";
            }

            // Si hay errores, mostrar advertencia en la página de importación
            if ($totalErrores > 0 || count($jugadoresNoEncontrados) > 0) {
                return redirect()
                    ->route('pagos.import')
                    ->with('warning', $mensaje)
                    ->with('failures', $filasFallidas)
                    ->with('jugadores_no_encontrados', $jugadoresNoEncontrados)
                    ->with('estadisticas', [
                        'importados' => $importados,
                        'ignorados' => $ignorados,
                    ]);
            }

            return redirect()
                ->route('pagos.index')
                ->with('success', $mensaje);
                
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            
            $failures = $e->failures();
            $errores = [];
            
            foreach ($failures as $failure) {
                $errores[] = "Fila {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return redirect()
                ->route('pagos.import')
                ->withErrors(['import' => 'Error al importar el archivo.'])
                ->with('error_details', $errores)
                ->with('failures', $failures);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->route('pagos.import')
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }
}

