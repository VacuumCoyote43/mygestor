@extends('layouts.layoutMaster')

@section('title', 'Crear Gasto')

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Crear Nuevo Gasto</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('gastos.store') }}" method="POST" id="gastoForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipo_gasto" class="form-label">Tipo de Gasto <span class="text-danger">*</span></label>
                                <select class="form-select @error('tipo_gasto') is-invalid @enderror" 
                                        id="tipo_gasto" name="tipo_gasto" required>
                                    <option value="">Seleccione un tipo</option>
                                    <option value="equipación" {{ old('tipo_gasto') == 'equipación' ? 'selected' : '' }}>Equipación</option>
                                    <option value="ficha" {{ old('tipo_gasto') == 'ficha' ? 'selected' : '' }}>Ficha</option>
                                    <option value="seguro" {{ old('tipo_gasto') == 'seguro' ? 'selected' : '' }}>Seguro</option>
                                    <option value="partido" {{ old('tipo_gasto') == 'partido' ? 'selected' : '' }}>Partido</option>
                                    <option value="transporte" {{ old('tipo_gasto') == 'transporte' ? 'selected' : '' }}>Transporte</option>
                                    <option value="otro" {{ old('tipo_gasto') == 'otro' ? 'selected' : '' }}>Otro</option>
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
                                       value="{{ old('fecha_gasto', date('Y-m-d')) }}" required>
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
                                                {{ old('proveedor_id') == $proveedor->id_proveedor ? 'selected' : '' }}>
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
                                       value="{{ old('importe_total_gasto') }}" required>
                                @error('importe_total_gasto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion_gasto" class="form-label">Descripción</label>
                        <textarea class="form-control @error('descripcion_gasto') is-invalid @enderror" 
                                  id="descripcion_gasto" name="descripcion_gasto" rows="3">{{ old('descripcion_gasto') }}</textarea>
                        @error('descripcion_gasto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- Módulo de Reparto de Gasto -->
                    <div class="mb-4" id="repartoContainer">
                        <h5 class="mb-3">
                            <i class="fas fa-share-alt me-2"></i>Reparto del Gasto
                        </h5>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Nota:</strong> Para usar el reparto automático, primero guarde el gasto. 
                            Luego podrá aplicar el reparto desde la vista de edición.
                        </div>

                        <!-- Selector de tipo de reparto (deshabilitado en creación) -->
                        <div class="mb-3">
                            <label for="tipoReparto" class="form-label">Tipo de Reparto</label>
                            <select class="form-select" id="tipoReparto" disabled>
                                <option value="">Seleccione un tipo de reparto</option>
                                <option value="equitativo">Equitativo (todos los jugadores)</option>
                                <option value="personalizado">Personalizado (seleccionar jugadores)</option>
                                <option value="regla">Según Regla (avanzado)</option>
                            </select>
                            <small class="form-text text-muted">
                                El reparto automático estará disponible después de guardar el gasto.
                            </small>
                        </div>

                        <!-- Sección: Reparto Manual Tradicional -->
                        <div class="mb-3">
                            <h6 class="mb-3">Asignación Manual de Jugadores</h6>
                            <div id="jugadoresContainer">
                                <!-- Los jugadores se agregarán aquí dinámicamente -->
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
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('gastos.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('page-script')
<script>
// Guardar lista de jugadores para uso en JavaScript
const jugadores = @json($jugadores);
let jugadorIndex = 0;

$(document).ready(function() {
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