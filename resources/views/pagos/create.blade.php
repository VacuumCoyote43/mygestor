@extends('layouts.layoutMaster')

@section('title', 'Registrar Pago')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Registrar Nuevo Pago</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('pagos.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="id_jugador" class="form-label">Jugador <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_jugador') is-invalid @enderror" 
                                id="id_jugador" name="id_jugador" required>
                            <option value="">Seleccione un jugador</option>
                            @foreach($jugadores as $jugador)
                                <option value="{{ $jugador->id_jugador }}" 
                                        {{ old('id_jugador', $jugadorId ?? '') == $jugador->id_jugador ? 'selected' : '' }}
                                        data-saldo="{{ $jugador->saldo_jugador }}">
                                    {{ $jugador->nombre_jugador }}
                                    @if($jugador->saldo_jugador > 0)
                                        (Deuda: €{{ number_format($jugador->saldo_jugador, 2, ',', '.') }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('id_jugador')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    @if(isset($jugadorId) && isset($importe))
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Pago de deuda:</strong> Se ha pre-rellenado el importe con la deuda actual del jugador. Puedes modificar el importe si es necesario.
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_pago" class="form-label">Fecha <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('fecha_pago') is-invalid @enderror" 
                                       id="fecha_pago" name="fecha_pago" 
                                       value="{{ old('fecha_pago', date('Y-m-d')) }}" required>
                                @error('fecha_pago')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="importe_pago" class="form-label">Importe <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" 
                                       class="form-control @error('importe_pago') is-invalid @enderror" 
                                       id="importe_pago" name="importe_pago" 
                                       value="{{ old('importe_pago', $importe ?? '') }}" required>
                                @error('importe_pago')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if(isset($importe))
                                    <small class="form-text text-muted">Deuda actual del jugador pre-rellenada</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="concepto_pago" class="form-label">Concepto</label>
                        <input type="text" class="form-control @error('concepto_pago') is-invalid @enderror" 
                               id="concepto_pago" name="concepto_pago" 
                               value="{{ old('concepto_pago') }}"
                               placeholder="Ej: Pago de equipación, Cuota mensual, etc.">
                        @error('concepto_pago')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Nota:</strong> El saldo del jugador se actualizará automáticamente al guardar este pago.
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('pagos.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Registrar Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
