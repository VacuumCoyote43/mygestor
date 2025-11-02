<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\JugadoresImport;
use App\Exports\JugadoresTemplateExport;
use Illuminate\Support\Facades\DB;

class JugadorImportController extends Controller
{
    /**
     * Muestra el formulario de importación
     */
    public function index()
    {
        return view('jugadores.import');
    }

    /**
     * Descarga el template Excel para importar jugadores
     */
    public function downloadTemplate()
    {
        return Excel::download(new JugadoresTemplateExport(), 'molde-jugadores.xlsx');
    }

    /**
     * Procesa el archivo Excel y importa los jugadores
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

            $import = new JugadoresImport();
            Excel::import($import, $request->file('file'));

            $filasFallidas = method_exists($import, 'failures') ? $import->failures() : collect();
            $totalErrores = count($filasFallidas);

            DB::commit();

            $mensaje = 'Jugadores importados correctamente.';
            
            if ($totalErrores > 0) {
                $mensaje .= " Se encontraron {$totalErrores} error(es) en algunas filas.";
                return redirect()
                    ->route('jugadores.import')
                    ->with('warning', $mensaje)
                    ->with('failures', $filasFallidas);
            }

            return redirect()
                ->route('jugadores.index')
                ->with('success', $mensaje);
                
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            
            $failures = $e->failures();
            $errores = [];
            
            foreach ($failures as $failure) {
                $errores[] = "Fila {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return redirect()
                ->route('jugadores.import')
                ->withErrors(['import' => 'Error al importar el archivo.'])
                ->with('error_details', $errores)
                ->with('failures', $failures);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->route('jugadores.import')
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }
}

