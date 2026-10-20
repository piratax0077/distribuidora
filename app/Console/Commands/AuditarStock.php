<?php

namespace App\Console\Commands;

use App\repuesto;
use App\saldo;
use App\Services\StockService;
use Illuminate\Console\Command;

class AuditarStock extends Command
{
    protected $signature = 'stock:auditar {--codigo=} {--limite=200}';

    protected $description = 'Compara el stock físico de repuestos con la tabla saldos sin modificar datos';

    public function handle(StockService $stockService): int
    {
        $query = repuesto::where('activo', 1)->orderBy('id');

        if ($codigo = trim((string) $this->option('codigo'))) {
            $query->where('codigo_interno', $codigo);
        }

        $diferencias = [];
        $limite = max(1, (int) $this->option('limite'));

        $query->chunkById(500, function ($repuestos) use ($stockService, &$diferencias, $limite) {
            foreach ($repuestos as $item) {
                $locales = array_unique(array_filter([
                    (int) $item->id_local,
                    (int) $item->id_local_dos,
                    (int) $item->id_local_tres,
                ]));

                foreach ($locales as $localId) {
                    $columna = $stockService->columnForLocal($item, $localId);
                    $stockRepuesto = (int) $item->{$columna};
                    $stockSaldo = (int) saldo::where('id_repuestos', $item->id)
                        ->where('id_local', $localId)
                        ->sum('saldo');

                    if ($stockRepuesto !== $stockSaldo) {
                        $diferencias[] = [
                            $item->codigo_interno,
                            $localId,
                            $columna,
                            $stockRepuesto,
                            $stockSaldo,
                            $stockRepuesto - $stockSaldo,
                        ];
                    }

                    if (count($diferencias) >= $limite) {
                        return false;
                    }
                }
            }
        });

        if (!$diferencias) {
            $this->info('No se encontraron diferencias en el alcance revisado.');
            return 0;
        }

        $this->table(
            ['Código', 'Local', 'Columna', 'Repuestos', 'Saldos', 'Diferencia'],
            $diferencias
        );
        $this->warn('Auditoría de solo lectura: no se modificó ningún stock.');

        return 1;
    }
}
