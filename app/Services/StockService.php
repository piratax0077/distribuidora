<?php

namespace App\Services;

use App\repuesto;
use App\saldo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Resuelve la columna física asociada a un local usando la configuración
     * del repuesto. Esta es la única regla que deben usar ventas y devoluciones.
     */
    public function columnForLocal(repuesto $repuesto, int $localId): string
    {
        if ((int) $repuesto->id_local === $localId) {
            return 'stock_actual';
        }

        if ((int) $repuesto->id_local_dos === $localId) {
            return 'stock_actual_mermas';
        }

        if ((int) $repuesto->id_local_tres === $localId) {
            return 'stock_actual_preventa';
        }

        throw ValidationException::withMessages([
            'local_id' => "El local {$localId} no está configurado como ubicación del repuesto {$repuesto->codigo_interno}.",
        ]);
    }

    /**
     * Ajusta stock sobre una fila previamente bloqueada o bloqueándola aquí.
     * Debe ejecutarse dentro de una transacción cuando forma parte de un DTE.
     */
    public function adjust(int $repuestoId, int $localId, int $delta): repuesto
    {
        return DB::transaction(function () use ($repuestoId, $localId, $delta) {
            $repuesto = repuesto::where('id', $repuestoId)
                ->where('activo', 1)
                ->lockForUpdate()
                ->first();

            if (!$repuesto) {
                throw ValidationException::withMessages([
                    'repuesto_id' => 'El repuesto no existe o está inactivo.',
                ]);
            }

            $column = $this->columnForLocal($repuesto, $localId);
            $nuevoStock = (int) $repuesto->{$column} + $delta;

            if ($nuevoStock < 0) {
                throw ValidationException::withMessages([
                    'cantidad' => "Stock insuficiente para {$repuesto->codigo_interno} en el local seleccionado.",
                ]);
            }

            $repuesto->{$column} = $nuevoStock;
            $repuesto->save();

        // Mantener la tabla histórica de saldos alineada mientras siga siendo
        // consumida por reportes y módulos antiguos.
            $saldo = saldo::where('id_repuestos', $repuestoId)
                ->where('id_local', $localId)
                ->lockForUpdate()
                ->first();

            if ($saldo) {
                // La columna física del repuesto es la fuente operativa actual.
                // Al tocar el producto se corrige su espejo por local.
                $saldo->saldo = $nuevoStock;
                $saldo->save();
            } else {
                saldo::create([
                    'id_repuestos' => $repuestoId,
                    'id_local' => $localId,
                    'saldo' => $nuevoStock,
                    'activo' => 1,
                    'usuarios_id' => auth()->id(),
                ]);
            }

            return $repuesto;
        });
    }
}
