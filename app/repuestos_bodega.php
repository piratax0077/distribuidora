<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class repuestos_bodega extends Model
{
    use HasFactory;
    protected $table='repuestos_bodega';
    protected $fillable=[
        'id',
        'id_repuesto',
        'id_bodega_origen',
        'id_bodega_destino',
        'stock',
        'created_at',
        'updated_at'
    ];
}
