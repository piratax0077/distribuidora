<?php

namespace App\Helpers;

class ExcelValidator
{
    /**
     * Valida una fila de datos de Excel según los campos requeridos y opcionales.
     *
     * @param array $fila La fila de datos (índice => valor).
     * @param array $camposRequeridos Lista de campos obligatorios.
     * @param int|null $index Para mensajes con número de fila.
     * @return array ['valid' => true/false, 'errores' => [...]]
     */
    public static function validarFila(array $fila, array $camposRequeridos = [], int $index = null)
    {
        $errores = [];

        foreach ($camposRequeridos as $campo) {
            if (!isset($fila[$campo]) || trim($fila[$campo]) === '') {
                $errores[] = "Campo '{$campo}' vacío";
            }
        }

        // Ejemplo de validación por tipo de dato
        if (isset($fila['precio_neto']) && !is_numeric($fila['precio_neto'])) {
            $errores[] = "El campo 'precio_neto' debe ser numérico";
        }

        if (isset($fila['stock']) && !is_numeric($fila['stock'])) {
            $errores[] = "El campo 'stock' debe ser numérico";
        }

        $mensaje = [];
        if (count($errores) > 0) {
            $filaNum = $index !== null ? "Fila " . ($index + 2) . ": " : '';
            $mensaje[] = $filaNum . implode(', ', $errores);
        }

        return [
            'valid' => count($errores) === 0,
            'errores' => $mensaje
        ];
    }
}
