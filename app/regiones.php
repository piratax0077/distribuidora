<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class regiones extends Model
{
    use HasFactory;
    protected $table = 'regiones';
    protected $fillable = ['nombre', 'sigla'];
}
