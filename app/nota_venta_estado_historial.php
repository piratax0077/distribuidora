<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class nota_venta_estado_historial extends Model
{
    protected $table = 'nota_venta_estado_historial';

    protected $fillable = [
        'nota_venta_seguimiento_id', 'tipo_estado', 'estado_anterior',
        'estado_nuevo', 'observacion', 'usuario_id',
    ];
}
