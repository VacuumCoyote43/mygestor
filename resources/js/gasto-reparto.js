/**
 * Módulo de Reparto Automático de Gastos
 * Maneja la lógica del frontend para repartir gastos entre jugadores
 */

// Variables globales
let gastoId = null;
let tipoRepartoActual = null;

// Formateador de moneda
const formatter = new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
});

/**
 * Inicializar el módulo de reparto
 * @param {number} idGasto ID del gasto (null si es creación)
 */
function inicializarReparto(idGasto = null) {
    gastoId = idGasto;
    
    // Configurar eventos
    $('#tipoReparto').on('change', cambiarTipoReparto);
    $('#aplicarReparto').on('click', aplicarReparto);
    $('#calcularAutomatico').on('click', calcularAutomatico);
    
    // Si hay un gasto existente, cargar asignaciones actuales
    if (gastoId) {
        cargarAsignacionesActuales();
    }
}

/**
 * Cambiar el tipo de reparto y mostrar/ocultar secciones correspondientes
 */
function cambiarTipoReparto() {
    tipoRepartoActual = $('#tipoReparto').val();
    
    // Ocultar todas las secciones
    $('.reparto-section').hide();
    
    switch (tipoRepartoActual) {
        case 'equitativo':
            $('#section-equitativo').show();
            break;
        case 'personalizado':
            $('#section-personalizado').show();
            cargarTablaJugadores();
            break;
        case 'regla':
            $('#section-regla').show();
            break;
        default:
            break;
    }
}

/**
 * Cargar tabla de jugadores para reparto personalizado
 */
