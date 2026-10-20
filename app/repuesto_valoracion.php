<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class repuesto_valoracion extends Model
{
    protected $table = 'repuesto_valoraciones';

    protected $fillable = ['repuesto_id', 'usuario_id', 'valoracion'];

    protected $casts = ['valoracion' => 'integer'];
}
