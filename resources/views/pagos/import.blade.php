@extends('layouts.layoutMaster')

@section('title', 'Importar Pagos')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0">
                        <i class="fas fa-file-import me-2"></i>Importar Pagos desde Excel
                    </h2>
                    <p class="text-muted">Sube un archivo .xlsx o .csv con las columnas requeridas</p>
                </div>
                <a href="{{ route('pagos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Instrucciones:</strong>
                        <ul class="mb-0 mt-2">
                            <li>El archivo debe contener una primera fila con encabezados: <strong>jugador, fecha, importe, concepto</strong></li>
                            <li>Las columnas <strong>jugador, fecha e importe</strong> son obligatorias</li>
                            <li>La columna <strong>concepto</strong> es opcional</li>
                            <li>El campo <strong>jugador</strong> puede ser el nombre completo o el email del jugador</li>
                            <li>El formato de fecha puede ser: YYYY-MM-DD, DD/MM/YYYY, DD-MM-YYYY</li>
                            <li>El importe debe ser numérico (ej: 30.00, 25.5, 100)</li>
                            <li>Tamaño máximo del archivo: 2MB</li>
                            <li><strong>Importante:</strong> Los saldos de los jugadores se actualizarán automáticamente</li>
                        </ul>
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

                    @if (session('estadisticas'))
                        <div class="alert alert-success mt-3">
                            <h5><i class="fas fa-chart-bar me-2"></i>Estadísticas de importación:</h5>
                            <ul class="mb-0">
                                <li><strong>Pagos importados:</strong> {{ session('estadisticas')['importados'] }}</li>
                                <li><strong>Filas ignoradas:</strong> {{ session('estadisticas')['ignorados'] }}</li>
                            </ul>
                        </div>
                    @endif

                    @if (isset($jugadores_no_encontrados) && count($jugadores_no_encontrados) > 0)
                        <div class="alert alert-warning mt-3">
                            <h5><i class="fas fa-user-times me-2"></i>Jugadores no encontrados:</h5>
                            <ul class="mb-0">
                                @foreach (array_slice($jugadores_no_encontrados, 0, 10) as $jugador)
                                    <li>{{ $jugador }}</li>
                                @endforeach
                                @if (count($jugadores_no_encontrados) > 10)
                                    <li><em>... y {{ count($jugadores_no_encontrados) - 10 }} más</em></li>
                                @endif
                            </ul>
                            <small class="text-muted">Las filas con estos jugadores fueron ignoradas.</small>
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

                    <form method="POST" action="{{ route('pagos.import.store') }}" enctype="multipart/form-data" class="mt-4">
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
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('pagos.index') }}'">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="fas fa-upload me-1"></i>Importar Pagos
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
                                    <th>jugador</th>
                                    <th>fecha</th>
                                    <th>importe</th>
                                    <th>concepto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Juan Pérez</td>
                                    <td>2025-01-15</td>
                                    <td>30.00</td>
                                    <td>Cuota mensual</td>
                                </tr>
                                <tr>
                                    <td>Pedro García</td>
                                    <td>2025-01-15</td>
                                    <td>25.50</td>
                                    <td>Equipación</td>
                                </tr>
                                <tr>
                                    <td>Luis Martínez</td>
                                    <td>2025-01-16</td>
                                    <td>20</td>
                                    <td>Seguro anual</td>
                                </tr>
                                <tr>
                                    <td>juan@mail.com</td>
                                    <td>15/01/2025</td>
                                    <td>15.00</td>
                                    <td>Material deportivo</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-secondary mt-3 mb-0">
                        <small>
                            <strong>Nota:</strong> El campo <strong>jugador</strong> puede ser el nombre completo del jugador o su email. 
                            Si el jugador no se encuentra en la base de datos, esa fila será ignorada.
                        </small>
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
