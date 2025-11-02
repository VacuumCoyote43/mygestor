@extends('layouts.layoutMaster')

@section('title', 'Detalle Pago')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-money-bill-wave me-2"></i>Pago #{{ $pago->id_pago }}</h1>
        <p class="text-muted">Detalle del pago registrado</p>
    </div>
    <div>
        <a href="{{ route('pagos.edit', $pago->id_pago) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Editar
        </a>
        <a href="{{ route('pagos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Pago</h5>
            </div>
            <div class="card-body">
                <p><strong>ID:</strong> {{ $pago->id_pago }}</p>
                <p><strong>Jugador:</strong> 
                    <a href="{{ route('jugadores.show', $pago->id_jugador) }}">
                        {{ $pago->jugador->nombre_jugador }}
                    </a>
                </p>
                <p><strong>Fecha:</strong> {{ $pago->fecha_pago->format('d/m/Y') }}</p>
                <p><strong>Concepto:</strong> {{ $pago->concepto_pago ?? 'Sin concepto' }}</p>
                <hr>
                <div class="text-center">
                    <h2 class="text-success mb-0">
                        €{{ number_format($pago->importe_pago, 2, ',', '.') }}
                    </h2>
                    <p class="text-muted mb-0">Importe del pago</p>
                </div>
                <hr>
                <p class="mb-0"><small class="text-muted">
                    <i class="fas fa-calendar me-1"></i>
                    Registrado el {{ $pago->created_at->format('d/m/Y H:i') }}
                </small></p>
            </div>
        </div>
    </div>
</div>
@endsection
