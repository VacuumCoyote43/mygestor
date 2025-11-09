@extends('layouts.layoutMaster')

@section('title', 'Liquidaciones del Proveedor')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-money-bill-wave me-2"></i>Liquidaciones de {{ $proveedor->nombre_proveedor }}</h1>
        <p class="text-muted">Gestión de pagos al proveedor</p>
    </div>
    <div>
        <a href="{{ route('proveedores.liquidaciones.create', $proveedor->id_proveedor) }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Nueva Liquidación
        </a>
        <a href="{{ route('proveedores.show', $proveedor->id_proveedor) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-wallet me-2"></i>Saldo Total</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="text-info">
                    €{{ number_format($proveedor->saldo_proveedor, 2, ',', '.') }}
                </h2>
                <p class="text-muted mb-0">Total de gastos</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Total Pagado</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="text-success">
                    €{{ number_format($liquidaciones->sum('monto'), 2, ',', '.') }}
                </h2>
                <p class="text-muted mb-0">En liquidaciones</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Deuda Pendiente</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="text-danger">
                    €{{ number_format($proveedor->saldo_proveedor - $liquidaciones->sum('monto'), 2, ',', '.') }}
                </h2>
                <p class="text-muted mb-0">Por liquidar</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Historial de Liquidaciones</h5>
    </div>
    <div class="card-body">
        @if($liquidaciones->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped data-table">
                    <thead>
                        <tr>
                            <th>Fecha de Pago</th>
                            <th class="text-end">Monto (€)</th>
                            <th>Método de Pago</th>
                            <th>Notas</th>
                            <th>Fecha de Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($liquidaciones as $liq)
                        <tr>
                            <td>{{ $liq->fecha_pago->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <span class="badge bg-success">
                                    €{{ number_format($liq->monto, 2, ',', '.') }}
                                </span>
                            </td>
                            <td>{{ $liq->metodo_pago ?? '-' }}</td>
                            <td>{{ $liq->nota ?? '-' }}</td>
                            <td>{{ $liq->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-info">
                            <th>Total</th>
                            <th class="text-end">€{{ number_format($liquidaciones->sum('monto'), 2, ',', '.') }}</th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No hay liquidaciones registradas para este proveedor</p>
                <a href="{{ route('proveedores.liquidaciones.create', $proveedor->id_proveedor) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Registrar Primera Liquidación
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
