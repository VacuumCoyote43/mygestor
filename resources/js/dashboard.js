/**
 * Dashboard de Gestión Económica
 * Script principal para cargar datos vía AJAX y generar gráficos con Chart.js
 */

// Variables globales para los gráficos
let chartGastosPorTipo = null;
let chartDeudaJugadores = null;
let chartPagosMensuales = null;

// Formateador de moneda
const formatter = new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
});

/**
 * Función principal: se ejecuta cuando el DOM está listo
 */
$(document).ready(function() {
    console.log('Cargando dashboard...');
    loadDashboardData();
});

/**
 * Cargar datos del dashboard mediante AJAX
 */
function loadDashboardData() {
    $.ajax({
        url: '/api/dashboard/stats',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log('Datos cargados:', data);
            updateSummaryCards(data);
            createGastosPorTipoChart(data.gastos_por_tipo);
            createDeudaJugadoresChart(data.deuda_por_jugador);
            createPagosMensualesChart(data.pagos_mensuales);
            populateJugadoresTable(data.jugadores);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar datos:', error);
            showError('Error al cargar los datos del dashboard. Por favor, recarga la página.');
        }
    });
}

/**
 * Actualizar las tarjetas de resumen numérico
 */
function updateSummaryCards(data) {
    $('#card-total-gastos').text(formatter.format(data.total_gastos));
    $('#card-total-pagos').text(formatter.format(data.total_pagos));
    $('#card-deuda-jugadores').text(formatter.format(data.deuda_jugadores));
    $('#card-deuda-proveedores').text(formatter.format(data.deuda_proveedores));
    
    // Actualizar tarjetas de proveedores
    if (data.total_pagado_proveedores !== undefined) {
        $('#card-total-pagado-proveedores').text(formatter.format(data.total_pagado_proveedores));
        $('#card-deuda-pendiente-proveedores').text(formatter.format(data.deuda_proveedores));
        
        // Colorear según si hay deuda o no
        const deudaPendiente = $('#card-deuda-pendiente-proveedores');
        if (data.deuda_proveedores > 0) {
            deudaPendiente.removeClass('text-success').addClass('text-danger');
        } else {
            deudaPendiente.removeClass('text-danger').addClass('text-success');
        }
    }
    
    // Calcular total gastos proveedores
    const totalGastosProveedores = data.total_gastos_proveedores !== undefined 
        ? data.total_gastos_proveedores 
        : data.total_gastos;
    $('#card-total-gastos-proveedores').text(formatter.format(totalGastosProveedores));

    // Colorear tarjetas según valores
    const totalCards = $('#summaryCards .card');
    totalCards.each(function(index) {
        $(this).removeClass('border-danger border-success border-warning border-info');
        
        if (index === 0) { // Total Gastos
            $(this).addClass('border-danger');
        } else if (index === 1) { // Total Pagos
            $(this).addClass('border-success');
        } else if (index === 2) { // Deuda Jugadores
            $(this).addClass('border-warning');
        } else if (index === 3) { // Deuda Proveedores
            $(this).addClass('border-info');
        }
    });
}

/**
 * Crear gráfico de barras: Gastos por Tipo
 */
function createGastosPorTipoChart(gastosPorTipo) {
    const ctx = document.getElementById('chartGastosPorTipo').getContext('2d');
    
    // Destruir gráfico anterior si existe
    if (chartGastosPorTipo) {
        chartGastosPorTipo.destroy();
    }

    const labels = gastosPorTipo.map(item => item.tipo);
    const data = gastosPorTipo.map(item => item.total);

    chartGastosPorTipo = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Importe (€)',
                data: data,
                backgroundColor: [
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(13, 110, 253, 0.8)',
                    'rgba(25, 135, 84, 0.8)',
                    'rgba(108, 117, 125, 0.8)',
                    'rgba(111, 66, 193, 0.8)'
                ],
                borderColor: [
                    'rgba(220, 53, 69, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(13, 110, 253, 1)',
                    'rgba(25, 135, 84, 1)',
                    'rgba(108, 117, 125, 1)',
                    'rgba(111, 66, 193, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Total: ' + formatter.format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatter.format(value);
                        }
                    }
                }
            }
        }
    });
}

/**
 * Crear gráfico circular (pie): Distribución de Deuda entre Jugadores
 */
