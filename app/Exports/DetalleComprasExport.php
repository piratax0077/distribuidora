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

use App\compras_det;

class DetalleComprasExport implements FromQuery, WithTitle, WithHeadings, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    /**
    * @return \Illuminate\Database\Query\Builder
    */

    public function __construct($fechaInicial, $fechaFinal)
    {
        $this->fechaInicial = $fechaInicial;
        $this->fechaFinal = $fechaFinal;
    }

    public function query()
    {
        $detalle = compras_det::query()
            ->selectRaw('
                compras_cab.factura_numero as num_factura,
                compras_cab.factura_fecha as fecha_compra,
                proveedores.empresa_nombre_corto as proveedor,
                repuestos.codigo_interno,
                repuestos.descripcion,
                compras_det.cantidad,
                compras_det.pu as precio_unitario,
                compras_det.subtotal,
                compras_det.costos as flete,
                compras_det.precio_sugerido,
                locales.local_nombre as bodega,
                users.name as usuario
            ')
            ->join('compras_cab', 'compras_cab.id', 'compras_det.id_factura_cab')
            ->join('repuestos', 'compras_det.id_repuestos', 'repuestos.id')
            ->leftJoin('proveedores', 'compras_cab.factura_id_proveedor', 'proveedores.id')
            ->join('locales', 'compras_det.id_local', 'locales.id')
            ->leftJoin('users', 'compras_cab.usuarios_id', 'users.id')
            ->whereRaw('compras_cab.factura_fecha BETWEEN ? AND ?', [$this->fechaInicial, $this->fechaFinal])
            ->where('compras_det.activo', 1)
            ->orderBy('compras_cab.factura_fecha', 'ASC')
            ->orderBy('compras_cab.factura_numero', 'ASC');

        return $detalle;
    }

    public function title(): string
    {
        return 'Detalle compras ' . $this->fechaInicial . ' al ' . $this->fechaFinal;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $cellRange = 'A1:L1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($cellRange)
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('4472C4');

                // Color blanco para el texto del encabezado
                $event->sheet->getDelegate()->getStyle($cellRange)
                        ->getFont()
                        ->getColor()
                        ->setARGB('FFFFFF');

                // Formato para las columnas de dinero
                $lastRow = $event->sheet->getDelegate()->getHighestRow();
                $event->sheet->getDelegate()->getStyle('G2:G'.$lastRow)->getNumberFormat()->setFormatCode('#,##0');
                $event->sheet->getDelegate()->getStyle('H2:H'.$lastRow)->getNumberFormat()->setFormatCode('#,##0');
                $event->sheet->getDelegate()->getStyle('I2:I'.$lastRow)->getNumberFormat()->setFormatCode('#,##0');
                $event->sheet->getDelegate()->getStyle('J2:J'.$lastRow)->getNumberFormat()->setFormatCode('#,##0');

                // Formato para fechas
                $event->sheet->getDelegate()->getStyle('B2:B'.$lastRow)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Precio unitario
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Subtotal
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Flete
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Precio sugerido
            'B' => NumberFormat::FORMAT_DATE_DDMMYYYY, // Fecha
        ];
    }

    public function headings(): array
    {
        return [
            "N° Factura",
            "Fecha Compra",
            "Proveedor",
            "Código Interno",
            "Descripción",
            "Cantidad",
            "Precio Unitario",
            "Subtotal",
            "Flete",
            "Precio Sugerido",
            "Bodega",
            "Usuario"
        ];
    }
}
