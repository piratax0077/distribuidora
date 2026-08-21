<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\cliente_vendedor;
use App\carrito_transferido;
use App\compras_det;
use App\compras_cab;
use App\boleta;
use App\boleta_detalle;
use App\factura;
use App\factura_detalle;
use App\local;
use App\modificaciones_stock;
use App\repuesto;
use App\solicitudes;
use App\pais;
use App\permissions_detail;
use App\devolucion_mercaderia;
use App\ticket;
use App\vale_mercaderia_detalle;
use App\traspaso_mercaderia_detalle;
use App\repuestofoto;
use Carbon\Carbon; // para tratamiento de fechas
use Illuminate\Support\Facades\Auth;

class inventario_controlador extends Controller
{
    //
    public function index(){
        try {
            $locales=local::where('activo',1)->get();

            if(count($locales)>0){
                return view('inventario.principal',['locales' => $locales]);
            }else{
                return "cero";
            }
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }


    }

    public function inventario_por_local($id){
        /* 1 == Bodega
           3 == Tienda

            id_familia = 312 ES FAMILIA SI DEFINIR
           */
          $id_familia_sin_definir = 312;

        try{
            $repuestos = repuesto::where('stock_actual','>',0)->where('activo',1)->get();

            $local_id = $id;
            // foreach($repuestos as $repuesto){
            //     $repuesto->precio_venta = $repuesto->precio_venta * $repuesto->stock_actual;
            // }
            $v = view('fragm.inventario_por_bodega',compact('repuestos','local_id'))->render();
            return $v;
        }catch(Exception $e){
            return $e->getMessage();
        }

    }

