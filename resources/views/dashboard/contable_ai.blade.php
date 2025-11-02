@extends('layouts.layoutMaster')

@section('title', 'Contabilidad AI')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-line me-2"></i>Dashboard de Contabilidad AI
                    </h1>
                    <p class="text-muted">Análisis financiero, tendencias y predicciones</p>
                </div>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver al Dashboard
                </a>
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

    <!-- Tarjetas de Resumen -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Total Ingresos</h6>
                            <h3 class="mb-0 text-success">
                                €{{ number_format($totalIngresos, 2, ',', '.') }}
                            </h3>
                            <small class="text-muted">Todos los pagos registrados</small>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-arrow-up fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Total Gastos</h6>
                            <h3 class="mb-0 text-danger">
                                €{{ number_format($totalGastos, 2, ',', '.') }}
                            </h3>
                            <small class="text-muted">Todos los gastos registrados</small>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-arrow-down fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Saldo Total</h6>
                            <h3 class="mb-0 {{ $saldoTotal >= 0 ? 'text-success' : 'text-danger' }}">
                                €{{ number_format(abs($saldoTotal), 2, ',', '.') }}
                            </h3>
                            <small class="text-muted">
                                {{ $saldoTotal >= 0 ? 'Beneficio' : 'Pérdida' }}
                            </small>
                        </div>
                        <div class="{{ $saldoTotal >= 0 ? 'text-success' : 'text-danger' }}">
                            <i class="fas fa-balance-scale fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Deuda Promedio</h6>
                            <h3 class="mb-0 text-warning">
                                €{{ number_format($promedioDeuda, 2, ',', '.') }}
                            </h3>
                            <small class="text-muted">
                                {{ $jugadoresConDeuda }} jugador(es) con deuda
                            </small>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Predicciones -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-crystal-ball me-2"></i>Predicción Próximo Mes - Ingresos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 text-success">
                                €{{ number_format($prediccionIngresos, 2, ',', '.') }}
                            </h3>
                            <small class="text-muted">Estimación basada en regresión lineal</small>
                        </div>
                        <div>
                            @if($variacionIngresos > 0)
                                <span class="badge bg-success">
                                    <i class="fas fa-arrow-up"></i> +{{ number_format(abs($variacionIngresos), 1) }}%
                                </span>
                            @elseif($variacionIngresos < 0)
                                <span class="badge bg-danger">
                                    <i class="fas fa-arrow-down"></i> {{ number_format($variacionIngresos, 1) }}%
                                </span>
                            @else
                                <span class="badge bg-secondary">Sin variación</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-crystal-ball me-2"></i>Predicción Próximo Mes - Gastos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 text-danger">
                                €{{ number_format($prediccionGastos, 2, ',', '.') }}
                            </h3>
                            <small class="text-muted">Estimación basada en regresión lineal</small>
                        </div>
                        <div>
                            @if($variacionGastos > 0)
                                <span class="badge bg-danger">
                                    <i class="fas fa-arrow-up"></i> +{{ number_format(abs($variacionGastos), 1) }}%
                                </span>
                            @elseif($variacionGastos < 0)
                                <span class="badge bg-success">
                                    <i class="fas fa-arrow-down"></i> {{ number_format($variacionGastos, 1) }}%
                                </span>
                            @else
                                <span class="badge bg-secondary">Sin variación</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row g-3 mb-4">
        <!-- Gráfico de Líneas: Ingresos vs Gastos -->
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Tendencias Mensuales - Ingresos vs Gastos
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartIngresosGastos" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <!-- Gráfico de Barras: Saldo Mensual -->
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Saldo Mensual (Últimos 12 Meses)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartSaldoMensual" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('page-script')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos desde el controlador
    const meses = @json($meses);
    const labels = meses.map(m => m.nombre);
    const ingresos = meses.map(m => m.ingresos);
    const gastos = meses.map(m => m.gastos);
    const saldos = meses.map(m => m.saldo);

    // Configuración común
    Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
    Chart.defaults.font.size = 12;

    // Gráfico de Líneas: Ingresos vs Gastos
    const ctxIngresosGastos = document.getElementById('chartIngresosGastos').getContext('2d');
    new Chart(ctxIngresosGastos, {
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
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                },
                {
                    label: 'Gastos',
                    data: gastos,
                    borderColor: 'rgb(220, 53, 69)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
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
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });

    // Gráfico de Barras: Saldo Mensual
    const ctxSaldoMensual = document.getElementById('chartSaldoMensual').getContext('2d');
    new Chart(ctxSaldoMensual, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Saldo Mensual',
                data: saldos,
                backgroundColor: saldos.map(s => s >= 0 ? 'rgba(40, 167, 69, 0.8)' : 'rgba(220, 53, 69, 0.8)'),
                borderColor: saldos.map(s => s >= 0 ? 'rgb(40, 167, 69)' : 'rgb(220, 53, 69)'),
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
                            const valor = context.parsed.y;
                            const signo = valor >= 0 ? '+' : '';
                            return 'Saldo: ' + signo + '€' + valor.toLocaleString('es-ES', {
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
});
</script>
@endpush
@endsection
