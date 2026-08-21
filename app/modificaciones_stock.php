<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class modificaciones_stock extends Model
{
    use HasFactory;
    protected $table = 'modificaciones_stock';
    protected $fillable=[
        'id_repuesto',
        'fecha_modificacion',
        'stock',
        'stock_anterior',
        'id_responsable_stock',
    ];
}
