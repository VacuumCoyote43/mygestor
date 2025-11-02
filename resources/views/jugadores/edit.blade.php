@extends('layouts.layoutMaster')

@section('title', 'Editar Jugador')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Editar Jugador</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('jugadores.update', $jugador->id_jugador) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="nombre_jugador" class="form-label">Nombre del Jugador <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nombre_jugador') is-invalid @enderror" 
                               id="nombre_jugador" name="nombre_jugador" value="{{ old('nombre_jugador', $jugador->nombre_jugador) }}" required>
                        @error('nombre_jugador')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="dni" class="form-label">DNI</label>
                        <input type="text" class="form-control @error('dni') is-invalid @enderror" 
                               id="dni" name="dni" value="{{ old('dni', $jugador->dni ?? '') }}">
                        @error('dni')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                        <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                               id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $jugador->fecha_nacimiento ?? '') }}">
                        @error('fecha_nacimiento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="dorsal" class="form-label">Dorsal</label>
                        <input type="number" class="form-control @error('dorsal') is-invalid @enderror" 
                               id="dorsal" name="dorsal" value="{{ old('dorsal', $jugador->dorsal ?? '') }}">
                        @error('dorsal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="talla_camiseta" class="form-label">Talla camiseta</label>
                            <input type="text" class="form-control @error('talla_camiseta') is-invalid @enderror" 
                                   id="talla_camiseta" name="talla_camiseta" value="{{ old('talla_camiseta', $jugador->talla_camiseta ?? '') }}">
                            @error('talla_camiseta')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="talla_pantalon" class="form-label">Talla pantalón</label>
                            <input type="text" class="form-control @error('talla_pantalon') is-invalid @enderror" 
                                   id="talla_pantalon" name="talla_pantalon" value="{{ old('talla_pantalon', $jugador->talla_pantalon ?? '') }}">
                            @error('talla_pantalon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="talla_medias" class="form-label">Talla medias</label>
                            <input type="text" class="form-control @error('talla_medias') is-invalid @enderror" 
                                   id="talla_medias" name="talla_medias" value="{{ old('talla_medias', $jugador->talla_medias ?? '') }}">
                            @error('talla_medias')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Saldo actual:</strong> 
                        @if($jugador->saldo_jugador > 0)
                            <span class="text-danger">€{{ number_format($jugador->saldo_jugador, 2, ',', '.') }}</span> (debe)
                        @elseif($jugador->saldo_jugador < 0)
                            <span class="text-success">€{{ number_format(abs($jugador->saldo_jugador), 2, ',', '.') }}</span> (a favor)
                        @else
                            <span>€0,00</span>
                        @endif
                        <br>
                        <small>El saldo se calcula automáticamente según gastos y pagos. No se puede editar manualmente.</small>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('jugadores.index') }}" class="btn btn-secondary">
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
@endsection
