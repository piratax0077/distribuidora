<?php

namespace App\Imports;

use App\cliente_modelo;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientesImport implements ToArray, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function array(array $rows)
    {
        $clientes = [];

        foreach($rows as $row)
        {
            $clientes[] = new cliente_modelo([
                'codigo' => $row['codigo'],
                'razon_social' => $row['razon_social'],
                'ac' => $row['ac'],
                'bl' => $row['bl'],
                'rut' => $row['rut'],
                'giro' => $row['giro'],
                'pais' => $row['pais'],
                'region' => $row['region'],
                'ciudad' => $row['ciudad'],
                'comuna' => $row['comuna'],
                'direccion' => $row['direccion'],
                'dpto' => $row['dpto'],
                'codigo_postal' => $row['codigo_postal'],
                'email' => $row['email'],
                'website' => $row['website'],
                'telefono1' => $row['telefono1'],
                'telefono2' => $row['telefono2'],
                'telefono3' => $row['telefono3'],
                'nombre_primer_contacto' => $row['nombre_primer_contacto'],
                'mail_primer_contacto' => $row['mail_primer_contacto'],
                'cargo_primer_contacto' => $row['cargo_primer_contacto'],
                'codicion_venta' => $row['codicion_venta'],
                'monto_autorizado' => $row['monto_autorizado'],
                'vendedor' => $row['vendedor'],
                'cobrador' => $row['cobrador'],
                'email_dte' => $row['email_dte']
            ]);
        }

        return $clientes;
    }
}
