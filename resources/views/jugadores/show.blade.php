@extends('layouts.layoutMaster')

@section('title', 'Detalle Jugador')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-user me-2"></i>{{ $jugador->nombre_jugador }}</h1>
        <p class="text-muted">Información detallada del jugador</p>
    </div>
    <div>
        <a href="{{ route('jugadores.edit', $jugador->id_jugador) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Editar
        </a>
        <a href="{{ route('jugadores.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Información Personal -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información Personal</h5>
            </div>
            <div class="card-body">
                <p><strong>ID:</strong> {{ $jugador->id_jugador }}</p>
                <p><strong>Nombre:</strong> {{ $jugador->nombre_jugador }}</p>
                <p><strong>DNI:</strong> 
                    @if($jugador->dni)
                        {{ $jugador->dni }}
                    @else
                        <span class="text-muted">No especificado</span>
                    @endif
                </p>
                <p><strong>Fecha de Nacimiento:</strong> 
                    @if($jugador->fecha_nacimiento)
                        {{ \Carbon\Carbon::parse($jugador->fecha_nacimiento)->format('d/m/Y') }}
                        <small class="text-muted">({{ \Carbon\Carbon::parse($jugador->fecha_nacimiento)->age }} años)</small>
                    @else
                        <span class="text-muted">No especificada</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Información Deportiva -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-futbol me-2"></i>Información Deportiva</h5>
            </div>
            <div class="card-body">
                <p><strong>Dorsal:</strong> 
                    @if($jugador->dorsal)
                        <span class="badge bg-primary">{{ $jugador->dorsal }}</span>
                    @else
                        <span class="text-muted">No asignado</span>
                    @endif
                </p>
                <hr>
                <p class="mb-2"><strong>Equipación:</strong></p>
                <ul class="list-unstyled mb-0">
                    <li><strong>Talla Camiseta:</strong> 
                        @if($jugador->talla_camiseta)
                            {{ $jugador->talla_camiseta }}
                        @else
                            <span class="text-muted">No especificada</span>
                        @endif
                    </li>
                    <li><strong>Talla Pantalón:</strong> 
                        @if($jugador->talla_pantalon)
                            {{ $jugador->talla_pantalon }}
                        @else
                            <span class="text-muted">No especificada</span>
                        @endif
                    </li>
                    <li><strong>Talla Medias:</strong> 
                        @if($jugador->talla_medias)
                            {{ $jugador->talla_medias }}
                        @else
                            <span class="text-muted">No especificada</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Saldo y Resumen -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header {{ $jugador->saldo_jugador > 0 ? 'bg-danger' : ($jugador->saldo_jugador < 0 ? 'bg-success' : 'bg-secondary') }} text-white">
                <h5 class="mb-0"><i class="fas fa-wallet me-2"></i>Saldo</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="{{ $jugador->saldo_jugador > 0 ? 'text-danger' : ($jugador->saldo_jugador < 0 ? 'text-success' : 'text-secondary') }}">
                    €{{ number_format($jugador->saldo_jugador, 2, ',', '.') }}
                </h2>
                @if($jugador->saldo_jugador > 0)
                    <p class="text-muted mb-0">El jugador debe esta cantidad</p>
                @elseif($jugador->saldo_jugador < 0)
                    <p class="text-muted mb-0">El jugador tiene saldo a favor</p>
                @else
                    <p class="text-muted mb-0">Saldo al día</p>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Resumen</h5>
            </div>
            <div class="card-body">
                <p><strong>Total Gastos:</strong> 
                    €{{ number_format($jugador->gastos->sum('pivot.importe_asignado'), 2, ',', '.') }}
                </p>
                <p><strong>Total Pagos:</strong> 
                    €{{ number_format($jugador->pagos->sum('importe_pago'), 2, ',', '.') }}
                </p>
                <p><strong>Nº Gastos:</strong> {{ $jugador->gastos->count() }}</p>
                <p><strong>Nº Pagos:</strong> {{ $jugador->pagos->count() }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Información del Sistema -->
<div class="row g-3 mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Información del Sistema</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <p><strong>Modo de creación:</strong> 
                            <span class="badge bg-info">{{ ucfirst($jugador->mode ?? 'manual') }}</span>
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Creado:</strong> 
                            {{ $jugador->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Última actualización:</strong> 
                            {{ $jugador->updated_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Última actualización hace:</strong> 
                            {{ $jugador->updated_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Gastos -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Gastos Asociados</h5>
                <span class="badge bg-light text-dark">{{ $gastos->total() }}</span>
            </div>
            <div class="card-body">
                @if($gastos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Concepto</th>
                                    <th class="text-end">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gastos as $gasto)
                                    <tr>
                                        <td>{{ $gasto->fecha_gasto->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('gastos.show', $gasto->id_gasto) }}">
                                                {{ $gasto->tipo_gasto }}
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            €{{ number_format($gasto->pivot->importe_asignado, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($gastos->hasPages())
                        <div class="card-footer">
                            {{ $gastos->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    @endif
                @else
                    <p class="text-muted mb-0 text-center">No hay gastos asociados</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Pagos -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Historial de Pagos</h5>
                <span class="badge bg-light text-dark">{{ $pagos->total() }}</span>
            </div>
            <div class="card-body">
                @if($pagos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Concepto</th>
                                    <th class="text-end">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pagos as $pago)
                                    <tr>
                                        <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                                        <td>{{ $pago->concepto_pago ?? 'Sin concepto' }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-success">
                                                €{{ number_format($pago->importe_pago, 2, ',', '.') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($pagos->hasPages())
                        <div class="card-footer">
                            {{ $pagos->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    @endif
                @else
                    <p class="text-muted mb-0 text-center">No hay pagos registrados</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
