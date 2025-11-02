@extends('layouts.layoutMaster')

@section('title', 'Detalle Gasto')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-receipt me-2"></i>Gasto #{{ $gasto->id_gasto }}</h1>
        <p class="text-muted">{{ $gasto->tipo_gasto }}</p>
    </div>
    <div>
        <a href="{{ route('gastos.edit', $gasto->id_gasto) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Editar
        </a>
        <a href="{{ route('gastos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Gasto</h5>
            </div>
            <div class="card-body">
                <p><strong>ID:</strong> {{ $gasto->id_gasto }}</p>
                <p><strong>Fecha:</strong> {{ $gasto->fecha_gasto->format('d/m/Y') }}</p>
                <p><strong>Tipo:</strong> {{ $gasto->tipo_gasto }}</p>
                <p><strong>Proveedor:</strong> 
                    @if($gasto->proveedor)
                        <a href="{{ route('proveedores.show', $gasto->proveedor_id) }}">
                            {{ $gasto->proveedor->nombre_proveedor }}
                        </a>
                    @else
                        <span class="text-muted">Sin proveedor</span>
                    @endif
                </p>
                <p><strong>Importe Total:</strong> 
                    <span class="h5 text-danger">
                        €{{ number_format($gasto->importe_total_gasto, 2, ',', '.') }}
                    </span>
                </p>
                @if($gasto->descripcion_gasto)
                    <p><strong>Descripción:</strong><br>{{ $gasto->descripcion_gasto }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header {{ $gasto->estaCompletamenteRepartido() ? 'bg-success' : 'bg-warning' }} text-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Estado de Reparto</h5>
            </div>
            <div class="card-body">
                @php
                    $totalAsignado = $gasto->jugadores->sum('pivot.importe_asignado');
                    $porcentaje = $gasto->importe_total_gasto > 0 
                        ? ($totalAsignado / $gasto->importe_total_gasto) * 100 
                        : 0;
                @endphp
                <div class="text-center mb-3">
                    <h2 class="{{ $gasto->estaCompletamenteRepartido() ? 'text-success' : 'text-warning' }}">
                        {{ number_format($porcentaje, 1) }}%
                    </h2>
                    <p class="text-muted mb-0">Repartido</p>
                </div>
                <div class="progress mb-2">
                    <div class="progress-bar {{ $gasto->estaCompletamenteRepartido() ? 'bg-success' : 'bg-warning' }}" 
                         role="progressbar" 
                         style="width: {{ $porcentaje }}%">
                    </div>
                </div>
                <p class="mb-1"><strong>Total Asignado:</strong> 
                    €{{ number_format($totalAsignado, 2, ',', '.') }}
                </p>
                <p class="mb-0"><strong>Pendiente:</strong> 
                    <span class="{{ $gasto->importe_total_gasto - $totalAsignado > 0.01 ? 'text-danger' : 'text-success' }}">
                        €{{ number_format($gasto->importe_total_gasto - $totalAsignado, 2, ',', '.') }}
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Jugadores Asignados ({{ $gasto->jugadores->count() }})</h5>
    </div>
    <div class="card-body">
        @if($gasto->jugadores->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Jugador</th>
                            <th class="text-end">Importe Asignado</th>
                            <th class="text-end">% del Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gasto->jugadores as $jugador)
                            <tr>
                                <td>
                                    <a href="{{ route('jugadores.show', $jugador->id_jugador) }}">
                                        {{ $jugador->nombre_jugador }}
                                    </a>
                                </td>
                                <td class="text-end">
                                    €{{ number_format($jugador->pivot->importe_asignado, 2, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    @php
                                        $porcentajeJugador = $gasto->importe_total_gasto > 0 
                                            ? ($jugador->pivot->importe_asignado / $gasto->importe_total_gasto) * 100 
                                            : 0;
                                    @endphp
                                    {{ number_format($porcentajeJugador, 1) }}%
                                </td>
                            </tr>
                        @endforeach
                        <tr class="table-info fw-bold">
                            <td>Total</td>
                            <td class="text-end">
                                €{{ number_format($totalAsignado, 2, ',', '.') }}
                            </td>
                            <td class="text-end">{{ number_format($porcentaje, 1) }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0 text-center">Este gasto no ha sido asignado a ningún jugador aún</p>
        @endif
    </div>
</div>
@endsection
