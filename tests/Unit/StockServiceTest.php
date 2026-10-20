<?php

namespace Tests\Unit;

use App\repuesto;
use App\Services\StockService;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class StockServiceTest extends TestCase
{
    private function repuestoConfigurado(): repuesto
    {
        $repuesto = new repuesto();
        $repuesto->codigo_interno = 'TEST-001';
        $repuesto->id_local = 1;
        $repuesto->id_local_dos = 3;
        $repuesto->id_local_tres = 4;

        return $repuesto;
    }

    public function test_resuelve_la_columna_de_cada_local_configurado(): void
    {
        $service = new StockService();
        $repuesto = $this->repuestoConfigurado();

        $this->assertSame('stock_actual', $service->columnForLocal($repuesto, 1));
        $this->assertSame('stock_actual_mermas', $service->columnForLocal($repuesto, 3));
        $this->assertSame('stock_actual_preventa', $service->columnForLocal($repuesto, 4));
    }

    public function test_rechaza_un_local_no_configurado_en_el_repuesto(): void
    {
        $this->expectException(ValidationException::class);

        (new StockService())->columnForLocal($this->repuestoConfigurado(), 99);
    }
}
