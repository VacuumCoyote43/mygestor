<?php

namespace App\Imports;

use App\Models\Jugador;
use App\Models\PagoJugador;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Carbon\Carbon;

class PagosImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    /**
     * Contador de pagos importados e ignorados
     */
    private $importados = 0;
    private $ignorados = 0;
    private $jugadoresNoEncontrados = [];

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Normalizar nombres de columnas (case-insensitive)
        $jugadorNombre = trim($this->getValue($row, ['jugador', 'Jugador', 'JUGADOR', 'nombre', 'Nombre']) ?? '');
        $fecha = $this->getValue($row, ['fecha', 'Fecha', 'FECHA', 'fecha_pago', 'Fecha Pago']);
        $importe = $this->getValue($row, ['importe', 'Importe', 'IMPORTE', 'importe_pago', 'Importe Pago']);
        $concepto = $this->getValue($row, ['concepto', 'Concepto', 'CONCEPTO', 'concepto_pago', 'Concepto Pago']);

        // Validar que existan los campos obligatorios
        if (empty($jugadorNombre) || empty($fecha) || empty($importe)) {
            $this->ignorados++;
            return null;
        }

        // Buscar jugador por nombre o email
        $jugador = Jugador::where('nombre_jugador', 'like', '%' . $jugadorNombre . '%')
            ->orWhere('email_jugador', $jugadorNombre)
            ->first();

        if (!$jugador) {
            $this->ignorados++;
            $this->jugadoresNoEncontrados[] = $jugadorNombre;
            return null; // Ignorar si no se encuentra
        }

        // Procesar fecha
        try {
            $fechaPago = Carbon::parse($fecha);
        } catch (\Exception $e) {
            $this->ignorados++;
            return null;
        }

        // Procesar importe (aceptar formato con coma o punto)
        $importePago = (float) str_replace(',', '.', str_replace(['€', '$', ' '], '', $importe));

        if ($importePago <= 0) {
            $this->ignorados++;
            return null;
        }

        // Normalizar concepto
        $conceptoPago = !empty($concepto) ? trim($concepto) : 'Sin concepto';

        $this->importados++;

        // Crear el pago (el saldo se actualiza automáticamente mediante eventos del modelo)
        return new PagoJugador([
            'id_jugador' => $jugador->id_jugador,
            'fecha_pago' => $fechaPago->format('Y-m-d'),
            'importe_pago' => $importePago,
            'concepto_pago' => $conceptoPago,
        ]);
    }

    /**
     * Obtiene un valor del array intentando diferentes claves (case-insensitive)
     */
    private function getValue(array $row, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($row[$key])) {
                return $row[$key];
            }
        }
        return null;
    }

    /**
     * Reglas de validación para cada fila
     */
    public function rules(): array
    {
        return [
            'jugador' => 'required|string|max:255',
            'fecha' => 'required|date',
            'importe' => 'required|numeric|min:0.01',
            'concepto' => 'nullable|string|max:255',
        ];
    }

    /**
     * Mensajes de validación personalizados
     */
    public function customValidationMessages(): array
    {
        return [
            'jugador.required' => 'El campo jugador es obligatorio.',
            'fecha.required' => 'El campo fecha es obligatorio.',
            'fecha.date' => 'El formato de la fecha no es válido.',
            'importe.required' => 'El campo importe es obligatorio.',
            'importe.numeric' => 'El importe debe ser un número.',
            'importe.min' => 'El importe debe ser mayor a 0.',
        ];
    }

    /**
     * Obtener estadísticas de importación
     */
    public function getImportados(): int
    {
        return $this->importados;
    }

    /**
     * Obtener contador de ignorados
     */
    public function getIgnorados(): int
    {
        return $this->ignorados;
    }

    /**
     * Obtener lista de jugadores no encontrados
     */
    public function getJugadoresNoEncontrados(): array
    {
        return array_unique($this->jugadoresNoEncontrados);
    }
}