    public function traslado(Request $request){
        $repuesto_id= $request->id_repuesto;
        $cantidad = $request->cantidad;
        try {
            $nueva_solicitud = new solicitudes;
            $nueva_solicitud->id_repuestos = intval($repuesto_id);
            $nueva_solicitud->usuario_id = Auth::user()->id;
            $nueva_solicitud->cantidad = intval($cantidad);
            $nueva_solicitud->activo = 1;
            $nueva_solicitud->save();
            return 'OK';
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function ticket(){
        $tickets = ticket::select('ticket.*','users.name')->join('users','ticket.usuario_id','users.id')->orderBy('ticket.id','desc')->get();
        $completados = 0;
        $ingresados = 0;
        $en_proceso = 0;
        foreach($tickets as $t){
            if($t->estado == 3){
                $completados++;
            }elseif($t->estado == 1){
                $en_proceso++;
            }else{
                $ingresados++;
            }
        }
        return view('inventario.ticket',[
            'tickets' => $tickets,
            'completados' => $completados,
            'ingresados' => $ingresados,
            'en_proceso' => $en_proceso
        ]);
    }

    public function dameticket($id){
        $ticket = ticket::find($id)->toJson();
        return $ticket;
    }

    public function guardar_ticket(Request $r){

        //Reglas de Validación
        $reglas=[
            'descripcion'=>'required|max:600',
            'imagen' => 'required'
        ];

        // Validación de imagen



        if($r->foto==1)
        {
            $archivo=$r->file('imagen');
            if(is_null($archivo))
            {
                return response()->json(["ERROR"=>"No ha elegido imagen..."],500);
            }else{
                if(filesize($archivo)>500000)
                {
                    return response()->json(["ERROR"=>"Imagen muy grande..."],500);
                }else{
                    $tipos=['jpg','jpeg','png'];
                    $m=substr($archivo->getClientMimeType(),6); // viene así: image/jpeg, image/png
                    //return response()->json(["ERROR"=>$m],500);
                    if(!in_array($m,$tipos))
                        return response()->json(["ERROR"=>"Elija tipo de imagen JPG o PNG"],500);
                }

            }
        }

        //Mensajes de error para validación
        $mensajes=[
            'descripcion.required'=>'Debe Ingresar una descripción para el ticket',
            'descripcion.max'=>'El nombre del parámetro debe tener como máximo 600 caracteres.',
        ];

        //

        //$this->validate($r,$reglas,$mensajes); //Validación

        try
        {
        if($r->modifika==0) //nuevo
        {
            $t=new ticket;
            $t->usuario_id=Auth::user()->id;
            $t->descripcion=$r->descripcion;
            $t->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha de hoy
            $t->activo = 1;
            $t->estado = $r->estado;
            if($r->foto==0)
            {
                $t->image_path=$r->valor;
            }else{
                $t->image_path=$archivo->store('fotozzz','public');
                $ruta_origen = "C:/xampp/htdocs/repuestos/storage/app/public/".$t->image_path;
                $ruta_destino = "C:/xampp/htdocs/repuestos/public_original/storage/".$t->image_path;
                copy($ruta_origen, $ruta_destino);
            }

        }else{ //Modificación
            $t=ticket::find($r->ide);
            return $t;
            $t->usuario_id=Auth::user()->id;
            $t->descripcion=$r->descripcion;
            $t->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha de hoy
            $t->activo = 1;
            $t->estado = $r->estado;
            if($r->foto==0)
            {
                $t->image_path=$r->valor;
            }else{
                if(!is_null($archivo))
                    {
                        $t->image_path=$archivo->store('fotozzz','public');
                        $ruta_origen = "C:/xampp/htdocs/repuestos/storage/app/public/".$t->image_path;
                        $ruta_destino = "C:/xampp/htdocs/repuestos/public_original/storage/".$t->image_path;
                        copy($ruta_origen, $ruta_destino);
                    }
                }


            }
            $t->save();

            return "OK";
        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }
    }

    public function damerepuesto($id){
        try {
            //code...
            $repuesto=repuesto::where('repuestos.id',$id)
                    ->join('proveedores','repuestos.id_proveedor','proveedores.id')
                    ->select('proveedores.empresa_nombre','repuestos.*')
                    ->get();

            return $repuesto;
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
        // $repuesto = repuesto::where('id', $id)
        // ->get();


    }

    public function eliminarticket($id){
        $ticket = ticket::find($id);
        $ticket->delete();
        return redirect('/ticket');
    }



    public function pedidos_a_bodega_vista(){
        try {
            $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 4 && $permiso_detalle->usuarios_id == Auth::user()->id && $permiso->path_ruta == '/solicitudes'){
                    $solicitudes = devolucion_mercaderia::where('activo',1)->orderBy('created_at','desc')->groupBy('num_nc')->get();

                    return view('inventario.pedidos_a_bodega',['solicitudes' => $solicitudes]);
                    }
            }
            $user = Auth::user();
            if($user->rol->nombrerol === "Administrador"){
                $solicitudes = devolucion_mercaderia::where('activo',1)->orderBy('created_at','desc')->groupBy('num_nc')->get();

                return view('inventario.pedidos_a_bodega',['solicitudes' => $solicitudes]);
            }else{
                return redirect('home');
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }


    }
    public function revisar_solicitud(){
        $user_id = Auth::user()->id;
        $solicitudes = devolucion_mercaderia::where('activo',1)->get();
        return count($solicitudes);
    }

    public function eliminar_solicitud($id){

        try {
            $solicitud = solicitudes::where('id',$id)->first();

            if (!$solicitud) {
                return response('Solicitud no encontrada.', 404);
            }

            $solicitud->activo = 0;
            $solicitud->save();
            $solicitudes = solicitudes::where('activo',1)->get();

            $v = view('fragm.tarjetas_pedidos',compact('solicitudes'))->render();
            return $v;
        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
    }

    public function misventas(){
        try {
            $misventas = carrito_transferido::select('carrito_transferido.*',
                                    'repuestos.codigo_interno',
                                    'repuestos.descripcion',
                                    'repuestos.oferta',
                                    'repuestos.id as idrepuesto',
                                    'users.name',
                                    'clientes.nombres as cliente')
                                    ->where('carrito_transferido.usuarios_id',Auth::user()->id)
                                    ->where('carrito_transferido.estado',0)
                                    ->join('repuestos','carrito_transferido.id_repuestos','repuestos.id')
                                    ->join('clientes','carrito_transferido.cliente_id','clientes.id')
                                    ->join('users','carrito_transferido.usuarios_id','users.id')
                                    ->distinct()
                                    ->groupBy('carrito_transferido.carrito_id')
                                    ->orderBy('carrito_transferido.id','desc')
                                    ->get();

            $misnoventas = carrito_transferido::select('carrito_transferido.*',
                                    'repuestos.codigo_interno',
                                    'repuestos.descripcion',
                                    'repuestos.oferta',
                                    'repuestos.id as idrepuesto',
                                    'users.name',
                                    'clientes.nombres as cliente')
                                    ->where('carrito_transferido.usuarios_id',Auth::user()->id)
                                    ->where('carrito_transferido.estado',1)
                                    ->join('repuestos','carrito_transferido.id_repuestos','repuestos.id')
                                    ->join('clientes','carrito_transferido.cliente_id','clientes.id')
                                    ->join('users','carrito_transferido.usuarios_id','users.id')
                                    ->distinct()
                                    ->groupBy('carrito_transferido.carrito_id')
                                    ->orderBy('carrito_transferido.id','desc')
                                    ->get();

            $misclientes = cliente_vendedor::select('cliente_vendedor.*','clientes.nombres','clientes.telf1','clientes.email')
                                    ->where('cliente_vendedor.id_vendedor',Auth::user()->id)
                                    ->join('clientes','cliente_vendedor.id_cliente','clientes.id')
                                    ->get();

            return view('inventario.misventas',[
                'misventas' => $misventas,
                'misnoventas' => $misnoventas,
                'misclientes' => $misclientes
            ])->render();
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }

    }

    public function dame_solicitudes(){
        try {
            $solicitudes = devolucion_mercaderia::selectRaw('num_nc, MAX(id) as id, MAX(created_at) as created_at')
                                ->where('activo',1)
                                ->groupBy('num_nc')
                                ->orderByRaw('MAX(created_at) desc')
                                ->get();
            $v = view('fragm.tarjetas_pedidos',compact('solicitudes'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function procesar_solicitud(Request $req){
        $req->validate([
            'id_repuesto'  => 'required|integer|exists:repuestos,id',
            'id_solicitud' => 'required|integer',
            'cantidad'     => 'required|integer|min:1',
        ]);

        $id_repuesto  = $req->id_repuesto;
        $id_solicitud = $req->id_solicitud;
        $cantidad     = intval($req->cantidad);

        try {
            $repuesto  = repuesto::find($id_repuesto);
            $solicitud = solicitudes::find($id_solicitud);

            if (!$repuesto) {
                return response()->json(['error' => 'Repuesto no encontrado.'], 404);
            }
            if (!$solicitud) {
                return response()->json(['error' => 'Solicitud no encontrada.'], 404);
            }

            $repuesto->stock_actual = $repuesto->stock_actual + $cantidad;
            $solicitud->activo = 0;

            $solicitud->save();
            $repuesto->save();
            return $repuesto;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function notaventa(){
        return view('inventario.notaventa')->render();
    }

    public function dame_devoluciones(Request $request){
        try {

            //code...
            $devoluciones = devolucion_mercaderia::select('devolucion_mercaderia.*','locales.local_nombre')
            ->join('locales','devolucion_mercaderia.local_id','locales.id')
            ->where('devolucion_mercaderia.repuesto_id',$request->id_repuesto)
            ->get();

            // Devoluciones por facturas rechazadas
            $detalle_ventas_rep = factura_detalle::where('id_repuestos', $request->id_repuesto)->get();

            foreach($detalle_ventas_rep as $detalle) {
                $factura = factura::where('id', $detalle->id_factura)
                    ->where('estado_sii','RECHAZADO')
                    ->where('devuelto',1)
                    ->first();

                if ($factura) {
                    $devoluciones[] = [
                        'fecha_emision' => $factura->fecha_emision,
                        'num_nc' => 'RECHAZADA', // o podrías usar el num_factura
                        'cantidad' => $detalle->cantidad,
                        'local_nombre' => 'Factura Rechazada',
                        'local_id' => $detalle->id_local
                    ];
                }

            }


            // el local_id 2 corresponde a casa matriz y se guardaba pensando que era mermas, pero mermas actualmente es el local_id 4
            // foreach($devoluciones as $d){
            //     if($d->local_id == 2){
            //         $d->local_nombre = "Mermas";
            //     }
            // }
            // Corregir nombre para devoluciones desde casa matriz que eran mermas
            foreach($devoluciones as &$d){
                if($d['local_nombre'] == 'Casa Matriz' || $d['local_nombre'] == 'Sucursal Central'){ // o ID si lo prefieres
                    $d['local_nombre'] = 'Mermas';
                }
            }
            return $devoluciones;
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }

    }

    public function dame_info_repuesto_fechas(Request $req){

        $id = $req->id_repuesto;
        $fecha_inicio = $req->fecha_inicio;
        $fecha_fin = $req->fecha_fin;
        $data = [];
        $repuesto = repuesto::select('repuestos.id',
                                    'repuestos.descripcion',
                                    'repuestos.stock_actual',
                                    'repuestos.stock_actual_mermas',
                                    'repuestos.precio_venta',
                                    'repuestos.precio_antiguo',
                                    'users.name')
                                ->leftjoin('users','repuestos.usuario_id_modifica','users.id')
                                ->where('repuestos.id',$id)
                                ->get();


        if($repuesto->count() > 0){
            try {
                $factura_detalle = compras_det::select('compras_det.*','compras_cab.factura_numero','proveedores.empresa_nombre_corto')
                                        ->join('compras_cab','compras_det.id_factura_cab','compras_cab.id')
                                        ->leftjoin('proveedores','compras_cab.factura_id_proveedor','proveedores.id')
                                        ->where('compras_det.id_repuestos',$repuesto[0]->id)
                                        ->whereBetween('compras_cab.factura_fecha', [$fecha_inicio, $fecha_fin])
                                        ->orderBy('compras_cab.factura_fecha','desc')
                                        ->get();


                $boleta = boleta_detalle::select('boletas_detalle.cantidad','boletas_detalle.precio_venta','boletas.fecha_emision as fecha_emision','users.name','boletas.num_boleta as num_doc','locales.local_nombre','clientes.nombres as nombre_cliente')
                                        ->where('boletas_detalle.id_repuestos',$repuesto[0]->id)
                                        ->join('locales','boletas_detalle.id_local','locales.id')
                                        ->join('users','boletas_detalle.usuarios_id','users.id')
                                        ->join('boletas','boletas.id','boletas_detalle.id_boleta')
                                        ->leftjoin('clientes','boletas.id_cliente','clientes.id')
                                        ->whereBetween('boletas.fecha_emision', [$fecha_inicio, $fecha_fin])
                                        // ->where('boletas.estado',1)
                                        ->orderBy('boletas.fecha_emision','asc')
                                        ->get();

                foreach($boleta as $b){
                    $b->tipo_doc='boleta';
                }
                $factura = factura_detalle::select('facturas_detalle.cantidad','facturas_detalle.precio_venta','facturas.fecha_emision as fecha_emision','users.name','facturas.num_factura as num_doc','locales.local_nombre','clientes.nombres as nombre_cliente')
                                            ->where('facturas_detalle.id_repuestos',$repuesto[0]->id)
                                            ->leftjoin('locales','facturas_detalle.id_local','locales.id')
                                            ->leftjoin('users','facturas_detalle.usuarios_id','users.id')
                                            ->leftjoin('facturas','facturas.id','facturas_detalle.id_factura')
                                            ->leftjoin('clientes','facturas.id_cliente','clientes.id')
                                            ->whereBetween('facturas.fecha_emision', [$fecha_inicio, $fecha_fin])
                                            // ->where('facturas.estado',1)
                                            ->orderBy('facturas.fecha_emision','asc')
                                            ->get();


                foreach($factura as $f){
                    $f->tipo_doc='factura';
                }

                $valemercaderia = vale_mercaderia_detalle::select('vale_mercaderia_detalle.cantidad','vale_mercaderia.created_at as fecha_emision','users.name','vale_mercaderia.numero_boucher as num_doc')
                                                            ->where('vale_mercaderia_detalle.repuesto_id',$repuesto[0]->id)
                                                            ->join('users','vale_mercaderia_detalle.usuario_id','users.id')
                                                            ->join('vale_mercaderia','vale_mercaderia.id','vale_mercaderia_detalle.vale_mercaderia_id')
                                                            ->whereBetween('vale_mercaderia.created_at', [$fecha_inicio, $fecha_fin])
                                                            ->orderBy('vale_mercaderia.created_at','asc')
                                                            ->get();

                foreach($valemercaderia as $v){
                    $v->tipo_doc='valemercaderia';
                }

                $traspasos = traspaso_mercaderia_detalle::select('traspaso_mercaderia_detalle.*','users.name')
                                                        ->join('traspaso_mercaderia','traspaso_mercaderia_detalle.id_traspaso_mercaderia','traspaso_mercaderia.id')
                                                        ->join('users','traspaso_mercaderia.usuario_id','users.id')
                                                        ->where('traspaso_mercaderia_detalle.repuesto_id',$repuesto[0]->id)
                                                        ->get();

                foreach($factura_detalle as $f){
                    //Le damos formato a la fecha
                    $f->created_at = Carbon::parse($f->created_at)->format("d-m-Y");
                }
                //Juntamos todas las ventas, tanto boletas, facturas y vale de mercaderia
                // Ordenamos por fecha_emision DESC antes de formatear para que el sort sea correcto
                $boleta_detalle = $boleta->mergeRecursive($factura)->mergeRecursive($valemercaderia)
                                         ->sortByDesc('fecha_emision')
                                         ->values();

                foreach($boleta_detalle as $b){
                    $b->fecha_emision = Carbon::parse($b->fecha_emision)->format("d-m-Y");

                }

                foreach($traspasos as $t){
                    $t->fecha_emision = Carbon::parse($t->fecha_emision)->format("d-m-Y");
                }
                $imagen_repuesto = repuestofoto::select('urlfoto')->where('id_repuestos',$repuesto[0]->id)->get();
                //Formateamos la fecha de modificación del stock
                $repuesto[0]->fecha_ultima = Carbon::parse($repuesto[0]->fecha_ultima)->format("d-m-Y");
                foreach($factura_detalle as $f){
                    //Separamos el valor de created_at para que solo me muestre la fecha en formato YYYY-MM-DD que será guardado en la primera posición
                    $porciones = explode("T", $f->created_at);
                    //Creamos el atributo fecha para asignarle la nueva fecha formateada
                    $f->fecha = Carbon::parse($porciones[0])->format("d-m-Y");
                }
                $modificaciones_stock = modificaciones_stock::select('modificaciones_stock.*','users.name')
                                                            ->join('users','modificaciones_stock.id_responsable_stock','users.id')
                                                            ->where('modificaciones_stock.id_repuesto',$repuesto[0]->id)
                                                            ->where('modificaciones_stock.activo',1)
                                                            ->orderBy('modificaciones_stock.fecha_modificacion','desc')
                                                            ->get();
                foreach($modificaciones_stock as $m){
                    $m->fecha_modificacion_stock = Carbon::parse($m->fecha_modificacion)->format("d-m-Y");
                }

                $devoluciones_mercaderia = devolucion_mercaderia::where('repuesto_id',$repuesto[0]->id)->where('activo',0)->get();
                $total_devoluciones = 0;
                foreach($devoluciones_mercaderia as $d){
                    $total_devoluciones += $d->cantidad;
                }
                $imagen_repuesto = repuestofoto::select('urlfoto')->where('id_repuestos',$repuesto[0]->id)->get();
                array_push($data,$factura_detalle,$boleta_detalle,$repuesto[0],$traspasos,$modificaciones_stock, $devoluciones_mercaderia, $total_devoluciones, $imagen_repuesto);
                return $data;
            } catch (\Exception $e) {
                return $e->getMessage();
            }

        }else{
            return ["error","Verifique el codigo interno"];
        }
    }

}
