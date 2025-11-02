@extends('layouts.layoutMaster')

@section('title', 'Jugadores')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-users me-2"></i>Jugadores</h1>
        <p class="text-muted">Gestión de jugadores del equipo</p>
    </div>
    <div>
        <a href="{{ route('jugadores.import') }}" class="btn btn-success me-2">
            <i class="fas fa-file-import me-1"></i>Importar desde Excel
        </a>
        <a href="{{ route('jugadores.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Nuevo Jugador
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
                        <th>Nombre</th>
                        <th>Modo</th>
                        <th class="text-end">Saldo</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jugadores as $jugador)
                        <tr>
                            <td>{{ $jugador->id_jugador }}</td>
                            <td>
                                <a href="{{ route('jugadores.show', $jugador->id_jugador) }}">
                                    {{ $jugador->nombre_jugador }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    {{ ucfirst($jugador->mode ?? 'manual') }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($jugador->saldo_jugador > 0)
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <a href="{{ route('pagos.create', ['jugador_id' => $jugador->id_jugador, 'importe' => $jugador->saldo_jugador]) }}" 
                                           class="btn btn-sm btn-success" 
                                           title="Registrar pago de €{{ number_format($jugador->saldo_jugador, 2, ',', '.') }}">
                                            <i class="fas fa-money-bill-wave me-1"></i>Pagar
                                        </a>
                                        <span class="badge bg-danger">
                                            €{{ number_format($jugador->saldo_jugador, 2, ',', '.') }}
                                        </span>
                                    </div>
                                @elseif($jugador->saldo_jugador < 0)
                                    <span class="badge bg-success">
                                        €{{ number_format(abs($jugador->saldo_jugador), 2, ',', '.') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">€0,00</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('jugadores.show', $jugador->id_jugador) }}" 
                                       class="btn btn-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('jugadores.edit', $jugador->id_jugador) }}" 
                                       class="btn btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('jugadores.destroy', $jugador->id_jugador) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirmDelete('¿Está seguro de eliminar este jugador?')">
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
        @if($jugadores->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Mostrando {{ $jugadores->firstItem() }} a {{ $jugadores->lastItem() }} de {{ $jugadores->total() }} registros
                    </div>
                    <div>
                        {{ $jugadores->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
