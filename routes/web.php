<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContabilidadAIController;
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\JugadorController;
use App\Http\Controllers\JugadorImportController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\PagoJugadorController;
use App\Http\Controllers\PagoImportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Rutas de autenticación (públicas)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

// Logout (solo autenticados)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/dashboard/contable-ai', [ContabilidadAIController::class, 'index'])->name('dashboard.contable_ai');

    // Estadísticas Financieras
    Route::prefix('dashboard/estadisticas')->name('dashboard.estadisticas.')->group(function () {
        Route::get('/', [EstadisticasController::class, 'index'])->name('index');
        Route::get('/datos', [EstadisticasController::class, 'datosGraficos'])->name('datos');
        Route::post('/export/pdf', [EstadisticasController::class, 'exportPDF'])->name('export.pdf');
        Route::post('/export/excel', [EstadisticasController::class, 'exportExcel'])->name('export.excel');
    });

    // Jugadores
    Route::prefix('jugadores')->name('jugadores.')->group(function () {
        // Importación de jugadores
        Route::get('/import', [JugadorImportController::class, 'index'])->name('import');
        Route::get('/import/template', [JugadorImportController::class, 'downloadTemplate'])->name('import.template');
        Route::post('/import', [JugadorImportController::class, 'import'])->name('import.store');
        Route::get('/', [JugadorController::class, 'index'])->name('index');
        Route::get('/create', [JugadorController::class, 'create'])->name('create');
        Route::post('/', [JugadorController::class, 'store'])->name('store');
        Route::get('/{id}', [JugadorController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [JugadorController::class, 'edit'])->name('edit');
        Route::put('/{id}', [JugadorController::class, 'update'])->name('update');
        Route::delete('/{id}', [JugadorController::class, 'destroy'])->name('destroy');
    });

    // Proveedores
    Route::prefix('proveedores')->name('proveedores.')->group(function () {
        Route::get('/', [ProveedorController::class, 'index'])->name('index');
        Route::get('/create', [ProveedorController::class, 'create'])->name('create');
        Route::post('/', [ProveedorController::class, 'store'])->name('store');
        Route::get('/{id}', [ProveedorController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ProveedorController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ProveedorController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProveedorController::class, 'destroy'])->name('destroy');
    });

    // Gastos
    Route::prefix('gastos')->name('gastos.')->group(function () {
        Route::get('/', [GastoController::class, 'index'])->name('index');
        Route::get('/create', [GastoController::class, 'create'])->name('create');
        Route::post('/', [GastoController::class, 'store'])->name('store');
        Route::get('/{id}', [GastoController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [GastoController::class, 'edit'])->name('edit');
        Route::put('/{id}', [GastoController::class, 'update'])->name('update');
        Route::delete('/{id}', [GastoController::class, 'destroy'])->name('destroy');
    });

    // API para reparto de gastos
    Route::prefix('api/gastos')->group(function () {
        Route::post('/{id}/repartir/equitativo', [\App\Http\Controllers\GastoRepartoController::class, 'repartirEquitativo'])->name('gastos.repartir.equitativo');
        Route::post('/{id}/repartir/personalizado', [\App\Http\Controllers\GastoRepartoController::class, 'repartirPersonalizado'])->name('gastos.repartir.personalizado');
        Route::post('/{id}/repartir/regla', [\App\Http\Controllers\GastoRepartoController::class, 'repartirPorRegla'])->name('gastos.repartir.regla');
    });

    // Pagos
    Route::prefix('pagos')->name('pagos.')->group(function () {
        Route::get('/import', [PagoImportController::class, 'index'])->name('import');
        Route::post('/import', [PagoImportController::class, 'import'])->name('import.store');
        Route::get('/', [PagoJugadorController::class, 'index'])->name('index');
        Route::get('/create', [PagoJugadorController::class, 'create'])->name('create');
        Route::post('/', [PagoJugadorController::class, 'store'])->name('store');
        Route::get('/{id}', [PagoJugadorController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PagoJugadorController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PagoJugadorController::class, 'update'])->name('update');
        Route::delete('/{id}', [PagoJugadorController::class, 'destroy'])->name('destroy');
    });

    // Panel de Administración (solo admins)
    Route::middleware('rol:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/create', [AdminController::class, 'create'])->name('create');
        Route::post('/', [AdminController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminController::class, 'destroy'])->name('destroy');
    });

}); // Cierre del middleware auth