@extends('layouts.layoutMaster')

@section('title', 'Editar Gasto')

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Editar Gasto #{{ $gasto->id_gasto }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('gastos.update', $gasto->id_gasto) }}" method="POST" id="gastoForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipo_gasto" class="form-label">Tipo de Gasto <span class="text-danger">*</span></label>
                                <select class="form-select @error('tipo_gasto') is-invalid @enderror" 
                                        id="tipo_gasto" name="tipo_gasto" required>
                                    <option value="">Seleccione un tipo</option>
                                    <option value="equipación" {{ old('tipo_gasto', $gasto->tipo_gasto) == 'equipación' ? 'selected' : '' }}>Equipación</option>
                                    <option value="ficha" {{ old('tipo_gasto', $gasto->tipo_gasto) == 'ficha' ? 'selected' : '' }}>Ficha</option>
                                    <option value="seguro" {{ old('tipo_gasto', $gasto->tipo_gasto) == 'seguro' ? 'selected' : '' }}>Seguro</option>
                                    <option value="partido" {{ old('tipo_gasto', $gasto->tipo_gasto) == 'partido' ? 'selected' : '' }}>Partido</option>
                                    <option value="transporte" {{ old('tipo_gasto', $gasto->tipo_gasto) == 'transporte' ? 'selected' : '' }}>Transporte</option>
                                    <option value="otro" {{ old('tipo_gasto', $gasto->tipo_gasto) == 'otro' ? 'selected' : '' }}>Otro</option>
                                </select>
                                @error('tipo_gasto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_gasto" class="form-label">Fecha <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('fecha_gasto') is-invalid @enderror" 
                                       id="fecha_gasto" name="fecha_gasto" 
                                       value="{{ old('fecha_gasto', $gasto->fecha_gasto->format('Y-m-d')) }}" required>
                                @error('fecha_gasto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="proveedor_id" class="form-label">Proveedor</label>
                                <select class="form-select @error('proveedor_id') is-invalid @enderror" 
                                        id="proveedor_id" name="proveedor_id">
                                    <option value="">Sin proveedor</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id_proveedor }}" 
                                                {{ old('proveedor_id', $gasto->proveedor_id) == $proveedor->id_proveedor ? 'selected' : '' }}>
                                            {{ $proveedor->nombre_proveedor }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('proveedor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="importe_total_gasto" class="form-label">Importe Total <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" 
                                       class="form-control @error('importe_total_gasto') is-invalid @enderror" 
                                       id="importe_total_gasto" name="importe_total_gasto" 
                                       value="{{ old('importe_total_gasto', $gasto->importe_total_gasto) }}" required>
                                @error('importe_total_gasto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion_gasto" class="form-label">Descripción</label>
                        <textarea class="form-control @error('descripcion_gasto') is-invalid @enderror" 
                                  id="descripcion_gasto" name="descripcion_gasto" rows="3">{{ old('descripcion_gasto', $gasto->descripcion_gasto) }}</textarea>
                        @error('descripcion_gasto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- Módulo de Reparto Automático -->
                    <div class="mb-4" id="repartoContainer">
                        <h5 class="mb-3">
                            <i class="fas fa-share-alt me-2"></i>Reparto del Gasto
                        </h5>

                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Estado actual:</strong> 
                            @if($gasto->jugadores->count() > 0)
                                Este gasto está asignado a {{ $gasto->jugadores->count() }} jugador(es).
                            @else
                                Este gasto aún no ha sido asignado a ningún jugador.
                            @endif
                        </div>

                        <!-- Selector de tipo de reparto -->
                        <div class="mb-3">
                            <label for="tipoReparto" class="form-label">Tipo de Reparto</label>
                            <select class="form-select" id="tipoReparto">
                                <option value="">Seleccione un tipo de reparto</option>
                                <option value="equitativo">Equitativo (todos los jugadores)</option>
                                <option value="personalizado">Personalizado (seleccionar jugadores)</option>
                                <option value="regla">Según Regla (avanzado)</option>
                            </select>
                            <small class="form-text text-muted">
                                Seleccione cómo desea repartir el gasto entre los jugadores.
                            </small>
                        </div>

                        <!-- Sección: Reparto Equitativo -->
                        <div id="section-equitativo" class="reparto-section mb-3" style="display: none;">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-equals me-2"></i>Reparto Equitativo
                                    </h6>
                                    <p class="card-text text-muted">
                                        El importe total se dividirá equitativamente entre todos los jugadores activos del equipo.
                                    </p>
                                    <div class="alert alert-info">
                                        <strong>Importe por jugador:</strong> 
                                        <span id="importeEquitativo">€0,00</span>
                                    </div>
                                    <button type="button" class="btn btn-primary" id="aplicarReparto">
                                        <i class="fas fa-check me-1"></i>Aplicar Reparto Equitativo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Sección: Reparto Personalizado -->
                        <div id="section-personalizado" class="reparto-section mb-3" style="display: none;">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-user-edit me-2"></i>Reparto Personalizado
                                    </h6>
                                    <p class="card-text text-muted mb-3">
                                        Seleccione los jugadores que participarán en este gasto y asigne el importe a cada uno.
                                    </p>
                                    
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm table-bordered" id="tablaJugadoresReparto">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 5%;">
                                                        <input type="checkbox" id="selectAllJugadores" title="Seleccionar todos">
                                                    </th>
                                                    <th>Jugador</th>
                                                    <th style="width: 25%;">Importe (€)</th>
                                                    <th style="width: 15%;">Porcentaje (%)</th>
                                                    <th style="width: 15%;" class="text-end">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Se llenará dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body p-2">
                                                    <small class="text-muted">Importe Total:</small>
                                                    <strong id="importeTotalDisplay">€0,00</strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body p-2">
                                                    <small class="text-muted">Total Asignado:</small>
                                                    <strong id="totalAsignado">€0,00</strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body p-2">
                                                    <small class="text-muted">Diferencia:</small>
                                                    <strong id="diferencia" class="text-success">€0,00</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-info btn-sm" id="calcularAutomatico">
                                            <i class="fas fa-calculator me-1"></i>Calcular Automático
                                        </button>
                                        <button type="button" class="btn btn-primary" id="aplicarReparto">
                                            <i class="fas fa-check me-1"></i>Aplicar Reparto Personalizado
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección: Reparto por Regla -->
                        <div id="section-regla" class="reparto-section mb-3" style="display: none;">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-cogs me-2"></i>Reparto Según Regla
                                    </h6>
                                    <p class="card-text text-muted">
                                        Funcionalidad avanzada para aplicar reglas de reparto personalizadas.
                                    </p>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Esta funcionalidad está en desarrollo. Por ahora, utilice el reparto equitativo o personalizado.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Sección tradicional de asignación manual (para compatibilidad) -->
                    <div class="mb-3">
                        <h5 class="mb-3">Asignación Manual de Jugadores (Método Tradicional)</h5>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            También puede asignar jugadores manualmente usando el método tradicional.
                        </div>
                        
                        <div id="jugadoresContainer">
                            @foreach($gasto->jugadores as $jugador)
                                <div class="row mb-2 jugador-row" data-index="{{ $loop->index }}">
                                    <div class="col-md-5">
                                        <select class="form-select jugador-select" name="jugadores[{{ $loop->index }}][id]" required>
                                            <option value="">Seleccione un jugador</option>
                                            @foreach($jugadores as $j)
                                                <option value="{{ $j->id_jugador }}" 
                                                        {{ $j->id_jugador == $jugador->id_jugador ? 'selected' : '' }}>
                                                    {{ $j->nombre_jugador }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="number" step="0.01" min="0" 
                                               class="form-control importe-input" 
                                               name="jugadores[{{ $loop->index }}][importe]" 
                                               value="{{ $jugador->pivot->importe_asignado }}"
                                               placeholder="Importe asignado" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-sm eliminarJugador">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-secondary" id="agregarJugador">
                                <i class="fas fa-plus me-1"></i>Agregar Jugador
                            </button>
                            <button type="button" class="btn btn-sm btn-info" id="repartirProporcional">
                                <i class="fas fa-equals me-1"></i>Repartir Proporcionalmente
                            </button>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('gastos.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar resumen del reparto -->
<div class="modal fade" id="modalResumenReparto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Resumen del Reparto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Se llenará dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('page-script')
<!-- Script del módulo de reparto -->
<script src="{{ asset('js/gasto-reparto.js') }}"></script>

<script>
// Guardar lista de jugadores y datos del gasto para uso en JavaScript (global)
var jugadores = @json($jugadores);
var importeTotal = {{ $gasto->importe_total_gasto }};

$(document).ready(function() {
    // Inicializar módulo de reparto
    gastoId = {{ $gasto->id_gasto }};
    inicializarReparto(gastoId);
    
    // Actualizar importe equitativo cuando cambia el importe total
    $('#importe_total_gasto').on('input', function() {
        const total = parseFloat($(this).val()) || 0;
        const numJugadores = jugadores.length;
        const importePorJugador = numJugadores > 0 ? total / numJugadores : 0;
        
        $('#importeEquitativo').text(new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'EUR'
        }).format(importePorJugador));
        
        $('#importeTotalDisplay').text(new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'EUR'
        }).format(total));
    });
    
    // Inicializar importe equitativo
    const numJugadores = jugadores.length;
    const importePorJugador = numJugadores > 0 ? importeTotal / numJugadores : 0;
    $('#importeEquitativo').text(new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(importePorJugador));
    
    $('#importeTotalDisplay').text(new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(importeTotal));

    // Seleccionar todos los jugadores
    $('#selectAllJugadores').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.jugador-checkbox').prop('checked', isChecked).trigger('change');
    });

    // Funcionalidad tradicional de asignación manual
    let jugadorIndex = {{ $gasto->jugadores->count() }};

    $('#agregarJugador').on('click', function() {
        const html = `
            <div class="row mb-2 jugador-row" data-index="${jugadorIndex}">
                <div class="col-md-5">
                    <select class="form-select jugador-select" name="jugadores[${jugadorIndex}][id]" required>
                        <option value="">Seleccione un jugador</option>
                        ${jugadores.map(j => 
                            `<option value="${j.id_jugador}">${j.nombre_jugador}</option>`
                        ).join('')}
                    </select>
                </div>
                <div class="col-md-5">
                    <input type="number" step="0.01" min="0" 
                           class="form-control importe-input" 
                           name="jugadores[${jugadorIndex}][importe]" 
                           placeholder="Importe asignado" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm eliminarJugador">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#jugadoresContainer').append(html);
        jugadorIndex++;
    });

    $(document).on('click', '.eliminarJugador', function() {
        $(this).closest('.jugador-row').remove();
    });

    $('#repartirProporcional').on('click', function() {
        const importeTotal = parseFloat($('#importe_total_gasto').val()) || 0;
        const numJugadores = $('.jugador-row').length;
        
        if (numJugadores === 0) {
            alert('Primero debe agregar jugadores');
            return;
        }

        if (importeTotal <= 0) {
            alert('Debe ingresar un importe total válido');
            return;
        }

        const importePorJugador = importeTotal / numJugadores;
        $('.importe-input').val(importePorJugador.toFixed(2));
    });
});
</script>
@endpush
@endsection