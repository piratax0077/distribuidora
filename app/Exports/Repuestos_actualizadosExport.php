<?php

namespace App\Exports;

use App\repuesto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Repuestos_actualizadosExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $repuestos;

    public function __construct($repuestos)
    {
        $this->repuestos = $repuestos;
    }

    public function collection()
    {
        return $this->repuestos->map(function($repuesto) {
            $repuesto->stock_actual = (string) $repuesto->stock_actual; // Convertir a cadena de texto
            $repuesto->stock_actual_preventa = (string) $repuesto->stock_actual_preventa; // Convertir a cadena de texto
            return $repuesto;
        });
    }

    public function headings(): array
    {
        return [
            'Codigo Mexico',
            'Codigo fabrica',
            'Codigo interno',
            'Descripción',
            'Stk. Bodega',
            'Stk. Preventa',
            'Precio Neto',
            'Precio Compra',
            'Pais',
            'Marca'
            // Agrega más columnas según tus necesidades
        ];
    }
}
