<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas Financieras - {{ date('d/m/Y') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #333;
            color: white;
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background-color: #f0f0f0;
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .totales {
            margin-top: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border-left: 4px solid #007bff;
        }
        .totales h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .totales p {
            margin: 5px 0;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Estadísticas Financieras</h1>
        <p>Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
        <p>Generado el: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Totales -->
    <div class="totales">
        <h3>Resumen del Período</h3>
        <p><strong>Total Ingresos:</strong> €{{ number_format($totalIngresos, 2, ',', '.') }}</p>
        <p><strong>Total Gastos:</strong> €{{ number_format($totalGastos, 2, ',', '.') }}</p>
        <p><strong>Saldo Total:</strong> €{{ number_format($totalIngresos - $totalGastos, 2, ',', '.') }}</p>
    </div>

    <!-- Ingresos por Jugador -->
    @if($ingresosPorJugador->count() > 0)
        <div class="section">
            <div class="section-title">Ingresos por Jugador</div>
            <table>
                <thead>
                    <tr>
                        <th>Jugador</th>
                        <th style="text-align: right;">Total (€)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ingresosPorJugador as $item)
                        <tr>
                            <td>{{ $item->nombre_jugador }}</td>
                            <td style="text-align: right;">€{{ number_format($item->total, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Gastos por Proveedor -->
    @if($gastosPorProveedor->count() > 0)
        <div class="section">
            <div class="section-title">Gastos por Proveedor</div>
            <table>
                <thead>
                    <tr>
                        <th>Proveedor</th>
                        <th style="text-align: right;">Total (€)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gastosPorProveedor as $item)
                        <tr>
                            <td>{{ $item->nombre_proveedor ?: 'Sin proveedor' }}</td>
                            <td style="text-align: right;">€{{ number_format($item->total, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Balance Mensual -->
    @if($balanceMensual->count() > 0)
        <div class="section">
            <div class="section-title">Balance Mensual</div>
            <table>
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th style="text-align: right;">Ingresos (€)</th>
                        <th style="text-align: right;">Gastos (€)</th>
                        <th style="text-align: right;">Balance (€)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($balanceMensual as $mes)
                        <tr>
                            <td>{{ $mes['nombre'] }}</td>
                            <td style="text-align: right;">€{{ number_format($mes['ingresos'], 2, ',', '.') }}</td>
                            <td style="text-align: right;">€{{ number_format($mes['gastos'], 2, ',', '.') }}</td>
                            <td style="text-align: right; color: {{ $mes['balance'] >= 0 ? 'green' : 'red' }};">
                                €{{ number_format($mes['balance'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        <p>Documento generado automáticamente por el sistema de gestión de equipo</p>
    </div>
</body>
</html>

