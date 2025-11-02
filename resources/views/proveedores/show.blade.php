@extends('layouts.layoutMaster')

@section('title', 'Detalle Proveedor')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-store me-2"></i>{{ $proveedor->nombre_proveedor }}</h1>
        <p class="text-muted">Información del proveedor</p>
    </div>
    <div>
        <a href="{{ route('proveedores.edit', $proveedor->id_proveedor) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Editar
        </a>
        <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información</h5>
            </div>
            <div class="card-body">
                <p><strong>ID:</strong> {{ $proveedor->id_proveedor }}</p>
                <p><strong>Nombre:</strong> {{ $proveedor->nombre_proveedor }}</p>
                <p><strong>Tipo:</strong> 
                    <span class="badge bg-secondary">{{ $proveedor->tipo_proveedor }}</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-wallet me-2"></i>Saldo</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="text-info">
                    €{{ number_format($proveedor->saldo_proveedor, 2, ',', '.') }}
                </h2>
                <p class="text-muted mb-0">Total de gastos con este proveedor</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Estadísticas</h5>
            </div>
            <div class="card-body">
                <p><strong>Total Gastos:</strong> {{ $gastos->count() }}</p>
                <p><strong>Total Importe:</strong> 
                    €{{ number_format($gastos->sum('importe_total_gasto'), 2, ',', '.') }}
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card">
        <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Gastos Asociados</h5>
    </div>
    <div class="card-body">
        @if($gastos->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th class="text-end">Importe</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gastos as $gasto)
                            <tr>
                                <td>{{ $gasto->id_gasto }}</td>
                                <td>{{ $gasto->fecha_gasto->format('d/m/Y') }}</td>
                                <td>{{ $gasto->tipo_gasto }}</td>
                                <td>{{ $gasto->descripcion_gasto ?? '-' }}</td>
                                <td class="text-end">
                                    €{{ number_format($gasto->importe_total_gasto, 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('gastos.show', $gasto->id_gasto) }}" 
                                       class="btn btn-sm btn-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($gastos->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Mostrando {{ $gastos->firstItem() }} a {{ $gastos->lastItem() }} de {{ $gastos->total() }} registros
                        </div>
                        <div>
                            {{ $gastos->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                </div>
            @endif
        @else
            <p class="text-muted mb-0 text-center">No hay gastos asociados a este proveedor</p>
        @endif
    </div>
</div>
@endsection
