<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class carrito_transferido extends Model
{
    protected $table='carrito_transferido';
    protected $fillable=[
        'numero_carrito',
        'usuarios_id',
        'cajeros_id',
        'cliente_id',
        'item',
        'id_repuestos',
        'id_local',
        'id_unidad_venta',
        'cantidad',
        'pu',
        'subtotal_item',
        'descuento_item',
        'total_item',
        'titulo',
        'image_path',
        'descripcion_tipo_despacho',
        'url_pdf',
        'tipo_doc',
        'num_doc'
    ];

    public function dame_todo_carrito()
    {
        // OLD $todo_el_carrito=carrito_compra::where('usuarios_id', $c->usuarios_id)->get();
        $todo_el_carrito=carrito_transferido::select('carrito_transferido.*',
                                    'repuestos.codigo_interno',
                                    'repuestos.descripcion',
                                    'repuestos.oferta',
                                    'repuestos.id as idrepuesto',
                                    'users.name',
                                    'clientes.nombres as cliente')
                                    ->where('carrito_transferido.cajeros_id',Auth::user()->id)
                                    ->where('carrito_transferido.numero_carrito','!=',null)
                                    ->join('repuestos','carrito_transferido.id_repuestos','repuestos.id')
                                    ->join('clientes','carrito_transferido.cliente_id','clientes.id')
                                    ->join('users','carrito_transferido.usuarios_id','users.id')
                                    ->distinct()
                                    ->groupBy('carrito_transferido.carrito_id')
                                    ->orderBy('carrito_transferido.numero_carrito','desc')
                                    ->take(600)
                                    ->get();
        //Debugbar::addMessage('carrito_compra->dame_todo_carrito','depurador');
        return $todo_el_carrito;
    }

    public function dame_todo_carrito_cliente($cliente_id)
    {
        // OLD $todo_el_carrito=carrito_compra::where('usuarios_id', $c->usuarios_id)->get();
        $todo_el_carrito=carrito_transferido::select('carrito_transferido.*',
                                    'repuestos.codigo_interno',
                                    'repuestos.oferta',
                                    'repuestos.descripcion',
                                    'marcarepuestos.marcarepuesto as marca',
                                    'paises.nombre_pais as pais',
                                    'ofertas_pagina_web.activo',
                                    'clientes.nombres as cliente',
                                    'locales.local_nombre',
                                    'users.name as vendedor')
                                    ->where('carrito_transferido.cajeros_id',Auth::user()->id)
                                    ->where('carrito_transferido.carrito_id',$cliente_id)
                                    ->where('carrito_transferido.estado',1)
                                    ->leftjoin('repuestos','carrito_transferido.id_repuestos','repuestos.id')
                                    ->leftjoin('ofertas_pagina_web','repuestos.id','ofertas_pagina_web.id_repuesto')
                                    ->leftjoin('marcarepuestos','repuestos.id_marca','marcarepuestos.id')
                                    ->leftjoin('paises','repuestos.id_pais','paises.id')
                                    ->leftjoin('clientes','carrito_transferido.cliente_id','clientes.id')
                                    ->leftjoin('users','carrito_transferido.usuarios_id','users.id')
                                    ->join('locales','carrito_transferido.id_local','locales.id')
                                    ->orderBy('carrito_transferido.item','ASC')
                                    ->distinct()
                                    ->get();
        //Debugbar::addMessage('carrito_compra->dame_todo_carrito','depurador');
        return $todo_el_carrito;
    }

    public function dame_todo_carrito_cliente_vendedor($cliente_id)
    {
        // OLD $todo_el_carrito=carrito_compra::where('usuarios_id', $c->usuarios_id)->get();
        $todo_el_carrito=carrito_transferido::select('carrito_transferido.*',
                                    'repuestos.codigo_interno',
                                    'repuestos.oferta',
                                    'repuestos.descripcion',
                                    'marcarepuestos.marcarepuesto as marca',
                                    'paises.nombre_pais as pais',
                                    'ofertas_pagina_web.activo',
                                    'clientes.nombres as cliente',)
                                    ->where('carrito_transferido.carrito_id',$cliente_id)
                                    ->where('carrito_transferido.estado',1)
                                    ->leftjoin('repuestos','carrito_transferido.id_repuestos','repuestos.id')
                                    ->leftjoin('ofertas_pagina_web','repuestos.id','ofertas_pagina_web.id_repuesto')
                                    ->leftjoin('marcarepuestos','repuestos.id_marca','marcarepuestos.id')
                                    ->leftjoin('paises','repuestos.id_pais','paises.id')
                                    ->leftjoin('clientes','carrito_transferido.cliente_id','clientes.id')
                                    ->orderBy('carrito_transferido.item','ASC')
                                    ->distinct()
                                    ->get();
        //Debugbar::addMessage('carrito_compra->dame_todo_carrito','depurador');
        return $todo_el_carrito;
    }

    public function dame_todo_carrito_cliente_facturado($cliente_id)
    {
        // OLD $todo_el_carrito=carrito_compra::where('usuarios_id', $c->usuarios_id)->get();
        $todo_el_carrito=carrito_transferido::select('carrito_transferido.*',
                                    'repuestos.codigo_interno',
                                    'repuestos.oferta',
                                    'repuestos.descripcion',
                                    'marcarepuestos.marcarepuesto as marca',
                                    'paises.nombre_pais as pais',
                                    'ofertas_pagina_web.activo',
                                    'clientes.nombres as cliente',)
                                    ->where('carrito_transferido.carrito_id',$cliente_id)
                                    ->join('repuestos','carrito_transferido.id_repuestos','repuestos.id')
                                    ->leftjoin('ofertas_pagina_web','repuestos.id','ofertas_pagina_web.id_repuesto')
                                    ->leftjoin('marcarepuestos','repuestos.id_marca','marcarepuestos.id')
                                    ->leftjoin('paises','repuestos.id_pais','paises.id')
                                    ->leftjoin('clientes','carrito_transferido.cliente_id','clientes.id')
                                    ->orderBy('carrito_transferido.item','ASC')
                                    ->distinct()
                                    ->get();
        //Debugbar::addMessage('carrito_compra->dame_todo_carrito','depurador');
        return $todo_el_carrito;
    }

    public function dame_total()
    {
        $total = carrito_transferido::where('cajeros_id', Auth::user()->id)->where('estado',1)->sum('total_item');
        return $total;

    }

    public function dame_total_cliente($cliente_id){


        $total = carrito_transferido::where('cajeros_id', Auth::user()->id)->where('carrito_id',$cliente_id)->where('numero_carrito','!=',null)->where('estado',1)->sum('total_item');
        return $total;
    }

    public function dame_total_cliente_vendedor($cliente_id){


        $total = carrito_transferido::where('carrito_id',$cliente_id)->where('numero_carrito','!=',null)->where('estado',1)->sum('total_item');
        return $total;
    }

    public function dame_total_sin_iva_cliente($cliente_id){
        $total = carrito_transferido::where('cajeros_id', Auth::user()->id)->where('carrito_id',$cliente_id)->where('numero_carrito','!=',null)->where('estado',1)->sum('total_item');
        $total_mas_iva=$total + ($total * Session::get('PARAM_IVA'));
        return $total_mas_iva;

    }

    public function dame_total_sin_iva_cliente_vendedor($cliente_id){
        $total = carrito_transferido::where('carrito_id',$cliente_id)->where('numero_carrito','!=',null)->where('estado',1)->sum('total_item');
        $total_mas_iva=$total + ($total * Session::get('PARAM_IVA'));
        return $total_mas_iva;

    }
}
