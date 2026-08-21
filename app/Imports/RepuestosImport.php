<?php
namespace App\Imports;

use App\repuesto;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RepuestosImport implements ToArray, WithHeadingRow
{
    public function array(array $rows)
    {
        $repuestos = [];

        foreach ($rows as $row)
        {
            $repuestos[] = new repuesto([
                'codigo' => $row['codigo'],
                'codigo_ips' => $row['codigo_ips'],
                'codigo' => $row['codigo'],
                'foto' => $row['foto'],
                'descrip' => $row['descrip'],
                'descri_sof' => $row['descri_sof'],
                'cu' => $row['cu'],
                'ft' => $row['ft'],
                'st' => $row['st'],
                'linea' => $row['linea'],
                'cif' => $row['cif'],
                'costo' => $row['costo'],
                'precio' => $row['precio_neto'],
                'marca_rep' => $row['marca_rep'],
                'pais_proc' => $row['pais_proc'],
                'proveedor' => $row['proveedor'],
                'stock' => $row['stock'],
                'codigo_de_barra' => $row['codigo_de_barra'],
            ]);
        }

        return $repuestos;
    }
}
