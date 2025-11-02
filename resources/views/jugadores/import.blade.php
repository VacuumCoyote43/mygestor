@extends('layouts.layoutMaster')

@section('title', 'Importar Jugadores')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0">
                        <i class="fas fa-file-import me-2"></i>Importar Jugadores desde Excel
                    </h2>
                    <p class="text-muted">Sube un archivo .xlsx o .csv con las columnas requeridas</p>
                </div>
                <a href="{{ route('jugadores.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Instrucciones:</strong>
                        <ul class="mb-0 mt-2">
                            <li>El archivo debe contener una primera fila con encabezados: <strong>nombre, dni, fecha_nacimiento, dorsal, talla_camiseta, talla_pantalon, talla_medias, saldo</strong></li>
                            <li>La columna <strong>nombre</strong> es obligatoria</li>
                            <li>Las demás columnas son opcionales</li>
                            <li>El formato de fecha debe ser: <strong>YYYY-MM-DD</strong> (ej: 1990-05-15)</li>
                            <li>El formato de saldo debe ser numérico (ej: 25.5, -10, 0)</li>
                            <li>Tamaño máximo del archivo: 2MB</li>
                        </ul>
                        <div class="mt-3">
                            <a href="{{ route('jugadores.import.template') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-download me-1"></i>Descargar Molde Excel
                            </a>
                            <small class="text-muted ms-2">Descarga un archivo de ejemplo con el formato correcto</small>
                        </div>
                    </div>

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mt-3">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (isset($failures) && count($failures) > 0)
                        <div class="alert alert-warning mt-3">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Errores encontrados:</h5>
                            <ul class="mb-0">
                                @foreach ($failures as $failure)
                                    <li>
                                        <strong>Fila {{ $failure->row() }}:</strong> 
                                        {{ implode(', ', $failure->errors()) }}
                                        @if($failure->values())
                                            <br><small class="text-muted">Valores: {{ json_encode($failure->values()) }}</small>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (isset($error_details))
                        <div class="alert alert-danger mt-3">
                            <h5><i class="fas fa-times-circle me-2"></i>Detalles del error:</h5>
                            <ul class="mb-0">
                                @foreach ($error_details as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('jugadores.import.store') }}" enctype="multipart/form-data" class="mt-4">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">
                                <i class="fas fa-file-excel me-1"></i>Seleccionar archivo Excel
                            </label>
                            <input type="file" 
                                   name="file" 
                                   id="file" 
                                   class="form-control @error('file') is-invalid @enderror" 
                                   accept=".xlsx,.csv"
                                   required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Formatos permitidos: .xlsx, .csv
                            </small>
                        </div>

                        <div id="preview-container" class="mt-3" style="display: none;">
                            <h5>Vista previa del archivo:</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="preview-table">
                                    <thead></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <small class="text-muted">Solo se muestran las primeras 5 filas como vista previa.</small>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('jugadores.index') }}'">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="fas fa-upload me-1"></i>Importar Jugadores
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>Ejemplo de formato Excel
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>nombre</th>
                                    <th>dni</th>
                                    <th>fecha_nacimiento</th>
                                    <th>dorsal</th>
                                    <th>talla_camiseta</th>
                                    <th>talla_pantalon</th>
                                    <th>talla_medias</th>
                                    <th>saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Juan Pérez</td>
                                    <td>12345678A</td>
                                    <td>1990-05-15</td>
                                    <td>10</td>
                                    <td>M</td>
                                    <td>M</td>
                                    <td>M</td>
                                    <td>0</td>
                                </tr>
                                <tr>
                                    <td>Pedro García</td>
                                    <td>87654321B</td>
                                    <td>1992-08-20</td>
                                    <td>7</td>
                                    <td>L</td>
                                    <td>L</td>
                                    <td>L</td>
                                    <td>25.5</td>
                                </tr>
                                <tr>
                                    <td>Luis Martínez</td>
                                    <td>11223344C</td>
                                    <td>1995-03-10</td>
                                    <td>9</td>
                                    <td>S</td>
                                    <td>S</td>
                                    <td>S</td>
                                    <td>-10</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('page-script')
<script>
$(document).ready(function() {
    const fileInput = $('#file');
    const previewContainer = $('#preview-container');
    const previewTable = $('#preview-table');
    
    // Vista previa del archivo (solo para CSV)
    fileInput.on('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Solo mostrar vista previa para CSV
        if (file.name.endsWith('.csv')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const text = e.target.result;
                const lines = text.split('\n').filter(line => line.trim());
                
                if (lines.length > 0) {
                    // Parsear CSV básico
                    const rows = [];
                    lines.slice(0, 6).forEach(line => {
                        // Separar por comas, pero respetar comillas
                        const columns = line.split(',').map(col => col.trim().replace(/^"|"$/g, ''));
                        rows.push(columns);
                    });

                    if (rows.length > 0) {
                        // Crear encabezados
                        let thead = '<thead class="table-light"><tr>';
                        rows[0].forEach(header => {
                            thead += `<th>${header}</th>`;
                        });
                        thead += '</tr></thead>';
                        previewTable.html(thead);

                        // Crear filas
                        let tbody = '<tbody>';
                        rows.slice(1, 6).forEach(row => {
                            tbody += '<tr>';
                            row.forEach(cell => {
                                tbody += `<td>${cell || '-'}</td>`;
                            });
                            tbody += '</tr>';
                        });
                        tbody += '</tbody>';
                        previewTable.append(tbody);

                        previewContainer.show();
                    }
                }
            };
            reader.readAsText(file);
        } else {
            previewContainer.hide();
        }
    });

    // Deshabilitar botón de envío mientras se procesa
    $('form').on('submit', function() {
        const submitBtn = $('#submit-btn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Importando...');
    });
});
</script>
@endpush
@endsection
