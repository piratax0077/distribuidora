<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class proveedor_ips extends Model
{
    use HasFactory;
    protected $table='proveedores_ips';
    protected $fillable=[
        'nombre_proveedor',
        'direccion',
        'telefono',
        'correo',
        'contacto',
        'telefono_contacto',
        'correo_contacto',
    ];
}
