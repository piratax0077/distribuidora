<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class repuesto extends Model
{
    protected $table='repuestos';
    //FALTA PAIS DE ORIGEN Y STOCK
    protected $fillable=[
        'codigo',
        'codigo_interno',
        'descripcion',
        'observaciones',
        'precio_compra',
        'pu_neto',
        'precio_venta',
        'exento_iva',
        'stock_minimo',
        'stock_maximo',
        'stock_actual',
        'stock_actual_mermas',
        'stock_actual_preventa',
        'codigo_barras',
        'medidas',
        'linea',
        'cu',
        'ft',
        'st',
        'familia',
        'id_marca',
        'id_proveedor',
        'id_pais',
        'usuarios_id',
        'activo',
        'oferta',
        'estado',
        'id_usuario_modifica',
        'id_responsable_stock',
        'fecha_actualizacion_stock',
        'fecha_actualiza_precio'
    ];

  	public function familia()
  	{
  		return $this->belongsTo('App\familia','id_familia');
  	}

    public function marcarepuesto()
    {
    	return $this->belongsTo('App\marcarepuesto','id_marca_repuesto');
    }

    public function proveedor()
    {
    	return $this->belongsTo('App\proveedor','id_proveedor');
    }

    public function pais()
    {
        return $this->belongsTo('App\pais','id_pais');
    }

    public function solicitud()
    {
        return $this->hasMany('App\solicitudes');
    }
}
