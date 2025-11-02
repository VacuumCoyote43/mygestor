@extends('layouts.layoutMaster')

@section('title', 'Gastos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-receipt me-2"></i>Gastos</h1>
        <p class="text-muted">Gestión de gastos del equipo</p>
    </div>
    <a href="{{ route('gastos.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nuevo Gasto
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Proveedor</th>
                        <th class="text-end">Importe Total</th>
                        <th class="text-end">Asignado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gastos as $gasto)
                        <tr>
                            <td>{{ $gasto->id_gasto }}</td>
                            <td>{{ $gasto->fecha_gasto->format('d/m/Y') }}</td>
                            <td>{{ $gasto->tipo_gasto }}</td>
                            <td>
                                @if($gasto->proveedor)
                                    <a href="{{ route('proveedores.show', $gasto->proveedor_id) }}">
                                        {{ $gasto->proveedor->nombre_proveedor }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                €{{ number_format($gasto->importe_total_gasto, 2, ',', '.') }}
                            </td>
                            <td class="text-end">
                                @php
                                    $totalAsignado = $gasto->jugadores->sum('pivot.importe_asignado');
                                    $porcentaje = $gasto->importe_total_gasto > 0 
                                        ? ($totalAsignado / $gasto->importe_total_gasto) * 100 
                                        : 0;
                                @endphp
                                @if(abs($porcentaje - 100) < 0.01)
                                    <span class="badge bg-success">
                                        €{{ number_format($totalAsignado, 2, ',', '.') }}
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        €{{ number_format($totalAsignado, 2, ',', '.') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('gastos.show', $gasto->id_gasto) }}" 
                                       class="btn btn-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('gastos.edit', $gasto->id_gasto) }}" 
                                       class="btn btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('gastos.destroy', $gasto->id_gasto) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirmDelete()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
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
    </div>
</div>
@endsection
