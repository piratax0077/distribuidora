<?php

namespace App\Exports;

use App\cliente_modelo;
use App\cliente_vendedor;
use App\cliente_direcciones;
use App\User;
use App\ciudades;
use App\regiones;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;

class ClientesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $row_number = 0;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Obtener todos los clientes con sus direcciones, regiones, ciudades y vendedores
        $clientes = cliente_modelo::select(
                'clientes.id',
                'clientes.rut',
                'clientes.tipo_cliente',
                'clientes.nombres',
                'clientes.apellidos',
                'clientes.empresa',
                'clientes.telf1',
                'clientes.telf2',
                'clientes.email',
                'clientes.contacto',
                'clientes.contacto_ventas',
                'clientes.contacto_compras',
                'clientes.telfc',
                'clientes.credito',
                'clientes.limite',
                'clientes.dias',
                'clientes.descuento',
                'clientes.tipo_descuento',
                'clientes.porcentaje',
                'clientes.activo',
                'clientes.descripcion_activo',
                'cliente_direcciones.direccion',
                'cliente_direcciones.numero as numero_direccion',
                'ciudades.nombre as ciudad',
                'regiones.nombre as region',
                'regiones.sigla as sigla_region'
            )
            ->leftJoin('cliente_direcciones', 'clientes.id', '=', 'cliente_direcciones.id_cliente')
            ->leftJoin('ciudades', 'cliente_direcciones.id_comuna', '=', 'ciudades.id')
            ->leftJoin('regiones', 'cliente_direcciones.id_region', '=', 'regiones.id')
            ->orderBy('clientes.id')
            ->get();

        // Para cada cliente, obtener sus vendedores
        foreach ($clientes as $cliente) {
            $vendedores = cliente_vendedor::select(
                    'users.name as vendedor_nombre',
                    'users.email as vendedor_email',
                    'users.telefono as vendedor_telefono',
                    'cliente_vendedor.fecha_asignacion',
                    'cliente_vendedor.fecha_desasignacion',
                    'cliente_vendedor.activo as vendedor_activo'
                )
                ->join('users', 'cliente_vendedor.id_vendedor', '=', 'users.id')
                ->where('cliente_vendedor.id_cliente', $cliente->id)
                ->get();

            // Convertir vendedores a string
            $vendedores_nombres = $vendedores->pluck('vendedor_nombre')->implode(', ');
            $vendedores_emails = $vendedores->pluck('vendedor_email')->implode(', ');
            $vendedores_telefonos = $vendedores->pluck('vendedor_telefono')->implode(', ');
            $vendedores_fechas = $vendedores->map(function($v) {
                $estado = $v->vendedor_activo == 1 ? 'Activo' : 'Inactivo';
                $fecha_des = $v->fecha_desasignacion ? ' - Desasignado: ' . $v->fecha_desasignacion : '';
                return 'Asignado: ' . $v->fecha_asignacion . $fecha_des . ' (' . $estado . ')';
            })->implode(' | ');

            // Agregar vendedores al objeto cliente
            $cliente->vendedores_nombres = $vendedores_nombres ?: 'Sin vendedor asignado';
            $cliente->vendedores_emails = $vendedores_emails ?: 'N/A';
            $cliente->vendedores_telefonos = $vendedores_telefonos ?: 'N/A';
            $cliente->vendedores_fechas = $vendedores_fechas ?: 'N/A';
        }

        return $clientes;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'RUT',
            'Tipo Cliente',
            'Nombres',
            'Apellidos',
            'Empresa',
            'Teléfono 1',
            'Teléfono 2',
            'Email',
            'Contacto',
            'Contacto Ventas',
            'Contacto Compras',
            'Teléfono Contacto',
            'Crédito',
            'Límite',
            'Días',
            'Descuento',
            'Tipo Descuento',
            'Porcentaje',
            'Activo',
            'Descripción Estado',
            'Dirección',
            'Número',
            'Ciudad/Comuna',
            'Región',
            'Sigla Región',
            'Vendedores Asignados',
            'Emails Vendedores',
            'Teléfonos Vendedores',
            'Fechas Asignación Vendedores'
        ];
    }

    /**
     * @param mixed $cliente
     * @return array
     */
    public function map($cliente): array
    {
        $this->row_number++;
        
        return [
            $cliente->id,
            $cliente->rut,
            $cliente->tipo_cliente == 0 ? 'Persona Natural' : 'Empresa',
            $cliente->nombres,
            $cliente->apellidos,
            $cliente->empresa,
            $cliente->telf1,
            $cliente->telf2,
            $cliente->email,
            $cliente->contacto,
            $cliente->contacto_ventas,
            $cliente->contacto_compras,
            $cliente->telfc,
            $cliente->credito == 1 ? 'Sí' : 'No',
            $cliente->limite,
            $cliente->dias,
            $cliente->descuento,
            $cliente->tipo_descuento,
            $cliente->porcentaje,
            $cliente->activo == 1 ? 'Activo' : 'Inactivo',
            $cliente->descripcion_activo,
            $cliente->direccion ?: 'Sin dirección',
            $cliente->numero_direccion ?: 'S/N',
            $cliente->ciudad ?: 'Sin ciudad',
            $cliente->region ?: 'Sin región',
            $cliente->sigla_region ?: 'N/A',
            $cliente->vendedores_nombres,
            $cliente->vendedores_emails,
            $cliente->vendedores_telefonos,
            $cliente->vendedores_fechas
        ];
    }
}