function cargarTablaJugadores() {
    const tbody = $('#tablaJugadoresReparto tbody');
    tbody.empty();
    
    // Obtener jugadores desde el selector o datos disponibles
    const jugadores = obtenerJugadoresDisponibles();
    
    jugadores.forEach(function(jugador) {
        const row = `
            <tr data-jugador-id="${jugador.id_jugador}">
                <td>
                    <div class="form-check">
                        <input class="form-check-input jugador-checkbox" 
                               type="checkbox" 
                               id="jug_${jugador.id_jugador}"
                               value="${jugador.id_jugador}">
                        <label class="form-check-label" for="jug_${jugador.id_jugador}">
                            ${jugador.nombre_jugador}
                        </label>
                    </div>
                </td>
                <td>
                    <input type="number" 
                           step="0.01" 
                           min="0" 
                           class="form-control form-control-sm importe-jugador" 
                           placeholder="0.00"
                           data-jugador-id="${jugador.id_jugador}"
                           disabled>
                </td>
                <td>
                    <input type="number" 
                           step="0.01" 
                           min="0" 
                           max="100" 
                           class="form-control form-control-sm porcentaje-jugador" 
                           placeholder="0%"
                           data-jugador-id="${jugador.id_jugador}"
                           disabled>
                </td>
                <td class="text-end">
                    <span class="badge bg-secondary importe-display-${jugador.id_jugador}">€0,00</span>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    // Eventos para checkboxes
    $('.jugador-checkbox').on('change', function() {
        const jugadorId = $(this).val();
        const isChecked = $(this).is(':checked');
        
        $(`.importe-jugador[data-jugador-id="${jugadorId}"]`).prop('disabled', !isChecked);
        $(`.porcentaje-jugador[data-jugador-id="${jugadorId}"]`).prop('disabled', !isChecked);
        
        if (!isChecked) {
            $(`.importe-jugador[data-jugador-id="${jugadorId}"]`).val('');
            $(`.porcentaje-jugador[data-jugador-id="${jugadorId}"]`).val('');
            $(`.importe-display-${jugadorId}`).text('€0,00');
        }
    });
    
    // Eventos para inputs de importe
    $('.importe-jugador').on('input', function() {
        const jugadorId = $(this).data('jugador-id');
        const importe = parseFloat($(this).val()) || 0;
        const importeTotal = parseFloat($('#importe_total_gasto').val()) || 0;
        
        if (importeTotal > 0) {
            const porcentaje = (importe / importeTotal) * 100;
            $(`.porcentaje-jugador[data-jugador-id="${jugadorId}"]`).val(porcentaje.toFixed(2));
        }
        
        $(`.importe-display-${jugadorId}`).text(formatter.format(importe));
        actualizarTotales();
    });
    
    // Eventos para inputs de porcentaje
    $('.porcentaje-jugador').on('input', function() {
        const jugadorId = $(this).data('jugador-id');
        const porcentaje = parseFloat($(this).val()) || 0;
        const importeTotal = parseFloat($('#importe_total_gasto').val()) || 0;
        
        const importe = (importeTotal * porcentaje) / 100;
        $(`.importe-jugador[data-jugador-id="${jugadorId}"]`).val(importe.toFixed(2));
        $(`.importe-display-${jugadorId}`).text(formatter.format(importe));
        actualizarTotales();
    });
}

/**
 * Obtener lista de jugadores disponibles
 */
function obtenerJugadoresDisponibles() {
    // Intentar obtener desde los datos del formulario
    if (typeof jugadores !== 'undefined' && Array.isArray(jugadores) && jugadores.length > 0) {
        return jugadores;
    }
    
    // Si no están disponibles, hacer una petición AJAX para obtenerlos
    let jugadoresData = [];
    
    $.ajax({
        url: '/jugadores',
        method: 'GET',
        async: false, // Sincrónico para obtener los datos antes de continuar
        success: function(html) {
            // Parsear HTML sería complejo, mejor usar un endpoint JSON
            // Por ahora retornamos vacío
        }
    });
    
    return jugadoresData;
}

/**
 * Calcular reparto automático (equitativo entre jugadores seleccionados)
 */
function calcularAutomatico() {
    const importeTotal = parseFloat($('#importe_total_gasto').val()) || 0;
    
    if (importeTotal <= 0) {
        mostrarAlerta('error', 'Debe ingresar un importe total válido');
        return;
    }
    
    const jugadoresSeleccionados = $('.jugador-checkbox:checked');
    
    if (jugadoresSeleccionados.length === 0) {
        mostrarAlerta('error', 'Debe seleccionar al menos un jugador');
        return;
    }
    
    const importePorJugador = importeTotal / jugadoresSeleccionados.length;
    
    jugadoresSeleccionados.each(function() {
        const jugadorId = $(this).val();
        $(`.importe-jugador[data-jugador-id="${jugadorId}"]`).val(importePorJugador.toFixed(2));
        
        const porcentaje = (importePorJugador / importeTotal) * 100;
        $(`.porcentaje-jugador[data-jugador-id="${jugadorId}"]`).val(porcentaje.toFixed(2));
        $(`.importe-display-${jugadorId}`).text(formatter.format(importePorJugador));
    });
    
    actualizarTotales();
}

/**
 * Actualizar totales del reparto
 */
function actualizarTotales() {
    let totalAsignado = 0;
    
    $('.importe-jugador').each(function() {
        if (!$(this).prop('disabled')) {
            const valor = parseFloat($(this).val()) || 0;
            totalAsignado += valor;
        }
    });
    
    const importeTotal = parseFloat($('#importe_total_gasto').val()) || 0;
    const diferencia = importeTotal - totalAsignado;
    
    $('#totalAsignado').text(formatter.format(totalAsignado));
    $('#diferencia').text(formatter.format(diferencia));
    
    // Colorear según diferencia
    if (Math.abs(diferencia) < 0.01) {
        $('#diferencia').removeClass('text-danger text-warning').addClass('text-success');
    } else if (diferencia > 0) {
        $('#diferencia').removeClass('text-success text-danger').addClass('text-warning');
    } else {
        $('#diferencia').removeClass('text-success text-warning').addClass('text-danger');
    }
}

/**
 * Aplicar el reparto según el tipo seleccionado
 */
function aplicarReparto() {
    if (!gastoId) {
        mostrarAlerta('error', 'Primero debe guardar el gasto antes de aplicar el reparto');
        return;
    }
    
    tipoRepartoActual = $('#tipoReparto').val();
    
    if (!tipoRepartoActual) {
        mostrarAlerta('error', 'Debe seleccionar un tipo de reparto');
        return;
    }
    
    let url = '';
    let data = {};
    
    switch (tipoRepartoActual) {
        case 'equitativo':
            url = `/api/gastos/${gastoId}/repartir/equitativo`;
            break;
            
        case 'personalizado':
            // Recopilar datos de jugadores seleccionados
            const jugadoresData = [];
            $('.jugador-checkbox:checked').each(function() {
                const jugadorId = $(this).val();
                const importe = parseFloat($(`.importe-jugador[data-jugador-id="${jugadorId}"]`).val()) || 0;
                
                if (importe > 0) {
                    jugadoresData.push({
                        id_jugador: parseInt(jugadorId),
                        importe: parseFloat(importe.toFixed(2))
                    });
                }
            });
            
            if (jugadoresData.length === 0) {
                mostrarAlerta('error', 'Debe seleccionar al menos un jugador con importe asignado');
                return;
            }
            
            url = `/api/gastos/${gastoId}/repartir/personalizado`;
            data = { jugadores: jugadoresData };
            break;
            
        case 'regla':
            // Por ahora implementamos básico
            url = `/api/gastos/${gastoId}/repartir/regla`;
            data = {
                tipo_regla: 'equitativo',
                regla_data: {}
            };
            break;
            
        default:
            mostrarAlerta('error', 'Tipo de reparto no válido');
            return;
    }
    
    // Deshabilitar botón mientras se procesa
    const $btn = $('#aplicarReparto');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Aplicando...');
    
    // Enviar petición AJAX
    $.ajax({
        url: url,
        method: 'POST',
        data: data,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                mostrarAlerta('success', response.message);
                mostrarResumenReparto(response.data);
                
                // Si estamos en la vista de edición, recargar la página después de un momento
                if (gastoId) {
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                }
            } else {
                mostrarAlerta('error', response.message);
            }
        },
        error: function(xhr) {
            let message = 'Error al aplicar el reparto';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            
            mostrarAlerta('error', message);
        },
        complete: function() {
            $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>Aplicar Reparto');
        }
    });
}

/**
 * Cargar asignaciones actuales del gasto
 */
function cargarAsignacionesActuales() {
    // Hacer petición para obtener las asignaciones actuales
    $.ajax({
        url: `/gastos/${gastoId}`,
        method: 'GET',
        success: function(html) {
            // Extraer datos del HTML (mejor sería tener un endpoint JSON)
            // Por ahora, asumimos que los datos están en el DOM
        }
    });
}

/**
 * Mostrar resumen del reparto en un modal
 */
function mostrarResumenReparto(data) {
    let html = `
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Jugador</th>
                        <th class="text-end">Importe Asignado</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    if (data.asignaciones && data.asignaciones.length > 0) {
        data.asignaciones.forEach(function(asignacion) {
            html += `
                <tr>
                    <td>${asignacion.nombre}</td>
                    <td class="text-end">${formatter.format(asignacion.importe)}</td>
                </tr>
            `;
        });
    }
    
    html += `
                </tbody>
                <tfoot>
                    <tr class="table-info">
                        <th>Total</th>
                        <th class="text-end">${formatter.format(data.total_asignado)}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="alert alert-info mt-3">
            <strong>Tipo de reparto:</strong> ${data.tipo_reparto}<br>
            <strong>Jugadores afectados:</strong> ${data.numero_jugadores}
        </div>
    `;
    
    // Mostrar en modal o alerta
    $('#modalResumenReparto .modal-body').html(html);
    $('#modalResumenReparto').modal('show');
}

/**
 * Mostrar alerta
 */
function mostrarAlerta(tipo, mensaje) {
    const alertClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
    const icon = tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${icon} me-2"></i>${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Eliminar alertas anteriores
    $('.alert-reparto').remove();
    
    // Agregar nueva alerta
    $('#repartoContainer').prepend(alert);
    
    // Auto-ocultar después de 5 segundos
    setTimeout(function() {
        $('.alert-reparto').fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}
