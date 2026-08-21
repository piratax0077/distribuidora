<?php
namespace App\Imports;

use App\repuesto;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RepuestosCambioValorCompra implements ToArray, WithHeadingRow
{
    public function array(array $rows)
    {
        $repuestos = [];

        foreach ($rows as $row)
        {
            $repuestos[] = [
                'codigo_mexic' => $row['codigo_mexic'] ?? '',
                'codigo_fabrica' => $row['codigo_fabrica'] ?? '',
                'codigo_interno' => $row['codigo_interno'] ?? '',
                'descripcion' => $row['descripcion'] ?? '',
                'stk_bodega' => $row['stk_bodega'] ?? 0,
                'stk_preventa' => $row['stk_preventa'] ?? 0,
                'pais' => $row['pais'] ?? '',
                'marca' => $row['marca'] ?? '',
                'precio_compra' => $row['precio_compra'] ?? 0,
                'precio_neto_octubre' => $row['precio_neto_octubre'] ?? 0,
            ];
        }

        return $repuestos;
    }
}
