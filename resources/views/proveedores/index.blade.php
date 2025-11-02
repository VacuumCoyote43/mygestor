@extends('layouts.layoutMaster')

@section('title', 'Proveedores')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-store me-2"></i>Proveedores</h1>
        <p class="text-muted">Gestión de proveedores</p>
    </div>
    <a href="{{ route('proveedores.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nuevo Proveedor
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th class="text-end">Saldo</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($proveedores as $proveedor)
                        <tr>
                            <td>{{ $proveedor->id_proveedor }}</td>
                            <td>
                                <a href="{{ route('proveedores.show', $proveedor->id_proveedor) }}">
                                    {{ $proveedor->nombre_proveedor }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $proveedor->tipo_proveedor }}</span>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-info">
                                    €{{ number_format($proveedor->saldo_proveedor, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('proveedores.show', $proveedor->id_proveedor) }}" 
                                       class="btn btn-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('proveedores.edit', $proveedor->id_proveedor) }}" 
                                       class="btn btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('proveedores.destroy', $proveedor->id_proveedor) }}" 
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
        @if($proveedores->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Mostrando {{ $proveedores->firstItem() }} a {{ $proveedores->lastItem() }} de {{ $proveedores->total() }} registros
                    </div>
                    <div>
                        {{ $proveedores->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
