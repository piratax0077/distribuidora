<?php
namespace App\Imports;

use App\repuesto;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RepuestosEdit implements ToArray, WithHeadingRow
{
    public function array(array $rows)
    {
        $repuestos = [];

        foreach ($rows as $row)
        {
            $repuestos[] = new repuesto([
                'codigo' => $row['codigo_mexico'],
                'codigo_ips' => $row['codigo_interno'],
                'marca_rep' => $row['marcas'],
                'pais_proc' => $row['nueva_procedencia'],
            ]);

        }

        return $repuestos;
    }
}
