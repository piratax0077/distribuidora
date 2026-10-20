<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cliente_modelo extends Model
{
    protected $table='clientes';
    protected $fillable=[
        'rut',
        'tipo_cliente',
        'nombres',
        'apellidos',
        'empresa',
        'telf1',
        'telf2',
        'email',
        'contacto',
        'contacto_ventas',
        'contacto_compras',
        'telfc',
        'credito',
        'limite',
        'dias',
        'descuento',
        'tipo_descuento',
        'porcentaje',
        'descuento_web',
        'porcentaje_web',
        'veces_buscado',
        'activo',
        'descripcion_activo',
        'usuarios_id'
    ];

    public function cotizacion()
    {
        return $this->hasMany('App\cotizacion');
    }
}
