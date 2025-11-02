@extends('layouts.layoutMaster')

@section('title', 'Estadísticas Financieras')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Estadísticas Financieras
                    </h1>
                    <p class="text-muted">Análisis detallado de ingresos, gastos y balance</p>
                </div>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros de Fecha -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="filtroForm" method="GET" action="{{ route('dashboard.estadisticas.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="fecha_inicio" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>Fecha Inicio
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_inicio" 
                                   name="fecha_inicio" 
                                   value="{{ $fechasFiltro['inicio'] }}" 
                                   required>
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_fin" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>Fecha Fin
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_fin" 
                                   name="fecha_fin" 
                                   value="{{ $fechasFiltro['fin'] }}" 
                                   required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i>Filtrar
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnActualizarGraficos">
                                <i class="fas fa-sync me-1"></i>Actualizar Gráficos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-2 opacity-75">Total Ingresos</h6>
                            <h3 class="mb-0">€{{ number_format($totalIngresos, 2, ',', '.') }}</h3>
                            <small class="opacity-75">Período seleccionado</small>
                        </div>
                        <div>
                            <i class="fas fa-arrow-up fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-2 opacity-75">Total Gastos</h6>
                            <h3 class="mb-0">€{{ number_format($totalGastos, 2, ',', '.') }}</h3>
                            <small class="opacity-75">Período seleccionado</small>
                        </div>
                        <div>
                            <i class="fas fa-arrow-down fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-subtitle mb-2 opacity-75">Saldo Total</h6>
                            <h3 class="mb-0">€{{ number_format(abs($saldoPeriodo), 2, ',', '.') }}</h3>
                            <small class="opacity-75">
                                {{ $saldoPeriodo >= 0 ? 'Beneficio' : 'Pérdida' }}
                            </small>
                        </div>
                        <div>
                            <i class="fas fa-balance-scale fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    @if(count($alertas) > 0)
        <div class="row mb-4">
            <div class="col-12">
                @foreach($alertas as $alerta)
                    <div class="alert alert-{{ $alerta['tipo'] }} alert-dismissible fade show" role="alert">
                        <i class="fas fa-{{ $alerta['icono'] }} me-2"></i>
                        {{ $alerta['mensaje'] }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Botones de Exportación -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('dashboard.estadisticas.export.pdf') }}" style="display: inline;">
                            @csrf
                            <input type="hidden" name="fecha_inicio" value="{{ $fechasFiltro['inicio'] }}">
                            <input type="hidden" name="fecha_fin" value="{{ $fechasFiltro['fin'] }}">
                            <button type="submit" class="btn btn-outline-primary" id="exportPDF">
                                <i class="fas fa-file-pdf me-1"></i>Exportar PDF
                            </button>
                        </form>
                        <form method="POST" action="{{ route('dashboard.estadisticas.export.excel') }}" style="display: inline;">
                            @csrf
                            <input type="hidden" name="fecha_inicio" value="{{ $fechasFiltro['inicio'] }}">
                            <input type="hidden" name="fecha_fin" value="{{ $fechasFiltro['fin'] }}">
                            <button type="submit" class="btn btn-outline-success" id="exportExcel">
                                <i class="fas fa-file-excel me-1"></i>Exportar Excel
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row g-3 mb-4">
        <!-- Ingresos por Jugador -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-check me-2"></i>Ingresos por Jugador
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartIngresosJugador" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Gastos por Proveedor -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-store me-2"></i>Gastos por Proveedor
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartGastosProveedor" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Mensual Comparado -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Balance Mensual Comparado
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartBalanceMensual" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Alertas -->
    @if(count($alertas) > 0)
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>Alertas del Período
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Icono</th>
                                        <th>Tipo</th>
                                        <th>Mensaje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($alertas as $alerta)
                                        <tr>
                                            <td>
                                                <i class="fas fa-{{ $alerta['icono'] }} text-{{ $alerta['tipo'] }}"></i>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $alerta['tipo'] }}">
                                                    {{ ucfirst($alerta['tipo']) }}
                                                </span>
                                            </td>
                                            <td>{{ $alerta['mensaje'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('page-script')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos desde el controlador
    const ingresosPorJugador = @json($ingresosPorJugador);
    const gastosPorProveedor = @json($gastosPorProveedor);
    const balanceMensual = @json($balanceMensual);

    let chartIngresos, chartGastos, chartBalance;

    // Configuración común
    Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
    Chart.defaults.font.size = 12;

    // Función para crear gráfico de ingresos por jugador
    function crearGraficoIngresos() {
        const ctx = document.getElementById('chartIngresosJugador').getContext('2d');
        const labels = ingresosPorJugador.map(item => item.nombre);
        const datos = ingresosPorJugador.map(item => item.total);

        if (chartIngresos) {
            chartIngresos.destroy();
        }

        chartIngresos = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ingresos (€)',
                    data: datos,
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: 'rgb(40, 167, 69)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '€' + context.parsed.y.toLocaleString('es-ES', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '€' + value.toLocaleString('es-ES', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        }
                    }
                }
            }
        });
    }

    // Función para crear gráfico de gastos por proveedor
    function crearGraficoGastos() {
        const ctx = document.getElementById('chartGastosProveedor').getContext('2d');
        const labels = gastosPorProveedor.map(item => item.nombre);
        const datos = gastosPorProveedor.map(item => item.total);

        if (chartGastos) {
            chartGastos.destroy();
        }

        chartGastos = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Gastos (€)',
                    data: datos,
                    backgroundColor: 'rgba(220, 53, 69, 0.8)',
                    borderColor: 'rgb(220, 53, 69)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '€' + context.parsed.y.toLocaleString('es-ES', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '€' + value.toLocaleString('es-ES', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        }
                    }
                }
            }
        });
    }

    // Función para crear gráfico de balance mensual
    function crearGraficoBalance() {
        const ctx = document.getElementById('chartBalanceMensual').getContext('2d');
        const labels = balanceMensual.map(item => item.nombre);
        const ingresos = balanceMensual.map(item => item.ingresos);
        const gastos = balanceMensual.map(item => item.gastos);
        const balances = balanceMensual.map(item => item.balance);

        if (chartBalance) {
            chartBalance.destroy();
        }

        chartBalance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Ingresos',
                        data: ingresos,
                        borderColor: 'rgb(40, 167, 69)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Gastos',
                        data: gastos,
                        borderColor: 'rgb(220, 53, 69)',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Balance',
                        data: balances,
                        borderColor: 'rgb(0, 123, 255)',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        borderDash: [5, 5],
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': €' + context.parsed.y.toLocaleString('es-ES', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return '€' + value.toLocaleString('es-ES', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        },
                        grid: {
                            color: function(context) {
                                if (context.tick.value === 0) {
                                    return 'rgba(0, 0, 0, 0.3)';
                                }
                                return 'rgba(0, 0, 0, 0.1)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Inicializar gráficos
    crearGraficoIngresos();
    crearGraficoGastos();
    crearGraficoBalance();

    // Actualizar gráficos con AJAX
    document.getElementById('btnActualizarGraficos').addEventListener('click', function() {
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;

        fetch('{{ route('dashboard.estadisticas.datos') }}?' + new URLSearchParams({
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin
        }), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Actualizar datos
            ingresosPorJugador.length = 0;
            ingresosPorJugador.push(...data.ingresosPorJugador);
            
            gastosPorProveedor.length = 0;
            gastosPorProveedor.push(...data.gastosPorProveedor);
            
            balanceMensual.length = 0;
            balanceMensual.push(...data.balanceMensual);

            // Recrear gráficos
            crearGraficoIngresos();
            crearGraficoGastos();
            crearGraficoBalance();
        })
        .catch(error => {
            console.error('Error al actualizar gráficos:', error);
            alert('Error al actualizar los gráficos. Por favor, recarga la página.');
        });
    });
});
</script>
@endpush
@endsection