function createDeudaJugadoresChart(deudaPorJugador) {
    const ctx = document.getElementById('chartDeudaJugadores').getContext('2d');
    
    // Destruir gráfico anterior si existe
    if (chartDeudaJugadores) {
        chartDeudaJugadores.destroy();
    }

    // Si no hay deuda, mostrar mensaje
    if (deudaPorJugador.length === 0) {
        ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
        ctx.font = '16px Arial';
        ctx.fillStyle = '#6c757d';
        ctx.textAlign = 'center';
        ctx.fillText('No hay deudas pendientes', ctx.canvas.width / 2, ctx.canvas.height / 2);
        return;
    }

    const labels = deudaPorJugador.map(item => item.nombre);
    const data = deudaPorJugador.map(item => item.saldo);

    // Generar colores dinámicamente
    const colors = generateColors(deudaPorJugador.length);

    chartDeudaJugadores = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors.backgroundColor,
                borderColor: colors.borderColor,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 15,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = formatter.format(context.parsed);
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

/**
 * Crear gráfico de líneas: Evolución Mensual de Pagos
 */
function createPagosMensualesChart(pagosMensuales) {
    const ctx = document.getElementById('chartPagosMensuales').getContext('2d');
    
    // Destruir gráfico anterior si existe
    if (chartPagosMensuales) {
        chartPagosMensuales.destroy();
    }

    const labels = pagosMensuales.map(item => item.label);
    const data = pagosMensuales.map(item => item.total);

    chartPagosMensuales = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pagos Recibidos (€)',
                data: data,
                borderColor: 'rgba(25, 135, 84, 1)',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: 'rgba(25, 135, 84, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Total: ' + formatter.format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatter.format(value);
                        }
                    }
                }
            }
        }
    });
}

/**
 * Poblar la tabla de jugadores con DataTables
 */
function populateJugadoresTable(jugadores) {
    const tbody = $('#tablaJugadores tbody');
    tbody.empty();

    jugadores.forEach(function(jugador) {
        const rowClass = jugador.saldo > 0 ? 'debe-dinero' : '';
        const estadoClass = jugador.saldo > 0 ? 'estado-deuda' : 
                           (jugador.saldo < 0 ? 'estado-a-favor' : 'estado-al-dia');
        const estadoBadge = jugador.saldo > 0 ? 'bg-danger' : 
                           (jugador.saldo < 0 ? 'bg-info' : 'bg-success');

        const row = `
            <tr class="${rowClass}">
                <td>
                    <a href="/jugadores/${jugador.id}">
                        <strong>${jugador.nombre}</strong>
                    </a>
                </td>
                <td class="text-end">${formatter.format(jugador.total_gastos)}</td>
                <td class="text-end">${formatter.format(jugador.total_pagos)}</td>
                <td class="text-end ${estadoClass}">
                    <span class="badge ${estadoBadge}">
                        ${formatter.format(jugador.saldo)}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge ${estadoBadge}">
                        ${jugador.estado}
                    </span>
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    // Inicializar DataTables si no está ya inicializada
    if (!$.fn.DataTable.isDataTable('#tablaJugadores')) {
        $('#tablaJugadores').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[3, 'desc']], // Ordenar por saldo descendente
            pageLength: 25,
            responsive: true,
            columnDefs: [
                { targets: [1, 2, 3], type: 'num' } // Tipos numéricos para ordenar correctamente
            ]
        });
    }
}

/**
 * Generar colores para gráficos
 */
function generateColors(count) {
    const colors = [
        'rgba(220, 53, 69, 0.8)', 'rgba(255, 193, 7, 0.8)', 'rgba(13, 110, 253, 0.8)',
        'rgba(25, 135, 84, 0.8)', 'rgba(108, 117, 125, 0.8)', 'rgba(111, 66, 193, 0.8)',
        'rgba(253, 126, 20, 0.8)', 'rgba(13, 202, 240, 0.8)', 'rgba(102, 16, 242, 0.8)',
        'rgba(214, 51, 132, 0.8)'
    ];
    const borderColors = [
        'rgba(220, 53, 69, 1)', 'rgba(255, 193, 7, 1)', 'rgba(13, 110, 253, 1)',
        'rgba(25, 135, 84, 1)', 'rgba(108, 117, 125, 1)', 'rgba(111, 66, 193, 1)',
        'rgba(253, 126, 20, 1)', 'rgba(13, 202, 240, 1)', 'rgba(102, 16, 242, 1)',
        'rgba(214, 51, 132, 1)'
    ];

    const bgColors = [];
    const bColors = [];

    for (let i = 0; i < count; i++) {
        bgColors.push(colors[i % colors.length]);
        bColors.push(borderColors[i % borderColors.length]);
    }

    return {
        backgroundColor: bgColors,
        borderColor: bColors
    };
}

/**
 * Mostrar mensaje de error
 */
function showError(message) {
    const alert = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('.container-fluid').prepend(alert);
}
