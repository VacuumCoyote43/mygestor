@extends('layouts.layoutMaster')

@section('title', 'Pagos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-money-bill-wave me-2"></i>Pagos</h1>
        <p class="text-muted">Historial de pagos de jugadores</p>
    </div>
    <div>
        <a href="{{ route('pagos.import') }}" class="btn btn-success me-2">
            <i class="fas fa-file-import me-1"></i>Importar desde Excel
        </a>
        <a href="{{ route('pagos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Nuevo Pago
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Jugador</th>
                        <th>Concepto</th>
                        <th class="text-end">Importe</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagos as $pago)
                        <tr>
                            <td>{{ $pago->id_pago }}</td>
                            <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('jugadores.show', $pago->id_jugador) }}">
                                    {{ $pago->jugador->nombre_jugador }}
                                </a>
                            </td>
                            <td>{{ $pago->concepto_pago ?? 'Sin concepto' }}</td>
                            <td class="text-end">
                                <span class="badge bg-success">
                                    €{{ number_format($pago->importe_pago, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('pagos.show', $pago->id_pago) }}" 
                                       class="btn btn-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('pagos.edit', $pago->id_pago) }}" 
                                       class="btn btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('pagos.destroy', $pago->id_pago) }}" 
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
        @if($pagos->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Mostrando {{ $pagos->firstItem() }} a {{ $pagos->lastItem() }} de {{ $pagos->total() }} registros
                    </div>
                    <div>
                        {{ $pagos->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
