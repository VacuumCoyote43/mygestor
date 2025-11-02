@extends('layouts.layoutMaster')

@section('title', 'Dashboard - Gestión de Equipo')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="fas fa-tachometer-alt me-2"></i>Dashboard de Gestión Económica</h1>
                    <p class="text-muted">Resumen visual y numérico de toda la información del equipo</p>
                </div>
                <div>
                    <a href="{{ route('dashboard.contable_ai') }}" class="btn btn-primary me-2">
                        <i class="fas fa-chart-line me-1"></i>Análisis AI
                    </a>
                    <a href="{{ route('dashboard.estadisticas.index') }}" class="btn btn-info">
                        <i class="fas fa-chart-bar me-1"></i>Estadísticas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen Numérico Principal -->
    <div class="row g-3 mb-4" id="summaryCards">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-receipt fa-2x text-danger"></i>
                    </div>
                    <h5 class="card-title text-muted mb-2">Total Gastos</h5>
                    <h2 class="mb-0 text-danger" id="card-total-gastos">€0,00</h2>
                    <small class="text-muted">Equipo completo</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                    </div>
                    <h5 class="card-title text-muted mb-2">Total Pagos</h5>
                    <h2 class="mb-0 text-success" id="card-total-pagos">€0,00</h2>
                    <small class="text-muted">Recibidos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                    </div>
                    <h5 class="card-title text-muted mb-2">Deuda Jugadores</h5>
                    <h2 class="mb-0 text-warning" id="card-deuda-jugadores">€0,00</h2>
                    <small class="text-muted">Pendiente de cobro</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-store fa-2x text-info"></i>
                    </div>
                    <h5 class="card-title text-muted mb-2">Deuda Proveedores</h5>
                    <h2 class="mb-0 text-info" id="card-deuda-proveedores">€0,00</h2>
                    <small class="text-muted">Pendiente de pago</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row g-3 mb-4">
        <!-- Gráfico de Barras: Gastos por Tipo -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Gastos por Tipo
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartGastosPorTipo" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico Circular: Distribución de Deuda -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Distribución de Deuda entre Jugadores
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartDeudaJugadores" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Líneas: Evolución Mensual de Pagos -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Evolución Mensual de Pagos Recibidos
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartPagosMensuales" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Jugadores -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header ">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>Resumen por Jugador
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaJugadores">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th class="text-end">Total Gastos</th>
                                    <th class="text-end">Total Pagos</th>
                                    <th class="text-end">Saldo Actual</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargarán dinámicamente con AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-style')
<style>
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    #tablaJugadores tbody tr.debe-dinero {
        background-color: #fff5f5;
    }
    #tablaJugadores tbody tr.debe-dinero:hover {
        background-color: #ffe6e6;
    }
    .estado-deuda {
        color: #dc3545;
        font-weight: bold;
    }
    .estado-al-dia {
        color: #198754;
        font-weight: bold;
    }
    .estado-a-favor {
        color: #0d6efd;
        font-weight: bold;
    }
</style>
@endpush

@push('page-script')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- Dashboard JavaScript -->
@vite(['resources/js/dashboard.js'])
@endpush