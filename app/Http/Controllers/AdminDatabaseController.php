<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Process\Process;

class AdminDatabaseController extends Controller
{
    public function index()
    {
        // Obtener lista de archivos de exportación
        $exports = [];
        if (Storage::exists('exports')) {
            $files = Storage::files('exports');
            $exports = collect($files)->map(function($file) {
                return [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => Storage::size($file),
                    'date' => Storage::lastModified($file)
                ];
            })->sortByDesc('date')->values();
        }

        return view('admin.database', compact('exports'));
    }

    public function export()
    {
        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();
            $database = Config::get('database.connections.' . Config::get('database.default') . '.database');
            
            $filename = 'backup_' . date('Y-m-d_His') . '.sql';
            $filepath = storage_path('app/exports/' . $filename);

            // Crear directorio si no existe
            if (!file_exists(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }

            if ($driver === 'mysql') {
                $host = Config::get('database.connections.mysql.host');
                $port = Config::get('database.connections.mysql.port', 3306);
                $username = Config::get('database.connections.mysql.username');
                $password = Config::get('database.connections.mysql.password');

                // Construir comando mysqldump
                $command = 'mysqldump';
                $command .= ' -h ' . escapeshellarg($host);
                $command .= ' -P ' . escapeshellarg($port);
                $command .= ' -u ' . escapeshellarg($username);
                if ($password) {
                    $command .= ' -p' . escapeshellarg($password);
                }
                $command .= ' ' . escapeshellarg($database);
                $command .= ' > ' . escapeshellarg($filepath);

                // En Windows, usar cmd
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $command = 'cmd /c "' . $command . '"';
                }

                $process = Process::fromShellCommandline($command);
                $process->setTimeout(300);
                $process->run();

                if (!$process->isSuccessful()) {
                    $error = $process->getErrorOutput() ?: $process->getOutput();
                    throw new \Exception('Error al exportar: ' . $error);
                }
            } elseif ($driver === 'sqlite') {
                // Para SQLite, simplemente copiar el archivo
                $sqlitePath = Config::get('database.connections.sqlite.database');
                if (!file_exists($sqlitePath)) {
                    throw new \Exception('Archivo SQLite no encontrado');
                }
                copy($sqlitePath, $filepath);
            } else {
                throw new \Exception('Tipo de base de datos no soportado: ' . $driver);
            }

            return back()->with('success', 'Base de datos exportada correctamente.')->with('exported_file', $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar la base de datos: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:sql,db|max:51200', // máximo 50 MB
        ]);

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filename = 'import_' . date('Y-m-d_His') . '.' . $extension;
            
            // Guardar archivo temporalmente
            $filepath = $file->storeAs('imports', $filename);
            $fullPath = storage_path('app/' . $filepath);

            $connection = DB::connection();
            $driver = $connection->getDriverName();

            if ($driver === 'mysql') {
                $host = Config::get('database.connections.mysql.host');
                $port = Config::get('database.connections.mysql.port', 3306);
                $username = Config::get('database.connections.mysql.username');
                $password = Config::get('database.connections.mysql.password');
                $database = Config::get('database.connections.mysql.database');

                // Construir comando mysql
                $command = 'mysql';
                $command .= ' -h ' . escapeshellarg($host);
                $command .= ' -P ' . escapeshellarg($port);
                $command .= ' -u ' . escapeshellarg($username);
                if ($password) {
                    $command .= ' -p' . escapeshellarg($password);
                }
                $command .= ' ' . escapeshellarg($database);
                $command .= ' < ' . escapeshellarg($fullPath);

                // En Windows, usar cmd
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $command = 'cmd /c "' . $command . '"';
                }

                $process = Process::fromShellCommandline($command);
                $process->setTimeout(300);
                $process->run();

                if (!$process->isSuccessful()) {
                    $error = $process->getErrorOutput() ?: $process->getOutput();
                    throw new \Exception('Error al importar: ' . $error);
                }
            } elseif ($driver === 'sqlite' && $extension === 'db') {
                // Para SQLite, reemplazar el archivo
                $sqlitePath = Config::get('database.connections.sqlite.database');
                $backupPath = $sqlitePath . '.backup.' . date('Y-m-d_His');
                
                // Hacer backup del archivo actual
                if (file_exists($sqlitePath)) {
                    copy($sqlitePath, $backupPath);
                }
                
                // Copiar el archivo importado
                copy($fullPath, $sqlitePath);
            } elseif ($extension === 'sql') {
                // Para archivos SQL, ejecutar las queries
                $sql = file_get_contents($fullPath);
                DB::unprepared($sql);
            } else {
                throw new \Exception('Tipo de archivo no soportado para este tipo de base de datos');
            }

            // Eliminar archivo temporal
            Storage::delete($filepath);

            return back()->with('success', 'Base de datos importada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al importar la base de datos: ' . $e->getMessage());
        }
    }

    public function download($filename)
    {
        $filepath = 'exports/' . $filename;
        
        if (Storage::exists($filepath)) {
            return Storage::download($filepath, $filename);
        }
        
        return back()->with('error', 'Archivo no encontrado.');
    }

    public function delete($filename)
    {
        $filepath = 'exports/' . $filename;
        
        if (Storage::exists($filepath)) {
            Storage::delete($filepath);
            return back()->with('success', 'Archivo eliminado correctamente.');
        }
        
        return back()->with('error', 'Archivo no encontrado.');
    }
}
