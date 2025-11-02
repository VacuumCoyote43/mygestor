@extends('layouts.layoutMaster')

@section('title', 'Crear Proveedor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-store me-2"></i>Crear Nuevo Proveedor</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('proveedores.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="nombre_proveedor" class="form-label">Nombre del Proveedor <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nombre_proveedor') is-invalid @enderror" 
                               id="nombre_proveedor" name="nombre_proveedor" value="{{ old('nombre_proveedor') }}" required>
                        @error('nombre_proveedor')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="tipo_proveedor" class="form-label">Tipo de Proveedor <span class="text-danger">*</span></label>
                        <select class="form-select @error('tipo_proveedor') is-invalid @enderror" 
                                id="tipo_proveedor" name="tipo_proveedor" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="ropa" {{ old('tipo_proveedor') == 'ropa' ? 'selected' : '' }}>Ropa</option>
                            <option value="federación" {{ old('tipo_proveedor') == 'federación' ? 'selected' : '' }}>Federación</option>
                            <option value="equipación" {{ old('tipo_proveedor') == 'equipación' ? 'selected' : '' }}>Equipación</option>
                            <option value="seguro" {{ old('tipo_proveedor') == 'seguro' ? 'selected' : '' }}>Seguro</option>
                            <option value="transporte" {{ old('tipo_proveedor') == 'transporte' ? 'selected' : '' }}>Transporte</option>
                            <option value="otro" {{ old('tipo_proveedor') == 'otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                        @error('tipo_proveedor')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
