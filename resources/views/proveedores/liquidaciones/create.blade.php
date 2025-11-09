@extends('layouts.layoutMaster')

@section('title', 'Registrar Liquidación')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Registrar Pago a {{ $proveedor->nombre_proveedor }}</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Saldo del proveedor:</strong> €{{ number_format($proveedor->saldo_proveedor, 2, ',', '.') }}
                </div>

                <form action="{{ route('proveedores.liquidaciones.store', $proveedor->id_proveedor) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="monto" class="form-label">Monto (€) <span class="text-danger">*</span></label>
                        <input type="number" 
                               step="0.01" 
                               min="0.01"
                               class="form-control @error('monto') is-invalid @enderror" 
                               id="monto" 
                               name="monto" 
                               value="{{ old('monto') }}" 
                               required>
                        @error('monto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="fecha_pago" class="form-label">Fecha de Pago <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control @error('fecha_pago') is-invalid @enderror" 
                               id="fecha_pago" 
                               name="fecha_pago" 
                               value="{{ old('fecha_pago', date('Y-m-d')) }}" 
                               required>
                        @error('fecha_pago')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="metodo_pago" class="form-label">Método de Pago</label>
                        <select class="form-select @error('metodo_pago') is-invalid @enderror" 
                                id="metodo_pago" 
                                name="metodo_pago">
                            <option value="">Seleccione un método</option>
                            <option value="Transferencia" {{ old('metodo_pago') == 'Transferencia' ? 'selected' : '' }}>Transferencia</option>
                            <option value="Efectivo" {{ old('metodo_pago') == 'Efectivo' ? 'selected' : '' }}>Efectivo</option>
                            <option value="Bizum" {{ old('metodo_pago') == 'Bizum' ? 'selected' : '' }}>Bizum</option>
                            <option value="Cheque" {{ old('metodo_pago') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                            <option value="Tarjeta" {{ old('metodo_pago') == 'Tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                            <option value="Otro" {{ old('metodo_pago') == 'Otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                        @error('metodo_pago')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nota" class="form-label">Notas</label>
                        <textarea class="form-control @error('nota') is-invalid @enderror" 
                                  id="nota" 
                                  name="nota" 
                                  rows="3" 
                                  placeholder="Información adicional sobre el pago...">{{ old('nota') }}</textarea>
                        @error('nota')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('proveedores.liquidaciones.index', $proveedor->id_proveedor) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Guardar Liquidación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
