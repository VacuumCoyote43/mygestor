@extends('layouts.layoutMaster')

@section('title', 'Gestión de Base de Datos')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-database me-2"></i>Gestión de Base de Datos
                    </h1>
                    <p class="text-muted">Exportar e importar copias de seguridad de la base de datos</p>
                </div>
                <a href="{{ route('admin.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3">
        <!-- Exportar Base de Datos -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-download me-2"></i>Exportar Base de Datos
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Crea una copia de seguridad completa de la base de datos actual.
                        El archivo se guardará en formato SQL.
                    </p>
                    <form method="POST" action="{{ route('admin.database.export') }}">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-file-export me-1"></i>Exportar Base de Datos
                        </button>
                    </form>
                    @if(session('exported_file'))
                        <div class="mt-3">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Última exportación:</strong> {{ session('exported_file') }}
                            </div>
                            <a href="{{ route('admin.database.download', session('exported_file')) }}" 
                               class="btn btn-outline-primary w-100">
                                <i class="fas fa-download me-1"></i>Descargar Última Exportación
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Importar Base de Datos -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-upload me-2"></i>Importar Base de Datos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Advertencia:</strong> La importación reemplazará todos los datos actuales. 
                        Se recomienda hacer una exportación antes de importar.
                    </div>
                    <form method="POST" action="{{ route('admin.database.import') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">Seleccionar archivo SQL o DB</label>
                            <input type="file" 
                                   class="form-control @error('file') is-invalid @enderror" 
                                   id="file" 
                                   name="file" 
                                   accept=".sql,.db" 
                                   required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Tamaño máximo: 50 MB. Formatos soportados: .sql, .db
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" onclick="return confirm('¿Está seguro de importar esta base de datos? Esto reemplazará todos los datos actuales.')">
                            <i class="fas fa-file-import me-1"></i>Importar Base de Datos
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Exportaciones -->
    @if(isset($exports) && $exports->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Exportaciones Guardadas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre del Archivo</th>
                                    <th>Tamaño</th>
                                    <th>Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($exports as $export)
                                <tr>
                                    <td>
                                        <i class="fas fa-file-archive me-2"></i>
                                        {{ $export['name'] }}
                                    </td>
                                    <td>{{ number_format($export['size'] / 1024, 2) }} KB</td>
                                    <td>{{ date('d/m/Y H:i:s', $export['date']) }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.database.download', $export['name']) }}" 
                                               class="btn btn-info" title="Descargar">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <form action="{{ route('admin.database.delete', $export['name']) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('¿Está seguro de eliminar este archivo?')">
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
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
