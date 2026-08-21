<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use App\factura_detalle;
use App\boleta_detalle;
use App\nota_de_credito_detalle;

class DetalleVentasExport implements FromQuery,WithTitle,WithHeadings,ShouldAutoSize,WithEvents,WithColumnFormatting
{
    /**
    * @return \Illuminate\Database\Query\Builder
    */

    public function __construct($fechaInicial, $fechaFinal, $tipoDte)
    {
        $this->fechaInicial = $fechaInicial;
        $this->fechaFinal = $fechaFinal;
        $this->tipoDte = $tipoDte;
    }

    public function query()
    {
        if($this->tipoDte == 33){
            $detalle = factura_detalle::query()->selectRaw('facturas.num_factura as num_doc, "factura" as tipo_doc, facturas.fecha_emision, repuestos.codigo_interno, facturas_detalle.cantidad')
            ->join('facturas', 'facturas.id', 'facturas_detalle.id_factura')
            ->join('repuestos', 'facturas_detalle.id_repuestos', 'repuestos.id')
            ->whereRaw('facturas.fecha_emision BETWEEN ? AND ?', [$this->fechaInicial, $this->fechaFinal])
            ->orderBy('facturas.num_factura', 'ASC');

        }
        if($this->tipoDte == 39){
            $detalle = boleta_detalle::query()->selectRaw('boletas.num_boleta as num_doc, "boleta" as tipo_doc, boletas.fecha_emision, repuestos.codigo_interno, boletas_detalle.cantidad')
            ->join('boletas', 'boletas.id', 'boletas_detalle.id_boleta')
            ->join('repuestos', 'boletas_detalle.id_repuestos', 'repuestos.id')
            ->whereRaw('boletas.fecha_emision BETWEEN ? AND ?', [$this->fechaInicial, $this->fechaFinal])
            ->orderBy('boletas.num_boleta', 'ASC');
        }
        if($this->tipoDte == 61){
            $detalle = nota_de_credito_detalle::query()->selectRaw('notas_de_credito.num_nota_credito as num_doc, "Nota de credito" as tipo_doc, notas_de_credito.fecha_emision, repuestos.codigo_interno, notas_de_credito_detalle.cantidad')
            ->join('notas_de_credito', 'notas_de_credito.id', 'notas_de_credito_detalle.id_nota_de_credito')
            ->join('repuestos','notas_de_credito_detalle.id_repuestos','repuestos.id')
            ->whereRaw('notas_de_credito.fecha_emision BETWEEN ? AND ?', [$this->fechaInicial, $this->fechaFinal])
            ->orderBy('notas_de_credito.num_nota_credito', 'ASC');
        }
        return $detalle;
    }

    public function title(): string
    {
        return 'Detalle ventas ' . $this->fechaInicial . ' al ' . $this->fechaFinal;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:E1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(16);
                $event->sheet->getDelegate()->getStyle($cellRange)
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('DD4B39');

            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            //'D' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
    }

    public function headings(): array
    {
        return ["N° Doc","Tipo documento","Fecha emisión","Codigo interno","Cantidad"];
    }
}
