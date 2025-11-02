<?php

namespace App\Imports;

use App\Models\Jugador;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use Carbon\Carbon;

class JugadoresImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Limpiar y validar datos, normalizar nombres de columnas (case-insensitive)
        $nombre = trim($this->getValue($row, ['nombre', 'Nombre', 'NOMBRE', 'nombre_jugador']) ?? '');
        $dni = $this->getValue($row, ['dni', 'DNI', 'Dni']);
        $fechaNacimiento = $this->getValue($row, ['fecha_nacimiento', 'Fecha de nacimiento', 'fecha nacimiento', 'fecha_nac', 'Fecha nacimiento']);
        $dorsal = $this->getValue($row, ['dorsal', 'Dorsal', 'DORSAL']);
        $tallaCamiseta = $this->getValue($row, ['talla_camiseta', 'Talla camiseta', 'talla camiseta', 'Talla Camiseta']);
        $tallaPantalon = $this->getValue($row, ['talla_pantalon', 'Talla pantalón', 'talla pantalon', 'Talla Pantalon']);
        $tallaMedias = $this->getValue($row, ['talla_medias', 'Talla medias', 'talla medias', 'Talla Medias']);
        $saldo = $this->getValue($row, ['saldo', 'Saldo', 'SALDO', 'saldo_jugador']);

        // Normalizar valores
        $dni = !empty($dni) ? trim($dni) : null;
        $saldo = isset($saldo) && $saldo !== '' ? (float) str_replace(',', '.', $saldo) : 0;

        // Procesar fecha de nacimiento
        $fechaNacimientoParsed = null;
        if (!empty($fechaNacimiento)) {
            try {
                $fechaNacimientoParsed = Carbon::parse($fechaNacimiento)->format('Y-m-d');
            } catch (\Exception $e) {
                $fechaNacimientoParsed = null;
            }
        }

        // Si no hay nombre, usar 'Sin nombre'
        if (empty($nombre)) {
            $nombre = 'Sin nombre';
        }

        // Preparar datos para updateOrCreate
        $data = [
            'nombre_jugador' => $nombre,
            'dni' => $dni, // Incluir DNI en los datos
            'fecha_nacimiento' => $fechaNacimientoParsed,
            'dorsal' => !empty($dorsal) ? (int) $dorsal : null,
            'talla_camiseta' => !empty($tallaCamiseta) ? trim($tallaCamiseta) : null,
            'talla_pantalon' => !empty($tallaPantalon) ? trim($tallaPantalon) : null,
            'talla_medias' => !empty($tallaMedias) ? trim($tallaMedias) : null,
            'mode' => 'importado',
            'saldo_jugador' => $saldo,
        ];

        // Si el jugador ya existe (por DNI), se actualiza. Si no, se crea uno nuevo.
        // Solo actualizar si hay DNI, si no crear nuevo sin DNI
        if (!empty($dni)) {
            $jugador = Jugador::updateOrCreate(
                ['dni' => $dni],
                $data
            );
        } else {
            // Si no hay DNI, crear nuevo jugador
            $jugador = new Jugador($data);
            $jugador->save();
        }

        return $jugador;
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
            'nombre' => 'required|string|max:255',
            'saldo' => 'nullable|numeric',
        ];
    }

    /**
     * Mensajes de validación personalizados
     */
    public function customValidationMessages(): array
    {
        return [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.string' => 'El campo nombre debe ser texto.',
            'saldo.numeric' => 'El saldo debe ser un número.',
        ];
    }
}

