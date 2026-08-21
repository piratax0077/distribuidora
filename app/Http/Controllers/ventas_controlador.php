<?php
namespace App\Http\Controllers;
//ini_set('max_execution_time', 40); //en segundos. borrar después

use Debugbar;
use App\local;
use App\abono;
use App\abono_detalle;
use App\notifications\CarritoTransferidoNotification;
use Carbon\Carbon; // para tratamiento de fechas
use Illuminate\Http\Request;
use App\boleta;
use App\boleta_detalle;
use App\carrito_compra;
use App\carrito_guardado;
use App\carrito_transferido;
use App\carrito_virtual;
use App\carrito_virtual_detalle;
use App\cliente_direcciones;
use App\clientes_giro;
use App\cliente_vendedor;
use App\oferta_catalogo;
use App\cliente_modelo;
use App\cliente_cuenta;
use App\ciudades;
use App\correlativo;
use App\folio;
use App\cotizacion;
use App\cotizacion_detalle;
use App\consignacion;
use App\consignacion_detalle;
use App\descuento;
use App\fabricante;
use App\factura;
use App\factura_detalle;
use App\nota_de_credito;
use App\nota_de_credito_detalle;
use App\nota_de_debito;
use App\nota_de_debito_detalle;
use App\familia;
use App\formapago;
use App\marcarepuesto;
use App\marcavehiculo;
use App\modelovehiculo;
use App\oem;
use App\pago;
use App\repuesto;
use App\repuestofoto;
use App\saldo;
use App\similar;
use App\User;
use App\proveedor;
use App\regiones;
use App\guia_de_despacho;
use App\guia_de_despacho_detalle;
use App\stock_tienda;
use App\vale_mercaderia;
use App\vale_mercaderia_detalle;
use App\vale_consignacion;
use App\vale_consignacion_detalle;
use App\permissions_detail;
use App\oferta_pagina_web;
use App\armado_kit;
use App\armado_kit_detalle;
use App\compras_cab;
use App\compras_det;
use App\regulador_voltaje;
use App\dte_rechazados;
use App\stock_minimo;
Use App\motorPartida;
use App\cliente_formas_pago;
use Session;
use App\servicios_sii\ClsSii;
use App\servicios_sii\FirmaElectronica;
use App\servicios_sii\Auto;
use App\servicios_sii\Sii;

//Excel
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\DetalleVentasExport;

use App\Http\Controllers\correos_controlador;


use App\Http\Controllers\clientes_controlador;

use Illuminate\Support\Facades\Auth;

class ventas_controlador extends Controller
{

    /*
    private function dame_un_repuesto($id)
    {
    $repuesto=repuesto::where('repuestos.id',$id)
    ->where('repuestos.activo',1)
    ->join('familias','repuestos.id_familia','familias.id')
    ->join('marcavehiculos','repuestos.id_marca_vehiculo','marcavehiculos.idmarcavehiculo')
    ->join('modelovehiculos','repuestos.id_modelo_vehiculo','modelovehiculos.id')
    ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
    ->join('proveedores','repuestos.id_proveedor','proveedores.id')
    ->join('paises','repuestos.id_pais','paises.id')
    ->get();

    return $repuesto;
    }

    private function damesimilares($id_repuesto)
    {
    $s=similar::select('marcavehiculos.marcanombre','modelovehiculos.modelonombre','similares.anios_vehiculo')
    ->where('similares.id_repuestos',$id_repuesto)
    ->where('similares.activo',1)
    ->join('marcavehiculos','similares.id_marca_vehiculo','marcavehiculos.idmarcavehiculo')
    ->join('modelovehiculos','similares.id_modelo_vehiculo','modelovehiculos.id')
    ->get();
    return $s;
    }

    private function damefotosrepuesto($id_repuesto)
    {
    $rf=repuestofoto::select('urlfoto')
    ->where('id_repuestos',$id_repuesto)
    ->where('activo',1)
    ->get();
    return $rf;
    }

     */


    public function confirmaringreso($path){
        $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == $path){
                        return true;
                    }
            }
        $user = Auth::user();
        if ($user->rol->nombrerol == "Administrador") {
            return true;
        } else {
            return false;
        }
    }
    public function dame_relacionados($id_repuesto)
    {

        $repuestos = repuesto::where('repuestos_relacionados.id_repuesto_principal', $id_repuesto)
        ->where('repuestos.activo',1)
            ->join('repuestos_relacionados', 'repuestos.id', 'repuestos_relacionados.id_repuesto_relacionado')
            ->select('repuestos_relacionados.id AS id_relacionado', 'repuestos.*')
            ->get();
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();
         //Mandamos array con los id_repuestos que tengan fotos
         $tienen_foto=repuestofoto::select('id_repuestos')
         ->distinct()
         ->get()
         ->toArray();
        $criterio="relacionados";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos','criterio','tienen_foto'))->render();
        return $v;
    }

    public function damemarcas()
    {
        //usando auth en el grupo de rutas. ver web.php
        $marcas = marcavehiculo::where('activo', '=', 1)
            ->select('idmarcavehiculo', 'marcanombre', 'urlfoto')
            ->orderBy('marcanombre')
            ->get();
        $v = view('fragm.marca_vehiculos', compact('marcas'))->render();
        return $v;
    }

    public function damemodelos($idmarca)
    {
        //usando auth en el grupo de rutas. ver web.php
        $modelos = modelovehiculo::where('activo', '=', 1)
            ->where('marcavehiculos_idmarcavehiculo', $idmarca)
            ->orderBy('modelonombre')
            ->get();
        $v = view('fragm.modelo_vehiculos', compact('modelos'))->render();
        return $v;
    }

    private function revisar($repuestos,$encontrados){
        $huy=$encontrados->where('codigo_interno','CZA50')->count();
        if($repuestos->count()==0){
            $repuestos=$encontrados;
        }else{
            $repuestos=$repuestos->merge($encontrados);
        }
        //return $repuestos->unique();
        return $repuestos;
    }

    public function buscar_por_descripcion($dato){ //busqueda principal
        try {
            $op = substr($dato, 0, 1);
            $desc = substr(trim($dato), 1);
            $de = array(" de ", " DE ", " dE ", " De ");
            $descripcion = str_replace($de, " ", $desc);
            $descripcion= str_replace("  "," ",$descripcion);
            $descripcion=str_replace("_&_","/",$descripcion);
            $descripcion_original=$descripcion;
            $descripcion_sin_guiones= str_replace("-","",$descripcion);
            $buscado_original=trim($descripcion_original);
            $buscado_sin_guiones=$descripcion_sin_guiones;
            $terminos=explode(" ",$descripcion);

            $resultados = [];

            $contador_descripcion = 0;
            $contador_codigo_interno = 0;

            foreach ($terminos as $termino) {
                $repuestos_descripcion = repuesto::select('repuestos.*','marcarepuestos.marcarepuesto as marca')->where('descripcion', 'like', '%' . $termino . '%')->join('marcarepuestos','repuestos.id_marca','marcarepuestos.id')->get();

                if (!$repuestos_descripcion->isEmpty()) {
                    foreach ($repuestos_descripcion as $repuesto) {
                        $resultados[] = $repuesto;
                        $contador_descripcion++;
                    }
                }

                $repuestos_codigo_interno = repuesto::select('repuestos.*','marcarepuestos.marcarepuesto as marca')->where('codigo_interno', 'like', '%' . $termino . '%')->join('marcarepuestos','repuestos.id_marca','marcarepuestos.id')->get();
                if(!$repuestos_codigo_interno->isEmpty()){
                    foreach ($repuestos_codigo_interno as $repuesto) {
                        $resultados[] = $repuesto;
                        $contador_codigo_interno++;
                    }
                }
            }

            $total = 0;
            foreach ($resultados as $r) {
                $total++;
            }



            $quien_busca=User::find(Auth::user()->id)->name;
            $id_buscados=\DB::table('busquedas')->insertGetId(['buscado'=>$desc,'encontrados'=>"INICIO",'fecha_hora_buscado'=>date("Y-m-d H:i:s"),'quien_busca'=>$quien_busca]);


            $desde = 'd';
            $permiso = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/repuesto/modificar-precio')->first();
                if($permiso){
                    $permiso_modificar = true;
                }else{
                    $permiso_modificar = false;
                }

            $permiso_ = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/actualizar-precio')->first();
                if($permiso_){
                    $permiso_actualizar = true;
                }else{
                    $permiso_actualizar = false;
                }



            //$v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto','arreglo','stocks_repuesto','permiso_modificar','permiso_actualizar'))->render();

            // Obtener nombres de locales una sola vez
            $locales = local::whereIn('id', [1, 2, 3])->pluck('local_nombre', 'id');

            foreach($resultados as $repuesto){
                // verificar si el repuesto esta en oferta
                $oferta = oferta_pagina_web::where('id_repuesto',$repuesto->id)->where('activo',1)->first();
                if(isset($oferta)){
                    $repuesto->precio_venta = $oferta->precio_actualizado;
                    $repuesto->oferta = 1;
                }else{
                    $repuesto->oferta = 0;
                }
            }

            // Agrupar stocks por repuesto (id del repuesto como clave)
            $stocks_repuesto = [];

            foreach($resultados as $repuesto){
                $stocks_del_repuesto = [];

                // Primera ubicación (id_local = 1)
                if($repuesto->id_local == 1 && $repuesto->stock_actual > 0){
                    $stocks_del_repuesto[] = [
                        'stock_actual' => $repuesto->stock_actual,
                        'local_nombre' => $locales->get(1, 'Bodega Principal'),
                        'id' => $repuesto->id,
                        'id_local' => 1
                    ];
                }

                // Segunda ubicación (id_local_dos = 2)
                if($repuesto->id_local_dos == 2 && $repuesto->stock_actual_mermas > 0){
                    $stocks_del_repuesto[] = [
                        'stock_actual' => $repuesto->stock_actual_mermas,
                        'local_nombre' => $locales->get(2, 'Mermas'),
                        'id' => $repuesto->id,
                        'id_local' => 2
                    ];
                }

                // Tercera ubicación (id_local_tres = 3)
                if($repuesto->id_local_tres == 3 && $repuesto->stock_actual_preventa > 0){
                    $stocks_del_repuesto[] = [
                        'stock_actual' => $repuesto->stock_actual_preventa,
                        'local_nombre' => $locales->get(3, 'Preventa'),
                        'id' => $repuesto->id,
                        'id_local' => 3
                    ];
                }

                // Agrupar por ID de repuesto
                $stocks_repuesto[$repuesto->id] = $stocks_del_repuesto;
            }

            $v = view('ips.fragm.ventas_productos',compact('resultados','total','stocks_repuesto'))->render();

            return $v;
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }

    }

        public function cargar_consignacion_home($num_consignacion){
            try {

                $consignacion = consignacion::where('num_consignacion',$num_consignacion)->first();
                $consignacion->fecha_emision = Carbon::parse($consignacion->fecha_emision)->format('d-m-Y');

                $consignacion_detalle = consignacion_detalle::where('id_consignacion',$consignacion->id)->get();

                $repuestos = [];
                foreach($consignacion_detalle as $detalle){
                    $repuesto = repuesto::where('id',$detalle->id_repuestos)->first();
                    $repuesto->cantidad = $detalle->cantidad;
                    // verificar si el repuesto esta en oferta
                    $oferta = oferta_pagina_web::where('id_repuesto',$repuesto->id)->where('activo',1)->first();
                    // verificar si la familia del repuesta esta en oferta
                    $familia_dcto = descuento::where('id_familia', $repuesto->id_familia)->where('activo',1)->first();
                    $repuesto->precio_normal = $repuesto->precio_venta;
                    if(isset($oferta)){
                        $repuesto->precio_venta = $oferta->precio_actualizado;

                    }elseif(!isset($oferta) && isset($familia_dcto)){
                        $repuesto->precio_venta = $repuesto->precio_venta - (($familia_dcto->porcentaje/100) * $repuesto->precio_venta);
                        $repuesto->oferta = 2;
                    }else{
                        $repuesto->oferta = 0;
                    }
                    array_push($repuestos,$repuesto);
                }
                return [$consignacion,$repuestos];
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

    public function all_ofertas(){
        try {
            $hoy = getdate();
            $dia = $hoy['mday'];
            $mes = $hoy['mon'];
            $year = $hoy['year'];
            $contador = 0;

            $fecha = $year.'-'.$mes.'-'.$dia;

            $repuestos = repuesto::select('repuestos.*','ofertas_pagina_web.precio_actualizado','ofertas_pagina_web.descuento','ofertas_pagina_web.desde','ofertas_pagina_web.hasta','marcarepuestos.marcarepuesto')

                                    ->where('repuestos.id_marca','<>',190)
                                    ->where('ofertas_pagina_web.activo',1)
                                    ->join('marcarepuestos','repuestos.id_marca','marcarepuestos.id')
                                    ->join('ofertas_pagina_web','repuestos.id','ofertas_pagina_web.id_repuesto')
                                    ->get();

            $repuestos_= [];
                foreach($repuestos as $repuesto){

                    //Revisamos si hay oferta disponible el dia de hoy

                        //Fecha ultima actualización del precio del repuesto
                        $firstDate = $repuesto->fecha_actualiza_precio;
                        $secondDate = date('d-m-Y H:i:s');

                        $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

                        $years  = floor($dateDifference / (365 * 60 * 60 * 24));
                        $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

                        $minutos = floor(abs($dateDifference / 60));
                        $horas = floor($minutos / 60);
                        $dias = floor($horas / 24);
                        $similares = $this->damesimilares($repuesto->id);
                        if($similares){

                            $rep = new oferta_catalogo;
                            $foto=repuestofoto::where('id_repuestos','=',$repuesto->id)->first();
                            if(isset($foto)){
                                $rep->id = $repuesto->id;
                                $rep->codigo_interno = $repuesto->codigo_interno;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = $foto->urlfoto;
                                $rep->precio_venta = $repuesto->precio_venta;
                                $rep->precio_actualizado = $repuesto->precio_actualizado;
                                $rep->descuento = $repuesto->descuento;
                                $rep->marcarepuesto = $repuesto->marca;
                                $rep->nombrefamilia = 'SIN FAMILIA';
                                $rep->stock_actual = $repuesto->stock_actual;
                                array_push($repuestos_,$rep);
                            }else{
                                $foto=repuestofoto::where('id_repuestos','=',62816)->first();
                                $rep->id = $repuesto->id;
                                $rep->descripcion = $repuesto->descripcion;
                                $rep->urlfoto = 'sin foto';
                                $rep->precio_venta = $repuesto->precio_venta;
                                $rep->precio_actualizado = $repuesto->precio_actualizado;
                                $rep->descuento = $repuesto->descuento;
                                $rep->marcarepuesto = $repuesto->marcarepuesto;
                                $rep->nombrefamilia = $repuesto->nombrefamilia;
                                $rep->stock_actual = $repuesto->stock_actual;
                                array_push($repuestos_,$rep);
                            }
                        }



                    }

                $familias_descuento = descuento::select('descuentos.*','familias.nombrefamilia')
                                                ->join('familias','descuentos.id_familia','familias.id')
                                                ->get();

                return [$repuestos_, $familias_descuento];
            } catch (\Exception $e) {
                return $e->getMessage();
            }
    }

    public function damesimilares($id_repuesto)
    {
        $s=similar::select('marcavehiculos.marcanombre','marcavehiculos.urlfoto','modelovehiculos.modelonombre','modelovehiculos.zofri','similares.id','similares.anios_vehiculo','marcavehiculos.idmarcavehiculo','modelovehiculos.id as id_modelo')
        ->where('similares.id_repuestos',$id_repuesto)
        ->where('similares.activo',1)
        ->join('marcavehiculos','similares.id_marca_vehiculo','marcavehiculos.idmarcavehiculo')
        ->join('modelovehiculos','similares.id_modelo_vehiculo','modelovehiculos.id')
        ->orderBy('marcavehiculos.marcanombre','ASC')
        ->get();
        return $s;
    }

    public function buscar_en_codint($buscado){
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);
        /*

        $terminos_encontrados=[];
        for($i=0;$i<count($terminos);$i++){
            $hay=repuesto::where('repuestos.codigo_interno',$terminos[$i])
            ->where('repuestos.activo',1)
            ->count();
            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                $sql.="repuestos.codigo_interno=? AND ";
            }
            $sql=$sql." repuestos.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);

            $encontrados=repuesto::whereRaw($sql,$param)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                            ->get();
            */
            $encontrados=Collect();
            if(count($terminos)==1){
                $encontrados=repuesto::where('repuestos.codigo_interno',$buscado)
                            ->where('repuestos.activo',1)
                            ->select('repuestos.*')
                            ->get();
            }
            if($encontrados->count()>0){
                $resp['resultado']=true;
                $resp['repuestos']=$encontrados;
            }

        return $resp;
    }

    public function buscar_en_codprov($buscado){
        $buscado=trim($buscado);
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);


        $encontrados=Collect();
        if(count($terminos)==1){
            $encontrados=repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$buscado. '%')
                        ->where('repuestos.activo',1)
                        ->where('repuestos.codigo_OEM_repuesto','!=','XPRESS')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
        }
        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
        }

        return $resp;
        /*
        $terminos_encontrados=[];
        for($i=0;$i<count($terminos);$i++){
            $hay=repuesto::where('cod_repuesto_proveedor',$terminos[$i])
            ->where('repuestos.activo',1)
            ->count();
            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                    $sql.="repuestos.cod_repuesto_proveedor LIKE ? AND ";
            }
            $sql=$sql." repuestos.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);

            $encontrados=repuesto::whereRaw($sql,$param)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                            ->get();
            if($encontrados->count()>0){
                $resp['resultado']=true;
                $resp['repuestos']=$encontrados;
            }
        }
        */

    }

    public function buscar_en_codfam($buscado){
        return false; // x q trae resultados generales de la familia y no lo que se especifica
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);
        $terminos_encontrados=[];
        $fam=[];
        $encontrados=Collect();
        for($i=0;$i<count($terminos);$i++){
            $hay=familia::where('nombrefamilia','LIKE','%'.$terminos[$i].'%')
            ->where('familias.activo',1)
            ->count();
            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                    $sql.="familias.nombrefamilia LIKE ? AND ";
            }
            $sql=$sql." familias.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);

            $fam = familia::select('id')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();
            if(count($fam)>0){
                $encontrados=repuesto::wherein('repuestos.id_familia',$fam)
                    ->where('repuestos.activo',1)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->get();
                if($encontrados->count()>0){
                    $resp['resultado']=true;
                    $resp['repuestos']=$encontrados;
                }
            }



        }
        return $resp;
    }

    public function buscar_en_codoem($buscado){
        $resp['resultado']=false;
        $buscado=str_replace("-","",$buscado);
        $terminos=explode(" ",$buscado);
        /*

        $terminos_encontrados=[];
        for($i=0;$i<count($terminos);$i++){
            $hay=oem::where('codigo_oem',$terminos[$i])->count();
            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){

            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                    $sql.="oems.codigo_oem LIKE ? AND ";
            }
            $sql=$sql." oems.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);

            $oem = oem::select('id_repuestos')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();

            if(count($oem)>0){
                $encontrados=repuesto::wherein('repuestos.id',$oem)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->get();
                if($encontrados->count()>0){
                    $resp['resultado']=true;
                    $resp['repuestos']=$encontrados;
                }
            }



        }
        */
        $encontrados=Collect();
        if(count($terminos)==1){
            $oem = oem::select('id_repuestos')
                        ->where('oems.codigo_oem','LIKE','%'.$buscado.'%')
                        ->where('oems.activo',1)
                        ->get()
                        ->toArray();
            if(count($oem)>0){
                $encontrados=repuesto::wherein('repuestos.id',$oem)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.codigo_OEM_repuesto','!=','XPRESS')
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->get();

            }
        }
        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
        }
        return $resp;
    }

    public function buscar_en_codAlternador($buscado){
        $resp['resultado']=false;
        $buscado=str_replace("-","",$buscado);
        $terminos=explode(" ",$buscado);

        $encontrados=Collect();
        if(count($terminos)==1){
            $data = regulador_voltaje::select('regulador_voltaje.id_repuesto')
                        ->where('regulador_voltaje.alternador','LIKE','%'.$buscado.'%')
                        ->where('regulador_voltaje.activo',1)
                        ->get()
                        ->toArray();

            if(count($data)>0){
                $encontrados=repuesto::wherein('repuestos.id',$data)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.codigo_OEM_repuesto','!=','XPRESS')
                    ->leftjoin('paises', 'repuestos.id_pais', 'paises.id')
                    ->leftjoin('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->leftjoin('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->get();

            }
        }
        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
        }
        return $resp;
    }

    public function buscar_en_codRectificador($buscado){
        $resp['resultado']=false;
        $buscado=str_replace("-","",$buscado);
        $terminos=explode(" ",$buscado);

        $encontrados=Collect();
        if(count($terminos)==1){
            $data = regulador_voltaje::select('id_repuesto')
                        ->where('regulador_voltaje.rectificador','LIKE','%'.$buscado.'%')
                        ->where('regulador_voltaje.activo',1)
                        ->get()
                        ->toArray();
            if(count($data)>0){
                $encontrados=repuesto::wherein('repuestos.id',$data)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.codigo_OEM_repuesto','!=','XPRESS')
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->get();

            }
        }
        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
        }
        return $resp;
    }

    public function buscar_en_codmotor($buscado){
        $resp['resultado']=false;
        $buscado=str_replace("-","",$buscado);
        $terminos=explode(" ",$buscado);

        $encontrados=Collect();
        if(count($terminos)==1){
            $data = motorPartida::select('id_repuesto')
                        ->where('motor','LIKE','%'.$buscado.'%')
                        ->where('activo',1)
                        ->get()
                        ->toArray();
            if(count($data)>0){
                $encontrados=repuesto::wherein('repuestos.id',$data)
                    ->where('repuestos.activo',1)
                    ->where('repuestos.codigo_OEM_repuesto','!=','XPRESS')
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->get();

            }
        }
        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
        }
        return $resp;
    }

    public function buscar_en_codfab($buscado){
        //repuestos_fabricantes.codigo_fab

        $encontró=false;
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);
        $encontrados=Collect();
        //Asumiendo que ingresó solo un término, lo buscamos con el algoritmo del guion
        if(count($terminos)==1){
            $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado. '%')
                                ->where('repuestos.activo',1)
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                ->get();
            if($encontrados->count()>0){
                $resp['resultado']=true;
                $resp['repuestos']=$encontrados;
                $encontró=true;
            }else{
                $guion="-";
                $buscado_original=$buscado;
                $buscado_sin_guiones= str_replace("-","",$buscado);
                $nf=strpos($buscado_original,$guion);
                if($nf===false){ //no hay guión, le va poniendo el guión en diferentes posiciones para buscarlo
                    for($i=1;$i<strlen($buscado_original);$i++){
                        $buskado = substr_replace($buscado_original, $guion, $i, 0); //ERROR al usar caracteres UTF-8 en las búsquedas, ejm PIÑ10
                        $numfil=fabricante::where('repuestos_fabricantes.codigo_fab','LIKE','%'.$buskado.'%')
                                            ->where('repuestos_fabricantes.activo',1)
                                            ->count();
                        if($numfil>0){
                            $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buskado. '%')
                                    ->where('repuestos.activo',1)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                    ->get();
                            if($encontrados->count()>0){
                                $resp['resultado']=true;
                                $resp['repuestos']=$encontrados;
                                $encontró=true;
                            }
                            break;
                        }
                    }

                }else{ //hay guión o guiones, lo busca tal cual
                    $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado_original. '%')
                                    ->where('repuestos.activo',1)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                    ->get();
                    if($encontrados->count()>0){
                        $resp['resultado']=true;
                        $resp['repuestos']=$encontrados;
                        $encontró=true;
                    }else{ //no hay tal cual, lo busca sin guiones
                        $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado_sin_guiones. '%')
                                    ->where('repuestos.activo',1)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                    ->get();
                        if($encontrados->count()>0){
                            $resp['resultado']=true;
                            $resp['repuestos']=$encontrados;
                            $encontró=true;
                        }
                    }
                }

            }



        } // fin 1 término

        return $resp;

        /*
        if($encontró) return $resp;

        //segunda parte: la búsqueda tiene más de 1 término
        $terminos_encontrados=[];
        for($i=0;$i<count($terminos);$i++){
            $hay=fabricante::where('repuestos_fabricantes.codigo_fab','%'.$terminos[$i].'%')
                            ->where('repuestos_fabricantes.activo',1)
                            ->count();
            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                $sql.="repuestos_fabricantes.codigo_fab LIKE ? AND ";
            }
            $sql=$sql." repuestos_fabricantes.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);

            $fab = fabricante::select('id_repuestos')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();
            if(count($fab)>0){
                $encontrados=repuesto::wherein('repuestos.id',$fab)
                    ->where('repuestos.activo',1)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->get();
                if($encontrados->count()>0){
                    $resp['resultado']=true;
                    $resp['repuestos']=$encontrados;
                }
            }



        }

        return $resp;
        */
    }

    public function buscar_en_nomfab($buscado){
        //primero en familia para reducir la cantidad de resultados
        $terminos_fam=explode(" ",$buscado);
        $terminos_encontrados_fam=[];
        $fam=[];
        $encontrados=Collect();
        for($i=0;$i<count($terminos_fam);$i++){
            $hay=familia::where('familias.nombrefamilia','LIKE','%'.$terminos_fam[$i].'%')
                            ->where('familias.activo',1)
                            ->count();
            if($hay>0){
                array_push($terminos_encontrados_fam,$terminos_fam[$i]);
            }
        }

        if(count($terminos_encontrados_fam)>0){
            $sql="";

            for($i=0;$i<count($terminos_encontrados_fam);$i++){
                    $sql.="familias.nombrefamilia LIKE ? AND ";
            }
            $sql=$sql." familias.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados_fam);$i++){
                $buscado_fam="%".$terminos_encontrados_fam[$i]."%";
                array_push($param,$buscado_fam);
            }
            array_push($param,1);

            $fam = familia::select('id')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();
        }

        //marcarepuestos.marcarepuesto
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);
        $terminos_encontrados=[];
        $mr=[];
        for($i=0;$i<count($terminos);$i++){
            $hay=marcarepuesto::where('marcarepuestos.marcarepuesto','LIKE','%'.$terminos[$i].'%')
                                ->where('marcarepuestos.activo',1)
                                ->count();
            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                $sql.="marcarepuestos.marcarepuesto LIKE ? AND ";
            }
            $sql=$sql." marcarepuestos.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);

            $mr = marcarepuesto::select('id')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();

            if(count($mr)>0){
                if(count($fam)>0){
                    $encontrados=repuesto::wherein('repuestos.id_marca_repuesto',$mr)
                        ->wherein('repuestos.id_familia',$fam)
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }else{
                    $terminos_encontrados_fab=[];
                    for($i=0;$i<count($terminos);$i++){
                        $hay=fabricante::where('repuestos_fabricantes.codigo_fab','LIKE'.'%'.$terminos[$i].'%')
                                        ->where('repuestos_fabricantes.activo',1)
                                        ->count();
                        if($hay>0){
                            array_push($terminos_encontrados_fab,$terminos[$i]);
                        }
                    }
                    if(count($terminos_encontrados_fab)>0){

                        $sql="";
                        for($i=0;$i<count($terminos_encontrados_fab);$i++){
                            $sql.="repuestos_fabricantes.codigo_fab LIKE ? AND ";
                        }
                        $sql=$sql." repuestos_fabricantes.activo=?";
                        $param=[];
                        for($i=0;$i<count($terminos_encontrados);$i++){
                            $buscado_fab="%".$terminos_encontrados[$i]."%";
                            array_push($param,$buscado_fab);
                        }
                        array_push($param,1);

                        $fab = fabricante::select('id_repuestos')
                                ->whereRaw($sql,$param) //->toSql();
                                ->get()
                                ->toArray();
                        if(count($fab)>0){
                            $encontrados=repuesto::wherein('repuestos.id_marca_repuesto',$mr)
                                ->wherein('repuestos.id_marca_repuesto',$fab)
                                ->where('repuestos.activo',1)
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                                ->get();
                            $encontrados=$encontrados->wherein('repuestos.id_marca_repuesto',$fab)->get();
                        }
                    }


                }
            }
            if($encontrados->count()>0){
                $resp['resultado']=true;
                $resp['repuestos']=$encontrados;
            }


        }
        return $resp;
    }



    public function buscar_en_marveh($buscado){
        //primero en familia para reducir la cantidad de resultados
        $terminos_fam=explode(" ",$buscado);
        $terminos_encontrados_fam=[];
        $fam=[];
        $encontrados=Collect();
        for($i=0;$i<count($terminos_fam);$i++){
            $hay=familia::where('familias.nombrefamilia','LIKE','%'.$terminos_fam[$i].'%')
                            ->where('familias.activo',1)
                            ->count();
            if($hay>0){
                array_push($terminos_encontrados_fam,$terminos_fam[$i]);
            }
        }

        if(count($terminos_encontrados_fam)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados_fam);$i++){
                    $sql.="familias.nombrefamilia LIKE ? AND ";
            }
            $sql=$sql." familias.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados_fam);$i++){
                $buscado_fam="%".$terminos_encontrados_fam[$i]."%";
                array_push($param,$buscado_fam);
            }
            array_push($param,1);

            $fam = familia::select('id')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();
        }

        //marcavehiculos.marcanombre y marcavehiculo->idmarcavehiculo
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);
        $terminos_encontrados=[];
        for($i=0;$i<count($terminos);$i++){
            $hay=marcavehiculo::where('marcavehiculos.marcanombre',$terminos[$i])
                                ->where('marcavehiculos.activo',1)
                                ->count();
            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                $sql.="marcavehiculos.marcanombre LIKE ? AND ";
            }

            $sql=$sql." marcavehiculos.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);

            $mv = marcavehiculo::select('idmarcavehiculo')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();

            $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_marca_vehiculo',$mv)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();

            if(count($apli)>0){
                if(count($fam)>0){
                    $encontrados=repuesto::wherein('repuestos.id',$apli)
                        ->wherein('repuestos.id_familia',$fam)
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }else{
                    $encontrados=repuesto::wherein('repuestos.id',$apli)
                    ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }
                if($encontrados->count()>0){
                    $resp['resultado']=true;
                    $resp['repuestos']=$encontrados;
                }
            }



        }
        return $resp;
    }

    public function buscar_en_modveh($buscado){
        //primero en familia para reducir la cantidad de resultados
        $terminos_fam=explode(" ",$buscado);
        $terminos_encontrados_fam=[];
        $fam=[];
        $encontrados=Collect();
        for($i=0;$i<count($terminos_fam);$i++){
            $hay=familia::where('familias.nombrefamilia','LIKE','%'.$terminos_fam[$i].'%')
                            ->where('familias.activo',1)
                            ->count();
            if($hay>0){
                array_push($terminos_encontrados_fam,$terminos_fam[$i]);
            }
        }
        if(count($terminos_encontrados_fam)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados_fam);$i++){
                    $sql.="familias.nombrefamilia LIKE ? AND ";
            }
            $sql=$sql." familias.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados_fam);$i++){
                $buscado_fam="%".$terminos_encontrados_fam[$i]."%";
                array_push($param,$buscado_fam);
            }
            array_push($param,1);

            $fam = familia::select('id','nombrefamilia')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();
        }


        //modelovehiculos.modelonombre, modelovehiculo->id
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);
        $terminos_encontrados=[];
        for($i=0;$i<count($terminos);$i++){
            $hay=modelovehiculo::where('modelovehiculos.modelonombre','LIKE','%'.$terminos[$i].'%')
                                ->where('modelovehiculos.activo',1)
                                ->count();

            if($hay>0){
                array_push($terminos_encontrados,$terminos[$i]);
            }
        }
        if(count($terminos_encontrados)>0){
            $sql="";
            for($i=0;$i<count($terminos_encontrados);$i++){
                $sql.="modelovehiculos.modelonombre LIKE ? AND ";
            }
            $sql=$sql." modelovehiculos.activo=?";
            $param=[];
            for($i=0;$i<count($terminos_encontrados);$i++){
                $buscado="%".$terminos_encontrados[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);
            $mv = modelovehiculo::select('id')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();

            $apli=similar::select('id_repuestos')
                            ->wherein('id_modelo_vehiculo',$mv)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();

            if(count($apli)>0){
                if(count($fam)>0){
                    $encontrados=repuesto::wherein('repuestos.id',$apli)
                        ->wherein('repuestos.id_familia',$fam)
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }else{
                    $encontrados=repuesto::wherein('repuestos.id',$apli)
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if($encontrados->count()>0){
                    $resp['resultado']=true;
                    $resp['repuestos']=$encontrados;
                }
            }



        }
        return $resp;
    }

    public function buscar_en_descrip($buscado){
        //repuestos.descripcion, repuesto->id
        $resp['resultado']=false;
        $buscado=trim($buscado);
        $terminos=explode(" ",trim($buscado));
        $encontrados=Collect();

        //NUEVO DESDE AQUI
        //TERMINOS EVALUADOS:
        //1. pastilla freno delantera outlander (mitsubishi outlander)
        //2. amortiguador delantero presag  (nissan presage)
        //3. correa distribucion porter (muchas familias)
        //4. FILTRO AIRE NISSAN march (trae muchas familias con 2 cantidades e incluso filtro aceite)
        //5. amortiguador march

        //PRIMERO Determinar que familia de repuestos esta buscando...
        $n_familia="";
        $n_terminos_encontrados=[];
        $decide_fam=[];
        $id_fam=0;
        for($i=0;$i<count($terminos);$i++){

            /*
            $n_hay=familia::where('nombrefamilia','LIKE','%'.$terminos[$i].'%')
                            ->where('familias.activo',1)
                            ->count();
            */


            $familys=familia::select('id','nombrefamilia')
                            ->where('nombrefamilia','LIKE','%'.$terminos[$i].'%')
                            ->where('familias.activo',1)
                            ->get();

            $n_hay=$familys->count();
            if($n_hay>0){
                array_push($n_terminos_encontrados,$terminos[$i]);
            }

            //qué id familia tiene más frecuencia
            foreach($familys as $famy)
            {
                if(!isset($decide_fam[$famy->id])) $decide_fam[$famy->id]=0;
                $decide_fam[$famy->id]++;
            }
        }

        $terer="";
        for($j=0;$j<count($n_terminos_encontrados);$j++) $terer.=$n_terminos_encontrados[$j]." ";
        $max_cant=0;
        $max_id_fam=0;
        foreach($decide_fam as $id_fam=>$cant){
            if($cant>$max_cant){
                $max_cant=$cant;
                $max_id_fam=$id_fam;
            }
        }
        $ids_max_fam=[];
        foreach($decide_fam as $id_fam=>$cant){
            if($cant==$max_cant) array_push($ids_max_fam,$id_fam);
        }

        $terminos2=[];

/*

        $terminos2=[];
        if(count($n_terminos_encontrados)>0){
            //CON LOS TÉRMINOS ENCONTRADOS DETERMINAR QUE FAMILIA
            $sql="";
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $sql.="familias.nombrefamilia LIKE ? AND ";
            }
            $sql=$sql." familias.activo=?";
            $param=[];
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $n_buscado='%'.$n_terminos_encontrados[$i]."%";
                array_push($param,$n_buscado);
            }
            array_push($param,1);
            $familias_encontradas=familia::select('familias.id')->whereRaw($sql,$param)->get()->toArray();
            //hasta aqui se obtienen los ID de las familias para ponerlas en la búsqueda de repuestos del campo id_familia

            if(count($familias_encontradas)>0){
                $que_familia=familia::find($familias_encontradas[0]['id'])->value("nombrefamilia");
            }else{
                $que_familia="Ninguna";
            }

            //quitar los términos anteriores encontrados para familias
            $terminos_temp=array_diff($terminos,$n_terminos_encontrados);
            foreach($terminos_temp as $j) array_push($terminos2,$j);

        }

        */

        //quitar los términos anteriores encontrados para familias
        $terminos_temp=array_diff($terminos,$n_terminos_encontrados);
        return json_encode($terminos_temp);
        foreach($terminos_temp as $j) array_push($terminos2,$j);


        if(count($terminos2)==0){ //no encontró repuesto para familias, y copia los terminos
            $terminos2=$terminos;
        }

        if(!isset($familias_encontradas)) $familias_encontradas=[];

        //buscar marca de vehículo
        $n_marca_vehiculo="";
        $n_terminos_encontrados=[];
        for($i=0;$i<count($terminos2);$i++){
            if(strlen($terminos2[$i])>0){
                $n_hay=marcavehiculo::where('marcanombre','LIKE','%'.$terminos2[$i].'%')
                                    ->where('marcavehiculos.activo',1)
                                    ->count();
                if($n_hay>0){
                    array_push($n_terminos_encontrados,$terminos2[$i]);
                    $n_marca_vehiculo.=$terminos2[$i]." ";
                }
            }
        }

        $n_marca_vehiculo_buscado=trim($n_marca_vehiculo);
        $terminos3=[];
        if(count($n_terminos_encontrados)>0){
            //DETERMINAR QUE MARCA VEHÍCULO
            $sql="";
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $sql.="(marcavehiculos.marcanombre LIKE ? OR marcavehiculos.marcanombre LIKE ? OR marcavehiculos.marcanombre LIKE ? OR marcavehiculos.marcanombre LIKE ?) AND ";
            }
            $sql=$sql." marcavehiculos.activo=?";
            $param=[];
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $al_inicio=$n_terminos_encontrados[$i]." %";
                $en_medio="% ".$n_terminos_encontrados[$i]." %";
                $al_final="% ".$n_terminos_encontrados[$i];
                $unico=$n_terminos_encontrados[$i];
                array_push($param,$al_inicio);
                array_push($param,$en_medio);
                array_push($param,$al_final);
                array_push($param,$unico);
            }
            array_push($param,1);
            $marca_vehiculo_encontrados=marcavehiculo::select('marcavehiculos.idmarcavehiculo')->whereRaw($sql,$param)->get()->toArray();
            $terminos_temp=array_diff($terminos2,$n_terminos_encontrados);
            foreach($terminos_temp as $j) array_push($terminos3,$j);
        }
        if(count($terminos3)==0){
            $terminos3=$terminos2;
        }

        if(!isset($marca_vehiculo_encontrados)) $marca_vehiculo_encontrados=[];

        //buscar modelo de vehículo
        //En terminos3 puede llegar el cilindraje del motor en formato 2.5 por ejemplo
        //y debe convertirse en 2500
        $cil_punto=[0.6,0.7,0.8,0.9,1.0,1.1,1.2,1.3,1.4,1.5,1.6,1.7,1.8,1.9,2.0,2.1,2.2,2.3,2.4,2.5,2.6,2.7,2.8,2.9,3.0,3.1,3.2,3.3,3.4,3.5,3.6,3.7,3.8,3.9,4.0,4.1,4.2,4.3,4.4,4.5,4.6,4.7,4.8,4.9,5.0,5.1,5.2,5.3,5.4,5.5,5.6,5.7,5.8,5.9,6.0,6.1,6.2,6.3,6.4,6.5,6.6,6.7,6.8,6.9,7.0];
        //$cil_coma=['0,6','0,7','0,8','0,9','1,1','1,1','1,2','1,3','1,4','1,5','1,6','1,7','1,8','1,9','2,0','2,1','2,2','2,3','2,4','2,5','2,6','2,7','2,8','2,9','3,0','3,1','3,2','3,3','3,4','3,5','3,6','3,7','3,8','3,9','4,0','4,1','4,2','4,3','4,4','4,5','4,6','4,7','4,8','4,9','5,0','5,1','5,2','5,3','5,4','5,5','5,6','5,7','5,8','5,9','6,0','6,1','6,2','6,3','6,4','6,5','6,6','6,7','6,8','6,9','7,0'];
        $cil_normal=[600,700,800,900,1000,1100,1200,1300,1400,1500,1600,1700,1800,1900,2000,2100,2200,2300,2400,2500,2600,2700,2800,2900,3000,3100,3200,3300,3400,3500,3600,3700,3800,3900,4000,4100,4200,4300,4400,4500,4600,4700,4800,4900,5000,5100,5200,5300,5400,5500,5600,5700,5800,5900,6000,6100,6200,6300,6400,6500,6600,6700,6800,6900,7000];

        $n_modelo_vehiculo="";
        $n_terminos_encontrados=[];
        for($i=0;$i<count($terminos3);$i++){
            if(strlen($terminos3[$i])>0){
                if(strpos($terminos3[$i],",")>0){
                    $terminos3[$i]=str_replace(',','.',$terminos3[$i]);
                }
                if(is_numeric($terminos3[$i])){
                    $valnum=$terminos3[$i];
                    settype($valnum,"float");
                    $i_punto=array_search($valnum,$cil_punto,true); //si no encuentra devuelve false, caso contrario el indice
                    if($i_punto===false){

                    }else{
                        $terminos3[$i]=$cil_normal[$i_punto];
                    }
                }


                $n_hay=modelovehiculo::where('modelonombre','LIKE','%'.$terminos3[$i].'%')
                                    ->where('modelovehiculos.activo',1)
                                    ->count();
                if($n_hay>0){
                    array_push($n_terminos_encontrados,$terminos3[$i]);
                    $n_modelo_vehiculo.=$terminos3[$i]." ";
                }
            }
        }

        $n_modelo_vehiculo_buscado=trim($n_modelo_vehiculo);
        $terminos4=[];
        if(count($n_terminos_encontrados)>0){
            //DETERMINAR QUE modelo VEHÍCULO
            $sql="";
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $sql.="modelovehiculos.modelonombre LIKE ? AND ";
            }
            $sql=$sql." modelovehiculos.activo=?";
            $param=[];
            for($i=0;$i<count($n_terminos_encontrados);$i++){
                $n_buscado="%".$n_terminos_encontrados[$i]."%";
                array_push($param,$n_buscado);
            }
            array_push($param,1);
            $modelo_vehiculo_encontrados=modelovehiculo::select('modelovehiculos.id')->whereRaw($sql,$param)->get()->toArray();
            $terminos_temp=array_diff($terminos3,$n_terminos_encontrados);
            foreach($terminos_temp as $j) array_push($terminos4,$j);

        }
        if(count($terminos4)==0){
            $terminos3=$terminos3;
        }

        if(!isset($modelo_vehiculo_encontrados)) $modelo_vehiculo_encontrados=[];


        //$resultado="familia: ".$que_familia.". MarcaVeh: ".$n_marca_vehiculo_buscado.". ModeloVeh: ".$n_modelo_vehiculo_buscado."(".count($modelo_vehiculo_encontrados).")";

//NUEVO HASTA AQUI

//familias_encontradas, marca_vehiculo_encontrados, modelo_vehiculo_encontrados
        if($id_fam>0){
            if(count($marca_vehiculo_encontrados)>0 && count($modelo_vehiculo_encontrados)>0){
                $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_marca_vehiculo',$marca_vehiculo_encontrados)
                            ->wherein('similares.id_modelo_vehiculo',$modelo_vehiculo_encontrados)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();
            }

            if(count($marca_vehiculo_encontrados)==0 && count($modelo_vehiculo_encontrados)>0){
                $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_modelo_vehiculo',$modelo_vehiculo_encontrados)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();
            }

            if(count($marca_vehiculo_encontrados)>0 && count($modelo_vehiculo_encontrados)==0){
                $apli=similar::select('similares.id_repuestos')
                            ->wherein('similares.id_marca_vehiculo',$marca_vehiculo_encontrados)
                            ->where('similares.activo',1)
                            ->get()
                            ->toArray();
            }

            if(count($marca_vehiculo_encontrados)==0 && count($modelo_vehiculo_encontrados)==0){

                //$encontrados=repuesto::wherein('repuestos.id_familia',$familias_encontradas)
                $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                                    ->where('repuestos.activo',1)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                                    ->get();

            }else{

                //$encontrados=repuesto::wherein('repuestos.id_familia',$familias_encontradas)
                $encontrados=repuesto::where('repuestos.id_familia',$max_id_fam)
                                    ->wherein('repuestos.id',$apli)
                                    ->where('repuestos.activo',1)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                                    ->get();

            }

        }

        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
        }

        return $resp;



    }

    public function buscar_solo_en_descrip($buscado){
        //repuestos.descripcion, repuesto->id
        $resp['resultado']=false;
        $terminos=explode(" ",$buscado);
        $encontrados=Collect();

        $sql="";
        for($i=0;$i<count($terminos);$i++){
            $sql.="(repuestos.descripcion LIKE ? OR repuestos.descripcion LIKE ? OR repuestos.descripcion LIKE ?) AND ";
        }
        $sql=$sql." repuestos.activo=?";

        $param=[];
        for($i=0;$i<count($terminos);$i++){
            $al_inicio=$terminos[$i]." %";
            $en_medio="% ".$terminos[$i]." %";
            $al_final="% ".$terminos[$i];
            array_push($param,$al_inicio);
            array_push($param,$en_medio);
            array_push($param,$al_final);
        }
        array_push($param,1);
        $encontrados=repuesto::whereRaw($sql,$param)
                        ->select('repuestos.*')
                        ->get();
        if($encontrados->count()>0){
            $resp['resultado']=true;
            $resp['repuestos']=$encontrados;
        }

        return $resp;

    }


    public function xbuscar_por_descripcion($dato){
        $op = substr($dato, 0, 1);
        $desc = substr(trim($dato), 1);
        $de = array(" de ", " DE ", " dE ", " De");
        $descripcion = str_replace($de, " ", $desc);
        $descripcion= str_replace("  "," ",$descripcion);
        $descripcion=str_replace("_&_","/",$descripcion);
        $descripcion_original=$descripcion;
        $descripcion_sin_guiones= str_replace("-","",$descripcion);
        $buscado_original=$descripcion_original;
        $buscado_sin_guiones=$descripcion_sin_guiones;
        $terminos=explode(" ",$descripcion);

        $repuestos=Collect(); //Colección que juntará todos los resultados a entregar

        $nt=count($terminos); // número de términos

        if($nt==1){
            //repuestos.codigo_interno

            $encontrados = repuesto::where('repuestos.codigo_interno', 'LIKE','%'.$buscado_original.'%')
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
            if($encontrados->count()>0){

                //$repuestos=$repuestos->merge($encontrados);
                $repuestos=$this->revisar($repuestos,$encontrados);
            }

            //repuestos.cod_repuesto_proveedor

            $encontrados = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$buscado_original . '%')
                ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
            if($encontrados->count()>0){

                $repuestos=$this->revisar($repuestos,$encontrados);
            }


            //repuestos_fabricantes.codigo_fab

            $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado_original. '%')
                                ->where('repuestos.activo',1)
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                ->get();
            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }else{
                $guion="-";
                $nf=strpos($buscado_original,$guion);
                if($nf===false){ //no hay guión
                    for($i=1;$i<strlen($buscado_original);$i++){
                        $buskado = substr_replace($buscado_original, $guion, $i, 0);
                        $numfil=fabricante::where('codigo_fab','LIKE','%'.$buskado.'%')
                                            ->count();
                        if($numfil>0){
                            $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buskado. '%')
                                    ->where('repuestos.activo',1)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                    ->get();
                            if($encontrados->count()>0){

                                $repuestos=$this->revisar($repuestos,$encontrados);
                            }
                            break;
                        }
                    }

                }else{ //hay guión
                    $encontrados = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado_sin_guiones. '%')
                                    ->where('repuestos.activo',1)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                                    ->get();
                    if($encontrados->count()>0){
                        $repuestos=$this->revisar($repuestos,$encontrados);
                    }
                }

            }

            //FALTA: nombre de fabricante



            //oems.codigo_oem

            $encontrados = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_original . '%')
                                ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guiones. '%')
                                ->where('repuestos.activo',1)
                                ->join('paises', 'repuestos.id_pais', 'paises.id')
                                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                                ->groupBy('repuestos.id')
                                ->get();
            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }

            //familias.nombrefamilia

            $fam = familia::select('id')
                ->where("familias.nombrefamilia","LIKE","%".$buscado_original."%")
                ->get()
                ->toArray();
            $encontrados = repuesto::where('repuestos.activo',1)
                                    ->wherein('repuestos.id_familia', $fam)
                                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                                    ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                                    ->get();
            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }

            //modelovehiculos.modelonombre
            $modelos = modelovehiculo::select('id')
                        ->where('modelonombre', 'LIKE', '%'.$buscado_original. '%')
                        ->get()
                        ->toArray();
            $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_modelo_vehiculo', $modelos)
                        ->get()
                        ->toArray();
            $encontrados = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->where('repuestos.activo',1)
                ->wherein('repuestos.id', $aplicaciones)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->orderBy('repuestos.descripcion')
                ->get();
            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }

            //repuestos.descripcion
            $encontrados = repuesto::where('repuestos.descripcion', 'LIKE', '%'.$buscado_original . '%')
                ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }

            //FALTA: marca de vehículo



        } // fin 1 término

        if($nt==2){
            //familias.nombrefamilia (hay hasta 4 términos)
            $sql="";
            for($i=0;$i<count($terminos);$i++){
                if($i==count($terminos)-1){
                    $sql.="familias.nombrefamilia LIKE ?";
                }else{
                    $sql.="familias.nombrefamilia LIKE ? AND ";
                }
            }

            $param=[];
            for($i=0;$i<count($terminos);$i++){
                $buscado="%".$terminos[$i]."%";
                array_push($param,$buscado);
            }
            array_push($param,1);
            $fam = familia::select('id')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();

            $encontrados = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->wherein('repuestos.id_familia', $fam)
                    ->where('repuestos.activo',1)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->orderBy('repuestos.descripcion')
                    ->get();


            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }



            //modelovehiculos.modelonombre
            $sql="";
            for($i=0;$i<count($terminos);$i++){
                if($i==count($terminos)-1){
                    $sql.="modelovehiculos.modelonombre LIKE ?";
                }else{
                    $sql.="modelovehiculos.modelonombre LIKE ? AND ";
                }
            }
            $modelos = modelovehiculo::select('id')
                    ->whereRaw($sql,$param) //->toSql();
                    ->get()
                    ->toArray();

            $aplicaciones = similar::select('id_repuestos')
                    ->wherein('id_modelo_vehiculo', $modelos)
                    ->get()
                    ->toArray();

            $encontrados = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->wherein('repuestos.id', $aplicaciones)
                    ->where('repuestos.activo',1)
                    ->join('paises', 'repuestos.id_pais', 'paises.id')
                    ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                    ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                    ->orderBy('repuestos.descripcion')
                    ->get();


            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }


            //repuestos.descripcion
            $sql="";
            //Buscar cada término en descripcion
            for($i=0;$i<count($terminos);$i++){
                $sql.="repuestos.descripcion LIKE ? AND ";
            }
            $sql=$sql." repuestos.activo=?";
            array_push($param,1);

            $encontrados = repuesto::whereRaw($sql,$param)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                            ->get();

            if($encontrados->count()>0){
                $repuestos=$this->revisar($repuestos,$encontrados);
            }

            //Combinaciones de 2 términos??
            //por ejm nombre fabricante y código del fabricante "mando mph45" -> PAF59

        } // fin 2 términos

        if($nt>2){
            //familias.nombrefamilia (hay hasta 4 términos)


            //modelovehiculos.modelonombre

            //repuestos.descripcion
        }

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();

        //Mandamos array con los id_repuestos que tengan fotos
        $tienen_foto=repuestofoto::select('id_repuestos')
                                    ->distinct()
                                    ->get()
                                    ->toArray();
        $desde = 'd';
        $criterio="nuevo algoritmo"; //$q;
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto'))->render();
        return $v;

/* ******************************************** */

        $sql1="";
        //Buscar cada término en descripcion
        for($i=0;$i<count($terminos);$i++){
            $sql1.="repuestos.descripcion LIKE ? AND ";
        }
        $sql1=$sql1." repuestos.activo=?";
        $param=[];
        for($i=0;$i<count($terminos);$i++){
            $buscado="%".$terminos[$i]."%";
            array_push($param,$buscado);
        }
        array_push($param,1);

        $encontrados = repuesto::whereRaw($sql1,$param)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();

        if($encontrados->count()>0){
            $repuestos=$repuestos->merge($encontrados);//$this->revisar($repuestos,$encontrados);
        }

        //Buscar en familia
        $sql="";
        for($i=0;$i<count($terminos);$i++){
            if($i==count($terminos)-1){
                $sql.="familias.nombrefamilia LIKE ?";
            }else{
                $sql.="familias.nombrefamilia LIKE ? OR ";
            }
        }

        $fam = familia::select('id')
                ->whereRaw($sql,$param)
                ->get()
                ->toArray();

        $sql="";
        for($i=0;$i<count($terminos);$i++){
            if($i==count($terminos)-1){
                $sql.="modelovehiculos.modelonombre LIKE ?";
            }else{
                $sql.="modelovehiculos.modelonombre LIKE ? OR ";
            }
        }

        $modelos = modelovehiculo::select('id')
                ->whereRaw($sql,$param)
                ->get()
                ->toArray();

        $aplicaciones = similar::select('id_repuestos')
                ->wherein('id_modelo_vehiculo', $modelos)
                ->get()
                ->toArray();

        $encontrados = repuesto::where('repuestos.activo',1)
            ->wherein('repuestos.id_familia', $fam)
            ->wherein('repuestos.id', $aplicaciones)
            ->join('paises', 'repuestos.id_pais', 'paises.id')
            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
            ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
            ->get();

        if($encontrados->count()>0){
            $repuestos=$this->revisar($repuestos,$encontrados);
        }



/*
        //Buscar en familia
        $sql="";
        for($i=0;$i<count($terminos);$i++){
            if($i==count($terminos)-1){
                $sql.="familias.nombrefamilia LIKE ?";
            }else{
                $sql.="familias.nombrefamilia LIKE ? OR ";
            }
        }

        $fam = familia::select('id')
                ->whereRaw($sql,$param)
                ->get()
                ->toArray();


        $encontrados = repuesto::where('repuestos.activo',1)
            ->wherein('repuestos.id_familia', $fam)
            ->join('paises', 'repuestos.id_pais', 'paises.id')
            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
            ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
            ->get();

        if($encontrados->count()>0){
            $repuestos=$repuestos->merge($encontrados);
        }

        */
        //Buscar en oem

        //Buscar en fabricantes

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();

        //Mandamos array con los id_repuestos que tengan fotos
        $tienen_foto=repuestofoto::select('id_repuestos')
                                    ->distinct()
                                    ->get()
                                    ->toArray();
        $desde = 'd';
        $criterio=""; //$q;
        //if($q=="codigo_oem") $desde="o";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto'))->render();
        return $v;
    }

    public function bbuscar_por_descripcion($dato) //original
    {
        //usando auth en el grupo de rutas. ver web.php
        $op = substr($dato, 0, 1);
        $desc = substr(trim($dato), 1);
        $de = array(" de ", " DE ", " dE ", " De ");
        $descripcion = str_replace($de, " ", $desc);
        $descripcion_original=$descripcion;
        $descripcion= str_replace("-","",$descripcion);
        $descripcion=str_replace("_&_","/",$descripcion);
        $descripcion_original=str_replace("_&_","/",$descripcion_original);
        $q = "nadita"; // Es el criterio de búsqueda discernido según el ingreso de texto del usuario en 1,2 ó 3 términos
        $numfil=0;
        $encontré=false;
        $repuestos=repuesto::where('id','fifi')->get(); //Para que devuelva un resultado vacio si no encuentra nada en ninguno de los algoritmos.
        //En este caso no es necesaria la familia para las búsquedas, entonces mas abajo (al terminar switch) se busca $fam en base a $fa
        //y al ponerle "nada de nada" no va a encontrar nada y no afectará el correr del algoritmo.
        $fa="nada de nada";
        $hay_fab=0;
        $fab_encontrado="";
        /* 16set2020: OJO: Podría mejarse si los resultados de las búsquedas se van agregando... es decir,
        primero busca por descripción y encuentra 10 resultados, (digamos res1)
        luego entra a la búsqueda por términos y en 2 términos encuentra marca_modelo otros 5 resultados... (res2)
        ENTONCES:
        resultado_total=res1+res2... y así sucesivamente...
        Entonces la pregunta sería ... en res1 tienes la colección "repuestos" y en res2 también, por lo que se sobreescribiría...
        como impedir ello y permitir agregar a la colección LARAVEL actual, la recién encontrada...

        */

        //Primero buscar en la descripción, si no hay luego en lo demás...

        $buscado=trim($descripcion);
        $buscado_original=$descripcion_original;

        //BUSQUEDA POR DESCRIPCION PRIMERO

        $sql1="";
        $repuestos=Collect();

        //Buscar cada término en descripcion
        $terminos=explode(" ",$descripcion_original);
        for($i=0;$i<count($terminos);$i++){
            $sql1.="repuestos.descripcion LIKE ? AND ";
        }
        $sql1=$sql1." repuestos.activo=?";
        $param=[];
        $busc="";
        for($i=0;$i<count($terminos);$i++){
            $busc="%".$terminos[$i]."%";
            array_push($param,$busc);
        }
        array_push($param,1);

        $encontrados = repuesto::whereRaw($sql1,$param)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();

        if($encontrados->count()>0){
            $repuestos=$repuestos->merge($encontrados);//$this->revisar($repuestos,$encontrados);
            $q="descripcion";
        }

        if($repuestos->count()>0) $encontré=true;

        if(!$encontré){
            $d = explode(' ', $descripcion);
            //Máximo 4 términos (partes) ejm filtro aceite mazda demio
            $num_t = count($d);
            switch ($num_t)
            {
                case 1:
                    /* #region Cantidad de términos de búsqueda 1*/

                    $buscado= trim($d[0]);
                    /*
                    Como es sólo un término de búsqueda, puedo simplicar para que busque en el siguiente orden:
                    1° Por código interno (pancho repuestos) y si no encuentra que busque
                    2° Por código de fabricante (marca de repuesto) en la tabla repuestos_fabricantes y si no encuentra que busque
                    3° Por código de proveedor y si no encuentra que busque
                    4° Por código OEM y si no encuentra que busque
                    5° Por medidas
                    6° En la descripción
                    */

                    //Por familia: Creo que traería demasiados resultados y sería muy lento, pero si después quiere, lo hago.

                    //Por código interno pancho repuestos
                    $numfil=repuesto::where('codigo_interno',$buscado)
                    ->where('repuestos.activo',1)
                    ->count();

                    if($numfil>0)
                    {
                        $encontré=true;
                        $q="codigo_interno";
                    }

                    //Por código de fabricante (marca de repuesto)
                    if(!$encontré)
                    {
                        $numfil=fabricante::where('codigo_fab','LIKE','%'.$buscado.'%')
                                        ->orWhere('codigo_fab','LIKE','%'.$buscado_original.'%')
                                        ->count();
                        if($numfil>0)
                        {
                            $encontré=true;
                            $q="codigo_fabricante";
                        }
                    }

                    $guion="-";
                    if(!$encontré)
                    {
                        $nf=strpos($buscado_original,$guion);
                        if($nf===false){ //no hay guión
                            for($i=1;$i<strlen($buscado_original);$i++){
                                $buskado = substr_replace($buscado_original, $guion, $i, 0);
                                $numfil=fabricante::where('codigo_fab','LIKE','%'.$buskado.'%')
                                                    ->count();
                                if($numfil>0){
                                    $encontré=true;
                                    $q="codigo_fabricante";
                                    $buscado=$buskado;
                                    break;
                                }
                            }

                        }
                    }

                    if(!$encontré)
                    {

                        for($i=1;$i<strlen($buscado);$i++){
                            $buskado = substr_replace($buscado, $guion, $i, 0);
                            $numfil=fabricante::where('codigo_fab','LIKE','%'.$buskado.'%')
                                                ->count();
                            if($numfil>0){
                                $encontré=true;
                                $q="codigo_fabricante";
                                $buscado=$buskado;
                                break;
                            }
                        }
                    }

                    //Por código de proveedor
                    if(!$encontré)
                    {
                        $numfil=repuesto::where('cod_repuesto_proveedor', 'LIKE', $buscado_original. '%')
                        ->where('repuestos.activo',1)
                        ->count();

                        if($numfil>0)
                        {
                            $encontré=true;
                            $q="codigo_proveedor";
                            $buscado=$buscado_original;
                        }
                    }

                    //Por código OEM
                    if(!$encontré)
                    {
                        $pos=strpos($buscado,"-");
                        if($pos===false)
                        {
                            $buscado_sin_guion=$buscado;
                            $buscado_con_guion=substr($buscado,0,5)."-".substr($buscado,5);
                        }else{
                            $buscado_sin_guion=str_replace("-","",$buscado); //quitar guion
                            $buscado_con_guion=$buscado;
                        }

                        $numfil=oem::where('codigo_oem', 'LIKE', $buscado_sin_guion. '%')
                                              ->orWhere('codigo_oem', 'LIKE', $buscado_con_guion. '%')
                                              ->count();
                        if($numfil>0)
                        {
                            $encontré=true;
                            $q="codigo_oem";
                        }
                    }

                    //Por código medidas
                    if(!$encontré)
                    {
                        $numfil=repuesto::where('repuestos.medidas', 'LIKE', $buscado. '%')
                        ->where('repuestos.activo',1)
                        ->count();

                        if($numfil>0)
                        {
                            $encontré=true;
                            $q="medida";
                        }
                    }

                    break;
                    /* #endregion */
                case 2:
                    /* #region Cantidad de términos de búsqueda 2*/

                    //caso fam fam
                    $fa =trim($d[0]) . " " . trim($d[1]);

                    $num_f = familia::where('nombrefamilia', 'LIKE', '%'.$fa . '%')->count();

                    if ($num_f > 0) //Hay familia con todo el término buscado
                    {
                        $encontré=true;
                        $q="fam_fam";
                    }

                    //caso fam marca_veh
                    if(!$encontré)
                    {
                        $fa = trim($d[0]); //familia
                        $num_f = familia::where('nombrefamilia', 'LIKE', '%'.$fa . '%')->count();
                        if ($num_f > 0) //encontró familia con el primer término, buscamos como marca vehiculo
                        {
                            $mav=trim($d[1]); //marca vehiculo
                            $num_mav = marcavehiculo::where('marcanombre', 'LIKE', '%'.$mav . '%')->count();
                            if ($num_mav > 0)
                            {
                                $encontré=true;
                                $q = "fam-marcaveh";
                            }
                        }
                    }

                    //caso fam modelo
                    if(!$encontré)
                    {
                        $num_f = familia::where('nombrefamilia', 'LIKE', '%'.$fa . '%')->count();
                        $mod = trim($d[1]); //modelo vehiculo
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        if ($num_f>0 && $num_mod > 0)
                        {
                            $encontré=true;
                            $q = "fam-modelo";
                        }

                    }

                    //caso fam marcarep
                    if(!$encontré)
                    {
                        $mar = trim($d[1]); //marca repuesto
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', '%'.$mar . '%')->count();
                        if ($num_f>0 && $num_mar > 0)
                        {
                            $encontré=true;
                            $q = "fam-marcarep";
                        }
                    }

                    //caso marcaveh marcaveh
                    if(!$encontré)
                    {
                        $mav=trim($d[0])." ".trim($d[1]); //marca vehiculo
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', '%'.$mav . '%')->count();
                        if ($num_mav > 0)
                        {
                            $encontré=true;
                            $q = "marcaveh-marcaveh";
                        }
                    }

                    if(!$encontré) //marcaveh modeloveh
                    {
                        $mav=trim($d[0]); //marca vehiculo
                        $mod=trim($d[1]); //modelo vehiculo
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', '%'.$mav . '%')->count();
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        if ($num_mav > 0 && $num_mod>0)
                        {
                            $encontré=true;
                            $q='marca_y_modelo_veh_sin_fam';
                        }
                    }

                    if(!$encontré) //modeloveh modeloveh
                    {
                        $mod=trim($d[0])." ".trim($d[1]); //modelo vehiculo
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        if ($num_mod>0)
                        {
                            $encontré=true;
                            $q='mod_mod_sin_fam';
                        }
                    }

                    if(!$encontré)
                    {
                        $mod=trim($d[0]); //modelo vehiculo
                        $mar=trim($d[1]); //marca repuesto
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', '%'.$mar . '%')->count();
                        if ($num_mod>0 && $num_mar>0)
                        {
                            $encontré=true;
                            $q='modeloveh_y_marcarep_sin_fam';
                        }
                    }

                    break;
                    /* #endregion */
                case 3:
                    /* #region Cantidad de términos de búsqueda 3*/

                    //caso fam fam marcaveh
                    if(!$encontré)
                    {
                        $fa = trim($d[0])." ".trim($d[1]); //familia
                        $num_f = familia::where('nombrefamilia', 'LIKE', '%'.$fa . '%')->count();
                        $mav = trim($d[2]); //marca veh
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', '%'.$mav . '%')->count();
                        if ($num_f > 0 && $num_mav > 0)
                        {
                            $encontré=true;
                            $q='fam_fam_marcaveh';
                        }
                    }

                    //Caso fam fam modeloveh
                    if(!$encontré)
                    {
                        $fa = trim($d[0])." ".trim($d[1]); //familia
                        $num_f = familia::where('nombrefamilia', 'LIKE','%'. $fa . '%')->count();
                        $mod = trim($d[2]); //modelo veh
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        if ($num_f > 0 && $num_mod > 0)
                        {
                            $encontré=true;
                            $q='fam_fam_modeloveh';
                        }
                    }


                    //caso fam fam marca_rep
                    if(!$encontré)
                    {
                        $fa = trim($d[0])." ".trim($d[1]); //familia
                        $num_f = familia::where('nombrefamilia', 'LIKE', '%'.$fa . '%')->count();
                        $mar = trim($d[2]); //marca repuesto
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', '%'.$mar . '%')->count();
                        if ($num_f>0 && $num_mar > 0)
                        {
                            $encontré=true;
                            $q = "fam-fam-marcarep";
                        }
                    }


                    //Caso fam marcaveh marcaveh
                    if(!$encontré)
                    {
                        $fa = trim($d[0]); //familia
                        $num_f = familia::where('nombrefamilia', 'LIKE', '%'.$fa . '%')->count();
                        $mav = trim($d[1])." ".trim($d[2]); //marca veh
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', '%'.$mav . '%')->count();
                        if ($num_f > 0 && $num_mav > 0)
                        {
                            $encontré=true;
                            $q='fam_marcaveh_marcaveh';
                        }
                    }

                    if(!$encontré)
                    {
                        //Fórmula: fam marcaveh modeloveh
                        $mav = trim($d[1]); //marca veh
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', '%'.$mav . '%')->count();
                        $mod=trim($d[2]); //modelo veh
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        if ($num_f > 0 && $num_mav > 0 && $num_mod>0)
                        {
                            $encontré=true;
                            $q='fam_marcaveh_modeloveh';
                        }
                    }

                    if(!$encontré)
                    {
                        //Fórmula: fam marcaveh marcarep
                        $mar=trim($d[2]); //marca rep
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', '%'.$mar . '%')->count();
                        if ($num_f > 0 && $num_mav > 0 && $num_mar>0)
                        {
                            $encontré=true;
                            $q='fam_marcaveh_marcarep';
                        }
                    }

                    if(!$encontré)
                    {
                        //Fórmula: fam modveh modveh
                        $mod=trim($d[1])." ".trim($d[2]); //modelo veh
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        if ($num_f > 0 && $num_mod>0)
                        {
                            $encontré=true;
                            $q='fam-modelo-modelo';
                        }
                    }

                    if(!$encontré)
                    {
                        //Fórmula: fam modveh marcarep
                        $mod=trim($d[1]); //modelo veh
                        $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod . '%')->count();
                        $mar=trim($d[2]); //marca rep
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', '%'.$mar . '%')->count();
                        if ($num_f > 0 && $num_mod>0 && $num_mar>0)
                        {
                            $encontré=true;
                            $q='fam_modeloveh_marcarep';
                        }
                    }

                    if(!$encontré)
                    {
                        //return $fam;
                        //Fórmula: fam marcarep marcarep "fabricas chinas / valeo phc"
                        $mar=trim($d[1])." ".trim($d[2]); //marca rep
                        $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', '%'.$mar . '%')->count();
                        if ($num_f > 0 && $num_mar>0)
                        {
                            $encontré=true;
                            $q='fam-marcarep-marcarep';
                        }
                    }

                    break;
                    /* #endregion */
                case 4:
                    /* #region Cantidad de términos de búsqueda 4  */
                    //23ENE2020 FALTA INCLUIR BUSQUEDA POR MARCA DE VEHICULO
                    //los dos primeros es la familia y los dos últimos son  marcaveh o modeloveh o marcarep
                    $fa = trim($d[0])." ".trim($d[1]); //familia
                    $num_f = familia::where('nombrefamilia', 'LIKE', '%'.$fa . '%')->count();
                    if ($num_f > 0) //encontró familia con el primer término, buscamos como modelo o marca el 2do término
                    {
                        $mav = trim($d[2])." ".trim($d[3]); //marcaveh
                        $num_mav = marcavehiculo::where('marcanombre', 'LIKE', '%'.$mav. '%')->count();
                        if ($num_mav > 0) {
                            $q = "marcaveh";
                         } else { //No hay por marcaveh, buscamos por modeloveh o marca de repuesto
                            $mod = trim($d[2])." ".trim($d[3]); //modelo
                            $num_mod = modelovehiculo::where('modelonombre', 'LIKE', '%'.$mod. '%')->count();
                            if ($num_mod > 0) {
                                $q = "modelo";
                            } else { //No hay por modelo, buscamos por marca de repuesto
                                $mar = trim($d[2])." ".trim($d[3]); //marca repuesto
                                $num_mar = marcarepuesto::where('marcarepuesto', 'LIKE', '%'.$mar . '%')->count();
                                if ($num_mar > 0) $q = "marcarep";
                            }
                        }
                    } // si no encuentra no interesa... resultado NO HAY
                    break;
                    /* #endregion */
                default: //aquí cuando escribe más de 4 términos que busque sólo en descripción kakita
                $buscado=trim($descripcion);
                $q="descripción";

            }

            if(!$encontré){
                $buscado=trim($descripcion);
                $q="descripción";
            }

            $fam = familia::select('id')
                ->where('nombrefamilia', 'LIKE', '%'.$fa . '%')
                ->get()
                ->toArray();

            //OBTENEMOS LOS DATOS REQUERIDOS SEGÚN LAS BÚSQUEDAS PREVIAS

            /* #region buscar por código interno un término*/
            if($q=='codigo_interno')
            {
                //return $op." codi inter: ***".$buscado."***";
                if ($op == 0) {
                    //return $op." codi inter: ***".$buscado."***";
                    $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE','%'.$buscado.'%')
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE', '%'.$buscado . '%')
                        ->where('repuestos.activo',1)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE', '%'.$buscado . '%')
                        ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::where('repuestos.codigo_interno', 'LIKE', '%'.$buscado . '%')
                        ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }
            }

            /* #endregion */

            /* #region buscar por fabricante (marca repuesto) un término*/
            if($q=="codigo_fabricante")
            {

                if ($op == 0) {
                    $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado. '%')
                        ->orWhere('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado_original. '%')
                        ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado. '%')
                        ->where('repuestos.activo',1)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado. '%')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$buscado. '%')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }
            }


            /* #endregion */

            /* #region buscar por proveedor un término */
            if($q=='codigo_proveedor')
            {
                if ($op == 0) {
                    $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$buscado . '%')
                    ->where('repuestos.activo',1)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$buscado. '%')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$buscado. '%')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$buscado. '%')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }
            }

            /* #endregion */

            /* #region buscar por oem un término */
            if($q=="codigo_oem")
            {
                $pos=strpos($buscado,"-");
                if($pos===false)
                {
                    $buscado_sin_guion=$buscado;
                    $buscado_con_guion=substr($buscado,0,5)."-".substr($buscado,5);
                }else{
                    $buscado_sin_guion=str_replace("-","",$buscado); //quitar guion
                    $buscado_con_guion=$buscado;
                }



                if ($op == 0) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                        ->where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion. '%')
                        ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                        ->where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion. '%')
                        ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                        ->where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion. '%')
                        ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                        ->where('repuestos.stock_actual', '>', 0)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                    ->where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion. '%')
                        ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                        ->groupBy('repuestos.id')
                        ->get();
                }
            }

            /* #endregion */

            /* #region buscar por medida un término */
            if($q=='medida')
            {
                if ($op == 0) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                    ->where('repuestos.medidas', 'LIKE', '%'.$buscado . '%')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                    ->where('repuestos.medidas', 'LIKE', '%'.$buscado . '%')
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::where('repuestos.activo',1)
                    ->where('repuestos.medidas', 'LIKE', '%'.$buscado . '%')
                        ->where('repuestos.stock_actual', '>', 0)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::where('repuestos.medidas', 'LIKE', '%'.$buscado . '%')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();
                }
            }

            /* #endregion */

            /* #region buscar por familia familia 2 términos */
            if($q=="fam_fam")
            {
                $fam = familia::select('id')
                ->where('nombrefamilia', 'LIKE', '%'.$fa . '%')
                ->get()
                ->toArray();

                if ($op == 0)
                {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->wherein('repuestos.id_familia', $fam)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 1)
                {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->wherein('repuestos.id_familia', $fam)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 2)
                {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->wherein('repuestos.id_familia', $fam)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 3)
                {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->wherein('repuestos.id_familia', $fam)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }
            }

            /* #endregion */

            /* #region buscar por marcaveh marcaveh 2 términos */
            if ($q == 'marcaveh-marcaveh')
                {
                    $marcasveh = marcavehiculo::select('idmarcavehiculo')
                        ->where('marcanombre', 'LIKE', '%'.$mav . '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_marca_vehiculo', $marcasveh)
                        ->get()
                        ->toArray();

                    if ($op == 0) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }


            /* #endregion */

                /* #region  buscar por modeloveh modeloveh sin fam 2 términos*/
                if ($q == 'mod_mod_sin_fam')
                {
                    $modelos = modelovehiculo::select('id')
                        ->where('modelonombre', 'LIKE', '%'.$mod. '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_modelo_vehiculo', $modelos)
                        ->get()
                        ->toArray();

                    if ($op == 0) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }
                /* #endregion */

            /* #region buscar por modeloveh y marcarepuesto 2 términos*/
            if($q=='modeloveh_y_marcarep_sin_fam')
            {
                $modelosveh = modelovehiculo::select('id')
                    ->where('modelonombre', 'LIKE', '%'.$mod . '%')
                    ->get()
                    ->toArray();

                $aplicaciones = similar::select('id_repuestos')
                    ->wherein('id_modelo_vehiculo', $modelosveh)
                    ->get()
                    ->toArray();

                $marcasrep=marcarepuesto::select('id')
                                                ->where('marcarepuesto','LIKE','%'.$mar.'%')
                                                ->get()
                                                ->toArray();

                if ($op == 0) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }
            }
            /* #endregion */

            /* #region  buscar por marca y modelo de vehiculo sin familia 2 términos*/
            if ($q == 'marca_y_modelo_veh_sin_fam')
            {
                $marcasveh = marcavehiculo::select('idmarcavehiculo')
                    ->where('marcanombre', 'LIKE', '%'.$mav . '%')
                    ->get()
                    ->toArray();

                $modelosveh = modelovehiculo::select('id')
                    ->where('modelonombre', 'LIKE', '%'.$mod . '%')
                    ->get()
                    ->toArray();

                $aplicaciones = similar::select('id_repuestos')
                    ->wherein('id_marca_vehiculo', $marcasveh)
                    ->wherein('id_modelo_vehiculo', $modelosveh)
                    ->get()
                    ->toArray();

                if ($op == 0) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 1) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 2) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }

                if ($op == 3) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->where('repuestos.stock_actual', '>', 0)
                        ->where('repuestos.medidas', '<>', 'No Definidas')
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }
            }
            /* #endregion */

            /* #region buscar por familia y aplicación 2 términos*/
            //BUZCAR: anillo d4bb / metal biela porter
            if($q=="fam_apli" && false)
            {

                $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_marca_vehiculo', $marcasveh)
                        ->get()
                        ->toArray();

                if ($op == 0 || $op==4) {
                    $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                    ->where('repuestos.activo',1)
                        ->wherein('repuestos.id_familia', $fam)
                        ->wherein('repuestos.id', $aplicaciones)
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->orderBy('repuestos.descripcion')
                        ->get();
                }
            }

            /* #endregion*/

                /* #region  buscar por fam marcaveh marcaveh o fam fam marcaveh 2 y 3 términos*/
                if ($q == 'fam_marcaveh_marcaveh' || $q == 'fam_fam_marcaveh' || $q=='fam-marcaveh')
                {
                    $marcasveh = marcavehiculo::select('idmarcavehiculo')
                        ->where('marcanombre', 'LIKE', '%'.$mav . '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_marca_vehiculo', $marcasveh)
                        ->get()
                        ->toArray();

                    if ($op == 0 || $op==4) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1 || $op==5) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2 || $op==6) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3 || $op==7) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }
                /* #endregion */

                /* #region  buscar por fam marca y modelo de vehiculo 3 términos*/
                if ($q == 'fam_marcaveh_modeloveh')
                {
                    $marcasveh = marcavehiculo::select('idmarcavehiculo')
                        ->where('marcanombre', 'LIKE', '%'.$mav . '%')
                        ->get()
                        ->toArray();

                    $modelosveh = modelovehiculo::select('id')
                        ->where('modelonombre', 'LIKE', '%'.$mod . '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_marca_vehiculo', $marcasveh)
                        ->wherein('id_modelo_vehiculo', $modelosveh)
                        ->get()
                        ->toArray();

                    if ($op == 0 || $op==4) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1 || $op==5) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2 || $op==6) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3 || $op==7) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }
                /* #endregion */

                /* #region buscar por fam marcaveh y marcarepuesto 3 términos*/
                if($q=='fam_marcaveh_marcarep')
                {
                    $marcasveh = marcavehiculo::select('idmarcavehiculo')
                        ->where('marcanombre', 'LIKE', '%'.$mav . '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_marca_vehiculo', $marcasveh)
                        ->get()
                        ->toArray();

                    $marcasrep=marcarepuesto::select('id')
                                                    ->where('marcarepuesto','LIKE','%'.$mar.'%')
                                                    ->get()
                                                    ->toArray();

                    if ($op == 0 || $op==4) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1 || $op==5) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2 || $op==6) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3 || $op==7) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }
                /* #endregion */

                /* #region buscar por fam modeloveh y marcarepuesto 3 términos*/
                if($q=='fam_modeloveh_marcarep')
                {
                    $modelosveh = modelovehiculo::select('id')
                        ->where('modelonombre', 'LIKE', '%'.$mod . '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_modelo_vehiculo', $modelosveh)
                        ->get()
                        ->toArray();

                    $marcasrep=marcarepuesto::select('id')
                                                    ->where('marcarepuesto','LIKE','%'.$mar.'%')
                                                    ->get()
                                                    ->toArray();

                    if ($op == 0 || $op==4) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1 || $op==5) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2 || $op==6) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3 || $op==7) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }
                /* #endregion */

                /* #region  buscar por fam modelo modelo vehiculo o fam fam modveh 2 y 3 términos*/
                if ($q == 'fam-modelo-modelo' || $q == 'fam_fam_modeloveh' || $q=='fam-modelo')
                {
                    $modelos = modelovehiculo::select('id')
                        ->where('modelonombre', 'LIKE', '%'.$mod. '%')
                        ->get()
                        ->toArray();

                    $aplicaciones = similar::select('id_repuestos')
                        ->wherein('id_modelo_vehiculo', $modelos)
                        ->get()
                        ->toArray();

                    if ($op == 0) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id', $aplicaciones)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }
                /* #endregion */

                /* #region buscar por fam marcarep marcarep o fam fam marcarep 2 y 3 términos*/
                if($q=='fam-marcarep-marcarep' || $q=='fam-fam-marcarep' || $q=="fam-marcarep")
                {
                    $marcasrep=marcarepuesto::select('id')
                                                    ->where('marcarepuesto','LIKE','%'.$mar.'%')
                                                    ->get()
                                                    ->toArray();

                    if ($op == 0) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 1) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 2) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }

                    if ($op == 3) {
                        $repuestos = repuesto::select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->where('repuestos.activo',1)
                            ->where('repuestos.stock_actual', '>', 0)
                            ->where('repuestos.medidas', '<>', 'No Definidas')
                            ->wherein('repuestos.id_familia', $fam)
                            ->wherein('repuestos.id_marca_repuesto', $marcasrep)
                            ->join('paises', 'repuestos.id_pais', 'paises.id')
                            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                            ->orderBy('repuestos.descripcion')
                            ->get();
                    }
                }
                /* #endregion */

        } //fin de buscar primero solo por descripción... no encontré

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();

        //Mandamos array con los id_repuestos que tengan fotos
        $tienen_foto=repuestofoto::select('id_repuestos')
                                                    ->distinct()
                                                    ->get()
                                                    ->toArray();
        $desde = 'd';
        $criterio=$q;
        if($q=="codigo_oem") $desde="o";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto'))->render();
        return $v;

    } // fin de buscar_por_descripcion

    public function buscar_por_codigo_proveedor($dato)
    {
        //usando auth en el grupo de rutas. ver web.php

        $op = substr($dato, 0, 1);
        $codigo_proveedor = substr($dato, 1);
        if ($op == 0) {
            $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$codigo_proveedor . '%')
            ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$codigo_proveedor . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$codigo_proveedor . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$codigo_proveedor . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

         //Mandamos array con los id_repuestos que tengan fotos
         $tienen_foto=repuestofoto::select('id_repuestos')
         ->distinct()
         ->get()
         ->toArray();

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();
        $desde = 'p';
        $criterio="por_codigo_proveedor";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto'))->render();
        return $v;
    }

    public function buscar_por_oem($dato)
    {
        //usando auth en el grupo de rutas. ver web.php
//SELECT repuestos.*,oems.codigo_oem FROM repuestos inner join oems on repuestos.id=oems.id_repuestos where oems.codigo_oem like '51720-29%' group by repuestos.id
        $op = substr($dato, 0, 1);
        $buscado = substr($dato, 1);
        $pos=strpos($buscado,"-");
        if($pos===false)
        {
            $buscado_sin_guion=$buscado;
            $buscado_con_guion=substr($buscado,0,5)."-".substr($buscado,5);
        }else{
            $buscado_sin_guion=str_replace("-","",$buscado); //quitar guion
            $buscado_con_guion=$buscado;
        }
        if ($op == 0) {
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')
            ->where('repuestos.activo',1)
                ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                ->groupBy('repuestos.id')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')
            ->where('repuestos.activo',1)
                ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                ->groupBy('repuestos.id')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')
            ->where('repuestos.activo',1)
                ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                ->groupBy('repuestos.id')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::where('oems.codigo_oem', 'LIKE', '%'.$buscado_sin_guion . '%')
            ->where('repuestos.activo',1)
                ->orWhere('oems.codigo_oem', 'LIKE', '%'.$buscado_con_guion. '%')
                ->where('repuestos.stock_actual', '>', 0)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('oems', 'repuestos.id', 'oems.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'oems.codigo_oem', 'repuestos.*')
                ->groupBy('repuestos.id')
                ->get();
        }

         //Mandamos array con los id_repuestos que tengan fotos
         $tienen_foto=repuestofoto::select('id_repuestos')
         ->distinct()
         ->get()
         ->toArray();

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();
        $desde = 'o';
        $criterio="por_oem";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto'))->render();
        return $v;
    }

    public function buscar_por_codigo_fabricante($dato)
    {
        //usando auth en el grupo de rutas. ver web.php

        $op = substr($dato, 0, 1);
        $fab_original = substr($dato, 1);
        $fab=str_replace("-","",$fab_original);
        if ($op == 0) {
            $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$fab . '%')
                                ->orWhere('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$fab_original . '%')
                ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')                ->groupBy('repuestos.id')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$fab . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')
                ->groupBy('repuestos.id')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$fab . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')
                ->groupBy('repuestos.id')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::where('repuestos_fabricantes.codigo_fab', 'LIKE', '%'.$fab . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('repuestos_fabricantes', 'repuestos.id', 'repuestos_fabricantes.id_repuestos')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos_fabricantes.codigo_fab', 'repuestos.*')
                ->groupBy('repuestos.id')
                ->get();
        }

         //Mandamos array con los id_repuestos que tengan fotos
         $tienen_foto=repuestofoto::select('id_repuestos')
         ->distinct()
         ->get()
         ->toArray();

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();
        $desde = 'f';
        $criterio="por_cod_fab";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto'))->render();
        return $v;
    }

    public function buscar_por_medidas($dato)
    {
        //usando auth en el grupo de rutas. ver web.php

        $op = substr($dato, 0, 1);
        $medidas = substr($dato, 1);
        if ($op == 0) {
            $repuestos = repuesto::where('repuestos.medidas', 'LIKE', '%'.$medidas . '%')
            ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::where('repuestos.medidas', 'LIKE', '%'.$medidas . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::where('repuestos.medidas', 'LIKE', '%'.$medidas . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::where('repuestos.medidas', 'LIKE', '%'.$medidas . '%')
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

         //Mandamos array con los id_repuestos que tengan fotos
         $tienen_foto=repuestofoto::select('id_repuestos')
         ->distinct()
         ->get()
         ->toArray();

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();
        $desde = 'm';
        $criterio="por_medidas";
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde','criterio','tienen_foto'))->render();
        return $v;
    }

    public function buscar_por_codigo_interno($codint)
    {



        $r = repuesto::where('repuestos.codigo_interno',$codint)
                                ->where('activo',1)
                                ->first();

        if(!is_null($r)){
            $resp=['id_repuesto'=>$r->id,'descripcion'=>$r->descripcion];
        }else{
            $resp=['id_repuesto'=>0,'descripcion'=>'NO SE ENCONTRÓ '.$codint];
        }
        return json_encode($resp);
    }

    public function buscar_por_modelo($dato)
    {
        try {
                    //usando auth en el grupo de rutas. ver web.php

        $op = substr($dato, 0, 1);
        $idmodelo = substr($dato, 1);

        $modelo = modelovehiculo::find($idmodelo);
        $quemodelo = 'para ' . $modelo->modelonombre . ' ' . $modelo->anios_vehiculo;

        /*
        $debug=$quemodelo;
        $vv=view('errors.debug_ajax',compact('debug'))->render();
        return $vv;
         */

        $aplicaciones = similar::select('id_repuestos')
            ->where('id_modelo_vehiculo', $idmodelo)
            ->get()
            ->toArray();

        if ($op == 0) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->orderBy('repuestos.descripcion')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->orderBy('repuestos.descripcion')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->orderBy('repuestos.descripcion')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->where('repuestos.stock_actual', '>', 0)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->orderBy('repuestos.descripcion')
                ->get();
        }

        //Mandamos array con los id_repuestos que tengan fotos
        $tienen_foto=repuestofoto::select('id_repuestos')
                                                    ->distinct()
                                                    ->get()
                                                    ->toArray();

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();
        $desde = 'm';
        $criterio="por_modelo";

        $arreglo = [];

        foreach ($repuestos as $repuesto) {
            $oferta = oferta_pagina_web::where('id_repuesto',$repuesto->id)->where('activo',1)->first();
            $familia_dcto = descuento::where('id_familia', $repuesto->id_familia)->where('activo',1)->first();
                if(isset($oferta)){
                    $repuesto->precio_venta = $oferta->precio_actualizado;
                }elseif(!isset($oferta) && isset($familia_dcto)){

                    $repuesto->precio_venta = $repuesto->precio_venta - (($familia_dcto->porcentaje/100) * $repuesto->precio_venta);
                    $repuesto->oferta = 2;
                }else{
                    $repuesto->oferta = 0;
                }
            $primera_foto_repuesto = repuestofoto::select('repuestos_fotos.urlfoto','repuestos.*','proveedores.empresa_nombre_corto','marcarepuestos.marcarepuesto','paises.nombre_pais')
                                        ->where('repuestos.id',$repuesto->id)
                                        ->join('repuestos','repuestos.id','repuestos_fotos.id_repuestos')
                                        ->join('proveedores','repuestos.id_proveedor','proveedores.id')
                                        ->join('paises','repuestos.id_pais','paises.id')
                                        ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                        ->get();
        if($primera_foto_repuesto->count() > 0){
            array_push($arreglo,$primera_foto_repuesto[0]);
        }else{
            try {
                //Si no tiene foto buscamos el repuesto con sus principales datos y le agregamos una url personalizada.
                $repuesto_ = repuesto::select('repuestos.*','proveedores.empresa_nombre_corto','marcarepuestos.marcarepuesto','paises.nombre_pais')
                                    ->where('repuestos.id',$repuesto->id)
                                    ->join('proveedores','repuestos.id_proveedor','proveedores.id')
                                    ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                    ->join('paises','repuestos.id_pais','paises.id')
                                    ->first();
                $repuesto_->urlfoto = 'fotozzz/sinfoto.png';
                array_push($arreglo,$repuesto_);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        if($repuesto->fecha_actualizacion_stock > $repuesto->fecha_actualizacion_stock_dos && $repuesto->fecha_actualizacion_stock > $repuesto->fecha_actualizacion_stock_tres){
            $fecha_ultima = $repuesto->fecha_actualizacion_stock;
            $repuesto->fecha_ultima = $fecha_ultima;
        }elseif($repuesto->fecha_actualizacion_stock_dos > $repuesto->fecha_actualizacion_stock && $repuesto->fecha_actualizacion_stock_dos > $repuesto->fecha_actualizacion_stock_tres){
                $fecha_ultima = $repuesto->fecha_actualizacion_stock_dos;
                $repuesto->fecha_ultima = $fecha_ultima;
        }else{
                $fecha_ultima = $repuesto->fecha_actualizacion_stock_tres;
                $repuesto->fecha_ultima = $fecha_ultima;
        }

    }

    $stocks_repuesto = [];

        foreach($repuestos as $repuesto){
            $stock_bodega = repuesto::select('repuestos.stock_actual','locales.local_nombre','repuestos.id','locales.id as id_local')
                                        ->join('locales', 'repuestos.local_id', 'locales.id')
                                        ->where('repuestos.id',$repuesto->id)
                                        ->where('repuestos.local_id',1)
                                        ->first();

            $stock_bodega_segunda_ubicacion = repuesto::select('repuestos.stock_actual_dos as stock_actual','locales.local_nombre','repuestos.id','locales.id as id_local')
                                        ->join('locales', 'repuestos.local_id_dos', 'locales.id')
                                        ->where('repuestos.id',$repuesto->id)
                                        ->where('repuestos.local_id_dos',1)
                                        ->first();

            $stock_tienda = repuesto::select('repuestos.stock_actual','locales.local_nombre','repuestos.id','locales.id as id_local')
                                        ->join('locales', 'repuestos.local_id', 'locales.id')
                                        ->where('repuestos.id',$repuesto->id)
                                        ->where('repuestos.local_id',3)
                                        ->first();

            $stock_tienda_segunda_ubicacion = repuesto::select('repuestos.stock_actual_dos as stock_actual','locales.local_nombre','repuestos.id','locales.id as id_local')
                                        ->join('locales', 'repuestos.local_id_dos', 'locales.id')
                                        ->where('repuestos.id',$repuesto->id)
                                        ->where('repuestos.local_id_dos',3)
                                        ->first();

            $stock_cm_tercera_ubicacion = repuesto::select('repuestos.stock_actual_tres as stock_actual','locales.local_nombre','repuestos.id','locales.id as id_local')
                                    ->join('locales', 'repuestos.local_id_tres', 'locales.id')
                                    ->where('repuestos.id',$repuesto->id)
                                    ->where('repuestos.local_id_tres',4)
                                    ->first();

            array_push($stocks_repuesto,$stock_bodega,$stock_bodega_segunda_ubicacion,$stock_tienda, $stock_tienda_segunda_ubicacion,$stock_cm_tercera_ubicacion);
        }
        $permiso_ = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/repuesto/modificar-precio')->first();
            if($permiso_){
                $permiso_modificar = true;
            }else{
                $permiso_modificar = false;
            }

            $permiso__ = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/actualizar-precio')->first();
            if($permiso__){
                $permiso_actualizar = true;
            }else{
                $permiso_actualizar = false;
            }

            foreach ($arreglo as $r) {
                $res = oferta_pagina_web::where('id_repuesto',$r->id)->where('activo',1)->first();
                if(isset($res)){
                    $r->precio_venta = $res->precio_actualizado;
                }else{
                    $r->oferta = 0;
                }
            }

        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde', 'quemodelo','criterio','tienen_foto','arreglo','stocks_repuesto','permiso_modificar','permiso_actualizar'))->render();
        return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function dame_familias_repuestos($dato)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
        $op = substr($dato, 0, 1);
        $idmodelo = substr($dato, 1);
        try {
            $modelo = modelovehiculo::find($idmodelo);
            $quemodelo =$modelo->modelonombre . ' ' . $modelo->anios_vehiculo;

            if ($op == 0) {
                $s = "SELECT repuestos.id_familia, UPPER(familias.nombrefamilia) as nombrefamilia,COUNT(repuestos.id_familia) as total
                FROM repuestos
                INNER JOIN familias ON repuestos.id_familia=familias.id
                WHERE repuestos.activo=1 AND repuestos.id in (SELECT similares.id_repuestos FROM similares WHERE similares.id_modelo_vehiculo=$idmodelo)
                GROUP BY repuestos.id_familia
                ORDER BY nombrefamilia";
            }

            if ($op == 1) {
                $s = "SELECT repuestos.id_familia, UPPER(familias.nombrefamilia) as nombrefamilia,COUNT(repuestos.id_familia) as total
                FROM repuestos
                inner join familias on repuestos.id_familia=familias.id
                WHERE repuestos.id in (SELECT similares.id_repuestos FROM similares WHERE similares.id_modelo_vehiculo=$idmodelo)
                AND repuestos.medidas<>'No Definidas'
                GROUP by repuestos.id_familia
                order by familias.nombrefamilia";
            }

            if ($op == 2) {
                $s = "SELECT repuestos.id_familia, UPPER(familias.nombrefamilia) as nombrefamilia,COUNT(repuestos.id_familia) as total FROM repuestos
                inner join familias on repuestos.id_familia=familias.id
                WHERE repuestos.id in (SELECT similares.id_repuestos FROM similares WHERE similares.id_modelo_vehiculo=$idmodelo)
                AND repuestos.stock_actual>0
                GROUP by repuestos.id_familia
                order by familias.nombrefamilia";
            }

            if ($op == 3) {
                $s = "SELECT repuestos.id_familia, UPPER(familias.nombrefamilia) as nombrefamilia,COUNT(repuestos.id_familia) as total FROM repuestos
                inner join familias on repuestos.id_familia=familias.id
                WHERE repuestos.id in (SELECT similares.id_repuestos FROM similares WHERE similares.id_modelo_vehiculo=$idmodelo)
                AND repuestos.stock_actual>0
                AND repuestos.medidas<>'No Definidas'
                GROUP by repuestos.id_familia
                order by familias.nombrefamilia";
            }

            $familias = \DB::select($s);
            $total_repuestos = 0;
            foreach ($familias as $repuesto) {
                $total_repuestos += $repuesto->total;
            }

            /*
            $debug=collect($familias)->toBase()->sum('total');
            $vv=view('errors.debug_ajax',compact('debug'))->render();
            return $vv;
             */
            //$total_repuestos=$familias->sum('total');
            $v = view('fragm.ventas_familias', compact('familias', 'dato', 'total_repuestos','quemodelo'))->render();
            return $v;

        } catch (\Exception $error) {
            $debug = $error;
            $v = view('errors.debug_ajax', compact('debug'))->render();
            return $v;
        }
    }


// Búsqueda de repuestos por familias y modelo
    public function dame_repuestos($id_familia, $dato)
    {
        try {
             //usando auth en el grupo de rutas. ver web.php //Valida sesión
        $op = substr($dato, 0, 1);
        $idmodelo = substr($dato, 1);

        $modelo = modelovehiculo::find($idmodelo);
        $quemodelo = 'para ' . $modelo->modelonombre . ' ' . $modelo->anios_vehiculo;

        $aplicaciones = similar::select('id_repuestos')
            ->where('id_modelo_vehiculo', $idmodelo)
            ->get()
            ->toArray();

        if ($op == 0) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->where('repuestos.id_familia', $id_familia)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias', 'repuestos.id_familia', 'familias.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 1) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->where('repuestos.id_familia', $id_familia)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias', 'repuestos.id_familia', 'familias.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 2) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->where('repuestos.id_familia', $id_familia)
                ->where('repuestos.stock_actual', '>', 0)
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias', 'repuestos.id_familia', 'familias.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        if ($op == 3) {
            $repuestos = repuesto::wherein('repuestos.id', $aplicaciones)
            ->where('repuestos.activo',1)
                ->where('repuestos.id_familia', $id_familia)
                ->where('repuestos.stock_actual', '>', 0)
                ->where('repuestos.medidas', '<>', 'No Definidas')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->join('familias', 'repuestos.id_familia', 'familias.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();
        }

        //Mandamos array con los id_repuestos que tengan fotos
        $tienen_foto=repuestofoto::select('id_repuestos')
        ->distinct()
        ->get()
        ->toArray();

        //enviar saldos por local: id_repuestos, id_local, saldo / locales: locales.local_nombre
        $saldos = saldo::select('saldos.id_repuestos', 'locales.local_nombre', 'saldos.id_local', 'saldos.saldo')
            ->where('saldos.saldo', '>', 0)
            ->join('locales', 'saldos.id_local', 'locales.id')
            ->get();
        $desde = 'm';
        $criterio="por_familias_y_modelos";

        $arreglo = [];

        foreach ($repuestos as $repuesto) {
            $oferta = oferta_pagina_web::where('id_repuesto',$repuesto->id)->where('activo',1)->first();
            //El idlocal 2 corresponde a ofertas para el sitio web
            $familia_dcto = descuento::where('id_familia', $repuesto->id_familia)->where('id_local','<>',2)->where('activo',1)->first();
            if(isset($oferta)){
                $repuesto->precio_venta = $oferta->precio_actualizado;
            }elseif(!isset($oferta) && isset($familia_dcto)){

                $repuesto->precio_venta = $repuesto->precio_venta - (($familia_dcto->porcentaje/100) * $repuesto->precio_venta);
                $repuesto->oferta = 2;
            }else{
                $repuesto->oferta = 0;
            }
            $primera_foto_repuesto = repuestofoto::select('repuestos_fotos.urlfoto','repuestos.*','proveedores.empresa_nombre_corto','marcarepuestos.marcarepuesto','paises.nombre_pais')
                                        ->where('repuestos.id',$repuesto->id)
                                        ->join('repuestos','repuestos.id','repuestos_fotos.id_repuestos')
                                        ->join('proveedores','repuestos.id_proveedor','proveedores.id')
                                        ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                        ->join('paises','repuestos.id_pais','paises.id')
                                        ->get();
        if($primera_foto_repuesto->count() > 0){
            array_push($arreglo,$primera_foto_repuesto[0]);
        }else{
            try {
                //Si no tiene foto buscamos el repuesto con sus principales datos y le agregamos una url personalizada.
                $repuesto_ = repuesto::select('repuestos.*','proveedores.empresa_nombre_corto','marcarepuestos.marcarepuesto','paises.nombre_pais')
                                    ->where('repuestos.id',$repuesto->id)
                                    ->join('proveedores','repuestos.id_proveedor','proveedores.id')
                                    ->join('marcarepuestos','repuestos.id_marca_repuesto','marcarepuestos.id')
                                    ->join('paises','repuestos.id_pais','paises.id')
                                    ->first();
                $repuesto_->urlfoto = 'fotozzz/sinfoto.png';
                array_push($arreglo,$repuesto_);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
        if($repuesto->fecha_actualizacion_stock > $repuesto->fecha_actualizacion_stock_dos && $repuesto->fecha_actualizacion_stock > $repuesto->fecha_actualizacion_stock_tres){
            $fecha_ultima = $repuesto->fecha_actualizacion_stock;
            $repuesto->fecha_ultima = $fecha_ultima;
        }elseif($repuesto->fecha_actualizacion_stock_dos > $repuesto->fecha_actualizacion_stock && $repuesto->fecha_actualizacion_stock_dos > $repuesto->fecha_actualizacion_stock_tres){
                $fecha_ultima = $repuesto->fecha_actualizacion_stock_dos;
                $repuesto->fecha_ultima = $fecha_ultima;
        }else{
                $fecha_ultima = $repuesto->fecha_actualizacion_stock_tres;
                $repuesto->fecha_ultima = $fecha_ultima;
        }
    }

    $stocks_repuesto = [];

        foreach($repuestos as $repuesto){
            $stock_bodega = repuesto::select('repuestos.stock_actual','locales.local_nombre','repuestos.id','locales.id as id_local')
                                        ->join('locales', 'repuestos.local_id', 'locales.id')
                                        ->where('repuestos.id',$repuesto->id)
                                        ->where('repuestos.local_id',1)
                                        ->first();

            $stock_bodega_segunda_ubicacion = repuesto::select('repuestos.stock_actual_dos as stock_actual','locales.local_nombre','repuestos.id','locales.id as id_local')
                                        ->join('locales', 'repuestos.local_id_dos', 'locales.id')
                                        ->where('repuestos.id',$repuesto->id)
                                        ->where('repuestos.local_id_dos',1)
                                        ->first();

            $stock_tienda = repuesto::select('repuestos.stock_actual','locales.local_nombre','repuestos.id','locales.id as id_local')
                                        ->join('locales', 'repuestos.local_id', 'locales.id')
                                        ->where('repuestos.id',$repuesto->id)
                                        ->where('repuestos.local_id',3)
                                        ->first();

            $stock_tienda_segunda_ubicacion = repuesto::select('repuestos.stock_actual_dos as stock_actual','locales.local_nombre','repuestos.id','locales.id as id_local')
                                        ->join('locales', 'repuestos.local_id_dos', 'locales.id')
                                        ->where('repuestos.id',$repuesto->id)
                                        ->where('repuestos.local_id_dos',3)
                                        ->first();
            $stock_cm_tercera_ubicacion = repuesto::select('repuestos.stock_actual_tres as stock_actual','locales.local_nombre','repuestos.id','locales.id as id_local')
                                    ->join('locales', 'repuestos.local_id_tres', 'locales.id')
                                    ->where('repuestos.id',$repuesto->id)
                                    ->where('repuestos.local_id_tres',4)
                                    ->first();

            array_push($stocks_repuesto,$stock_bodega,$stock_bodega_segunda_ubicacion,$stock_tienda, $stock_tienda_segunda_ubicacion,$stock_cm_tercera_ubicacion);
        }
        $permiso_ = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/repuesto/modificar-precio')->first();
            if($permiso_){
                $permiso_modificar = true;
            }else{
                $permiso_modificar = false;
            }

            $permiso__ = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/actualizar-precio')->first();
            if($permiso__){
                $permiso_actualizar = true;
            }else{
                $permiso_actualizar = false;
            }

            foreach ($arreglo as $r) {
                $oferta = oferta_pagina_web::where('id_repuesto',$r->id)->where('activo',1)->first();
                $familia_dcto = descuento::where('id_familia', $r->id_familia)->where('activo',1)->first();
                if(isset($oferta)){
                    $r->precio_venta = $oferta->precio_actualizado;
                }elseif(!isset($oferta) && isset($familia_dcto)){

                    $r->precio_venta = $r->precio_venta - (($familia_dcto->porcentaje/100) * $r->precio_venta);
                    $r->oferta = 2;
                }else{
                    $r->oferta = 0;
                }
            }
        $v = view('fragm.ventas_repuestos', compact('repuestos', 'saldos', 'desde', 'quemodelo','criterio','tienen_foto','arreglo','stocks_repuesto','permiso_modificar','permiso_actualizar'))->render();
        return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $regiones = regiones::all();
        $bodegas = local::where('activo',1)->get();

        $permiso_para_editar = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/agregar_referencia')->first();
            if($permiso_para_editar){
                $value_referencias = 1;
            }else{
                $value_referencias = 0;
            }

        $permiso_repuesto_expres = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/agregar_expres')->first();
        if($permiso_repuesto_expres){
            $value_expres = 1;
        }else{
            $value_expres = 0;
        }

        $permiso_solo_transferir = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/solo_carritos_transferidos')->first();

        if(!$permiso_solo_transferir){
            $value_solo_transferir = 1;
        }else{
            $value_solo_transferir = 0;
        }

        $permiso_cotizar = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/cotizaciones')->first();

        if(!$permiso_cotizar){
            $value_solo_cotizar = 1;
        }else{
            $value_solo_cotizar = 0;
        }

        $permiso_consignar = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/consignaciones')->first();

        if(!$permiso_consignar){
            $value_solo_consignar = 1;
        }else{
            $value_solo_consignar = 0;
        }

        $permiso_busqueda_express = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/busqueda_expres')->first();

        if(!$permiso_busqueda_express){
            $value_solo_busqueda = 1;
        }else{
            $value_solo_busqueda = 0;
        }

        $permiso_busqueda_cliente = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/busqueda_cliente')->first();

        if(!$permiso_busqueda_cliente){
            $value_solo_busqueda_cliente = 1;
        }else{
            $value_solo_busqueda_cliente = 0;
        }

        $permiso_borrar_carrito = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/borrar_carrito')->first();

        if(!$permiso_borrar_carrito){
            $value_borrar_carrito = 1;
        }else{
            $value_borrar_carrito = 0;
        }

        $permiso_guardar_carrito = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/guardar_carrito')->first();

        if(!$permiso_guardar_carrito){
            $value_guardar_carrito = 1;
        }else{
            $value_guardar_carrito = 0;
        }

        $permiso_recuperar_carrito = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/recuperar_carrito')->first();

        if(!$permiso_recuperar_carrito){
            $value_recuperar_carrito = 1;
        }else{
            $value_recuperar_carrito = 0;
        }

        $permiso_transferir_carrito = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/transferir_carrito')->first();

        if(!$permiso_transferir_carrito){
            $value_transferir_carrito = 1;
        }else{
            $value_transferir_carrito = 0;
        }

        $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                //Consultamos si el usuario tiene el permiso de ingresar a ventas principal.
                if($permiso_detalle->permission_id == 3 && $permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == '/ventas'){
                        return view('inventario.ventas_principal',[
                            'value_referencias' => $value_referencias,
                            'value_expres' => $value_expres,
                            'value_solo_transferir' => $value_solo_transferir,
                            'value_cotizar' => $value_solo_cotizar,
                            'value_consignar' => $value_solo_consignar,
                            'value_busqueda' => $value_solo_busqueda,
                            'value_busqueda_cliente' => $value_solo_busqueda_cliente,
                            'value_borrar_carrito' => $value_borrar_carrito,
                            'value_guardar_carrito' => $value_guardar_carrito,
                            'value_recuperar_carrito' => $value_recuperar_carrito,
                            'value_transferir_carrito' => $value_transferir_carrito,
                            'regiones' => $regiones,
                            'bodegas' => $bodegas
                        ]);
                    }
            }

            $user = Auth::user();
            //Si no tiene permiso especial de ventas, se consulta si el usuario es administrador puede ingresar a ventas principal
            if ($user->rol->nombrerol == "Administrador") {
                return view('inventario.ventas_principal',[
                    'value_referencias' => $value_referencias,
                    'value_expres' => $value_expres,
                    'value_solo_transferir' => $value_solo_transferir,
                    'value_cotizar' => $value_solo_cotizar,
                    'value_consignar' => $value_solo_consignar,
                    'value_busqueda' => $value_solo_busqueda,
                    'value_busqueda_cliente' => $value_solo_busqueda_cliente,
                    'value_borrar_carrito' => $value_borrar_carrito,
                    'value_guardar_carrito' => $value_guardar_carrito,
                    'value_recuperar_carrito' => $value_recuperar_carrito,
                    'value_transferir_carrito' => $value_transferir_carrito,
                    'regiones' => $regiones,
                    'bodegas' => $bodegas
                ]);
            } else {
                return redirect('home');
            }
    }

    public function exportacion(){
        return view('inventario.ventas_principal_exportacion',[]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
    }

    private function dame_correlativo($tip_doc)
    {

        $num=-1;
        $id_local = Session::get("local"); // es el local donde se ejecuta el terminal
        if($tip_doc=='cotizacion')
        {
            $fila = correlativo::where('id_local', $id_local)
                            ->where('documento', $tip_doc)
                            ->first();
            if(!is_null($fila))
            {
                $corr=$fila->correlativo; //0
                $max_folio=$fila->hasta; //0
                if($max_folio>=($corr+1)) $num=$corr;
            }
        }else{
            $fila=correlativo::where('id_local', $id_local)
                            ->where('documento', $tip_doc)
                            ->first();

            if(!is_null($fila))
            {
                $corr=$fila->correlativo;
                $max_folio=$fila->hasta;
                $el_siguiente=$corr+1;
                if($max_folio>=($el_siguiente)) $num=$corr;
                //FALTA:VERIFICAR EN LA TABLA RESPECTIVA (boletas o facturas) si hay existe ese número
                //esto debido a que A VECES en un determinado instante COINCIDE la elección del siguiente correlativo.

            }
        }
        return $num;
    }

    public function generar_xml(Request $r)
    {

        //Recibimos el descuento, si viene vacio será 0
        $descuento = intval($r->dcto);

        $ref1=json_decode($r->ref1);
        $ref2=json_decode($r->ref2);
        $ref3=json_decode($r->ref3);

        $referencias=[];
        if(count($ref1)>0){
            array_push($referencias,$ref1);
        }
        if(count($ref2)>0){
            array_push($referencias,$ref2);
        }
        if(count($ref3)>0){
            array_push($referencias,$ref3);
        }

        $Datos['referencias']=$referencias;

        if($r->fmapago=='contado'){
            $Datos['FmaPago']=1;
        }
        if($r->fmapago=='credito' || $r->fmapago=='delivery'){
            $Datos['FmaPago']=2;
        }

        if($r->docu=='cotizacion'){
            //FALTA: Revisar código de guardar_venta para empezar.

            return "algo";
        }


        //PREPARAMOS LOS DATOS A ENTREGAR A CLSSII

        //Correlativo
        $nume=$this->dame_correlativo($r->docu);

        if($nume<0) //Se acabó el correlativo autorizado por SII
        {
            $docu=strtoupper($r->docu);
            $estado=['estado'=>'ERROR_CAF','mensaje'=>$nume.": No hay correlativo autorizado por SII. Descargar nuevo CAF"];
            return json_encode($estado);
        }else{
            $nume++; //siguiente correlativo
            $Datos['folio_dte']=$nume;
            if($r->docu=='boleta') $Datos['tipo_dte']='39';
            if($r->docu=='factura') $Datos['tipo_dte']='33';
            if($r->docu=='notacredito') $Datos['tipo_dte']='61';
            if($r->docu=='notadebito') $Datos['tipo_dte']='56';
        }

        //Obtener cliente

        if($r->idcliente==0){ //no se eligió cliente
            $idcliente=$this->dame_cliente_sii();
        }else{
            $idcliente=$r->idcliente;
        }

        $cliente=cliente_modelo::find($idcliente);

        $rutCliente_con_guion=substr($cliente->rut,0,strlen($cliente->rut)-1)."-".substr($cliente->rut,strlen($cliente->rut)-1);

        //$rutCliente_con_guion=substr($cliente->rut,0,strlen($cliente->rut)-1)."-".substr($cliente->rut,strlen($cliente->rut)-1);
        if($cliente->tipo_cliente==0){ //persona natural
            $rz=$cliente->nombres." ".$cliente->apellidos;
        }
        if($cliente->tipo_cliente==1){ //empresa
            $rz=$cliente->empresa;
        }


        $id_direccion_cliente = $r->id_direccion;
        $id_giro_cliente = $r->id_giro;
        $direccion_cliente = cliente_direcciones::find($id_direccion_cliente);

        $giro_cliente = clientes_giro::find($id_giro_cliente);
        $cliente->direccion = trim($direccion_cliente->direccion);
        if($giro_cliente)
            $cliente->giro = trim($giro_cliente->giro);
        else
            $cliente->giro = "SIN GIRO";

        $id_region = $direccion_cliente->id_region;
        $id_comuna = $direccion_cliente->id_comuna;

        $region = regiones::find($id_region);
        $ciudad = ciudades::find($id_comuna);

        $cliente->direccion_comuna = $ciudad->nombre;
        $cliente->direccion_ciudad = $region->nombre;

        $Receptor=['RUTRecep'=>$rutCliente_con_guion,
                            'RznSocRecep'=>$rz,
                            'GiroRecep'=>$cliente->giro,
                            'DirRecep'=>$cliente->direccion,
                            'CmnaRecep'=>trim($cliente->direccion_comuna),
                            'CiudadRecep'=>trim($cliente->direccion_comuna)
                        ];

        //Obtener detalle del carrito
        $Detalle=[];
        //Si es que viene con vendedor_id es que el carrito fue transferido

        if(!empty($r->vendedor_id)){
            $carrito_transferido = new carrito_transferido();
            $ct = $carrito_transferido->dame_todo_carrito_cliente($r->carrito_id);
            //$dcto_x_item = round($descuento / $ct->count());
            // recoger el tipo de despacho y guardarlo en $Datos
            $Datos['tipo_despacho'] = $ct[0]->descripcion_tipo_despacho == null ? 0 : $ct[0]->descripcion_tipo_despacho;
            foreach($ct as $i){
                $repuesto = repuesto::where('id',$i->id_repuestos)->first();
                if($i->id_local == 1){
                    if($repuesto->stock_actual < $i->cantidad){
                        $estado=['estado'=>'ERROR_STOCK','mensaje'=>'No hay stock suficiente para el repuesto '.$repuesto->codigo_interno];
                        return json_encode($estado);
                    }
                }else if($i->id_local == 2){
                    if($repuesto->stock_actual_mermas < $i->cantidad){
                        $estado=['estado'=>'ERROR_STOCK','mensaje'=>'No hay stock suficiente para el repuesto '.$repuesto->codigo_interno];
                        return json_encode($estado);
                    }
                }else{
                    if($repuesto->stock_actual_preventa < $i->cantidad){
                        $estado=['estado'=>'ERROR_STOCK','mensaje'=>'No hay stock suficiente para el repuesto '.$repuesto->codigo_interno];
                        return json_encode($estado);
                    }
                }

               $porcentaje = $descuento / 100;
               $dcto_x_item = round(($i->pu * $porcentaje) * $i->cantidad);
                //Si el repuesto esta en oferta, se respeta el valor del repuesto en la oferta.
                try {
                    //Si el repuesto esta en oferta, se respeta el valor del repuesto en la oferta.
                    $o = oferta_pagina_web::where('id_repuesto',$i->id_repuestos)->first();
                    $d = descuento::where('id_familia',$i->id_familia)->where('activo',1)->first();
                    if($i->oferta == 1){
                        if($r->confirmado === "ofertado"){
                            $i->pu = $o->precio_actualizado;
                        }else{
                            $repuesto = repuesto::where('id',$i->id_repuestos)->first();
                            // verificar si aun queda stock del repuesto

                            $i->pu = $repuesto->precio_venta;

                        }

                    }else if($d){
                        if($r->confirmado === "ofertado"){
                            //$i->pu = $repuesto->precio_venta - (($d->porcentaje/100) * $repuesto->precio_venta);
                            //Se mantiene el pu que viene con descuento
                        }else{
                            $repuesto = repuesto::where('id',$i->id_repuestos)->first();
                            $i->pu = $repuesto->precio_venta;
                        }

                    }
                   } catch (\Exception $e) {
                    return $e->getMessage();
                   }
                //$precio_neto_item=$i->pu_neto; //round($i->pu/(1+Session::get('PARAM_IVA')),0);
                if($Datos['tipo_dte']=='39'){ //boleta
                    if($i->descuento_item>0){
                        $item=array(
                            'CdgItem' => $i->codigo_interno,
                            'NmbItem'=>$i->descripcion,
                            'QtyItem'=>$i->cantidad,
                            'PrcItem'=>round($i->pu,2),//intval($i->pu),
                            'DescuentoMonto'=>round($i->descuento_item,2) //intval($i->descuento_item)
                        );
                    }else{
                        $item=array(
                            'CdgItem' => $i->codigo_interno,
                            'NmbItem'=>$i->descripcion,
                            'QtyItem'=>$i->cantidad,
                            'PrcItem'=>round($i->pu,2),
                            'DescuentoMonto'=>round($dcto_x_item,2)
                        );
                    }
                }else{ // factura
                    if($dcto_x_item > 0){
                        $item=array(    'CdgItem' => $i->codigo_interno,
                                        'NmbItem'=>$i->descripcion,
                                        'QtyItem'=>$i->cantidad,
                                        'PrcItem'=>round($i->pu,2),//$i->pu_neto, //ya llega redondeado desde el carrito
                                        'DescuentoMonto'=>round($dcto_x_item,2)//round($i->descuento_item/(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP)
                                        );
                       }else{
                        if($i->descuento_item>0){

                            $item=array('CdgItem' => $i->codigo_interno,
                                        'NmbItem'=>$i->descripcion,
                                        'QtyItem'=>$i->cantidad,
                                        'PrcItem'=>round($i->pu,2),//$i->pu_neto, //ya llega redondeado desde el carrito
                                        'DescuentoMonto'=>round($i->descuento_item,2)//round($i->descuento_item/(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP)
                                        );
                        }else{
                            $item=array('CdgItem' => $i->codigo_interno,
                                        'NmbItem'=>$i->descripcion,
                                        'QtyItem'=>$i->cantidad,
                                        'PrcItem'=>round($i->pu,2)
                                        );
                        }
                       }
                }

                array_push($Detalle,$item);
            }
        }else{
            // Si no viene con vendedor_id es un carrito normal
            $carrito=new carrito_compra();
            $c=$carrito->dame_todo_carrito();
            //$dcto_x_item = round($descuento / $c->count());

            $Datos['tipo_despacho'] = $ct[0]->descripcion_tipo_despacho == null ? 0 : $ct[0]->descripcion_tipo_despacho;
            foreach($c as $i)
            {
                $porcentaje = $descuento / 100;
                //Se multiplica el precio final con el descuento por la cantidad de repuestos,
                //porque el descuento es en base al total.
                $dcto_x_item = round(($i->pu * $porcentaje) * $i->cantidad);
                try {
                    //Si el repuesto esta en oferta, se respeta el valor del repuesto en la oferta.
                    $o = oferta_pagina_web::where('id_repuesto',$i->idrepuesto)->first();
                    $d = descuento::where('id_familia',$i->id_familia)->where('activo',1)->first();
                    if($i->oferta == 1){
                        if($r->confirmado === "ofertado"){
                            $i->pu = $o->precio_actualizado;
                        }else{
                            $repuesto = repuesto::where('id',$i->id_repuestos)->first();
                            $i->pu = $repuesto->precio_venta;

                        }

                    }else if($d){
                        if($r->confirmado === "ofertado"){
                            //$i->pu = $repuesto->precio_venta - (($d->porcentaje/100) * $repuesto->precio_venta);
                        }else{
                            $repuesto = repuesto::where('id',$i->id_repuestos)->first();
                            $i->pu = $repuesto->precio_venta;
                        }

                    }
                   } catch (\Exception $e) {
                    return $e->getMessage();
                   }
                //$precio_neto_item=$i->pu_neto; //round($i->pu/(1+Session::get('PARAM_IVA')),0);
                if($Datos['tipo_dte']=='39'){ //boleta
                    if($i->descuento_item>0){
                        $item=array(
                            'CdgItem' => $i->codigo_interno,
                            'NmbItem'=>$i->descripcion,
                            'QtyItem'=>$i->cantidad,
                            'PrcItem'=>round($i->pu,2),//intval($i->pu),
                            'DescuentoMonto'=>round($i->descuento_item,2) //intval($i->descuento_item)
                        );
                    }else{
                        $item=array(
                            'CdgItem' => $i->codigo_interno,
                            'NmbItem'=>$i->descripcion,
                            'QtyItem'=>$i->cantidad,
                            'PrcItem'=>round($i->pu,2),
                            'DescuentoMonto'=>round($dcto_x_item,2) //intval($i->descuento_item)
                        );
                    }
                }else{ // factura
                   if($dcto_x_item > 0){
                    $item=array(
                        'CdgItem' => $i->codigo_interno,
                        'NmbItem'=>$i->descripcion,
                                    'QtyItem'=>$i->cantidad,
                                    'PrcItem'=>round($i->pu,2),//$i->pu_neto, //ya llega redondeado desde el carrito
                                    'DescuentoMonto'=>round($dcto_x_item,2)//round($i->descuento_item/(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP)
                                    );
                   }else{
                    if($i->descuento_item>0){

                        $item=array(
                            'CdgItem' => $i->codigo_interno,
                            'NmbItem'=>$i->descripcion,
                                    'QtyItem'=>$i->cantidad,
                                    'PrcItem'=>round($i->pu,2),//$i->pu_neto, //ya llega redondeado desde el carrito
                                    'DescuentoMonto'=>round($i->descuento_item,2)//round($i->descuento_item/(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP)
                                    );
                    }else{
                        $item=array(
                            'CdgItem' => $i->codigo_interno,
                            'NmbItem'=>$i->descripcion,
                                    'QtyItem'=>$i->cantidad,
                                    'PrcItem'=>round($i->pu,2)
                                    );
                    }
                   }

                }

                array_push($Detalle,$item);
            }
        }
        try {
            //code...

            $estado=ClsSii::generar_xml($Receptor,$Detalle,$Datos); //devuelve array

        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }

        if($estado['estado']=='GENERADO'){
            Session::put('xml',$Datos['tipo_dte']."_".$Datos['folio_dte'].".xml");
            Session::put('tipo_dte',$Datos['tipo_dte']);
            Session::put('tipo_dte_nombre',$r->docu);
            Session::put('folio_dte',$Datos['folio_dte']);
            Session::put('idcliente',$idcliente);
            $this->actualizar_correlativo($r->docu, $nume);
        }else{
            Session::put('xml',0);
            Session::put('tipo_dte',0);
            Session::put('tipo_dte_nombre','');
            Session::put('folio_dte',0);
            Session::put('idcliente',0);
            // en caso de que no se genere el xml, se debe guardar un registro en la tabla dte_rechazados
            $dte_rechazado = new dte_rechazados;
            $dte_rechazado->tipo_doc = $Datos['tipo_dte'];
            $dte_rechazado->fecha_emision = Carbon::today()->format('d-m-Y');
            $dte_rechazado->folio_doc = $Datos['folio_dte'];
            $dte_rechazado->id_cliente = $idcliente;
            //$dte_rechazado->save();

        }

        return json_encode($estado);
    }

    public function enviar_correos_dte($tipodte, $fechainicial, $fechafinal)

    {
        if($tipodte=='33'){
            try {
                $tipo_doc = 'Factura';
                $dtes=factura::select('facturas.fecha_emision','facturas.created_at',
                                'facturas.num_factura',
                                'facturas.total',
                                'facturas.trackid',
                                'facturas.estado_sii',
                                'facturas.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.empresa as razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                        ->join('clientes','facturas.id_cliente','clientes.id')
                        ->join('users','facturas.usuarios_id','users.id')
                        ->where('facturas.fecha_emision','>=',$fechainicial)
                        ->where('facturas.fecha_emision','<=',$fechafinal)
                        ->where('facturas.activo',1)
                        ->orderBy('facturas.id','DESC')
                        ->get();

                foreach($dtes as $dte){
                    if($dte->email != null && $dte->email != ''){
                        $docu_nombre="Factura";
                        $docu="factura_".$dte->num_factura.".pdf";
                        $docu_xml="33_".$dte->num_factura.".xml";
                        $archivo_ruta=base_path('storage/app/public/pdf').'/facturas/'.$docu;
                        $archivo_xml_ruta=base_path('xml/generados').'/facturas/'.$docu_xml;
                        //VERIFICAR LA RUTA DEL ARCHIVO A ADJUNTAR
                        if(file_exists($archivo_ruta) && file_exists($archivo_xml_ruta)){

                            if(file_exists($archivo_xml_ruta)){
                                $correo=new EnviarCorreo($dte->email,$archivo_ruta,$archivo_xml_ruta,$docu_nombre,$dte->num_factura);
                                //event(new MessageSending($correo));

                                \Mail::send($correo); //Devuelve void
                                $rpta=$dte->email." enviado.";
                                if( count(\Mail::failures()) > 0 ) {

                                    $rpta= "Error al enviar: <br>";

                                    foreach(\Mail::failures() as $email_malo) {
                                        $rpta.=" - $email_malo <br>";
                                    }

                                }
                            }else{
                                //Debugbar::info("NO Existe XML: ".$archivo_xml_ruta);
                                $rpta= "NO se ha generado el archivo XML.".$docu_xml;
                            }


                        }else{
                            //El archivo PDF solo se genera cuando envía a IMPRIMIR, antes
                            //Debugbar::info("NO Existe PDF: ".$archivo_ruta);
                            $rpta= "NO se ha generado el archivo PDF. Imprímalo ".$docu;
                        }
                    }
                }

            } catch (\Exception $e) {
                return $e->getMessage();
            }

        }
        if($tipodte=='39'){
            $dtes=boleta::select('boletas.fecha_emision','boletas.created_at',
                                'boletas.num_boleta',
                                'boletas.total',
                                'boletas.trackid',
                                'boletas.estado_sii',
                                'boletas.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                        ->join('clientes','boletas.id_cliente','clientes.id')
                        ->join('users','boletas.usuarios_id','users.id')
                        ->where('boletas.fecha_emision','>=',$fechainicial)
                        ->where('boletas.fecha_emision','<=',$fechafinal)
                        ->where('boletas.activo',1)
                        ->orderBy('boletas.id','DESC')
                        ->get();

        }
        if($tipodte=='61'){
            $dtes=nota_de_credito::select('notas_de_credito.fecha_emision','notas_de_credito.created_at',
                                'notas_de_credito.num_nota_credito',
                                'notas_de_credito.total',
                                'notas_de_credito.trackid',
                                'notas_de_credito.estado_sii',
                                'notas_de_credito.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.empresa as razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                        ->join('clientes','notas_de_credito.id_cliente','clientes.id')
                        ->join('users','notas_de_credito.usuarios_id','users.id')
                        ->where('notas_de_credito.fecha_emision','>=',$fechainicial)
                        ->where('notas_de_credito.fecha_emision','<=',$fechafinal)
                        ->where('notas_de_credito.activo',1)
                        ->orderBy('notas_de_credito.id','DESC')
                        ->get();

        }
        if($tipodte=='56'){
            $dtes = nota_de_debito::select('notas_de_debito.fecha_emision','notas_de_debito.created_at',
                                'notas_de_debito.num_nota_debito',
                                'notas_de_debito.total',
                                'notas_de_debito.trackid',
                                'notas_de_debito.estado_sii',
                                'notas_de_debito.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.empresa as razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                        ->join('clientes','notas_de_debito.id_cliente','clientes.id')
                        ->join('users','notas_de_debito.usuarios_id','users.id')
                        ->where('notas_de_debito.fecha_emision','>=',$fechainicial)
                        ->where('notas_de_debito.fecha_emision','<=',$fechafinal)
                        ->where('notas_de_debito.activo',1)
                        ->orderBy('notas_de_debito.id','DESC')
                        ->get();

        }
        if($tipodte=='52'){
            $dtes=guia_de_despacho::select('guias_de_despacho.fecha_emision','guias_de_despacho.created_at',
                                'guias_de_despacho.num_guia_despacho',
                                'guias_de_despacho.total',
                                'guias_de_despacho.trackid',
                                'guias_de_despacho.estado_sii',
                                'guias_de_despacho.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.empresa as razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                        ->join('clientes','guias_de_despacho.id_cliente','clientes.id')
                        ->join('users','guias_de_despacho.usuarios_id','users.id')
                        ->where('guias_de_despacho.fecha_emision','>=',$fechainicial)
                        ->where('guias_de_despacho.fecha_emision','<=',$fechafinal)
                        ->where('guias_de_despacho.activo',1)
                        ->orderBy('guias_de_despacho.id','DESC')
                        ->get();

        }

        return $dtes;
    }

    public function limpiar_sesion(){
        Session::put('xml',0);
        Session::put('tipo_dte',0);
        Session::put('tipo_dte_nombre','');
        Session::put('folio_dte',0);
        Session::put('idcliente',0);
    }

    public function set_xml_imprimir($xml){
        Session::put('xml',$xml);
    }

    public function dame_historial_cotizaciones($id_cliente){

        $hoy=Carbon::today();
        $fecha_hoy=$hoy->toDateString();

        //Borramos las cotizaciones vencidas. Pancho dice que despues de 30 días.
        //Si no se muestran las cotizaciones vencidas, por que no borrarlas de una vez???
        $fecha_30=$hoy->subDays(30);
        $borrados1=cotizacion::where('created_at','<',$fecha_30)
                                            ->delete();
        $borrados2=cotizacion_detalle::where('created_at','<',$fecha_30)
                                            ->delete();

        if($id_cliente==0){
            $cot=cotizacion::select(\DB::raw('cotizaciones.id,cotizaciones.activo,cotizaciones.num_cotizacion,cotizaciones.usuarios_id,cotizaciones.nombre_cotizacion,DATE_FORMAT(cotizaciones.fecha_emision,\'%d-%m-%Y\') as fecha, \'Ninguno\' as elcliente'))
                                ->where('cotizaciones.fecha_emision','>=',$fecha_30)
                                ->where('cotizaciones.activo',2)
                                ->where('cotizaciones.usuarios_id',Auth::user()->id)
                                ->orderBy('cotizaciones.id','DESC')
                                ->get();
        }else{
            $cot=cotizacion::select(\DB::raw('cotizaciones.id,cotizaciones.activo,cotizaciones.num_cotizacion,cotizaciones.usuarios_id,cotizaciones.nombre_cotizacion,DATE_FORMAT(cotizaciones.fecha_emision,\'%d-%m-%Y\') as fecha, IF(clientes.tipo_cliente=0,CONCAT(clientes.nombres,\' \',clientes.apellidos),clientes.razon_social) as elcliente'))
                                ->join('clientes','cotizaciones.id_cliente','clientes.id')
                                ->where('cotizaciones.fecha_emision','>=',$fecha_30)
                                ->where('cotizaciones.usuarios_id',Auth::user()->id)
                                ->where('cotizaciones.activo',2)
                                ->orderBy('cotizaciones.id','DESC')
                                ->get();
        }
        return [$cot->toJson(),Auth::user()];
    }

    public function cotizaciones(){
        $cot_json = $this->dame_historial_cotizaciones(0);
        $todas_cotizaciones = json_decode($cot_json);
        $cotizaciones = [];
        foreach($todas_cotizaciones as $c){
            if($c->activo == 1) array_push($cotizaciones, $c);
        }
        try {
            $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == '/ventas/cotizaciones'){
                    try {
                        return view('inventario.cotizaciones',['cotizaciones' => $cotizaciones]);
                    } catch (\Exception $e) {
                        return $e->getMessage();
                    }
                    }
            }
        if(Auth::user()->rol->nombrerol === "Administrador"){
                $v = view('inventario.cotizaciones',['cotizaciones' => $cotizaciones]);
                return $v;
            }else{
                return redirect('/home');
            }
        }catch (\Exception $e) {
            return $e->getMessage();
        }


    }

    public function cambiar_estado_cotizacion($id, $opcion){
        try {

            $cotizacion = cotizacion::find($id);
            if($opcion == 2){
                // 2 es aceptado
                $cotizacion->activo = 2;
            }else{
                // 0 es rechazado
                $cotizacion->activo = 0;
            }

            $cotizacion->save();

            $cot_json = $this->dame_historial_cotizaciones(0);
            $todas_cotizaciones = json_decode($cot_json);
            $cotizaciones = [];
            foreach($todas_cotizaciones as $c){
                if($c->activo == 1) array_push($cotizaciones, $c);
            }

            return view('fragm.cotizaciones',compact('cotizaciones'));
            //code...
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }
    }

    public function enviar_sii(Request $r)
    {

        $id_cliente=$r->idcliente==0 ? $this->dame_cliente_sii() : $r->idcliente; //para guardar la boleta y factura

        $consignacion = $r->consignacion;

        $vendedor_id = $r->vendedor_id;

        $dcto = intval($r->dcto);

        if(Session::get('xml')==0 )
        {
            $estado=['estado'=>'ERROR_XML','mensaje'=>'No se encuentra el XML generado.'];
            return json_encode($estado);
        }

        $RutEnvia = str_replace(".","",Session::get('PARAM_RUT_ENVIA'));
        $RutEmisor = str_replace(".","",Session::get('PARAM_RUT'));
        $d=Session::get('xml');
        $tipo_dte=Session::get('tipo_dte');

        if($tipo_dte=='39')
        {
            $doc=base_path().'/xml/generados/boletas/'.$d;
            $docu = 'boleta';
        }
        if($tipo_dte=='33')
        {
            $doc=base_path().'/xml/generados/facturas/'.$d;
            $docu = 'factura';
        }
        if($tipo_dte=='61')
        {
            $doc=base_path().'/xml/generados/notas_de_credito/'.$d;
            $docu = 'nota de credito';
        }
        if($tipo_dte=='56')
        {
            $doc=base_path().'/xml/generados/notas_de_debito/'.$d;
            $docu = 'nota de debito';
        }


        $tipo_docu="nada";
        $num_docu=0;
        $id_documento_pago = 0;
       //Recuperar el XML Generado para enviar
        try {
            $envio=file_get_contents($doc);
            if($tipo_dte=='39'){
                $rs=ClsSii::enviar_sii_boleta($RutEnvia,$RutEmisor,$envio); //recibe un array asoc, si OK, trackID es $estado['trackid']
                // asignamos datos faltos a rs
                // $rs['estado'] = 'OK';
                // $rs['mensaje'] = 'ERP';
                // $rs['trackid'] = '12345678';
                if($rs['estado']=='OK'){
                    $resultado_envio=$rs['mensaje'];
                    $estado_sii='RECIBIDO';
                    $estado=0;
                    $TrackID=$rs['trackid'];
                    $xml=new \SimpleXMLElement($envio, LIBXML_COMPACT);
                }else{
                    //Guardamos la boleta igualmente con el estado de rechazado
                    // en caso de que no se envié al sii, se debe guardar un registro en la tabla dte_rechazados
                    $dte_rechazado = new dte_rechazados;
                    $dte_rechazado->tipo_doc = $Datos['tipo_dte'];
                    $dte_rechazado->fecha_emision = Carbon::today()->format('d-m-Y');
                    $dte_rechazado->folio_doc = $Datos['folio_dte'];
                    $dte_rechazado->id_cliente = $idcliente;
                    //$dte_rechazado->save();
                    return json_encode($rs);
                }

                 //Correlativo
                $nume=$this->dame_correlativo('boleta');
                $nume++; //siguiente correlativo
                // $this->actualizar_correlativo($docu, $nume);

                $b = new boleta;
                $b->num_boleta = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->Folio);
                $b->fecha_emision = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->FchEmis);
                $b->es_credito=$r->venta=="credito" ? 1 : 0;
                $b->es_delivery=$r->venta=="delivery" ? 1 : 0;
                $b->id_cliente = $id_cliente;
                $b->estado = $estado;
                $b->estado_sii=$estado_sii;
                $b->resultado_envio=$resultado_envio;
                $b->trackid=$TrackID;
                $b->url_xml=$d;
                $b->total = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal);// round(intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal)*(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP); //incluye el iva
                $b->neto = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntNeto);//intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal);
                $b->exento = 0;
                $b->iva = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->IVA); //round($b->neto*Session::get('PARAM_IVA'),0,PHP_ROUND_HALF_UP);
                $b->activo = 1;
                if($r->vendedor_id){
                    $b->usuarios_id = intval($r->vendedor_id);
                }else{
                    $b->usuarios_id = Auth::user()->id;
                }


                $b->save();
                //hay que sacar lo que falta, pero el tema de montos sacarlos del XML enviado

                //Si tiene un vendedor_id significa que viene de un carrito transferido
                if($r->vendedor_id){

                    $carrito_transferido = new carrito_transferido();
                    $ct = $carrito_transferido->dame_todo_carrito_cliente($r->carrito_id);
                    foreach($ct as $i){

                        $porcentaje = $dcto / 100;
                        $dcto_x_item = round(($i->pu * $porcentaje) * $i->cantidad);

                        $bd = new boleta_detalle;

                        $bd->id_boleta = $b->id;
                        $bd->id_repuestos = $i->id_repuestos;
                        $bd->id_unidad_venta = $i->id_unidad_venta;
                        $bd->id_local = $i->id_local;
                        $bd->precio_venta = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->PrcItem);//*(1+Session::get('PARAM_IVA')); //$i->pu;
                        $bd->pu_neto = round($bd->precio_venta/(1+Session::get('PARAM_IVA')),2);
                        $bd->cantidad = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->QtyItem); //$i->cantidad;
                        $sb=intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->MontoItem);//*(1+Session::get('PARAM_IVA'));
                        $bd->subtotal = $sb;//$i->subtotal_item;
                        if(!is_null(intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->DescuentoMonto))){
                            $bd->descuento = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->DescuentoMonto); //round($i->descuento_item/(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP);
                        }else{
                            $bd->descuento=0;
                        }

                        $bd->total = $sb-$i->descuento_item;
                        $bd->activo = 1;
                        if($vendedor_id){
                            $bd->usuarios_id = $vendedor_id;
                        }else{
                            $bd->usuarios_id = Auth::user()->id;
                        }

                        //Buscamos el repuesto que se debe descontar el stock
                        $repuesto = repuesto::find($i->id_repuestos);

                        //Descontamos el stock de acuerdo al local
                        if($repuesto->id_local == $i->id_local){
                            $repuesto->stock_actual = $repuesto->stock_actual - intval($i->cantidad);
                            $repuesto->save();
                        }elseif($repuesto->id_local_dos == $i->id_local){
                            $repuesto->stock_actual_mermas = $repuesto->stock_actual_mermas - intval($i->cantidad);
                            $repuesto->save();
                        }elseif($repuesto->id_local_tres == $i->id_local){
                            $repuesto->stock_actual_preventa = $repuesto->stock_actual_preventa - intval($i->cantidad);
                            $repuesto->save();
                        }else{
                            $repuesto->stock_actual = $repuesto->stock_actual - intval($i->cantidad);
                            $repuesto->save();
                        }
                        $this->revisar_stock_minimo($repuesto->id);

                        $bd->save();


                        $i->estado = 0;
                        $i->tipo_doc = $docu;
                        $i->num_doc = $b->num_boleta;
                        $i->save();
                    }

                }else{
                    $carrito=new carrito_compra();
                    $c = $carrito->dame_todo_carrito();

                    foreach($c as $i){
                        $porcentaje = $dcto / 100;
                        $dcto_x_item = round(($i->pu * $porcentaje) * $i->cantidad);

                        $bd = new boleta_detalle;
                        $bd->id_boleta = $b->id;
                        $bd->id_repuestos = $i->id_repuestos;
                        $bd->id_unidad_venta = $i->id_unidad_venta;
                        $bd->id_local = $i->id_local;
                        $bd->precio_venta = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->PrcItem);//*(1+Session::get('PARAM_IVA')); //$i->pu;
                        $bd->pu_neto = round($bd->precio_venta/(1+Session::get('PARAM_IVA')),2);
                        $bd->cantidad = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->QtyItem); //$i->cantidad;
                        $sb=intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->MontoItem);//*(1+Session::get('PARAM_IVA'));
                        $bd->subtotal = $sb;//$i->subtotal_item;

                        if(!is_null(intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->DescuentoMonto))){
                            $bd->descuento = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->DescuentoMonto); //round($i->descuento_item/(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP);
                        }else{
                            $bd->descuento=0;
                        }

                        $bd->total = $sb-$i->descuento_item;
                        $bd->activo = 1;
                        if($vendedor_id){
                            $bd->usuarios_id = $vendedor_id;
                        }else{
                            $bd->usuarios_id = Auth::user()->id;
                        }
                        //Buscamos el repuesto que se debe descontar el stock
                        $repuesto = repuesto::find($i->id_repuestos);


                        //Descontamos el stock de acuerdo al local
                        $repuesto->stock_actual = $repuesto->stock_actual - intval($i->cantidad);
                        $repuesto->save();


                        $this->revisar_stock_minimo($repuesto->id);

                        // Actualizar tabla saldos considerando el local
                        // id_repuestos,id_local,saldo,activo,usuarios_id
                        $bd->save();


                    }

                }

                $tipo_docu="boleta";
                $num_docu=$b->num_boleta;
                $id_documento_pago = $b->id;

            } // fin DE BOLETA

            if($tipo_dte=='33'){ //FACTURA

                $rs=ClsSii::enviar_sii($RutEnvia,$RutEmisor,$envio); //recibe un array asoc, si OK, trackID es $estado['trackid']

                if($rs['estado']=='OK'){
                    $resultado_envio=$rs['mensaje'];
                    $xml=new \SimpleXMLElement($envio, LIBXML_COMPACT);
                    $estado_sii='RECIBIDO';
                    $estado=0;
                    $TrackID=$rs['trackid'];

                }else{
                    // en caso de que no se genere el xml, se debe guardar un registro en la tabla dte_rechazados
                    $dte_rechazado = new dte_rechazados;
                    $dte_rechazado->tipo_doc = $Datos['tipo_dte'];
                    $dte_rechazado->fecha_emision = Carbon::today()->format('d-m-Y');
                    $dte_rechazado->folio_doc = $Datos['folio_dte'];
                    $dte_rechazado->id_cliente = $idcliente;
                    //$dte_rechazado->save();
                    return json_encode($rs);
                    //$TrackID="---";
                    //$estado_sii=$rs['estado'];
                }


                //Correlativo
                $nume=$this->dame_correlativo('factura');
                $nume++; //siguiente correlativo
                // $this->actualizar_correlativo('factura', $nume);


                $f = new factura;
                $f->num_factura = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->Folio);
                $f->fecha_emision = strval($xml->SetDTE->DTE->Documento->Encabezado->IdDoc->FchEmis);
                $f->es_credito=$r->venta=="credito" ? 1 : 0;
                $f->es_delivery=$r->venta=="delivery" ? 1 : 0;
                $f->id_cliente = $id_cliente;
                $f->estado = $estado;
                $f->estado_sii=$estado_sii;
                $f->resultado_envio=$resultado_envio;
                $f->trackid=$TrackID;
                $f->url_xml=$d;
                $f->total =intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal); //incluye el iva
                $f->neto = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->MntNeto);
                $f->exento = 0;
                $f->iva = intval($xml->SetDTE->DTE->Documento->Encabezado->Totales->IVA);
                $f->activo = 1;
                if($vendedor_id){
                    $f->usuarios_id = $vendedor_id;
                }else{
                    $f->usuarios_id = Auth::user()->id;
                }
                $f->save();

                //hay que sacar lo que falta, pero el tema de montos sacarlos del XML enviado
                //Si la factura viene desde un carrito transferido

                if($r->vendedor_id){
                    $carrito_transferido = new carrito_transferido();
                    $ct = $carrito_transferido->dame_todo_carrito_cliente($r->carrito_id);


                    foreach($ct as $i){
                        $porcentaje = $dcto / 100;
                        $dcto_x_item = round(($i->pu * $porcentaje) * $i->cantidad);
                        $fd = new factura_detalle;
                        $fd->id_factura = $f->id;
                        $fd->id_repuestos = $i->id_repuestos;
                        $fd->id_unidad_venta = $i->id_unidad_venta;
                        $fd->id_local = $i->id_local;
                        $fd->precio_venta = $xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->PrcItem;//round($fd->pu_neto*(1+Session::get('PARAM_IVA')),2,PHP_ROUND_HALF_UP);
                        $fd->pu_neto = round($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->PrcItem/(1+Session::get('PARAM_IVA')),2);
                        $fd->cantidad = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->QtyItem); //$i->cantidad;
                        $fd->subtotal = $fd->precio_venta*$fd->cantidad;
                        //if hay descuento en el xml?? poner, sino 0;
                        if(!is_null(intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->DescuentoMonto))){
                            $fd->descuento = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->DescuentoMonto); //round($i->descuento_item/(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP);
                        }else{
                            $fd->descuento=0;
                        }

                        $fd->total = $fd->subtotal-$fd->descuento;
                        $fd->activo = 1;
                        if($vendedor_id){
                            $fd->usuarios_id = $vendedor_id;
                        }else{
                            $fd->usuarios_id = Auth::user()->id;
                        }
                        //Buscamos el repuesto que se debe descontar el stock
                        $repuesto = repuesto::find($i->id_repuestos);
                        //Descontamos el stock de acuerdo al local
                        if($repuesto->id_local == $i->id_local){
                            $repuesto->stock_actual = $repuesto->stock_actual - intval($i->cantidad);
                            $repuesto->save();
                        }elseif($repuesto->id_local_dos == $i->id_local){
                            $repuesto->stock_actual_mermas = $repuesto->stock_actual_mermas - intval($i->cantidad);
                            $repuesto->save();
                        }elseif($repuesto->id_local_tres == $i->id_local){
                            $repuesto->stock_actual_preventa = $repuesto->stock_actual_preventa - intval($i->cantidad);
                            $repuesto->save();
                        }else{
                            $repuesto->stock_actual = $repuesto->stock_actual - intval($i->cantidad);
                            $repuesto->save();
                        }


                        $this->revisar_stock_minimo($repuesto->id);

                        // Actualizar tabla saldos considerando el local
                        // id_repuestos,id_local,saldo,activo,usuarios_id
                        $fd->save();

                        $i->estado = 0;
                        $i->tipo_doc = $docu;
                        $i->num_doc = $f->num_factura;
                        $i->save();
                    }
                }else{
                    $carrito=new carrito_compra();
                    $c = $carrito->dame_todo_carrito();
                    foreach($c as $i){
                        $porcentaje = $dcto / 100;
                        $dcto_x_item = round(($i->pu * $porcentaje) * $i->cantidad);
                        $fd = new factura_detalle;
                        $fd->id_factura = $f->id;
                        $fd->id_repuestos = $i->id_repuestos;
                        $fd->id_unidad_venta = $i->id_unidad_venta;
                        $fd->id_local = $i->id_local;
                        $fd->precio_venta = $xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->PrcItem;//round($fd->pu_neto*(1+Session::get('PARAM_IVA')),2,PHP_ROUND_HALF_UP);
                        $fd->pu_neto = round($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->PrcItem/(1+Session::get('PARAM_IVA')),2);
                        $fd->cantidad = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->QtyItem); //$i->cantidad;
                        $fd->subtotal = $fd->precio_venta*$fd->cantidad;
                        //if hay descuento en el xml?? poner, sino 0;
                        if(!is_null(intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->DescuentoMonto))){
                            $fd->descuento = intval($xml->SetDTE->DTE->Documento->Detalle[$i->item-1]->DescuentoMonto); //round($i->descuento_item/(1+Session::get('PARAM_IVA')),0,PHP_ROUND_HALF_UP);
                        }else{
                            $fd->descuento=0;
                        }

                        $fd->total = $fd->subtotal-$fd->descuento;
                        $fd->activo = 1;
                        if($vendedor_id){
                            $fd->usuarios_id = $vendedor_id;
                        }else{
                            $fd->usuarios_id = Auth::user()->id;
                        }
                        //Buscamos el repuesto que se debe descontar el stock
                        $repuesto = repuesto::find($i->id_repuestos);


                            //Descontamos el stock de acuerdo al local
                            $repuesto->stock_actual = $repuesto->stock_actual - intval($i->cantidad);
                            $repuesto->save();

                            $this->revisar_stock_minimo($repuesto->id);
                            $fd->save();


                    }
                }

                $tipo_docu="factura";
                $num_docu=$f->num_factura;
                $id_documento_pago = $f->id;
            } // fin de FACTURA


            //Guardar detalle del multi pago
            //forma_pago,monto,referencia
            //Si existe numero de abono, se cambia el estado a pagado
            try {
                if(isset($r->num_abono)){
                    $num_abono = $r->num_abono;
                    $abono = abono::where('num_abono',$num_abono)->first();
                    $abono->activo = 0;
                    $abono->save();

                    $items = abono_detalle::where('id_abono',$abono->id)->get();
                    //items contiene todos los repuestos ingresados por pedido en el abono
                    foreach($items as $item){
                        $item->estado = 3; //Pagado
                        $item->save();
                    }
                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }



            if($r->venta=='contado'){
                //FALTA: VERIFICAR SI EL PAGO YA EXISTE Y PONERLO EN LA FECHA INDICADA.
                //EL CASO ES DEL CAMBIO DE UNA BOLETA DE AYER POR UNA FACTURA HOY QUE SE HACE MEDIANTE NOTA DE CREDITO
                //Y NO DUPLICAR EL PAGO SOBRE TODO LOS DE TRANSBANK...

                for ($i = 0; $i < count($r->forma_pago); $i++) {
                    $p = new pago;
                    $p->tipo_doc = substr($r->docu, 0, 2); //factura, boleta
                    $p->id_doc = $id_documento_pago; // Es el id del documento factura o boleta guardado más arriba
                    $p->id_cliente = $id_cliente;
                    $p->id_forma_pago = $r->forma_pago[$i];
                    //Si la forma de pago es con tarjeta de crédito le asignamos el detalle del pago
                    if($r->forma_pago[$i] == 2){
                        $p->referencia_pago = $r->nfp_credito;
                        //Si la forma de pago es con tarjeta de débito le asignamos el detalle del pago
                    }elseif($r->forma_pago[$i] == 5){
                        $p->referencia_pago = $r->nfp_debito;
                    }else{
                        //Por defecto debe guardar un 0
                        $p->referencia_pago = 0;
                    }
                    $p->fecha_pago = Carbon::today()->toDateString(); //Solo la fecha de hoy
                    $p->monto = $r->monto[$i];
                    $p->referencia = $r->referencia[$i];
                    $p->activo = 1;
                    if($vendedor_id){
                        $p->usuarios_id = $vendedor_id;
                    }else{
                        $p->usuarios_id = Auth::user()->id;
                    }
                    $p->save();
                }
            }

            /*
                Este fragmento de código es similar en clientes_controlador método agregacuenta
            */
            if($r->venta=='credito' || $r->venta=='delivery'){ //poner en la cuenta del cliente
                $cuenta=new cliente_cuenta;
                $cuenta->id_cliente=$id_cliente;
                $cuenta->fecha_operacion=Carbon::today()->toDateString(); //Solo la fecha;

                if($tipo_dte=='39'){
                    $total_deuda=$b->total;
                    $referencia_deuda="Boleta N° ".$b->num_boleta;
                }
                if($tipo_dte=='33'){
                    $total_deuda=$f->total;
                    $referencia_deuda="Factura N° ".$f->num_factura;
                }
                if($r->venta=='delivery'){
                    $referencia_deuda.=" %Delivery";
                }
                //es deuda
                $cuenta->pago=0;
                $cuenta->deuda=$total_deuda;

                $cuenta->referencia=$referencia_deuda;
                $cuenta->activo=1;
                if($vendedor_id){
                    $cuenta->usuarios_id = $vendedor_id;
                }else{
                    $cuenta->usuarios_id=Auth::user()->id;
                }

                $cuenta->save();
            }

        } catch (\Exception $e) {
            $estado=['estado'=>'ERROR','mensaje'=>$e->getMessage()];
            return json_encode($estado);
        }

        // Agregar datos del documento y correo del cliente para envío automático al ser aceptado por el SII
        if(isset($rs['estado']) && $rs['estado'] == 'OK'){
            $rs['num_doc'] = $num_docu;
            $rs['tipo_dte'] = $tipo_dte;
            $clienteCorreo = cliente_modelo::find($id_cliente);
            $rs['correo_cliente'] = ($clienteCorreo && $clienteCorreo->email) ? $clienteCorreo->email : '';
        }

        return json_encode($rs);
    }

    public function dame_carrito_transferido_vista_cliente_facturado($carrito_id){

        $carrito_transferido = new carrito_transferido();
        $ct = $carrito_transferido->dame_todo_carrito_cliente_facturado($carrito_id);
        return $ct;
    }

    public function revisar_stock_minimo($idrepuesto){
        // Revisamos si el repuesto ya existe en la tabla stock minimo
        $hoy = Carbon::today()->toDateString(); //Solo la fecha
        $repuesto = repuesto::find($idrepuesto);
        // Si el stock total es igual o menor que el stock minimo lo guardamos en la tabla stock_minimo
        $stock_total = intval($repuesto->stock_actual) + intval($repuesto->stock_actual_preventa);
        if(intval($repuesto->stock_minimo) >= intval($stock_total)){
            // guardamos el registro si el stock total es menor que el stock actual
            $stock_minimo = new stock_minimo();
            $stock_minimo->id_repuesto = $idrepuesto;
            $stock_minimo->fecha_emision = $hoy;
            $stock_minimo->save();
            // limpiamos el estado del repuesto
            $repuesto->estado = null;
            $repuesto->save();
        }
    }

    public function revisar_mail_estado($trackid)
    {
        //trackid viene desde ventas_principal.
        // tipo dte, numdoc estan en session tipo_dte y folio_dte

        $param=['trackid'=>$trackid];
        $param['tipo_dte']=Session::get('tipo_dte');
        $param['folio_dte']=Session::get('folio_dte');

        //AKI: ACTUALIZAR ESTADO...

        //aqui tal vez poner un try catch
        $rs=ClsSii::revisar_mail_estado($param);

        switch ($param['tipo_dte']){
            case '39':
                $dte=boleta::where('num_boleta',$param['folio_dte'])
                                    ->where('trackid',$param['trackid'])
                                    ->first();
            break;
            case '33':
                $dte=factura::where('num_factura',$param['folio_dte'])
                                    ->where('trackid',$param['trackid'])
                                    ->first();
            break;
        }

        //actualizar estado
        if(!is_null($dte)){ //encontrado
            $dte->estado_sii=$rs['estado'];
            $dte->estado=$rs['estado']=='ACEPTADO'?1:0;
            $dte->resultado_envio=$rs['mensaje'];
            $dte->save();
        }



        //mover el xml a carpeta enviados EJM
        //$source_file = 'foo/image.jpg';
        //$destination_path = 'bar/';
        //rename($source_file, $destination_path . pathinfo($source_file, PATHINFO_BASENAME));

        return json_encode($rs);


    }

    public function consignar(Request $r){
        try {
            $num=$this->dame_correlativo($r->docu) + 1;
            if($num<=0){
                $estado=['estado'=>'ERROR','mensaje'=>'No hay correlativos disponibles'];
                return json_encode($estado);
            }
            $carrito = new carrito_compra();
            $c = new consignacion;
            $c->num_consignacion = $num;
            $c->nombre_consignacion=$r->nombre_consignacion;
            $c->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha
            $c->fecha_expira = Carbon::today()->addDays(7)->toDateString();
            $c->id_cliente = $r->idcliente;
            $c->total = $carrito->dame_total(); //incluye el iva
            $c->neto = $carrito->dame_neto();
            $c->iva = $carrito->dame_iva();

            $c->activo = 1;
            $c->usuarios_id = Auth::user()->id;

            $c->save();

            $items=$carrito->dame_todo_carrito();
            foreach ($items as $i) {
                //Descontamos el stock de cada repuesto
                //Buscamos el repuesto y le descontamos el stock correspondiente al local de procedencia
                // 1: Bodega, 3: Tienda, 4: Casa Matríz
                $repuesto_buscado = repuesto::find($i->id_repuestos);
                switch ($i->id_local) {
                    case 1:
                        $repuesto_buscado->stock_actual -= $i->cantidad;
                        break;
                    case 3:
                        $repuesto_buscado->stock_actual_dos -= $i->cantidad;
                    break;
                    case 4:
                        $repuesto_buscado->stock_actual_tres -= $i->cantidad;
                    break;
                }
                //Guardamos el nuevo stock del repuesto en consignacion
                $repuesto_buscado->save();
                $cd = new consignacion_detalle;
                $cd->id_consignacion = $c->id;
                $cd->id_repuestos = $i->id_repuestos;

                // buscar el repuesto
                $repuesto = repuesto::find($i->id_repuestos);
                // buscamos si el repuesto esta en oferta
                $oferta = oferta_pagina_web::where('id_repuesto',$i->id_repuestos)->where('activo',1)->first();
                // verificar si la familia del repuesta esta en oferta
                $familia_dcto = descuento::where('id_familia', $repuesto->id_familia)->where('activo',1)->first();
                //$repuesto->precio_normal = $repuesto->precio_venta;
                if(isset($oferta)){
                    $repuesto->pu = $oferta->precio_actualizado;

                }elseif(!isset($oferta) && isset($familia_dcto)){
                    $repuesto->pu = $repuesto->precio_venta - (($familia_dcto->porcentaje/100) * $repuesto->precio_venta);
                    $repuesto->oferta = 2;
                }else{
                    $repuesto->pu = $repuesto->precio_venta;
                    $repuesto->oferta = 0;
                }

                $cd->id_unidad_venta = $i->id_unidad_venta;
                $cd->id_local=$i->id_local;
                $cd->precio_venta = $i->pu;
                $cd->cantidad = $i->cantidad;
                $cd->subtotal = $i->subtotal_item;
                $cd->descuento = $i->descuento_item;
                $cd->total = $i->total_item;
                $cd->activo = 1;
                $cd->usuarios_id = Auth::user()->id;
                $cd->save();


            } //Fin bucle carrito compras

            $num_docu = $c->num_consignacion;
            $this->actualizar_correlativo($r->docu, $num_docu);
            $estado=['estado'=>'OK','consignacion'=>$num_docu];
            return json_encode($estado);
        } catch (\Exception $error) {
            $estado=['estado'=>'ERRORRR','mensaje'=>$error->getMessage()];
            return json_encode($estado);
        }
    }

    public function cotizar(Request $r){
        try{
            $num=$this->dame_correlativo($r->docu) + 1;
            if($num<=0){
                $estado=['estado'=>'ERROR','mensaje'=>'No hay correlativos disponibles'];
                return json_encode($estado);
            }

            if($r->idcliente==0){ //no se eligió cliente
                $idcliente=$this->dame_cliente_sii();
            }else{
                $idcliente=$r->idcliente;
            }

            if($r->es_transferido == 'false'){
                $carrito=new carrito_compra();
                $items=$carrito->dame_todo_carrito();
            }else{
                $carrito=new carrito_transferido();
                $items=$carrito->dame_todo_carrito_cliente($r->nombre_carrito_transferido);
            }

            $c = new cotizacion;
            $c->num_cotizacion = $num;
            $c->nombre_cotizacion=$r->nombre_cotizacion;
            $c->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha
            $c->fecha_expira = Carbon::today()->addDays(21)->toDateString();
            $c->id_cliente = $r->idcliente;
            $c->total = $carrito->dame_total(); //incluye el iva
            $c->neto = $carrito->dame_neto();
            $c->iva = $carrito->dame_iva();

            $c->activo = 2;
            $c->usuarios_id = Auth::user()->id;
            $c->save();

            foreach ($items as $i) {
                $cd = new cotizacion_detalle;
                $cd->id_cotizacion = $c->id;
                $cd->id_repuestos = $i->id_repuestos;
                $cd->id_unidad_venta = $i->id_unidad_venta;
                $cd->id_local=$i->id_local;
                $cd->precio_venta = $i->pu;
                $cd->cantidad = $i->cantidad;
                $cd->subtotal = $i->subtotal_item;
                $cd->descuento = $i->descuento_item;
                $cd->total = $i->total_item;
                $cd->activo = 1;
                $cd->usuarios_id = Auth::user()->id;
                $cd->save();

            } //Fin bucle carrito compras

            $num_docu = $c->num_cotizacion;
            $this->actualizar_correlativo($r->docu, $num_docu);
            $estado=['estado'=>'OK','cotizacion'=>$num_docu];
            return json_encode($estado);
        } catch (\Exception $error) {
            $estado=['estado'=>'ERRORRR','mensaje'=>$error->getMessage()];
            return json_encode($estado);

        }
    }

    public function guardar_venta(Request $r){
        //usando auth en el grupo de rutas. ver web.php
        $carrito=new carrito_compra();
        $retornar_id = "0";
        $num_docu = 0;
        $id_cliente=$r->idcliente;
        if($id_cliente==0) //No se ha elegido cliente para cotización o boleta
        {
            $id_cliente=$this->dame_cliente_0();
            if($id_cliente<0)
            {
                $msje="eEn la tabla clientes no se ha definido el cliente 0000000000";
                return $msje;
            }
        }

        try {
            if ($r->docu == "cotizacion") //No hay forma de pago
            {
                //llega idcliente y docu q es cotizacion
                $c = new cotizacion;
                $c->num_cotizacion = $this->dame_correlativo($r->docu) + 1;
                $c->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha
                $c->fecha_expira = Carbon::today()->addDays($r->dias_expira)->toDateString();
                $c->id_cliente = $id_cliente;
                $c->total = $carrito->dame_total(); //incluye el iva
                $c->neto = $carrito->dame_neto();
                $c->iva = $carrito->dame_iva();

                $c->activo = 1;
                $c->usuarios_id = Auth::user()->id;
                $c->save();

                //Guarda detalle de cotización
                $cc = $carrito->dame_todo_carrito();
                foreach ($cc as $i) {
                    $cd = new cotizacion_detalle;
                    $cd->id_cotizacion = $c->id;
                    $cd->id_repuestos = $i->id_repuestos;
                    $cd->id_unidad_venta = $i->id_unidad_venta;
                    $cd->id_local=$i->id_local;
                    $cd->precio_venta = $i->pu;
                    $cd->cantidad = $i->cantidad;
                    $cd->subtotal = $i->subtotal_item;
                    $cd->descuento = $i->descuento_item;
                    $cd->total = $i->total_item;
                    $cd->activo = 1;
                    $cd->usuarios_id = Auth::user()->id;
                    $cd->save();

                } //Fin bucle carrito compras

                $num_docu = $c->num_cotizacion;
                $retornar_id = "co&" . $c->id."&".$num_docu."&".Carbon::today()->format('d-m-Y');

            } else { //venta factura o boleta
                $id_documento_pago = 0;
                if ($r->docu == "factura") //venta con factura
                {
                    //último correlativo utilizado
                    $nume=$this->dame_correlativo($r->docu);
                    if($nume<0) //Se acabó el correlativo autorizado por SII
                    {
                        return "eFACTURA:No hay correlativo autorizado por SII. Descargar nuevo CAF";
                    }else{
                        $nume++; //siguiente correlativo
                    }

                    //Envío al SII, retorna un JSON
                    $ref['id_cliente']=$id_cliente;
                    $rpta_sii=ClsSii::enviar_documento($r->docu,$nume,$ref);
                    $rs=json_decode($rpta_sii,true); //el true convierte en array asociativo... IMPORTANTE...
                    if($rs['estado']!="ACEPTADO")
                    {
                        return "e".$rs['estado'].": ".$rs['mensaje'];
                    }

                    $f = new factura;
                    $f->num_factura = $nume;
                    $f->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha
                    $f->id_cliente = $id_cliente;
                    $f->docum_referencia = "---";
                    $f->total = $carrito->dame_total(); //incluye el iva
                    $f->neto = $carrito->dame_neto();
                    $f->exento = 0;
                    $f->iva = $carrito->dame_iva();

                    $f->activo = 1;
                    $f->usuarios_id = Auth::user()->id;
                    $f->save();
                    $id_documento_pago = $f->id;

                    //Guarda detalle de factura, abrir carrito compras
                    $c = $carrito->dame_todo_carrito();
                    foreach ($c as $i) {
                        $fd = new factura_detalle;
                        $fd->id_factura = $f->id;
                        $fd->id_repuestos = $i->id_repuestos;
                        $fd->id_unidad_venta = $i->id_unidad_venta;
                        $fd->id_local = $i->id_local;
                        $fd->precio_venta = $i->pu;
                        $fd->cantidad = $i->cantidad;
                        $fd->subtotal = $i->subtotal_item;
                        $fd->descuento = $i->descuento_item;
                        $fd->total = $i->total_item;
                        $fd->activo = 1;
                        $fd->usuarios_id = Auth::user()->id;
                        $fd->save();

                        //Actualizar saldos en repuestos
                        //tabla SALDOS(id_repuestos,id_local,saldo)
                        //tabla REPUESTOS(id,stock_actual)
                        //en repuestocontrolador método público actualiza_saldos(operacion,idrep,idlocal,cantidad)
                        $rc = new repuestocontrolador();
                        $rc->actualiza_saldos("E", $i->id_repuestos, $i->id_local, $i->cantidad);

                    } //Fin bucle carrito compras

                    $num_docu = $f->num_factura;
                    $retornar_id = "fa&" . $f->id."&".$num_docu."&".Carbon::today()->format('d-m-Y');

                } else { // venta con boleta
                    if ($r->docu == "boleta") //venta con boleta
                    {
                        //Guarda cabecera de boleta
                        $nume=$this->dame_correlativo($r->docu);
                        if($nume<0) //Se acabó el correlativo autorizado por SII
                        {
                            return "eBOLETA:No hay correlativo autorizado por SII. Descargar nuevo CAF";
                        }else{
                            $nume++; //siguiente correlativo
                        }

                        $ref['id_cliente']=$id_cliente;
                        $rpta_sii=ClsSii::enviar_documento($r->docu,$nume,$ref);
                        $rs=json_decode($rpta_sii,true); //el true convierte en array asociativo... IMPORTANTE...

                        if($rs['estado']!="ACEPTADO")
                        {
                            switch ($rs['estado'])
                            {
                                case 'SIN_CORREO':
                                case 'ERROR_MAIL':
                                case 'ERROR_CAF':
                                case 'ERROR_TIMBRAR':
                                case 'ERROR_CERTIFICADO':
                                case 'ERROR_FIRMA_DTE':
                                case 'ERROR_AGREGAR_DTE':
                                case 'ERROR_CARATULA':
                                case 'ERROR_GENERAR_ENVIO_DTE':
                                case 'ERROR_TOKEN':
                                case 'ERROR_GUARDAR_XML':
                                case 'ERROR_ENVIO_SII': // Sii::enviar(...)
                                case 'ERROR_STATUS':
                                case 'ERROR_GET_ESTADO':
                                case 'ERROR_ESTADO_UPLOAD':
                                case 'ERROR_FATAL': // Exception
                                case 'ERROR_INDEFINIDO':
                                    return "e".$rs['estado'].": ".$rs['mensaje'];
                                break;
                                case 'ERROR_NO_EPR':
                                case 'ERROR_RECHAZADO':
                                case 'ERROR_REPARO':
                                    //guardar cabecera del documento especificando el error y el trackID si hay
                                    $b = new boleta;
                                    $b->num_boleta = $nume;
                                    $b->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha
                                    $b->id_cliente = $id_cliente;
                                    $b->estado = 0; //0.- que no es aceptado por SII. 1.- Es aceptado por SII
                                    $b->estado_sii=$rs['estado'];
                                    $b->trackid=$rs['trackid'];
                                    $b->url_xml=$rs['xml'];
                                    $b->total = 0;//$carrito->dame_total(); //incluye el iva
                                    $b->neto = 0; //$carrito->dame_neto();
                                    $b->exento = 0;
                                    $b->iva = 0; //$carrito->dame_iva();
                                    $b->activo = 1;
                                    $b->usuarios_id = Auth::user()->id;
                                    $b->save();
                                    $num_docu = $b->num_boleta;

                                    //guardar correlativo del documento
                                    $this->actualizar_correlativo($r->docu, $num_docu);
                                    return "e".$rs['estado'].": ".$rs['mensaje'];
                                break;
                                default:
                                    return "eError No conteplando en ClsSii::enviar_documento(...)";
                            }


                        }

                        //cabecera normal aceptada sin errores
                        $b = new boleta;
                        $b->num_boleta = $nume;
                        $b->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha
                        $b->id_cliente = $id_cliente;
                        $b->estado = 1;
                        $b->estado_sii=$rs['estado'];
                        $b->trackid=$rs['trackid'];
                        $b->url_xml=$rs['xml'];
                        $b->total = $carrito->dame_total(); //incluye el iva
                        $b->neto = $carrito->dame_neto();
                        $b->exento = 0;
                        $b->iva = $carrito->dame_iva();
                        $b->activo = 1;
                        $b->usuarios_id = Auth::user()->id;
                        $b->save();
                        $id_documento_pago = $b->id;

                        //Guarda detalle de boleta, abrir carrito compras
                        $c = $carrito->dame_todo_carrito();
                        foreach ($c as $i) {
                            $bd = new boleta_detalle;
                            $bd->id_boleta = $b->id;
                            $bd->id_repuestos = $i->id_repuestos;
                            $bd->id_unidad_venta = $i->id_unidad_venta;
                            $bd->id_local = $i->id_local;
                            $bd->precio_venta = $i->pu;
                            $bd->cantidad = $i->cantidad;
                            $bd->subtotal = $i->subtotal_item;
                            $bd->descuento = $i->descuento_item;
                            $bd->total = $i->total_item;
                            $bd->activo = 1;
                            $bd->usuarios_id = Auth::user()->id;
                            $bd->save();

                            //Actualizar saldos en repuestos
                            //tabla SALDOS(id_repuestos,id_local,saldo)
                            //tabla REPUESTOS(id,stock_actual)
                            //en  repuestocontrolador método público actualiza_saldos(operacion,idrep,idlocal,cantidad)
                            $rc = new repuestocontrolador();
                            $rc->actualiza_saldos("E", $i->id_repuestos, $i->id_local, $i->cantidad);

                        } //Fin bucle carrito compras

                        $num_docu = $b->num_boleta;
                        $retornar_id = "bo&" . $b->id."&".$num_docu."&".Carbon::today()->format('d-m-Y');
                    }
                } // fin venta boleta

                if ($r->venta == "contado") // a crédito no hay queforma de pago
                {
                    //Guardar detalle del multi pago
                    //forma_pago,monto,referencia
                    for ($i = 0; $i < count($r->forma_pago); $i++) {
                        $p = new pago;
                        $p->tipo_doc = substr($r->docu, 0, 2); //factura, boleta, cotizacion
                        $p->id_doc = $id_documento_pago;
                        $p->id_cliente = $id_cliente;
                        $p->id_forma_pago = $r->forma_pago[$i];
                        $p->fecha_pago = Carbon::today()->toDateString(); //Solo la fecha
                        $p->referencia = $r->referencia[$i];
                        $p->monto = $r->monto[$i];
                        $p->activo = 1;
                        $p->usuarios_id = Auth::user()->id;
                        $p->save();
                    }
                }
            }

            $this->actualizar_correlativo($r->docu, $num_docu);
            $referencia="---"; //para boletas y facturas
            return $retornar_id."&".$id_cliente."&".$referencia;
        } catch (\Exception $error) {
            $err="e".$error->getMessage();
            return $err;
        }

    }

    private function actualizar_correlativo($docu, $num)
    {
        $co = correlativo::where('documento', $docu)
            ->where('id_local', Session::get('local'))
            ->first();
        $co->correlativo = $num;
        $s=$co->save();
    }

    public function dame_historial_cotizaciones_vista(){
        return view('ventas.historial_cotizaciones');
    }

    public function buscar_cotizacion($tag){
        $cotizacion = cotizacion::where('nombre_cotizacion','like','%'.$tag.'%')->first();
        if($cotizacion){
            $detalle = cotizacion_detalle::select('cotizaciones_detalle.*','repuestos.descripcion','repuestos.precio_venta','repuestos.codigo_interno')
            ->where('cotizaciones_detalle.id_cotizacion',$cotizacion->id)
            ->join('repuestos','cotizaciones_detalle.id_repuestos','repuestos.id')
            ->get();
            $v = view('fragm.cotizacion',compact('cotizacion','detalle'));
            return $v;
        }

    }

    public function eliminar_cotizacion($id){
        try {
            //code...
            $cotizacion = cotizacion::find($id);
            $detalle = cotizacion_detalle::where('id_cotizacion',$id)->get();
            foreach($detalle as $d){
                //$d->delete();
            }
            $cotizacion->activo = 0;
            $cotizacion->save();
            $cotizaciones = $this->dame_historial_cotizaciones(0);
            return ['mensaje'=>'ok','cotizaciones'=>$cotizaciones];
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }

    }

    private function dameultimoitem($id_usu)
    {

        $ultimo = carrito_compra::where('usuarios_id', $id_usu)->max('item');//carrito_compra::where('usuarios_id', $id_usu)->latest()->value('item');
        if (is_null($ultimo)) {
            $ultimo = 0;
        }
        return $ultimo;
    }

    private function re_enumera_items_carrito()
    {
        $car=new carrito_compra();
        $carrito=$car->dame_todo_carrito();
        $c=1;
        foreach($carrito as $item){
            $item->item=$c;
            $item->save();
            $c++;
        }

    }

    private function re_enumera_items_carrito_transferido($cliente_id){
        $car=new carrito_transferido();
        $carrito=$car->dame_todo_carrito_cliente($cliente_id);
        $c=1;
        foreach($carrito as $item){
            $item->item=$c;
            $item->save();
            $c++;
        }
    }

    public function borrar_item_carrito($id)
    {
        // buscamos el carrito por id
        $carrito = carrito_compra::find($id);
        // return $carrito;
        // if($carrito->id_local !== 1){
        //     // se devuelve la cantidad del repuesto al stock_actual_mermas del repuesto
        //     $repuesto = repuesto::find($carrito->id_repuestos);
        //     $repuesto->stock_actual_mermas = $repuesto->stock_actual_mermas + $carrito->cantidad;
        //     $repuesto->save();
        // }
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
        carrito_compra::destroy($id);
        $this->re_enumera_items_carrito();
        return $this->dametotalcarrito();
    }

    public function borrar_item_carrito_transferido($id){
        // recuperamos el cliente id
        $carrito=carrito_transferido::find($id);
        $cliente_id=$carrito->carrito_id;

        carrito_transferido::destroy($id);
        $this->re_enumera_items_carrito_transferido($cliente_id);
        return [$this->dametotalcarritotransferido($cliente_id),$cliente_id];
    }

    private function dametotalcarrito()
    {
        //usando auth en el grupo de rutas. ver web.php
        $total = (new carrito_compra)->dame_total(Auth::user()->id);
        return $total;
    }

    private function dametotalcarritotransferido($cliente_id){
        $total=(new carrito_transferido)->dame_total_cliente($cliente_id);
        return $total;
    }

    public function agregar_carrito(Request $r)
    {

        //llega idrep y cantidad desde function agregar_carrito(id_rep) en ventas_principal.blade
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
        $user = Auth::user();
        try {

            //Buscar si artículo ya esta en el carrito
            $esta = carrito_compra::where('id_repuestos', $r->idrep)->where('usuarios_id',$user->id)->first();
            if (!is_null($esta)) {
                return "existe";
            }
            //En caso de que se quiera pagar el saldo pendiente de un abono
            if($r->desde == 'saldopendiente'){
                $c = new carrito_compra;
                $c->usuarios_id = Auth::user()->id;
                $c->item = $this->dameultimoitem(Auth::user()->id) + 1;

                //No tengo id repuestos, ya que es un repuesto mandado a pedir desde el extranjero y no existe en bodega
                $c->id_repuestos = $r->idrep;
                $c->id_local = $r->idlocal;
                $c->id_unidad_venta = 0;
                $c->cantidad = $r->cantidad;
                $c->pu = intval($r->total + (1+Session::get('PARAM_IVA'))) ; //Ya incluye el IVA, ESTE PRECIO DEBE PREDOMINAR
                $c->pu_neto=round($c->pu/(1+Session::get('PARAM_IVA')),2);
                $c->descuento_item = 0.00;
                $c->subtotal_item = $c->cantidad * $c->pu;
                $c->total_item = $c->subtotal_item - $c->descuento_item;
                $c->save();

            }else{
                //Aqui viene con un repuesto definido, que existe en bodega
                $repuesto = repuesto::find($r->idrep);
                if($r->idlocal == 1){
                    // verificar que la cantidad no sea mayor al stock actual
                    if(($r->cantidad > $repuesto->stock_actual)){
                        return 'error';
                    }
                }else if($r->idlocal == 2){
                    // verificar que la cantidad no sea mayor al stock disponible
                    if(($r->cantidad > $repuesto->stock_actual_mermas)){
                        return 'error';
                    }
                }else if($r->idlocal == 3){
                    // verificar que la cantidad no sea mayor al stock disponible
                    if(($r->cantidad > $repuesto->stock_actual_preventa)){
                        return 'error';
                    }
                }

                $nuevo_valor = $r->nuevo_valor;

                if($nuevo_valor !== null){
                   $tope = intval($repuesto->precio_venta * 0.10);
                    // convertir nuevo valor a entero
                    $nuevo_valor = intval($nuevo_valor);
                    // convertir precio de venta a entero
                    $precio_venta = intval($repuesto->precio_venta);

                    if($nuevo_valor > ($precio_venta + $tope) || $nuevo_valor < ($precio_venta - $tope)){
                        return 'valor';
                    }
                }else{
                    $rc = new repuestocontrolador;
                    $solicitudes = $rc->dameSolicitudesCambioPrecio();
                    if(count($solicitudes) > 0){
                        foreach($solicitudes as $s){
                            $hora_actual = date('H:i');
                            if($s->id_repuesto == $r->idrep && $s->estado == 1 && $s->id_usuario == Auth::user()->id && $s->duracion <= $hora_actual){
                                $nuevo_valor = $s->precio_nuevo;
                            }else{
                                $nuevo_valor = $repuesto->precio_venta;
                            }
                        }
                    }else{
                        $nuevo_valor = $repuesto->precio_venta;

                    }
                }




                //Recuperamos todas las familias que tengan descuentos
                $descuentos = descuento::all();

                    //Revisar si está en oferta
                    $o = oferta_pagina_web::where('id_repuesto',$r->idrep)->where('activo',1)->first();
                    $dcto_familia = descuento::where('id_familia',$repuesto->id_familia)->where('activo',1)->first();

                    $c = new carrito_compra;
                    $c->usuarios_id = Auth::user()->id;
                    $c->item = $this->dameultimoitem(Auth::user()->id) + 1;
                    // verificar si el item es menor a 20
                    if($c->item == 21){
                        return 'maximo';
                    }
                    $c->id_repuestos = $r->idrep;
                    $c->id_unidad_venta = 1;
                    $c->cantidad = $r->cantidad;
                    $c->pu = $nuevo_valor;
                    $c->id_local = $r->idlocal;
                    $c->pu_neto=round($c->pu/(1+Session::get('PARAM_IVA')),2);
                    $c->descuento_item = 0.00;
                    $c->subtotal_item = $c->cantidad * $c->pu;
                    $c->total_item = $c->subtotal_item - $c->descuento_item;

                    $c->save();

                    if ($r->idcliente != 0) //Se eligió cliente
                    {
                        $this->descuentos_carrito($r->idcliente);
                    }

                    // si el producto no viene de idlocal 1, descontar el stock de stock_actual_mermas
                    // if($r->idlocal!= 1){
                    //     $repuesto->stock_actual_mermas = $repuesto->stock_actual_mermas - $r->cantidad;
                    //     $repuesto->save();
                    // }

                    //Aplicar descuentos

                    $this->re_enumera_items_carrito();
                    return $this->dametotalcarrito();


            }


        } catch (\Exception $error) {
            $debug = $error;
            $v = view('errors.debug_ajax', compact('debug'))->render();
            return $v;
        }

    }

    public function agregar_carrito_transferido(Request $r){
        try {

            // $id = $r->id;
            // $cliente_id = $r->cliente_id;
            // $titulo = $r->titulo;
            $idrep = $r->idrep;
            $idlocal = $r->idlocal;
            $cantidad = $r->cantidad;
            $el_id_usuario = Auth::user()->id;
            $cliente_id = $r->cliente_id;
            $carrito_id = $r->carrito_id;

            //Buscar si artículo ya esta en el carrito
            $esta = carrito_transferido::where('cliente_id', $cliente_id)->where('id_repuestos',$idrep)->first();
            if (!is_null($esta)) {
                return "existe";
            }

            // revisar si hay stock en el local seleccionado
            $repuesto = repuesto::find($idrep);

            if($idlocal == 1 && ($cantidad > $repuesto->stock_actual)){
                return 'error';
            }elseif($idlocal == 2 && ($cantidad > $repuesto->stock_actual_mermas)){
                return 'error';
            }elseif($idlocal == 3 && ($cantidad > $repuesto->stock_actual_preventa)){
                return 'error';
            }

         // buscamos el ultimo carrito_transferido hacia el cajero
         $carrito_transferido = carrito_transferido::where('cajeros_id', $el_id_usuario)->orderBy('item', 'desc')->first();

         // buscamos el ultimo carrito_transferido del usuario
         $carrito_transferido = carrito_transferido::where('usuarios_id', $carrito_transferido->usuarios_id)->orderBy('item', 'desc')->first();

            $cajero_id = $carrito_transferido->cajeros_id;
            // buscar el repuesto
            $repuesto = repuesto::find($idrep);

            $el_id_usuario = Auth::user()->id;
            $ct=new carrito_transferido;
            $ct->usuarios_id = $el_id_usuario;

            $ct->cajeros_id = $cajero_id;
            $ct->cliente_id = $cliente_id;
            // avanzar el item en 1 del carrito_transferido
            $ct->item=$carrito_transferido->item + 1;
            $ct->id_repuestos=$idrep;

            $ct->id_local=$idlocal;
            $ct->id_unidad_venta=$repuesto->id_unidad_venta;
            $ct->cantidad=$cantidad;
            $o = oferta_pagina_web::where('id_repuesto',$r->idrep)->where('activo',1)->first();
            $dcto_familia = descuento::where('id_familia',$repuesto->id_familia)->where('activo',1)->first();
           //Si existe en oferta
           if(isset($o)){
                $ct->pu = $o->precio_actualizado; //Ya incluye el IVA, ESTE PRECIO DEBE PREDOMINAR, (ademas contiene el valor con el descuento en oferta)
            }elseif(!isset($o) && isset($dcto_familia) && ($dcto_familia->id_local <> 2)){

                $ct->pu = $repuesto->precio_venta - (($dcto_familia->porcentaje/100) * $repuesto->precio_venta);

            }else{
                $ct->pu = $repuesto->precio_venta;
            }
            // $ct->pu=$repuesto->precio_venta;
            $ct->subtotal_item=$ct->pu * $ct->cantidad;
            $ct->descuento_item=0.00;
            $ct->total_item=$ct->subtotal_item - $ct->descuento_item;
            $ct->titulo = $carrito_transferido->titulo;

            $ct->save();
            return $this->dametotalcarritotransferido($cliente_id);
        } catch (\Exception $error) {
            $debug = $error;
            $v = view('errors.debug_ajax', compact('debug'))->render();
            return $v;
        }

    }

    public function verificar_carrito_transferido(){
        try {
            $el_id_usuario = Auth::user()->id;
            // retornar todos los carritos transferidos del usuario
            $carrito_transferido = new carrito_transferido();
            $carrito_transferido = $carrito_transferido->dame_todo_carrito();
            $ct = [];
            foreach($carrito_transferido as $c){
                if($c->estado == 1){
                    array_push($ct,$c);
                }
            }
            return $ct;
        } catch (\Exception $error) {
            $debug = $error;
            $v = view('errors.debug_ajax', compact('debug'))->render();
            return $v;
        }
    }

    public function dame_abono($id){
        try {
            // return $id;
            $num_abono = $id;
            $abono = abono::where('num_abono',$num_abono)->first();
            if(!isset($abono)){
                return 'No existe este abono ... revise el codigo del abono';
            }
            $abono_detalle = abono_detalle::where('id_abono',$abono->id)->get();
            return [$abono,$abono_detalle];
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function dame_consignacion($id){
        try {
            // return $id;

            $consignacion = vale_consignacion::find($id);
            if(!isset($consignacion)){
                return 'No existe esta consignación ... revise el codigo';
            }
            $consignacion_detalle = vale_consignacion_detalle::where('id_doc',$consignacion->id)->where('devuelto',0)->get();
            return [$consignacion,$consignacion_detalle];
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function filtrar_vales($value){

            $vales = vale_mercaderia::where('activo',$value)->orderBy('numero_boucher','asc')->get();
            foreach($vales as $v){
                //Separamos el valor de updated_at para que solo me muestre la fecha en formato YYYY-MM-DD que será guardado en la primera posición
                $porciones = explode("T", $v->updated_at);
                //Creamos un nuevo atributo al objeto v que contendrá la fecha formateada correctamente.
                $v->fecha = Carbon::parse($porciones[0])->format("d-m-Y");
            }
            return $vales;

    }

    public function detalle_vale_mercaderia($numero_vale){
        try {
            $vale = vale_mercaderia::select('vale_mercaderia.*','users.name')
                            ->join('users','vale_mercaderia.usuarios_id','users.id')
                            ->where('vale_mercaderia.numero_boucher',$numero_vale)
                            ->first();
            $detalles = vale_mercaderia_detalle::select('vale_mercaderia_detalle.*','locales.local_nombre','repuestos.id as idrep','repuestos.precio_venta','repuestos.codigo_interno')
                                                ->where('vale_mercaderia_detalle.vale_mercaderia_id',$vale->id)
                                                ->join('repuestos','vale_mercaderia_detalle.repuesto_id','repuestos.id')
                                                ->join('locales','vale_mercaderia_detalle.local_id','locales.id')
                                                ->get();

            $v = view('fragm.busqueda_vale_mercaderia',compact('detalles','vale'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function detalle_ventas_vista(){
        $user = Auth::user();
        if($user->rol->nombrerol == "Administrador"){
            return view('ventas.detalle_ventas');
        }else{
            return redirect('/home');
        }

    }

    public function cambiar_precio_item_carrito($dato)
    {
        //06jun2020 por ahora no lo utilizo
        //Cuando se modifica un precio (en ventas->buscar->M) si se modifica
        //un repuesto debería modificar el precio del item del carrito si esta agregado.
        $rpta="XUXA";
        try{
            $d=explode("&",$dato);
            $idrep=$d[0];
            $nuevo_precio=$d[1];
            $idcliente=$d[2];
            //a todos los carritos pendientes...
            $cc=carrito_compra::where('id_repuestos',$idrep)->first();
            if(!is_null($cc))
            {
                $cc->pu=$nuevo_precio;
                $cc->subtotal_item=$cc->cantidad*$cc->pu;
                $cc->total_item=$cc->subtotal_item-$cc->descuento_item;
                $cc->save();
                //puxa... aqui revisar si el cliente tiene descuento para aplicarle...
                // XUXHE SU MAEEE...
                if ($r->idcliente != 0) //Se eligió cliente
                {
                    $this->descuentos_carrito($idcliente);
                }

                $rpta="OK";
            }else{
                $rpta="NO EXISTE";
            }

        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }
        return $rpta;
    }

    public function verificar_nombre_carrito($el_nombre_carrito,$id_cliente)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
        $el_id_usuario=Auth::user()->id;

        //Verificar que el carro no existe por nombre...
        $car=carrito_guardado::where('nombre_carrito',$el_nombre_carrito)
                                            ->where('usuarios_id',$el_id_usuario)
                                            ->first();
        if(!is_null($car))
        {
            return "existe";
        }else{
            $responde=$this->guardar_carrito_completo($el_nombre_carrito,$id_cliente,"NO");
            return $responde;
        }
    }

    public function vale_por_mercaderia(){
        return view('ventas.vale_por_mercaderia');
    }

    public function busqueda_vale_mercaderia($num_vale){
        try {
            $vale = vale_mercaderia::select('vale_mercaderia.*','users.name')
                                        ->join('users','vale_mercaderia.usuarios_id','users.id')
                                        ->where('vale_mercaderia.numero_boucher',$num_vale)
                                        ->first();

            $detalles = $this->dame_repuestos_valemercaderia($num_vale);
            $v = view('fragm.busqueda_vale_mercaderia',compact('vale','detalles'));
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function cargar_pedido($id_pedido){
        $num_abono = $id_pedido;
        try {
            $abono = abono::where('num_abono',$num_abono)->first();
            $abono->fecha_emision = Carbon::parse($abono->fecha_emision)->format('d-m-Y');
            $abono_detalle = abono_detalle::select('abono_detalle.*','abono_estado.descripcion_estado','repuestos.descripcion','repuestos.id as idrep')
                                            ->where('abono_detalle.id_abono',$abono->id)
                                            ->join('abono_estado','abono_estado.id','abono_detalle.estado')
                                            ->join('repuestos','abono_detalle.id_repuesto','repuestos.id')
                                            ->get();
            // sacar la hora desde el campo created_at y agregarla al abono
            $porciones = explode(" ", $abono->created_at);
            $abono->hora_emision = $porciones[1];
            $data = [$abono,$abono_detalle];
            return $data;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function guardar_vale_mercaderia(Request $req){
        try {
            $correlativo = $this->dame_correlativo('vale_mercaderia');
            $num_boucher = $correlativo;
            $vale_mercaderia = new vale_mercaderia;
            $vale_mercaderia->rut_cliente = $req->rut;
            $vale_mercaderia->nombre_cliente = $req->nombre_cliente;
            $vale_mercaderia->telefono_cliente = $req->telefono;
            $vale_mercaderia->descripcion = $req->descripcion;
            $vale_mercaderia->numero_documento = intval($req->numero_documento);
            $vale_mercaderia->tipo_doc = $req->tipo_doc;
            $vale_mercaderia->numero_boucher = $num_boucher+1;
            $vale_mercaderia->usuarios_id = Auth::user()->id;
            $vale_mercaderia->url_pdf = "vale_mercaderia_".$vale_mercaderia->numero_boucher.".pdf";
            $vale_mercaderia->valor = $req->valor;
            $vale_mercaderia->save();
            $cor = correlativo::find(7);
            $cor->correlativo = $num_boucher+1;
            $cor->save();
            $data = ["OK",$num_boucher+1];
            return $data;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }


    public function guardar_carrito_completo($el_nombre_carrito,$id_cliente,$existe)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión

        $el_id_usuario=Auth::user()->id;

        //Si existe el carrito, borrarlo porque confirmó que desea reemplazarlo en inventario.ventas_principal.blade.php (verificar_nombre_carrito())
        if($existe=="SI")
        {
            $borrados=carrito_guardado::where('nombre_carrito',$el_nombre_carrito)
                                    ->where('usuarios_id',$el_id_usuario)
                                    ->delete();
        }else{
            //NOTA: Guardar como máximo 5 carritos
            $cuantos=carrito_guardado::select('nombre_carrito')
                ->where('usuarios_id',$el_id_usuario)
                ->distinct()
                ->get()
                ->count();
            if($cuantos==5)
            {
                return "Solo se puede guardar 5 carritos como máximo.";
            }
        }

        //bucle del carrito activo
        $carrito_compra = carrito_compra::where('usuarios_id', $el_id_usuario)->get();
        foreach($carrito_compra as $cc)
        {
            $cg=new carrito_guardado;
            $cg->nombre_carrito=$el_nombre_carrito;
            $cg->usuarios_id = $el_id_usuario;
            $cg->id_cliente = $id_cliente;
            $cg->item=$cc->item;
            $cg->id_repuestos=$cc->id_repuestos;
            $cg->id_local=$cc->id_local;
            $cg->id_unidad_venta=$cc->id_unidad_venta;
            $cg->cantidad=$cc->cantidad;
            $cg->pu=$cc->pu;
            $cg->subtotal_item=$cc->subtotal_item;
            $cg->descuento_item=$cc->descuento_item;
            $cg->total_item=$cc->total_item;
            $cg->save();
        }
        return $el_nombre_carrito;
    }

    public function transferir_carrito_completo(Request $req){
      try {

        $id = $req->cajero_id;
        $cliente_id = $req->cliente_id;

        $titulo = $req->titulo;
        $carrito_id = $req->carrito_id;
        $el_id_usuario=Auth::user()->id;

        // Validar la imagen antes de procesar el carrito
        $image_path = null;
        if($req->hasFile('file')){
            try {
                $file = $req->file('file');

                $nombre = $file->getClientOriginalName();
                $nombre = time().$nombre;
                $file->move(public_path().'/imagenes/comprobantes_pago/',$nombre);
                $image_path = '/imagenes/comprobantes_pago/'.$nombre;
            } catch (\Exception $e) {
                return 'ERROR: Error al procesar la imagen: ' . $e->getMessage();
            }
        }

        $carrito_compra = carrito_compra::where('usuarios_id', $el_id_usuario)->get();
        // Validar si hay más de 20 productos en el carrito
        if($carrito_compra->count() > 20) {
            return 'ERROR: El carrito no puede contener más de 20 productos';
        }

        // Validar que el carrito no esté vacío
        if($carrito_compra->count() == 0) {
            return 'ERROR: El carrito está vacío';
        }

        // FELIPE BARRERA 238
        if($el_id_usuario == 238){
            $cli = cliente_vendedor::where('id_cliente', $cliente_id)->where('id_vendedor', $el_id_usuario)->where('activo',1)->first();
            if($cli){
                $el_id_usuario = $cli->id_vendedor;
            }else{
                $cliente_vendedor = cliente_vendedor::where('id_cliente',$cliente_id)->where('id_vendedor','<>',238)->where('activo',1)->first();
                $el_id_usuario = $cliente_vendedor->id_vendedor;
            }
        }

        $nuevo_correlativo = $this->dame_correlativo('carrito_transferido') + 1;
        if($nuevo_correlativo == 0){
            // buscamos el ultimo correlativo
            $ultimo = carrito_transferido::max('numero_carrito');
            $nuevo_correlativo = $ultimo + 1;
        }
        foreach($carrito_compra as $cc)
        {
            $ct=new carrito_transferido;
            $ct->numero_carrito = $nuevo_correlativo;

            $ct->usuarios_id = $el_id_usuario;
            $ct->cajeros_id = $id;
            $ct->cliente_id = $cliente_id;
            $ct->carrito_id = $nuevo_correlativo;
            $ct->fantasma_id = Auth::user()->id;
            $ct->id_direccion = $req->id_direccion;
            $ct->item=$cc->item;
            $ct->id_repuestos=$cc->id_repuestos;
            $ct->id_local=$cc->id_local;
            $ct->id_unidad_venta=$cc->id_unidad_venta;
            $ct->cantidad=$cc->cantidad;
            // verificar si el repuesto tiene oferta
            $o = oferta_pagina_web::where('id_repuesto',$cc->id_repuestos)->where('activo',1)->first();
            if(isset($o)){
                $ct->pu = $o->precio_actualizado; //Ya incluye el IVA, ESTE PRECIO DEBE PREDOMINAR, (ademas contiene el valor con el descuento en oferta)
                $ct->subtotal_item = $ct->pu * $ct->cantidad;
            }else{
                $ct->pu = $cc->pu;
                $ct->subtotal_item = $cc->subtotal_item;
            }
            $ct->descuento_item=$cc->descuento_item;
            $ct->total_item=$cc->total_item;
            $ct->titulo = $titulo;

            // Asignar la imagen procesada a todos los items
            if($image_path) {
                $ct->image_path = $image_path;
            }

            $ct->descripcion_tipo_despacho = $req->desc_tipo_despacho;
            $ct->fecha_emision = Carbon::now()->format('Y-m-d');

            $ct->save();
        }
        // actualizar correlativo
        $corr = correlativo::find(13);
        $corr->correlativo = $this->dame_correlativo('carrito_transferido') + 1;
        if($corr->correlativo == 0){
            $corr->correlativo = $nuevo_correlativo;
        }

        $corr->save();
        $this->enviar_correo_nota_venta($corr->correlativo);

        return 'OK';
      } catch (\Exception $e) {
        //throw $th;
        return $e->getMessage();
      }

    }

    public function enviar_correo_nota_venta($carrito_id){
        try {
            $correos_controller = new correos_controlador();
            // enviar un request con los datos necesarios
            $correo_destino = Auth::user()->email;

            // Crear una nueva instancia de Request
            $request = new Request();

            // Llenar el objeto Request con los datos necesarios
            $request->merge([
                'correo_destino' => $correo_destino,
                'carrito_id' => $carrito_id,
            ]);

            $resp = $correos_controller->enviar_correo_nota_venta($request);
            return $resp;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return 'OK';
    }

    public function cargar_carrito_completo($nombre_carrito)
    {
        $el_id_usuario=Auth::user()->id;
        //usar nombre del carrito (quien) y el id del usuario
        //borrar el carrito activo
        $this->borrar_carrito('actual');
        //copiar el carrito guardado hacia el carrito activo
        $guardado=carrito_guardado::where('usuarios_id',$el_id_usuario)
                                            ->where('nombre_carrito',$nombre_carrito)
                                            ->get();
        $id_cliente = $guardado[0]->id_cliente;
        if($id_cliente === 0){
            // Usuario por defecto
            $cliente = cliente_modelo::find(1);
        }else{
            $cliente = cliente_modelo::find($id_cliente);
        }
        foreach($guardado as $cg)
        {
            $cc=new carrito_compra;
            $cc->usuarios_id=$el_id_usuario;
            $cc->item=$cg->item;
            $cc->id_repuestos=$cg->id_repuestos;
            $cc->id_local=$cg->id_local;
            $cc->id_unidad_venta=$cg->id_unidad_venta;
            $cc->cantidad=$cg->cantidad;
            $cc->pu=$cg->pu;
            $cc->subtotal_item=$cg->subtotal_item;
            $cc->descuento_item=$cg->descuento_item;
            $cc->total_item=$cg->total_item;
            $cc->save();
        }
        return ["OK",$cliente];
    }

    public function eliminar_carrito_completo($nombre_carrito)
    {
        try {
            $el_id_usuario=Auth::user()->id;
            //usar nombre del carrito (quien) y el id del usuario
            //borrar el carrito activo
            // $this->borrar_carrito('actual');
            //copiar el carrito guardado hacia el carrito activo
            $guardado=carrito_guardado::where('usuarios_id',$el_id_usuario)
                                                ->where('nombre_carrito',$nombre_carrito)
                                                ->get();

            foreach($guardado as $item) $item->delete();

            return ['OK'];
        } catch (\Exception $e) {
            //throw $th;
            return ['ERROR',$e->getMessage()];
        }




    }

    public function dame_carritos_guardados()
    {
        //usando auth en el grupo de rutas. ver web.php
        $el_id_usuario=Auth::user()->id;
        $cgs=carrito_guardado::select('nombre_carrito')
                                        ->where('usuarios_id',$el_id_usuario)
                                        ->distinct()
                                        ->get()
                                        ->toJson();
        return $cgs;
    }

    public function borrar_carrito($cual)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
        $el_id_usuario=Auth::user()->id;
        if($cual=="actual")
        {
            $borrados=carrito_compra::where('usuarios_id',$el_id_usuario)
            ->delete();
        }
        if($cual=="guardados")
        {
            $borrados=carrito_guardado::where('usuarios_id',$el_id_usuario)
            ->delete();
        }
        if($cual=="transferidos")
        {
            $borrados=carrito_transferido::where('cajeros_id',$el_id_usuario)
            ->delete();
        }
        return $cual;

    }

    public function borrar_carrito_cliente($cliente_id){
        $el_id_usuario=Auth::user()->id;
        $borrados=carrito_transferido::where('carrito_id',$cliente_id)->where('cajeros_id',$el_id_usuario)->delete();
        $this->re_enumera_items_carrito_transferido($cliente_id);

        return ['mensaje' => 'ok'];
    }

    public function descuentos_carrito($id_cliente)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
        if($id_cliente==0){
            $tipo_descuento=0;
        }else{
            $c = cliente_modelo::select('tipo_descuento', 'porcentaje')
                ->where('id', $id_cliente)
                ->first();
            if(is_null($c)){
                $tipo_descuento=0;
            }else{
                $tipo_descuento=$c->tipo_descuento;
            }
        }


        $cc = carrito_compra::select('carrito_compras.*','repuestos.oferta')
                            ->where('carrito_compras.usuarios_id', Auth::user()->id)
                            ->join('repuestos','carrito_compras.id_repuestos','repuestos.id')
                            ->leftjoin('ofertas_pagina_web','ofertas_pagina_web.id_repuesto','repuestos.id')
                            ->get();

        if ($tipo_descuento == 0) // Sin Descuento
        {
            foreach ($cc as $item) {
                $item->descuento_item = 0;

                $item->total_item = $item->subtotal_item - $item->descuento_item;
                $item->save();

            }
        }

        if ($tipo_descuento == 1) //Descuento simple
        {
            //Que le aplique el porcentaje a cada item para que quede grabado y devuelva todo el carrito modificado.
            //Estoy buscando hacer "UPDATE carrito_compra set descuento_item=subtotal_item*porcentaje WHERE condicion"
            //Pero no se como hacerlo en laravel, como obtengo el valor subtotal_item en la misma query.
            //Asi que por ahora un bucle no mas... (21set2019)

            foreach ($cc as $item) {
                try {
                    $rep = repuesto::find($item->id_repuestos);
                    $dcto_familia = descuento::where('id_familia',$rep->id_familia)->first();
                    //Primero revisamos si tiene descuento por la familia
                    //Si el repuesto en el carrito no es oferta y no tiene descuento por familia se le aplica el descuento simple.
                    if($item->oferta == 0 && !$dcto_familia){

                        $item->descuento_item = $item->subtotal_item * $c->porcentaje / 100;
                        $item->total_item = $item->subtotal_item - $item->descuento_item;
                        $item->save();
                    }
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }

        }

        if ($tipo_descuento == 3) //Descuento por familia
        {
            //carrito_compras.id_repuestos / repuestos.id_familia / descuentos.id_familia, descuentos.porcentaje, descuentos.id_cliente

            foreach ($cc as $item) {
                $idfam = repuesto::where('id', $item->id_repuestos)->where('repuestos.activo',1)->value('id_familia');
                $porcentaje = descuento::where('id_cliente', $id_cliente)
                    ->where('id_familia', $idfam)
                    ->value('porcentaje');
                if (is_null($porcentaje)) {
                    $porcentaje = 0;
                }
                //No tiene descuento por familia
                $item->descuento_item = $item->subtotal_item * $porcentaje / 100;
                $item->total_item = $item->subtotal_item - $item->descuento_item;
                $item->save();
            }
        }
        return $id_cliente;

    }

    public function descuentos_carrito_transferido($id_cliente, $numero_carro)
    {

        try {
           //usando auth en el grupo de rutas. ver web.php //Valida sesión
            if($id_cliente==0){
                $tipo_descuento=0;
            }else{
                $c = cliente_modelo::select('tipo_descuento', 'porcentaje')
                    ->where('id', $id_cliente)
                    ->first();
                if(is_null($c)){
                    $tipo_descuento=0;
                }else{
                    $tipo_descuento=$c->tipo_descuento;
                }
            }




        $cc = carrito_transferido::select('carrito_transferido.*','repuestos.oferta')
                            ->where('carrito_transferido.cajeros_id', Auth::user()->id)
                            ->where('carrito_transferido.cliente_id',$numero_carro)
                            ->join('repuestos','carrito_transferido.id_repuestos','repuestos.id')
                            ->leftjoin('ofertas_pagina_web','ofertas_pagina_web.id_repuesto','repuestos.id')
                            ->get();



        if ($tipo_descuento == 0) // Sin Descuento
        {
            foreach ($cc as $item) {
                $item->descuento_item = 0;

                $item->total_item = $item->subtotal_item - $item->descuento_item;
                $item->save();

            }
        }

        if ($tipo_descuento == 1) //Descuento simple
        {
            //Que le aplique el porcentaje a cada item para que quede grabado y devuelva todo el carrito modificado.
            //Estoy buscando hacer "UPDATE carrito_compra set descuento_item=subtotal_item*porcentaje WHERE condicion"
            //Pero no se como hacerlo en laravel, como obtengo el valor subtotal_item en la misma query.
            //Asi que por ahora un bucle no mas... (21set2019)

            foreach ($cc as $item) {
                try {
                    $rep = repuesto::find($item->id_repuestos);
                    $dcto_familia = descuento::where('id_familia',$rep->id_familia)->first();
                    //Primero revisamos si tiene descuento por la familia
                    //Si el repuesto en el carrito no es oferta y no tiene descuento por familia se le aplica el descuento simple.
                    if($item->oferta == 0 && !$dcto_familia){

                        $item->descuento_item = $item->subtotal_item * $c->porcentaje / 100;
                        $item->total_item = $item->subtotal_item - $item->descuento_item;
                        $item->save();
                    }
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }


        }



        if ($tipo_descuento == 3) //Descuento por familia
        {
            //carrito_compras.id_repuestos / repuestos.id_familia / descuentos.id_familia, descuentos.porcentaje, descuentos.id_cliente

            foreach ($cc as $item) {
                $idfam = repuesto::where('id', $item->id_repuestos)->where('repuestos.activo',1)->value('id_familia');
                $porcentaje = descuento::where('id_cliente', $id_cliente)
                    ->where('id_familia', $idfam)
                    ->value('porcentaje');
                if (is_null($porcentaje)) {
                    $porcentaje = 0;
                }
                //No tiene descuento por familia
                $item->descuento_item = $item->subtotal_item * $porcentaje / 100;
                $item->total_item = $item->subtotal_item - $item->descuento_item;
                $item->save();
            }
        }
            //Devolvemos el numero del carrito transferido que viene representado como cliente_id
            $numero_carrito = $numero_carro;
            return $numero_carrito;
        } catch (\Exception $e) {
            return $e->getMessage();
        }


    }

    public function armar_kit(){
        $user = Auth::user();
        try {
            $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 4 && $permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == '/ventas/armar-kit'){
                        return view('ventas.armar_kit');
                    }
            }
        if($user->rol->nombrerol === "Administrador"){
                $v = view('ventas.armar_kit')->render();
                return $v;
            }else{
                return redirect('/home');
            }
        }catch (\Exception $e) {
            return $e->getMessage();
        }

        }

    public function agregar_repuesto_kit(Request $req){

        //Primero hay que preguntar si el repuesto existe en el kit
        try {
            $existe = armado_kit_detalle::where('id_kit',$req->id_kit)->where('id_repuesto',$req->id_repuesto)->first();
            $kit = repuesto::find($req->id_kit);

            // if($existe){
            //     return ['error','Repuesto ya existe en el kit'];
            // }else{

            // }
                $repuesto = repuesto::find($req->id_repuesto);
                if($req->local_id == 1){
                    //Si el local es bodega le asignamos la misma cantidad del stock del kit al repuesto agregado.
                    $cantidad_ = $kit->stock_actual;
                    if($req->cantidad > $repuesto->stock_actual){
                        return ['error','No existe demasiado stock en bodega'];
                    }
                }elseif($req->local_id == 3){
                    //Si el local es tienda le asignamos la misma cantidad del stock del kit al repuesto agregado.
                    $cantidad_ = $kit->stock_actual_dos;
                    if($req->cantidad > $repuesto->stock_actual_dos){
                        return ['error','No existe demasiado stock en tienda'];
                    }
                }else{
                    //Si el local es CASA MATRIZ le asignamos la misma cantidad del stock del kit al repuesto agregado.
                    $cantidad_ = $kit->stock_actual_tres;
                    if($req->cantidad > $repuesto->stock_actual_tres){
                        return ['error','No existe demasiado stock en Casa Matríz'];
                    }
                }
                $kit_detalle = new armado_kit_detalle;
                $kit_detalle->id_kit = $req->id_kit;
                $kit_detalle->id_repuesto = $req->id_repuesto;
                $kit_detalle->cantidad = $cantidad_;
                $kit_detalle->precio_unitario = $repuesto->precio_venta;
                $kit_detalle->usuario_id = Auth::user()->id;
                $kit_detalle->local_id = $req->local_id;
                $kit_detalle->total = $req->cantidad * $repuesto->precio_venta;
                $kit_detalle->activo = 1;
                $kit_detalle->save();
                $kit_completo = $this->dame_kit_completo($req->id_kit);
                //Actualizamos el valor del kit
                $kit = repuesto::find($req->id_kit);
                $precio_venta = 0;
                foreach ($kit_completo as $d) {
                    $precio_venta += intval($d->precio_venta) * 1;
                }
                $kit->precio_venta = $precio_venta;
                $kit->save();


                if($req->local_id_kit == 1){
                    $cantidad_a_descontar = $kit->stock_actual;
                }elseif($req->local_id_kit == 3){
                    $cantidad_a_descontar = $kit->stock_actual_dos;
                }else{
                    $cantidad_a_descontar = $kit->stock_actual_tres;
                }

                //Buscamos el stock del repuesto de acuerdo a su ubicacion y le restamos el stock del kit, para asegurar el stock del repuesto.


                if($req->local_id == 1){
                    if($repuesto->stock_actual >= $cantidad_a_descontar){
                        $repuesto->stock_actual = intval($repuesto->stock_actual) - intval($cantidad_a_descontar);
                    }

                }elseif($req->local_id == 3){
                    if($repuesto->stock_actual_dos >= $cantidad_a_descontar){
                        $repuesto->stock_actual_dos = intval($repuesto->stock_actual_dos) - intval($cantidad_a_descontar);
                    }

                }else{
                    if($repuesto->stock_actual_tres >= $cantidad_a_descontar){
                        $repuesto->stock_actual_tres = intval($repuesto->stock_actual_tres) - intval($cantidad_a_descontar);
                    }

                }
                $repuesto->save();
                return [$kit_completo, $repuesto, $kit];


        } catch (\Exception $e) {
            return $e->getMessage();
        }

        //Se debe crear un nuevo codigo interno con el prefijo de la familia kit generico, esto debe funcionar como una cotizacion
    }

    public function dame_kit_completo($id_kit){
        $kit_completo = armado_kit_detalle::select('armado_kit_detalle.*','repuestos.codigo_interno','repuestos.descripcion','repuestos.id as idrep','repuestos.precio_venta','locales.local_nombre')
                                            ->join('repuestos','armado_kit_detalle.id_repuesto','repuestos.id')
                                            ->join('locales','armado_kit_detalle.local_id','locales.id')
                                            ->where('armado_kit_detalle.id_kit', $id_kit)
                                            ->get();
        return $kit_completo;
    }

    public function eliminar_vale_mercaderia($id){
        $vale = vale_mercaderia::find($id);
        $vale->delete();
        return 'OK';
    }

    public function eliminar_consignacion($id){
        try {
            $consignacion = consignacion::where('id',$id)->first();
            $consignacion->activo = 0;
            $consignacion->save();
            // devolver los productos a su stock
            $detalles = consignacion_detalle::where('id_consignacion',$id)->get();
            foreach($detalles as $d){
                $repuesto = repuesto::find($d->id_repuestos);
                if($d->local_id == 1){
                    $repuesto->stock_actual += $d->cantidad;
                }elseif($d->local_id == 3){
                    $repuesto->stock_actual_dos += $d->cantidad;
                }else{
                    $repuesto->stock_actual_tres += $d->cantidad;
                }
                $repuesto->save();
            }
            return 'OK';
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }

    }

    public function eliminar_vale_consignacion($id){
        try {
            $vale = vale_consignacion::find($id);
            $detalle = vale_consignacion_detalle::where('id_doc', $vale->id)->get();
            foreach($detalle as $d){
                $d->delete();
            }
            $vale->delete();
            return 'OK';
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function eliminar_abono($id){
        $abono = abono::find($id);
        $abono->activo = 0;
        $abono->save();
        $abonos = $this->dame_abonos();
        return $abonos;
    }

    public function eliminar_abono_modal($id){
        $abono = abono::find($id);
        $abono->activo = 0;
        $abono->save();
        $abonos = $this->revisar_pedidos();
        return $abonos;
    }

    private function dame_abonos(){
        $abonos = abono::select('nombre_cliente','id','num_abono','fecha_emision')
                        ->where('activo',1)
                        ->distinct()
                        ->groupBy('nombre_cliente')
                        ->orderBy('id')
                        ->get();
        foreach($abonos as $a){
            $a->fecha_emision = Carbon::parse($a->fecha_emision)->format("d-m-Y");
        }


        return $abonos;
    }

    public function eliminar_repuesto_kit($idrep, $idkit){
        try {
            $ultimo_kit = armado_kit_detalle::where('id_repuesto',$idrep)->first();
            $repuesto_kit = repuesto::find($idrep);
            $local_a_devolver = $ultimo_kit->local_id;

            $ultimo_kit->delete();

            $detalle = $this->dame_kit_completo($idkit);
            $precio_venta = 0;
            foreach ($detalle as $d) {
                $precio_venta += intval($d->precio_venta) * 1;
            }
            //Buscamos el repuesto que contiene el kit
            $rep = repuesto::find($idkit);
            //Le devolvemos
            if($local_a_devolver == 1){
                $repuesto_kit->stock_actual += $rep->stock_actual;
            }elseif($local_a_devolver == 3){
                $repuesto_kit->stock_actual_dos += $rep->stock_actual_dos;
            }
            //Se actualiza el nuevo valor del kit
            $rep->precio_venta = $precio_venta;
            //Se guarda el nuevo valor
            $rep->save();

            return ['OK',$detalle,$precio_venta];
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function crear_kit($nombre){

        try {
            $nuevo_kit = new armado_kit;
            $nuevo_kit->nombre_kit = $nombre;
            $nuevo_kit->id_cliente = 4;
            $nuevo_kit->id_usuario = Auth::user()->id;
            $nuevo_kit->activo = 1;
            //$nuevo_kit->save();
            $ultimo_kit = $this->dame_ultimo_kit();
            if($ultimo_kit->nombre_kit == $nuevo_kit->nombre_kit){
                return ['ERROR','NO PUEDE SER EL MISMO KIT'];
            }
            return ['OK', $ultimo_kit];
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    private function dame_ultimo_kit(){
        $ultimo_kit = armado_kit::orderBy('id','asc')->first();
        return $ultimo_kit;
    }

    public function dame_formas_pago()
    {
        //usando auth en el grupo de rutas. ver web.php
        $formas = formapago::all();
        $v = view('fragm.dame_formas_pago', compact('formas'))->render();
        return $v;
    }

    public function dame_solo_formas_pago(Request $request){
        //usando auth en el grupo de rutas. ver web.php
        $formas = formapago::all();
        $formas_cliente_ids = [];
        if($request->has('id_cliente') && intval($request->id_cliente) > 0){
            $formas_cliente_ids = cliente_formas_pago::where('id_cliente', intval($request->id_cliente))
                ->pluck('id_forma_pago')
                ->toArray();
        }
        $v = view('fragm.dame_solo_formas_pago', compact('formas', 'formas_cliente_ids'))->render();
        return $v;
    }

    public function dame_formas_pago_delivery()
    {
        /* en la tabla formapago los registros son
            1. Efectivo
            2. Tarjeta Crédito
            3. Cheque
            4. Transferencia Banco
            5. Tarjeta Débito
            6. Otra
        */

        $fpd=[1,4];
        $formas = formapago::wherein('id',$fpd)->get();
        $v = view('fragm.dame_formas_pago_delivery', compact('formas'))->render();
        return $v;
    }

    public function dame_formas_pago_modificar_pagos(){
        $formas = formapago::where('activo',1)->get();
        return json_encode($formas);
    }

    public function cargar_pago($id){
        $pago=pago::find($id);
        return json_encode($pago);
    }

    public function actualizar_pago(Request $r){
        try {

            //Solo referencias de pago con tarjeta de credito y debito son para getnet
            if($r->id_forma_pago == 2 || $r->id_forma_pago == 5){

                pago::where('id',$r->id_pago_actualizar)
                ->update(['id_forma_pago'=>$r->id_forma_pago,'fecha_pago'=>$r->fecha_pago,'referencia'=>$r->referencia_pago,'referencia_pago'=> 2,'activo'=>$r->activo_pago]);
                $rpta=["estado"=>'OK'];
            }else{

                pago::where('id',$r->id_pago_actualizar)
                ->update(['id_forma_pago'=>$r->id_forma_pago,'fecha_pago'=>$r->fecha_pago,'referencia'=>$r->referencia_pago,'activo'=>$r->activo_pago]);
                $rpta=["estado"=>'OK'];
            }

        } catch (\Exception $e) {
            $rpta=["estado"=>'ERROR','mensaje'=>$e->getMessage()];
        }
        return json_encode($rpta);
    }

    public function agregar_pago(Request $r){

        $fecha_pago=$r->fecha_pago;
        list($tipo_doc,$id_doc,$num_doc,$total_doc,$id_cliente)=explode("_",$r->dato);
        if($tipo_doc=='33') $tipo_doc="fa";
        if($tipo_doc=='39') $tipo_doc="bo";

        try {
            for ($i = 0; $i < count($r->id_forma_pago); $i++) {
                $p = new pago;
                $p->tipo_doc = $tipo_doc; //factura, boleta
                $p->id_doc = $id_doc; // Es el id del documento factura o boleta guardado más arriba
                $p->id_cliente = $id_cliente;
                $p->id_forma_pago = $r->id_forma_pago[$i];
                $p->fecha_pago = $fecha_pago;
                $p->monto = $r->monto_forma_pago[$i];
                $p->referencia = $r->referencia_forma_pago[$i];
                $p->activo = 1;
                $p->usuarios_id = Auth::user()->id;
                $p->save();
            }
            //cambiar el estado de delivery a 2 según el documento
            if($tipo_doc=="bo"){
                $doc=boleta::find($id_doc);
            }else{
                $doc=factura::find($id_doc);
            }
            $doc->es_delivery=2; //Significa delivery pagado.
            $doc->save();
            return "OK";
        } catch (\Exception $e) {
            return $e->getMessage();
        }


    }

    public function confirmar_edicion_oferta(Request $req){
        try {
            $hoy=Carbon::today();
            $fecha_hoy=$hoy->toDateString();

            $id_oferta = $req->id_oferta;
            $desde = $req->desde;
            $hasta = $req->hasta;
            $oferta = oferta_pagina_web::find($id_oferta);

            $oferta->desde = $desde;
            $oferta->hasta = $hasta;
            if($fecha_hoy >= $desde && $fecha_hoy <= $hasta){
                $oferta->activo = 1;
            }else{
                $oferta->activo = 0;
            }
            $oferta->save();

            return 'OK';
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function dameoferta($idoferta){
        try {
            $oferta = oferta_pagina_web::select('ofertas_pagina_web.*','repuestos.codigo_interno','repuestos.descripcion','repuestos.precio_venta')
                                        ->join('repuestos','ofertas_pagina_web.id_repuesto','repuestos.id')
                                        ->where('ofertas_pagina_web.id',$idoferta)
                                        ->first();
            return $oferta;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function dame_carrito_vista()
    {
        try {
              //usando auth en el grupo de rutas. ver web.php //Valida sesión
            $car=new carrito_compra;
            $car_transferido = new carrito_transferido;
            $carrito=$car->dame_todo_carrito();

            $oferta = false;
            $total=$car->dame_total();
            $total_neto = $car->dame_total_sin_iva();

            $carrito_transferido = $car_transferido->dame_todo_carrito();

            foreach($carrito as $repuesto){
                // verificar si el repuesto esta en oferta
                $oferta_ = oferta_pagina_web::where('id_repuesto',$repuesto->id_repuestos)->first();
                if($oferta_){
                    $repuesto->pu = $oferta_->precio_actualizado;
                    $repuesto->subtotal_item = $oferta_->precio_actualizado * $repuesto->cantidad;
                    $oferta = true;
                }
            }

            foreach($carrito_transferido as $ct){

                    // guardar el image_path del usuario que enviar el carro
                    $vendedor = User::find($ct->usuarios_id);
                    $ct->image_path = $vendedor->image_path;
            }

            $v = view('fragm.ventas_carrito', compact('carrito', 'total','total_neto','carrito_transferido','oferta'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }


    }

    public function recargar_carrito_vista(){
        try {
            //usando auth en el grupo de rutas. ver web.php //Valida sesión
            $car=new carrito_compra;
            $car_transferido = new carrito_transferido;
            $carrito=$car->dame_todo_carrito();
            // foreach($carrito as $c){
            //     $o = oferta_pagina_web::where('id_repuesto',$c->id_repuestos)->first();
            //     if(isset($o)){
            //         $c->pu = $o->precio_actualizado;
            //         $c->subtotal_item = $o->precio_actualizado;
            //     }
            // }
            $total=0;
            $carrito_transferido = $car_transferido->dame_todo_carrito();

            foreach ($carrito as $c) {
                $repuesto = repuesto::find($c['idrepuesto']);
                $subtotal = intval($repuesto['precio_venta']) * intval($c['cantidad']);
                $c['pu'] = $repuesto['precio_venta'];
                $c['subtotal_item'] = $subtotal;

                $total += $c['subtotal_item'];
                $c['total_item'] = $total;
            }
            //Guardamos los nuevos valores del carrito
            //$carrito->save();


            $oferta = false;
            $v = view('fragm.ventas_carrito', compact('carrito', 'total','carrito_transferido','oferta'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function recargar_carrito_transferido_vista($cliente_id){

        try {
            //usando auth en el grupo de rutas. ver web.php //Valida sesión
            $car=new carrito_compra;
            $car_transferido = new carrito_transferido;
            //$carrito=$car->dame_todo_carrito();
            // foreach($carrito as $c){
            //     $o = oferta_pagina_web::where('id_repuesto',$c->id_repuestos)->first();
            //     if(isset($o)){
            //         $c->pu = $o->precio_actualizado;
            //         $c->subtotal_item = $o->precio_actualizado;
            //     }
            // }
            $total=0;
            $carrito = [];
            $carrito_transferido = $car_transferido->dame_todo_carrito_cliente($cliente_id);

            //Dejamos vacio el carrito transferido, ya que estará cargado
            //$carrito_transferido = [];
            foreach ($carrito_transferido as $c) {
                $repuesto = repuesto::find($c['id_repuestos']);
                $subtotal = intval($repuesto['precio_venta']) * intval($c['cantidad']);
                $c['pu'] = $repuesto['precio_venta'];
                $c['subtotal_item'] = $subtotal;

                $total += $c['subtotal_item'];
                $c['total_item'] = $total;
                $c['oferta'] = 0;
            }
            //Guardamos los nuevos valores del carrito
            //$carrito->save();


            $oferta = false;
            $total = $car_transferido->dame_total_cliente($cliente_id);
            $total_neto = $car_transferido->dame_total_sin_iva_cliente($cliente_id);

            $v = view('fragm.ventas_carrito_transferido', compact('carrito', 'total','total_neto','carrito_transferido','oferta'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function dame_carrito_transferido_vista()
    {

        $cajero_id = Auth::user()->id;

        //usando auth en el grupo de rutas. ver web.php //Valida sesión
        $car_transferido = new carrito_transferido;
        $total = $car_transferido->dame_total();
        $carrito_transferido = $car_transferido->dame_todo_carrito();

        $oferta = false;

        foreach ($carrito_transferido as $c) {
            // verificar si el repuesto esta en oferta
            $oferta = oferta_pagina_web::where('id_repuesto',$c->id_repuestos)->first();
            if($oferta){
                $c->pu = $oferta->precio_actualizado;
                $c->subtotal_item = $oferta->precio_actualizado;
                $c->oferta = 1;
                $oferta = true;
            }

        }


        $v = view('fragm.ventas_carrito_transferido', compact('carrito_transferido','total','oferta'))->render();
         return $v;

    }

    public function dame_carrito_transferido_vista_cliente($id, $tipo_documento = null){
        try {
            $numero_carrito = $id;
            $car_transferido = new carrito_transferido;
            $carrito_transferido = $car_transferido->dame_todo_carrito_cliente($numero_carrito);

            if($tipo_documento == null){
                $total = $car_transferido->dame_total_cliente($numero_carrito);
                $total_neto = $car_transferido->dame_total_sin_iva_cliente($numero_carrito);
            }else{
                if($tipo_documento == 'factura'){
                    $total = $car_transferido->dame_total_cliente($numero_carrito);
                    $total_neto = $car_transferido->dame_total_sin_iva_cliente($numero_carrito);
                }else{
                    $total = $car_transferido->dame_total_cliente($numero_carrito);
                    $total_neto = $total;
                    $total = $total / (1 + Session::get('PARAM_IVA'));
                }
            }


            $oferta = false;

            foreach ($carrito_transferido as $c) {
                // La marca de oferta debe depender de una oferta vigente, no de
                // registros históricos ni de la bandera general del repuesto.
                $c->oferta = 0;
                $oferta_activa = oferta_pagina_web::where('id_repuesto', $c->id_repuestos)
                    ->where('activo', 1)
                    ->first();

                if($oferta_activa){
                    $c->pu = $oferta_activa->precio_actualizado;
                    $c->subtotal_item = $oferta_activa->precio_actualizado * $c->cantidad;
                    $c->total_item = $c->subtotal_item;
                    $c->oferta = 1;
                    $oferta = true;
                }
            }

            $cliente = cliente_modelo::find($carrito_transferido[0]->cliente_id);

            $cc = new clientes_controlador();
            $giros = $cc->dame_giros_cliente($carrito_transferido[0]->cliente_id);
            $direcciones = $cc->dame_direcciones_cliente($carrito_transferido[0]->cliente_id);
            $formas_pago = $cc->dame_formas_pago_cliente($carrito_transferido[0]->cliente_id);

            $v = view('fragm.ventas_carrito_transferido', compact('carrito_transferido','total','total_neto','oferta'))->render();
            return ['vista' => $v, 'cliente' => $cliente, 'giros' => $giros, 'direcciones' => $direcciones, 'formas_pago' => $formas_pago,'carrito_transferido' => $carrito_transferido];
        } catch (\Exception $e) {
        //throw $th;
            return $e->getMessage();
        }

    }

    public function precios_boleta($id){
        try {
            $numero_carrito = $id;
            $car_transferido = new carrito_transferido;
            $carrito_transferido = $car_transferido->dame_todo_carrito_cliente($numero_carrito);


            $total_neto = $car_transferido->dame_total_sin_iva_cliente($numero_carrito);
            $total = $total_neto / (1 + Session::get('PARAM_IVA'));

            $oferta = false;

            foreach($carrito_transferido as $c){
                $repuesto = repuesto::find($c->id_repuestos);
                $c->pu = ($c->pu) * (1 + Session::get('PARAM_IVA'));
                $c->subtotal_item = ($c->pu) * $c->cantidad;
                $c->total_item = ($c->subtotal_item);
            }
            $v = view('fragm.ventas_carrito_transferido', compact('carrito_transferido','total','total_neto','oferta'))->render();
            return $v;
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }

    }

    public function precios_factura($id){
        $car = $this->dame_carrito_transferido_vista_cliente($id);
        $v = $car['vista'];
        return $v;
    }

    private function dame_cliente_0()
    {
        $rpta=-1; //No esta definido el cliente 0000000
        $c0=cliente_modelo::where('rut','LIKE','00000%')->first();
        if(!is_null($c0))
        {
            $rpta=$c0->id;
        }
        return $rpta;
    }

    private function dame_cliente_sii()
    {
        $rpta=-1; //No esta definido el rut del sii
        $c0=cliente_modelo::where('rut','60803000K')->first();
        if(!is_null($c0))
        {
            $rpta=$c0->id;
        }
        return $rpta;
    }

    public function dame_cotizaciones($id_cliente)
    {
        //usando auth en el grupo de rutas. ver web.php
        if($id_cliente==0) //En ventas no se ha elegido cliente
        {
            $id_cliente=$this->dame_cliente_0(); //Buscamos en la tabla clientes el ID del cliente NO ELEGIDO.
            if($id_cliente<0) //No se ha definido el cliente 000000000 que significa cliente NO ELEGIDO.
                return $id_cliente;
        }
        $hoy=Carbon::today();
        $fecha_hoy=$hoy->toDateString();

        //Borramos las cotizaciones vencidas. Pancho dice que despues de 30 días.
        //Si no se muestran las cotizaciones vencidas, por que no borrarlas de una vez???
        $fecha_30=$hoy->subDays(30);
        $borrados1=cotizacion::where('created_at','<=',$fecha_30)
                                            ->delete();
        $borrados2=cotizacion_detalle::where('created_at','<=',$fecha_30)
                                            ->delete();

        $cot=cotizacion::select(\DB::raw('cotizaciones.id,cotizaciones.num_cotizacion,cotizaciones.nombre_cotizacion,cotizaciones.usuarios_id,DATE_FORMAT(cotizaciones.fecha_emision,\'%d-%m-%Y\') as fecha, IF(clientes.tipo_cliente=0,CONCAT(clientes.nombres,\' \',clientes.apellidos),clientes.razon_social) as elcliente'))
                                ->join('clientes','cotizaciones.id_cliente','clientes.id')
                                ->where('id_cliente',$id_cliente)
                                ->where('fecha_expira','>=',$fecha_hoy)
                                ->where('fecha_emision','<=',$fecha_hoy)
                                ->where('usuarios_id',Auth::user()->id)
                                ->orderBy('fecha_emision','DESC')
                                ->get()->toJson();
        return $cot;
    }

    public function dame_cotizaciones_mes($datos)
    {
        list($nombre,$id_cliente)=explode("&",$datos);
        $hoy=Carbon::today();
        $fecha_hoy=$hoy->toDateString();

        //Borramos las cotizaciones vencidas. Pancho dice que despues de 30 días.
        //Si no se muestran las cotizaciones vencidas, por que no borrarlas de una vez???
        $fecha_30=$hoy->subDays(30);
        $borrados1=cotizacion::where('created_at','<',$fecha_30)
                                            ->delete();
        $borrados2=cotizacion_detalle::where('created_at','<',$fecha_30)
                                            ->delete();

        if($id_cliente==0){
            $cot=cotizacion::select(\DB::raw('cotizaciones.id,cotizaciones.num_cotizacion,cotizaciones.nombre_cotizacion,DATE_FORMAT(cotizaciones.fecha_emision,\'%d-%m-%Y\') as fecha, \'Ninguno\' as elcliente'))
                                ->where('cotizaciones.nombre_cotizacion','LIKE','%'.$nombre.'%')
                                ->where('cotizaciones.fecha_emision','>=',$fecha_30)
                                ->orderBy('cotizaciones.fecha_emision','DESC')
                                ->get();
        }else{
            $cot=cotizacion::select(\DB::raw('cotizaciones.id,cotizaciones.num_cotizacion,cotizaciones.nombre_cotizacion,DATE_FORMAT(cotizaciones.fecha_emision,\'%d-%m-%Y\') as fecha, IF(clientes.tipo_cliente=0,CONCAT(clientes.nombres,\' \',clientes.apellidos),clientes.razon_social) as elcliente'))
                                ->join('clientes','cotizaciones.id_cliente','clientes.id')
                                ->where('cotizaciones.nombre_cotizacion','LIKE','%'.$nombre.'%')
                                ->where('cotizaciones.fecha_emision','>=',$fecha_30)
                                ->orderBy('cotizaciones.fecha_emision','DESC')
                                ->get();
        }
        return $cot->toJson();
    }

    public function cargar_consignacion($num_consignacion){
        $el_id_usuario=Auth::user()->id;
        try {
            //borrar el carrito activo
            $this->borrar_carrito('actual');
            //copiar el carrito guardado hacia el carrito activo
            $id_consignacion=consignacion::where('num_consignacion',$num_consignacion)
                                    ->value('id');

            $consignacion=consignacion_detalle::where('id_consignacion',$id_consignacion)
                                            ->orderBy('id','ASC')
                                            ->get();
            $item=1;
            if($consignacion->count()>0){
                foreach($consignacion as $cot)
                {
                    $cc=new carrito_compra;
                    $cc->usuarios_id=$el_id_usuario;
                    $cc->item=$item;
                    $cc->id_repuestos=$cot->id_repuestos;
                    $cc->id_local=$cot->id_local;
                    $cc->id_unidad_venta=$cot->id_unidad_venta;
                    $cc->cantidad=$cot->cantidad;
                    $cc->pu=$cot->precio_venta;
                    $cc->pu_neto=round($cc->pu/(1+Session::get('PARAM_IVA')),2);
                    $cc->subtotal_item=$cot->subtotal;
                    $cc->descuento_item=$cot->descuento;
                    $cc->total_item=$cot->total;
                    $cc->save();
                    $item++;
                }
                return "OK";
            }else{
                return "No existe la consignacion ".$num_consignacion;
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function cargar_cotizacion($num_cotizacion)
    {
        $el_id_usuario=Auth::user()->id;
        try {
            //borrar el carrito activo
            $this->borrar_carrito('actual');
            //copiar el carrito guardado hacia el carrito activo
            $id_cotizacion=cotizacion::where('num_cotizacion',$num_cotizacion)
                                    ->value('id');

            $cotizacion=cotizacion_detalle::where('id_cotizacion',$id_cotizacion)
                                            ->orderBy('id','ASC')
                                            ->get();
            $item=1;
            if($cotizacion->count()>0){
                foreach($cotizacion as $cot)
                {
                    $cc=new carrito_compra;
                    $cc->usuarios_id=$el_id_usuario;
                    $cc->item=$item;
                    $cc->id_repuestos=$cot->id_repuestos;
                    $cc->id_local=$cot->id_local;
                    $cc->id_unidad_venta=$cot->id_unidad_venta;
                    $cc->cantidad=$cot->cantidad;
                    $cc->pu=$cot->precio_venta;
                    $cc->pu_neto=round($cc->pu/(1+Session::get('PARAM_IVA')),2);
                    $cc->subtotal_item=$cot->subtotal;
                    $cc->descuento_item=$cot->descuento;
                    $cc->total_item=$cot->total;
                    $cc->save();
                    $item++;
                }
                return "OK";
            }else{
                return "No existe la cotizacion ".$num_cotizacion;
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function cargar_cotizacion_bodega($num_cotizacion)
    {
        $el_id_usuario=Auth::user()->id;
        try {

            //copiar el carrito guardado hacia el carrito activo
            $cotizacion=cotizacion::where('num_cotizacion',$num_cotizacion)
                                    ->first();

            $cotizacion->fecha_emision = Carbon::parse($cotizacion->fecha_emision)->format("d-m-Y");

            if($cotizacion){
                $detalle = cotizacion_detalle::select('cotizaciones_detalle.*','repuestos.descripcion','repuestos.precio_venta','repuestos.codigo_interno')
                ->where('cotizaciones_detalle.id_cotizacion',$cotizacion->id)
                ->join('repuestos','cotizaciones_detalle.id_repuestos','repuestos.id')
                ->get();

                $v = view('fragm.cotizacion_bodega',compact('cotizacion','detalle'));
                return $v;
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function envio_directo(){
        echo "Iniciando envio directo<br>";
        // datos del envío
        $xml = file_get_contents(base_path().'/xml/generados/directo.xml');
        $RutEnvia = '5483206-0';
        $RutEmisor = '5483206-0';

        // solicitar token
        $clave1="juana206"; // juana206 //panchorepuestos8311048
        $clave2="panchorepuestos8311048";
        $archivo_firma=base_path().'/cert/juanita_libreDTE.p12';
        if(is_readable($archivo_firma))
        {
            $firma_config=['file'=>$archivo_firma,'pass'=>$clave1];
            $Firma=new FirmaElectronica($firma_config);
        }else{
            echo "no hay certificado firma<br>";
        }
        $token=Auto::getToken($Firma);
        echo "Token: ".$token."<br>";

        // enviar DTE
        $result = Sii::enviar($RutEnvia, $RutEmisor, $xml, $token);
        var_dump($result);
//$xml=$result['mensaje'];


        // Mostrar resultado del envío
        if ($xml->STATUS!='0') {
            echo"STATUS: ".$result->STATUS;
        }else{
            echo 'DTE envíado. Track ID '.$xml->TRACKID;
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //usando auth en el grupo de rutas. ver web.php //Valida sesión
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function probar_codint(){
        $max_id=repuesto::max('id');
        $max_buscar=100;
        $ids=[];
        $buscado=[];
        $campo=[];
        $registros=[];
        $encontrados=0;
        $no_encontrados=0;
        for($i=0;$i<$max_buscar;$i++){
            do{
                $valor=repuesto::where('repuestos.id',random_int(1,$max_id))
                    ->where('repuestos.activo',1)
                    ->value('codigo_interno');
            }while(is_null($valor));

            if(!is_null($valor)){
                $campo['valor']=$valor;
                $campo['resultado']=$this->buscar_por_descripcion("7".$valor);
                if($campo['resultado']==0){
                    $no_encontrados++;
                }else{
                    $encontrados++;
                }
                array_push($registros,$campo);
            }

        }
        return view('pruebas.prueba_buscar',compact('registros','encontrados','no_encontrados'))->render();
    }

    public function probar_codprov(){
        $max_id=repuesto::max('id');
        $max_buscar=100;
        $ids=[];
        $buscado=[];
        $campo=[];
        $registros=[];
        $encontrados=0;
        $no_encontrados=0;
        for($i=0;$i<$max_buscar;$i++){
            do{
                $valor=repuesto::where('repuestos.id',random_int(1,$max_id))
                    ->where('repuestos.activo',1)
                    ->value('cod_repuesto_proveedor');
            }while(is_null($valor));

            if(!is_null($valor)){
                $campo['valor']=$valor;
                $campo['resultado']=$this->buscar_por_descripcion("7".$valor);
                if($campo['resultado']==0){
                    $no_encontrados++;
                }else{
                    $encontrados++;
                }
                array_push($registros,$campo);
            }

        }
        return view('pruebas.prueba_buscar',compact('registros','encontrados','no_encontrados'))->render();
    }

    public function probar_codoem(){
        /*
SELECT repuestos.id,repuestos.codigo_interno,repuestos.descripcion,repuestos.version_vehiculo, repuestos.activo,oems.codigo_oem
FROM repuestos
inner join oems
on repuestos.id=oems.id_repuestos
where oems.codigo_oem='MS1173GP025'
        */

        $max_buscar=100;
        $ids=[];
        $buscado=[];
        $campo=[];
        $registros=[];
        $encontrados=0;
        $no_encontrados=0;
        $max_id=oem::max('id');
        $id_repuestos=repuesto::select('id')
                                ->where('activo',1)
                                ->get()
                                ->toArray();
/*
        $valor=oem::select('oems.codigo_oem')->where('oems.id',random_int(1,$max_id))
                    ->wherein('oems.id_repuestos',$id_repuestos)
                    //->value('oems.codigo_oem')
                    ->toSql();
        dd($valor);
        */
        for($i=0;$i<$max_buscar;$i++){
            do{
                $valor=oem::where('id',random_int(1,$max_id))
                            ->wherein('id_repuestos',$id_repuestos)
                            ->value('codigo_oem');
            }while(is_null($valor));

            if(!is_null($valor)){
                $campo['valor']=$valor;
                $campo['resultado']=$this->buscar_por_descripcion("7".$valor);
                if($campo['resultado']==0){
                    $no_encontrados++;
                }else{
                    $encontrados++;
                }
                array_push($registros,$campo);
            }

        }
        return view('pruebas.prueba_buscar',compact('registros','encontrados','no_encontrados'))->render();
    }

    public function probar_codfam(){
        $max_buscar=20;
        $ids=[];
        $buscado=[];
        $campo=[];
        $registros=[];
        $encontrados=0;
        $no_encontrados=0;
        $max_id=familia::max('id');

        for($i=0;$i<$max_buscar;$i++){
            do{
                $valor=familia::where('id',random_int(1,$max_id))
                            ->value('nombrefamilia');
            }while(is_null($valor));
            if(!is_null($valor)){
                $campo['valor']=$valor;
                $campo['resultado']=$this->buscar_por_descripcion("7".$valor);
                if($campo['resultado']==0){
                    $no_encontrados++;
                }else{
                    $encontrados++;
                }
                array_push($registros,$campo);
            }

        }
        return view('pruebas.prueba_buscar',compact('registros','encontrados','no_encontrados'))->render();
    }

    public function probar_codfab(){
        $max_buscar=20;
        $ids=[];
        $buscado=[];
        $campo=[];
        $registros=[];
        $encontrados=0;
        $no_encontrados=0;
        $max_id=fabricante::max('id');

        for($i=0;$i<$max_buscar;$i++){
            do{
                $valor=fabricante::where('id',random_int(1,$max_id))
                            ->value('codigo_fab');
            }while(is_null($valor));
            if(!is_null($valor)){
                $campo['valor']=$valor;
                $campo['resultado']=$this->buscar_por_descripcion("7".$valor);
                if($campo['resultado']==0){
                    $no_encontrados++;
                }else{
                    $encontrados++;
                }
                array_push($registros,$campo);
            }

        }
        return view('pruebas.prueba_buscar',compact('registros','encontrados','no_encontrados'))->render();
    }

    public function damedteporfechas($tipodte,$fechainicial,$fechafinal){
        if($tipodte=='33'){
            try {
                $dtes=factura::select('facturas.fecha_emision','facturas.created_at',
                                'facturas.num_factura',
                                'facturas.total',
                                'facturas.trackid',
                                'facturas.estado_sii',
                                'facturas.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.empresa as razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                        ->join('clientes','facturas.id_cliente','clientes.id')
                        ->join('users','facturas.usuarios_id','users.id')
                        ->where('facturas.fecha_emision','>=',$fechainicial)
                        ->where('facturas.fecha_emision','<=',$fechafinal)
                        ->where('facturas.activo',1)
                        ->orderBy('facturas.id','DESC')
                        ->get();

                $v=view('fragm.listado_dte_por_fechas',compact('dtes','tipodte'))->render();
                return $v;
            } catch (\Exception $e) {
                return $e->getMessage();
            }

        }
        if($tipodte=='39'){
            $dtes=boleta::select('boletas.fecha_emision','boletas.created_at',
                                'boletas.num_boleta',
                                'boletas.total',
                                'boletas.trackid',
                                'boletas.estado_sii',
                                'boletas.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                        ->join('clientes','boletas.id_cliente','clientes.id')
                        ->join('users','boletas.usuarios_id','users.id')
                        ->where('boletas.fecha_emision','>=',$fechainicial)
                        ->where('boletas.fecha_emision','<=',$fechafinal)
                        ->where('boletas.activo',1)
                        ->orderBy('boletas.id','DESC')
                        ->get();

            $v=view('fragm.listado_dte_por_fechas',compact('dtes','tipodte'))->render();
            return $v;
        }
        if($tipodte=='61'){
            $dtes=nota_de_credito::select('notas_de_credito.fecha_emision','notas_de_credito.created_at',
                                'notas_de_credito.num_nota_credito',
                                'notas_de_credito.total',
                                'notas_de_credito.trackid',
                                'notas_de_credito.estado_sii',
                                'notas_de_credito.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.empresa as razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                        ->join('clientes','notas_de_credito.id_cliente','clientes.id')
                        ->join('users','notas_de_credito.usuarios_id','users.id')
                        ->where('notas_de_credito.fecha_emision','>=',$fechainicial)
                        ->where('notas_de_credito.fecha_emision','<=',$fechafinal)
                        ->where('notas_de_credito.activo',1)
                        ->orderBy('notas_de_credito.id','DESC')
                        ->get();

            $v=view('fragm.listado_dte_por_fechas',compact('dtes','tipodte'))->render();
            return $v;
        }
        if($tipodte=='56'){
            $dtes = nota_de_debito::select('notas_de_debito.fecha_emision','notas_de_debito.created_at',
                                'notas_de_debito.num_nota_debito',
                                'notas_de_debito.total',
                                'notas_de_debito.trackid',
                                'notas_de_debito.estado_sii',
                                'notas_de_debito.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.empresa as razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                        ->join('clientes','notas_de_debito.id_cliente','clientes.id')
                        ->join('users','notas_de_debito.usuarios_id','users.id')
                        ->where('notas_de_debito.fecha_emision','>=',$fechainicial)
                        ->where('notas_de_debito.fecha_emision','<=',$fechafinal)
                        ->where('notas_de_debito.activo',1)
                        ->orderBy('notas_de_debito.id','DESC')
                        ->get();

            $v=view('fragm.listado_dte_por_fechas',compact('dtes','tipodte'))->render();
            return $v;
        }
        if($tipodte=='52'){
            $dtes=guia_de_despacho::select('guias_de_despacho.fecha_emision','guias_de_despacho.created_at',
                                'guias_de_despacho.num_guia_despacho',
                                'guias_de_despacho.total',
                                'guias_de_despacho.trackid',
                                'guias_de_despacho.estado_sii',
                                'guias_de_despacho.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.empresa as razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                        ->join('clientes','guias_de_despacho.id_cliente','clientes.id')
                        ->join('users','guias_de_despacho.usuarios_id','users.id')
                        ->where('guias_de_despacho.fecha_emision','>=',$fechainicial)
                        ->where('guias_de_despacho.fecha_emision','<=',$fechafinal)
                        ->where('guias_de_despacho.activo',1)
                        ->orderBy('guias_de_despacho.id','DESC')
                        ->get();

            $v=view('fragm.listado_dte_por_fechas',compact('dtes','tipodte'))->render();
            return $v;
        }
    }

    public function exportar_ventas_excel(Request $request){
        try {
            $fechaInicial = $request->query('fechainicial');
            $fechaFinal = $request->query('fechafinal');
            $tipoDte = $request->query('tipodte');
            $filename = 'repuestos_vendidos_'.$fechaInicial.'_'.$fechaFinal.'.xls';

            return Excel::download(new DetalleVentasExport($fechaInicial, $fechaFinal, $tipoDte), $filename);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function dameventasporfechas($tipodte,$fechainicial,$fechafinal){

        if($tipodte=='33'){
            $dtes=factura::select('facturas.fecha_emision','facturas.id as identificador','facturas.created_at',
                                'facturas.num_factura',
                                'facturas.total',
                                'facturas.trackid',
                                'facturas.estado_sii',
                                'facturas.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                        ->join('clientes','facturas.id_cliente','clientes.id')
                        ->join('users','facturas.usuarios_id','users.id')
                        ->where('facturas.fecha_emision','>=',$fechainicial)
                        ->where('facturas.fecha_emision','<=',$fechafinal)
                        ->where('facturas.activo',1)
                        ->orderBy('facturas.id','DESC')
                        ->get();

                $v=view('fragm.listado_detalleventas_por_fechas',compact('dtes','tipodte'))->render();
                return $v;
        }
        if($tipodte=='39'){
            $dtes=boleta::select('boletas.fecha_emision','boletas.id as identificador','boletas.created_at',
                                'boletas.num_boleta',
                                'boletas.total',
                                'boletas.trackid',
                                'boletas.estado_sii',
                                'boletas.url_xml',
                                'clientes.tipo_cliente',
                                'clientes.rut',
                                'clientes.razon_social',
                                'clientes.nombres',
                                'clientes.apellidos',
                                'clientes.email',
                                'users.name'
                                )
                                ->join('clientes','boletas.id_cliente','clientes.id')
                                ->join('users','boletas.usuarios_id','users.id')
                                ->where('boletas.fecha_emision','>=',$fechainicial)
                                ->where('boletas.fecha_emision','<=',$fechafinal)
                                ->where('boletas.activo',1)
                                ->orderBy('boletas.created_at','ASC')
                                ->get();


                $v=view('fragm.listado_detalleventas_por_fechas',compact('dtes','tipodte'))->render();
                return $v;
        }

        if($tipodte == '61'){
                    $dtes=nota_de_credito::select('notas_de_credito.fecha_emision','notas_de_credito.id as identificador','notas_de_credito.created_at',
                    'notas_de_credito.num_nota_credito',
                    'notas_de_credito.total',
                    'notas_de_credito.trackid',
                    'notas_de_credito.estado_sii',
                    'notas_de_credito.url_xml',
                    'clientes.tipo_cliente',
                    'clientes.rut',
                    'clientes.razon_social',
                    'clientes.nombres',
                    'clientes.apellidos',
                    'clientes.email',
                    'users.name'
                    )
            ->join('clientes','notas_de_credito.id_cliente','clientes.id')
            ->join('users','notas_de_credito.usuarios_id','users.id')
            ->where('notas_de_credito.fecha_emision','>=',$fechainicial)
            ->where('notas_de_credito.fecha_emision','<=',$fechafinal)
            ->where('notas_de_credito.activo',1)
            ->orderBy('notas_de_credito.id','DESC')
            ->get();

            $v=view('fragm.listado_detalleventas_por_fechas',compact('dtes','tipodte'))->render();
            return $v;
        }

    }

    public function devolverstockrechazado(Request $req){
        $tipodte = $req->tipodte;
        $num_doc = $req->num_doc;
        $mensaje = '';
        if($tipodte == '33'){
            $dte = factura::where('num_factura', $num_doc)->where('estado',0)->where('devuelto',0)->first();

            if($dte){
                $detalle = factura_detalle::where('id_factura', $dte->id)->get();
                $repuestosDevueltos = []; // Array para guardar los códigos internos
                foreach($detalle as $d){
                     $rep = repuesto::find($d->id_repuestos);
                     $d->repuesto = $rep;
                     if($d->id_local == 1){
                         $rep->stock_actual += $d->cantidad;
                     }else if($d->id_local == 2){
                         $rep->stock_actual_dos += $d->cantidad;
                     }else{
                         $rep->stock_actual_tres += $d->cantidad;
                     }
                     $dte->devuelto = 1;
                     $dte->save();

                     if($d->id_local == 1){
                        $destino = 'Bodega';
                     }else if($d->id_local == 2){
                        $destino = 'Mermas';
                     }else{
                        $destino = 'Preventa';
                     }
                    // Guardar info del repuesto devuelto
                    $repuestosDevueltos[] = [
                        'codigo_interno' => $rep->codigo_interno,
                        'cantidad_devuelta' => $d->cantidad,
                        'id_local' => $d->id_local,
                        'destino' => $destino
                    ];
                     $rep->save();
                }

                return ['estado' => 'ok', 'msj' => 'SUS PRODUCTOS FUERON DEVUELTOS', 'codigos_internos' => $repuestosDevueltos];
            }else{
                return ['estado' => 'error', 'msj' => 'ESTE DOCUMENTO YA FUE DEVUELTO'];
            }
        }else if($tipodte == '39'){
            $dte = boleta::where('num_boleta', $num_doc)->first();
            $detalle = boleta_detalle::where('id_boleta', $dte->id)->get();
            foreach($detalle as $d){
                 $rep = repuesto::find($d->id_repuestos);
                 $d->repuesto = $rep;
                 if($d->id_local == 1){
                     $rep->stock_actual += $d->cantidad;
                 }else if($d->id_local == 2){
                     $rep->stock_actual_dos += $d->cantidad;
                 }else{
                     $rep->stock_actual_tres += $d->cantidad;
                 }
                 $rep->save();
            }
            return ['estado' => 'ok', 'msj' => 'SUS PRODUCTOS FUERON DEVUELTOS'];
        }else{
            return ['estado' => 'error', 'msj' => 'no hay detalle'];
        }
    }

    public function modificarDte($valor, $num_dte, $tipo_dte){
        if($tipo_dte == 39){
            $boleta = boleta::where('boletas.id',$num_dte)->first();
            $boleta->es_credito = $valor;
            $boleta->save();
        }elseif($tipo_dte == 33){
            $factura = factura::where('facturas.id',$num_dte)->first();
            $factura->es_credito = $valor;
            $factura->save();
        }

        return 'OK';
    }

    public function damedetalleboleta($tipodte,$id_boleta){
        if($tipodte == 33){
            $boleta = factura::where('facturas.id',$id_boleta)->join('users','facturas.usuarios_id','users.id')->first();
            $boleta->fecha_emision = Carbon::parse($boleta->fecha_emision)->format("d-m-Y");
            $boleta_detalle = factura_detalle::where('facturas_detalle.id_factura',$id_boleta)
                                                ->join('repuestos','facturas_detalle.id_repuestos','repuestos.id')
                                                ->get();
            $v = view('fragm.detalleboleta',compact('boleta_detalle','boleta','tipodte'))->render();
            return $v;
        }

        if($tipodte == 39){
            try {
                $boleta = boleta::where('boletas.id',$id_boleta)->join('users','boletas.usuarios_id','users.id')->first();
                $boleta->fecha_emision = Carbon::parse($boleta->fecha_emision)->format("d-m-Y");
                $boleta_detalle = boleta_detalle::where('boletas_detalle.id_boleta',$id_boleta)
                                                ->join('repuestos','boletas_detalle.id_repuestos','repuestos.id')
                                                ->get();
                $v = view('fragm.detalleboleta',compact('boleta_detalle','boleta','tipodte'))->render();
                return $v;
            } catch (\Exception $e) {
                return $e->getMessage();
            }

        }

        if($tipodte == 61){

                $boleta = nota_de_credito::where('notas_de_credito.id',$id_boleta)->join('users','notas_de_credito.usuarios_id','users.id')->first();
                $boleta->fecha_emision = Carbon::parse($boleta->fecha_emision)->format("d-m-Y");
                $boleta_detalle = nota_de_credito_detalle::where('notas_de_credito_detalle.id_nota_de_credito',$id_boleta)
                                                ->join('repuestos','notas_de_credito_detalle.id_repuestos','repuestos.id')
                                                ->get();
                $v = view('fragm.detalleboleta',compact('boleta_detalle','boleta','tipodte'))->render();
                return $v;
        }

    }

    public function damedetalleboleta_num_doc($tipodte,$id_boleta){
        if($tipodte == 33){
            $boleta = factura::select('facturas.*','users.name')->where('facturas.num_factura',$id_boleta)->join('users','facturas.usuarios_id','users.id')->first();
            $boleta->fecha_emision = Carbon::parse($boleta->fecha_emision)->format("d-m-Y");
            $boleta_detalle = factura_detalle::where('facturas_detalle.id_factura',$boleta->id)
                                                ->join('repuestos','facturas_detalle.id_repuestos','repuestos.id')
                                                ->get();
            $v = view('fragm.detalleboleta',compact('boleta_detalle','boleta','tipodte'))->render();
            return $v;
        }

        if($tipodte == 39){
            try {
                $boleta = boleta::select('boletas.*','users.name')->where('boletas.num_boleta',$id_boleta)->join('users','boletas.usuarios_id','users.id')->first();

                $boleta->fecha_emision = Carbon::parse($boleta->fecha_emision)->format("d-m-Y");
                $boleta_detalle = boleta_detalle::where('boletas_detalle.id_boleta',$boleta->id)
                                                ->join('repuestos','boletas_detalle.id_repuestos','repuestos.id')
                                                ->get();
                $v = view('fragm.detalleboleta',compact('boleta_detalle','boleta','tipodte'))->render();
                return $v;
            } catch (\Exception $e) {
                return $e->getMessage();
            }

        }

        if($tipodte == 61){

                $boleta = nota_de_credito::where('notas_de_credito.num_nota_credito',$id_boleta)->join('users','notas_de_credito.usuarios_id','users.id')->first();
                $boleta->fecha_emision = Carbon::parse($boleta->fecha_emision)->format("d-m-Y");
                $boleta_detalle = nota_de_credito_detalle::where('notas_de_credito_detalle.id_nota_de_credito',$boleta->id)
                                                ->join('repuestos','notas_de_credito_detalle.id_repuestos','repuestos.id')
                                                ->get();
                $v = view('fragm.detalleboleta',compact('boleta_detalle','boleta','tipodte'))->render();
                return $v;
        }

    }

    public function eliminar_factura($id_factura){
        $fac = compras_cab::find($id_factura);
        $fac->delete();
        $detalle = compras_det::where('id_factura_cab',$id_factura)->get();
        //return $detalle;
        return 'OK';
    }

    public function arqueo(){
        try {
            $cajeros = user::where('role_id',20)->get();
            $user = Auth::user();
            $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 3 && $permiso_detalle->usuarios_id == Auth::user()->id){
                    return view('ventas.arqueo',compact('cajeros'))->render();
                    }
            }

            if($user->rol->nombrerol === "Cajer@" || $user->rol->nombrerol === "Administrador"){
                $v = view('ventas.arqueo',compact('cajeros'))->render();
                return $v;
            }else{
                return redirect('/home');
            }
        } catch (\Throwable $th) {
            //throw $th;
        }


    }

    public function arqueo_detalle_old($info){
        list($fecha, $doc, $id_usu) = explode("&", $info);
        $monto_1 = 0;
        $monto_2 = 0;
        $monto_3 = 0;
        $monto_4 = 0;
        $monto_5 = 0;
        $monto_6 = 0;

        $monto_1_f = 0;
        $monto_2_f = 0;
        $monto_3_f = 0;
        $monto_4_f = 0;
        $monto_5_f = 0;
        $monto_6_f = 0;

        $formas_pago = formapago::where('activo', 1)->orderBy('id')->get();

        $fecha_falsa = '2021-11-06';
        $boletas = boleta::where('fecha_emision', $fecha_falsa)->where('usuarios_id', $id_usu)->get();
        $total_boletas = count($boletas);

        $facturas = factura::where('fecha_emision', $fecha_falsa)->where('usuarios_id', $id_usu)->get();
        $total_facturas = count($facturas);

        $pagos_efectivo = pago::where('fecha_pago',$fecha_falsa)
                                ->where('usuarios_id',$id_usu)
                                ->where('activo',1)
                                ->where('tipo_doc','bo')
                                ->where('id_forma_pago',1)
                                ->get();
        $total_pagos_efectivo = count($pagos_efectivo);

        foreach($pagos_efectivo as $pago_efectivo){
            $monto_1 += $pago_efectivo->monto;
        }

        $pagos_efectivo_f = pago::where('fecha_pago',$fecha_falsa)
                                ->where('usuarios_id',$id_usu)
                                ->where('activo',1)
                                ->where('tipo_doc','fa')
                                ->where('id_forma_pago',1)
                                ->get();
        $total_pagos_efectivo_f = count($pagos_efectivo_f);

        foreach($pagos_efectivo_f as $pago_efectivo_f){
            $monto_1_f += $pago_efectivo_f->monto;
        }

        $pagos_tc = pago::where('fecha_pago',$fecha_falsa)
                                ->where('usuarios_id',$id_usu)
                                ->where('activo',2)
                                ->where('tipo_doc','bo')
                                ->where('id_forma_pago',2)
                                ->get();
        $total_pagos_tc = count($pagos_tc);

        foreach($pagos_tc as $pago_tc){
            $monto_2 += $pago_tc->monto;
        }

        $pagos_tc_f = pago::where('fecha_pago',$fecha_falsa)
                                ->where('usuarios_id',$id_usu)
                                ->where('activo',2)
                                ->where('tipo_doc','fa')
                                ->where('id_forma_pago',2)
                                ->get();
        $total_pagos_tc_f = count($pagos_tc_f);

        foreach($pagos_tc_f as $pago_tc_f){
            $monto_2_f += $pago_tc_f->monto;
        }

        $pagos_cheque = pago::where('fecha_pago',$fecha_falsa)
                                ->where('usuarios_id',$id_usu)
                                ->where('activo',3)
                                ->where('tipo_doc','bo')
                                ->where('id_forma_pago',3)
                                ->get();
        $total_pagos_cheque = count($pagos_cheque);

        foreach($pagos_cheque as $pago_cheque){
            $monto_3 += $pago_cheque->monto;
        }

        $pagos_cheque_f = pago::where('fecha_pago',$fecha_falsa)
                                ->where('usuarios_id',$id_usu)
                                ->where('activo',3)
                                ->where('tipo_doc','fa')
                                ->where('id_forma_pago',3)
                                ->get();
        $total_pagos_cheque_f = count($pagos_cheque_f);

        foreach($pagos_cheque_f as $pago_cheque_f){
            $monto_3_f += $pago_cheque_f->monto;
        }

        $pagos_tb = pago::where('fecha_pago',$fecha_falsa)
                                ->where('usuarios_id',$id_usu)
                                ->where('activo',3)
                                ->where('tipo_doc','bo')
                                ->where('id_forma_pago',4)
                                ->get();
        $total_pagos_tb = count($pagos_tb);


        foreach($pagos_tb as $pago_tb){
            $monto_4 += $pago_tb->monto;
        }

        $pagos_tb_f = pago::where('fecha_pago',$fecha_falsa)
                                ->where('usuarios_id',$id_usu)
                                ->where('activo',3)
                                ->where('tipo_doc','fa')
                                ->where('id_forma_pago',4)
                                ->get();
        $total_pagos_tb_f = count($pagos_tb_f);

        foreach($pagos_tb_f as $pago_tb_f){
            $monto_4_f += $pago_tb_f->monto;
        }

        $pagos_transbank = pago::where('fecha_pago',$fecha_falsa)
                                ->where('usuarios_id',$id_usu)
                                ->where('activo',1)
                                ->where('tipo_doc','bo')
                                ->where('id_forma_pago',5)
                                ->get();
        $total_pagos_transbank = count($pagos_transbank);

        foreach($pagos_transbank as $pago_transbank){
            $monto_5 += $pago_transbank->monto;
        }

        $pagos_transbank_f = pago::where('fecha_pago',$fecha_falsa)
                                ->where('usuarios_id',$id_usu)
                                ->where('activo',1)
                                ->where('tipo_doc','fa')
                                ->where('id_forma_pago',5)
                                ->get();
        $total_pagos_transbank_f = count($pagos_transbank_f);

        foreach($pagos_transbank_f as $pago_transbank_f){
            $monto_5_f += $pago_transbank_f->monto;
        }

        $monto_total = $monto_1 + $monto_2 + $monto_3 + $monto_4 + $monto_5;
        $monto_total_f = $monto_1_f + $monto_2_f + $monto_3_f + $monto_4_f + $monto_5_f;

        $v = view('fragm.arqueo_data',compact(
            'total_boletas',
            'total_facturas',
            'total_pagos_efectivo',
            'monto_total',
            'monto_total_f',
            'total_pagos_efectivo_f',
            'total_pagos_tc',
            'total_pagos_tc_f',
            'total_pagos_cheque',
            'total_pagos_cheque_f',
            'total_pagos_tb',
            'total_pagos_tb_f',
            'total_pagos_transbank',
            'total_pagos_transbank_f',
            'formas_pago'))->render();
        return $v;
    }

    public function arqueo_detalle($info){
        list($fecha, $doc, $id_usu) = explode("&", $info);

        $usuario = User::find($id_usu);

        $formas_pago = formapago::where('activo', 1)->orderBy('id')->get();
        //$formas_pago: id, formapago
        $fp_array = $formas_pago->toArray();

    // foreach ($usuarios as $usuario) {
        $totales[$usuario->name]['delivery'] = 0;
        foreach ($formas_pago as $forma) {
            // Boletas generedas por cajera Marveise
            $pago_bol = pago::select('pagos.*', 'boletas.id as id_boleta', 'boletas.num_boleta as num_boleta', 'boletas.es_delivery')
                ->join('boletas', 'pagos.id_doc', 'boletas.id')
                ->where('pagos.activo', 1)
                ->where('pagos.fecha_pago', $fecha)
                ->where('pagos.usuarios_id', $usuario->id)
                ->where('pagos.id_forma_pago', $forma->id)
                ->where('pagos.tipo_doc', 'bo')
                ->get();
            //Abonos realizados a cajera Marveise
            $pago_abono = pago::select('pagos.*', 'abono.id as id_abono', 'abono.num_abono as num_abono')
                ->join('abono', 'pagos.id_doc', 'abono.id')
                ->where('pagos.activo', 1)
                ->where('pagos.fecha_pago', $fecha)
                ->where('pagos.usuarios_id', $usuario->id)
                ->where('pagos.id_forma_pago', $forma->id)
                ->where('pagos.tipo_doc', 'ab')
                ->get();

            // Boletas generadas por cajera JORGE
            $pago_bol_jorge = pago::select('pagos.*', 'boletas.id as id_boleta', 'boletas.num_boleta as num_boleta', 'boletas.es_delivery')
                ->join('boletas', 'pagos.id_doc', 'boletas.id')
                ->where('pagos.activo', 1)
                ->where('pagos.fecha_pago', $fecha)
                ->where('pagos.usuarios_id', 9)
                ->where('pagos.id_forma_pago', $forma->id)
                ->where('pagos.tipo_doc', 'bo')
                ->get();
            // Boletas generadas por cajero MARIA JOSE
            $pago_bol_mj = pago::select('pagos.*', 'boletas.id as id_boleta', 'boletas.num_boleta as num_boleta', 'boletas.es_delivery')
                ->join('boletas', 'pagos.id_doc', 'boletas.id')
                ->where('pagos.activo', 1)
                ->where('pagos.fecha_pago', $fecha)
                ->where('pagos.usuarios_id', 12)
                ->where('pagos.id_forma_pago', $forma->id)
                ->where('pagos.tipo_doc', 'bo')
                ->get();

            //Boletas generadas por cajero FORMANDO VENDEDORES
            $pago_bol_formando_vendedores = pago::select('pagos.*', 'boletas.id as id_boleta', 'boletas.num_boleta as num_boleta', 'boletas.es_delivery')
                ->join('boletas', 'pagos.id_doc', 'boletas.id')
                ->where('pagos.activo', 1)
                ->where('pagos.fecha_pago', $fecha)
                ->where('pagos.usuarios_id', 43)
                ->where('pagos.id_forma_pago', $forma->id)
                ->where('pagos.tipo_doc', 'bo')
                ->get();
            // Marveise
            $pago_bol_resta = 0;
            $pago_bol_rechazados = 0;
            $pago_bol_delivery = 0;
            if ($pago_bol->count() > 0) {

                foreach ($pago_bol as $pb) {
                    $nc = nota_de_credito::where('activo', 1)
                        ->where('docum_referencia', 'LIKE', 'bo*' . $pb->num_boleta . '%')
                        ->first();
                    if (!is_null($nc)) {
                        $pago_bol_resta += $pb->monto;
                    }

                    $bol_rech = boleta::where('activo', 1)
                        ->where('estado_sii', '<>', 'ACEPTADO')
                        ->where('id', $pb->id_doc)
                        ->first();
                    if (!is_null($bol_rech)) {
                        $pago_bol_rechazados += $pb->monto;
                    }

                    if ($pb->es_delivery == 2) { //delivery pagado
                        $pago_bol_delivery += $pb->monto;
                        $totales[$usuario->name]['delivery'] += $pb->monto;
                    }
                }
            }
            // Maria Jose
            $pago_bol_resta_mj = 0;
            $pago_bol_rechazados_mj = 0;
            $pago_bol_delivery_mj = 0;
            if ($pago_bol_mj->count() > 0) {

                foreach ($pago_bol_mj as $pb) {
                    $nc = nota_de_credito::where('activo', 1)
                        ->where('docum_referencia', 'LIKE', 'bo*' . $pb->num_boleta . '%')
                        ->first();
                    if (!is_null($nc)) {
                        $pago_bol_resta_mj += $pb->monto;
                    }

                    $bol_rech = boleta::where('activo', 1)
                        ->where('estado_sii', '<>', 'ACEPTADO')
                        ->where('id', $pb->id_doc)
                        ->first();
                    if (!is_null($bol_rech)) {
                        $pago_bol_rechazados_mj+= $pb->monto;
                    }

                    if ($pb->es_delivery == 2) { //delivery pagado
                        $pago_bol_delivery_mj += $pb->monto;
                        $totales[$usuario->name]['delivery'] += $pb->monto;
                    }
                }
            }

            //Jorge
            $pago_bol_resta_jorge = 0;
            $pago_bol_rechazados_jorge = 0;
            $pago_bol_delivery_jorge = 0;
            if ($pago_bol_jorge->count() > 0) {

                foreach ($pago_bol as $pb) {
                    $nc = nota_de_credito::where('activo', 1)
                        ->where('docum_referencia', 'LIKE', 'bo*' . $pb->num_boleta . '%')
                        ->first();
                    if (!is_null($nc)) {
                        $pago_bol_resta += $pb->monto;
                    }

                    $bol_rech = boleta::where('activo', 1)
                        ->where('estado_sii', '<>', 'ACEPTADO')
                        ->where('id', $pb->id_doc)
                        ->first();
                    if (!is_null($bol_rech)) {
                        $pago_bol_rechazados += $pb->monto;
                    }

                    if ($pb->es_delivery == 2) { //delivery pagado
                        $pago_bol_delivery += $pb->monto;
                        // $totales[$usuario->name]['delivery'] += $pb->monto;
                    }
                }
            }

            //FORMANDO VENDEDORES
            $pago_bol_resta_formando_vendedores = 0;
            $pago_bol_rechazados_formando_vendedores = 0;
            $pago_bol_delivery_formando_vendedores = 0;
            if ($pago_bol_formando_vendedores->count() > 0) {

                foreach ($pago_bol as $pb) {
                    $nc = nota_de_credito::where('activo', 1)
                        ->where('docum_referencia', 'LIKE', 'bo*' . $pb->num_boleta . '%')
                        ->first();
                    if (!is_null($nc)) {
                        $pago_bol_resta_formando_vendedores += $pb->monto;
                    }

                    $bol_rech = boleta::where('activo', 1)
                        ->where('estado_sii', '<>', 'ACEPTADO')
                        ->where('id', $pb->id_doc)
                        ->first();
                    if (!is_null($bol_rech)) {
                        $pago_bol_rechazados_formando_vendedores += $pb->monto;
                    }

                    if ($pb->es_delivery == 2) { //delivery pagado
                        $pago_bol_delivery_formando_vendedores += $pb->monto;
                        // $totales[$usuario->name]['delivery'] += $pb->monto;
                    }
                }
            }

            //Resta deliverys, notas de crédito y rechazados de la misma fecha
            $boletas[$usuario->name][$forma->formapago] = ($pago_bol->sum('monto') + $pago_bol_formando_vendedores->sum('monto') + $pago_bol_jorge->sum('monto')) - $pago_bol_delivery - $pago_bol_resta - $pago_bol_resta_formando_vendedores - $pago_bol_rechazados - $pago_bol_rechazados_formando_vendedores;

            $pago_fac = pago::select('pagos.*', 'facturas.id as id_factura', 'facturas.num_factura as num_factura', 'facturas.es_delivery')
                ->join('facturas', 'pagos.id_doc', 'facturas.id')
                ->where('pagos.activo', 1)
                ->where('pagos.fecha_pago', $fecha)
                ->where('pagos.usuarios_id', $usuario->id)
                ->where('pagos.id_forma_pago', $forma->id)
                ->where('pagos.tipo_doc', 'fa')
                ->get();

            $pago_fac_jorge = pago::select('pagos.*', 'facturas.id as id_factura', 'facturas.num_factura as num_factura', 'facturas.es_delivery')
                ->join('facturas', 'pagos.id_doc', 'facturas.id')
                ->where('pagos.activo', 1)
                ->where('pagos.fecha_pago', $fecha)
                ->where('pagos.usuarios_id', 9)
                ->where('pagos.id_forma_pago', $forma->id)
                ->where('pagos.tipo_doc', 'fa')
                ->get();

            $pago_fac_mj = pago::select('pagos.*', 'facturas.id as id_factura', 'facturas.num_factura as num_factura', 'facturas.es_delivery')
                ->join('facturas', 'pagos.id_doc', 'facturas.id')
                ->where('pagos.activo', 1)
                ->where('pagos.fecha_pago', $fecha)
                ->where('pagos.usuarios_id', 12)
                ->where('pagos.id_forma_pago', $forma->id)
                ->where('pagos.tipo_doc', 'fa')
                ->get();

            $pago_fac_formando_vendedores = pago::select('pagos.*', 'facturas.id as id_factura', 'facturas.num_factura as num_factura', 'facturas.es_delivery')
                ->join('facturas', 'pagos.id_doc', 'facturas.id')
                ->where('pagos.activo', 1)
                ->where('pagos.fecha_pago', $fecha)
                ->where('pagos.usuarios_id', 43)
                ->where('pagos.id_forma_pago', $forma->id)
                ->where('pagos.tipo_doc', 'fa')
                ->get();

            $pago_fac_resta = 0;
            $pago_fac_rechazados = 0;
            $pago_fac_delivery = 0;
            if ($pago_fac->count() > 0) {

                foreach ($pago_fac as $pf) {
                    $nc = nota_de_credito::where('activo', 1)
                        ->where('docum_referencia', 'LIKE', 'fa*' . $pf->num_factura . '%')
                        ->first();
                    if (!is_null($nc)) {
                        $pago_fac_resta += $pf->monto;
                    }

                    $fac_rech = factura::where('activo', 1)
                        ->where('estado_sii', '<>', 'ACEPTADO')
                        ->where('id', $pf->id_doc)
                        ->first();
                    if (!is_null($fac_rech)) {
                        $pago_fac_rechazados += $pf->monto;
                    }

                    if ($pf->es_delivery == 2) { //delivery pagado
                        $pago_fac_delivery += $pf->monto;
                        // $totales[$usuario->name]['delivery'] += $pf->monto;
                    }
                }
            }

            $pago_fac_resta_mj = 0;
            $pago_fac_rechazados_mj = 0;
            $pago_fac_delivery_mj = 0;
            if ($pago_fac_mj->count() > 0) {

                foreach ($pago_fac_mj as $pf) {
                    $nc = nota_de_credito::where('activo', 1)
                        ->where('docum_referencia', 'LIKE', 'fa*' . $pf->num_factura . '%')
                        ->first();
                    if (!is_null($nc)) {
                        $pago_fac_resta_mj += $pf->monto;
                    }

                    $fac_rech = factura::where('activo', 1)
                        ->where('estado_sii', '<>', 'ACEPTADO')
                        ->where('id', $pf->id_doc)
                        ->first();
                    if (!is_null($fac_rech)) {
                        $pago_fac_rechazados_mj += $pf->monto;
                    }

                    if ($pf->es_delivery == 2) { //delivery pagado
                        $pago_fac_delivery_mj += $pf->monto;
                        // $totales[$usuario->name]['delivery'] += $pf->monto;
                    }
                }
            }

            $pago_fac_resta_jorge = 0;
            $pago_fac_rechazados_jorge = 0;
            $pago_fac_delivery_jorge = 0;
            if ($pago_fac_jorge->count() > 0) {

                foreach ($pago_fac_jorge as $pf) {
                    $nc = nota_de_credito::where('activo', 1)
                        ->where('docum_referencia', 'LIKE', 'fa*' . $pf->num_factura . '%')
                        ->first();
                    if (!is_null($nc)) {
                        $pago_fac_resta_jorge += $pf->monto;
                    }

                    $fac_rech = factura::where('activo', 1)
                        ->where('estado_sii', '<>', 'ACEPTADO')
                        ->where('id', $pf->id_doc)
                        ->first();
                    if (!is_null($fac_rech)) {
                        $pago_fac_rechazados_jorge += $pf->monto;
                    }

                    if ($pf->es_delivery == 2) { //delivery pagado
                        $pago_fac_delivery_jorge += $pf->monto;
                        // $totales[$usuario->name]['delivery'] += $pf->monto;
                    }
                }
            }

            $pago_fac_resta_formando_vendedores = 0;
            $pago_fac_rechazados_formando_vendedores = 0;
            $pago_fac_delivery_formando_vendedores = 0;
            if ($pago_fac_formando_vendedores->count() > 0) {

                foreach ($pago_fac_formando_vendedores as $pf) {
                    $nc = nota_de_credito::where('activo', 1)
                        ->where('docum_referencia', 'LIKE', 'fa*' . $pf->num_factura . '%')
                        ->first();
                    if (!is_null($nc)) {
                        $pago_fac_resta_formando_vendedores += $pf->monto;
                    }

                    $fac_rech = factura::where('activo', 1)
                        ->where('estado_sii', '<>', 'ACEPTADO')
                        ->where('id', $pf->id_doc)
                        ->first();
                    if (!is_null($fac_rech)) {
                        $pago_fac_rechazados_formando_vendedores += $pf->monto;
                    }

                    if ($pf->es_delivery == 2) { //delivery pagado
                        $pago_fac_delivery_formando_vendedores += $pf->monto;
                        // $totales[$usuario->name]['delivery'] += $pf->monto;
                    }
                }
            }
            //Resta notas de crédito y rechazados de la misma fecha
            $facturas[$usuario->name][$forma->formapago] = $pago_fac->sum('monto') + $pago_fac_formando_vendedores->sum('monto') + $pago_fac_jorge->sum('monto') - $pago_fac_delivery - $pago_fac_resta - $pago_fac_resta_formando_vendedores - $pago_fac_rechazados - $pago_fac_rechazados_formando_vendedores - $pago_fac_resta_jorge - $pago_fac_rechazados_jorge;

            //Abonos

            $pago_abono = pago::select('pagos.*','abono.id as id_abono')
            ->join('abono','pagos.id_doc','abono.id')
            ->where('pagos.activo',1)
            ->where('pagos.fecha_pago',$fecha)
            ->where('pagos.usuarios_id',$usuario->id)
            ->where('pagos.id_forma_pago',$forma->id)
            ->where('pagos.tipo_doc','ab')
            ->get();

            $abonos[$usuario->name][$forma->formapago] = $pago_abono->sum('monto');
            //FIN ABONOS

            $totales[$usuario->name][$forma->formapago] = $boletas[$usuario->name][$forma->formapago] + $facturas[$usuario->name][$forma->formapago];


        } //fin forma pago

    // } //fin usuarios

    $notcred = nota_de_credito::select('notas_de_credito.*', 'users.name as usuario', 'users.id as id_user')
        ->where('notas_de_credito.activo', 1)
        ->where('notas_de_credito.estado_sii', 'ACEPTADO')
        ->where('notas_de_credito.fecha_emision', $fecha)
        ->join('users', 'notas_de_credito.usuarios_id', 'users.id')
        ->get();


    $notcred_total = 0;
    if ($notcred->count() > 0) {
        foreach ($notcred as $nc) {
            list($tipo_doc, $num_doc, $fecha_doc) = explode("*", $nc->docum_referencia);
            //Sumar solo las NC que son anteriores a la fecha
            if ($fecha_doc <> $fecha) {
                $notcred_total += $nc->total;
            }

            //Agregar la forma de pago en url_pdf temporalmente no mas
            if ($tipo_doc == 'bo') {
                $id_bol = boleta::where('num_boleta', $num_doc)->value('id');
                $pagos_boleta = pago::where('id_doc', $id_bol)
                    ->where('tipo_doc', 'bo')
                    ->get();
                if ($pagos_boleta->count() == 0) {
                    $nc->url_pdf = "Crédito";
                    $nc->save();
                }
                if ($pagos_boleta->count() > 0) $nc->url_pdf = "MultiPago";
                if ($pagos_boleta->count() == 1) {
                    foreach ($pagos_boleta as $pagbol) {
                        for ($i = 0; $i < count($fp_array); $i++) {
                            if ($pagbol->id_forma_pago == $fp_array[$i]['id']) {
                                $nc->url_pdf = $fp_array[$i]['formapago'];
                                $nc->save();
                            }
                        }
                    }
                }
            }

            if ($tipo_doc == 'fa') {
                $id_fac = factura::where('num_factura', $num_doc)->value('id');
                $pagos_factura = pago::where('id_doc', $id_fac)
                    ->where('tipo_doc', 'fa')
                    ->get();
                if ($pagos_factura->count() == 0) {
                    $nc->url_pdf = "Crédito";
                    $nc->save();
                }
                if ($pagos_factura->count() > 0) $nc->url_pdf = "MultiPago";
                if ($pagos_factura->count() == 1) {

                    foreach ($pagos_factura as $pagfac) {
                        for ($i = 0; $i < count($fp_array); $i++) {
                            if ($pagfac->id_forma_pago == $fp_array[$i]['id']) {
                                $nc->url_pdf = $fp_array[$i]['formapago'];
                                $nc->save();
                            }
                        }
                    }
                }
            }
        }
    }

    $bol_rech = boleta::select('boletas.id', 'boletas.num_boleta as num_doc', 'boletas.total', 'boletas.resultado_envio as resultado', 'boletas.url_xml as xml', 'boletas.estado_sii', 'boletas.url_pdf', 'users.name as usuario')
        ->join('users', 'boletas.usuarios_id', 'users.id')
        ->where('boletas.activo', 1)
        ->where('boletas.estado_sii', '<>', 'ACEPTADO')
        ->where('boletas.fecha_emision', $fecha)
        ->get();

    //Si hay pagos en los documentos rechazados, los desactivamos
    foreach ($bol_rech as $br) {
        $cómo_pagó = "Crédito";
        $pbr = pago::where('tipo_doc', 'bo')
            ->where('id_doc', $br->id)
            ->get();

        //Desactivamos cada pago.
        if ($pbr->count() > 0 && $br->estado_sii == 'RECHAZADO') {
            foreach ($pbr as $pbr_temp) {
                $pbr_temp->activo = 0;
                $pbr_temp->save();
            }
        }

        //Agregamos la forma de pago
        if ($pbr->count() == 1) {
            $pbr_1 = pago::where('tipo_doc', 'bo')
                ->where('id_doc', $br->id)
                ->first();

            for ($i = 0; $i < count($fp_array); $i++) {
                if ($pbr_1->id_forma_pago == $fp_array[$i]['id']) {
                    $cómo_pagó = $fp_array[$i]['formapago'];
                }
            }
        }

        if ($pbr->count() > 1) $cómo_pagó = "MultiPago";
        $br->url_pdf = $cómo_pagó;
        $br->save();
    }

    $fac_rech = factura::select('facturas.id', 'facturas.num_factura as num_doc', 'facturas.total', 'facturas.resultado_envio as resultado', 'facturas.url_xml as xml', 'facturas.estado_sii', 'facturas.url_pdf', 'users.name as usuario')
        ->join('users', 'facturas.usuarios_id', 'users.id')
        ->where('facturas.activo', 1)
        ->where('facturas.estado_sii', '<>', 'ACEPTADO')
        ->where('facturas.fecha_emision', $fecha)
        ->get();
    //Si hay pagos en los documentos rechazados, los desactivamos

    foreach ($fac_rech as $fr) {
        $cómo_pagó = "Crédito";
        $pfr = pago::where('tipo_doc', 'fa')
            ->where('id_doc', $fr->id)
            ->get();

        //idem en boletas más arriba
        if ($pfr->count() > 0 && $fr->estado_sii == 'RECHAZADO') {
            foreach ($pfr as $pfr_temp) {
                $pfr_temp->activo = 0;
                $pfr_temp->save();
            }
        }

        //Agregamos la forma de pago
        if ($pfr->count() == 1) {
            $pfr_1 = pago::where('tipo_doc', 'fa')
                ->where('id_doc', $fr->id)
                ->first();

            for ($i = 0; $i < count($fp_array); $i++) {
                if ($pfr_1->id_forma_pago == $fp_array[$i]['id']) {
                    $cómo_pagó = $fp_array[$i]['formapago'];
                }
            }
        }

        if ($pfr->count() > 1) $cómo_pagó = "MultiPago";
        $fr->url_pdf = $cómo_pagó;
        $fr->save();
    }

    if ($fac_rech->count() > 0 && $bol_rech->count() > 0) {
        $rechazados = collect($fac_rech)->merge(collect($bol_rech));
    } else if ($fac_rech->count() == 0 && $bol_rech->count() > 0) {
        $rechazados = $bol_rech;
    } else if ($fac_rech->count() > 0 && $bol_rech->count() == 0) {
        $rechazados = $fac_rech;
    } else if ($fac_rech->count() == 0 && $bol_rech->count() == 0) {
        $rechazados = collect();
    }

    // $delivery_pendientes = $this->delivery_pendientes($fecha);

    $v = view('fragm.arqueo_data', compact('boletas', 'facturas', 'abonos','totales', 'formas_pago', 'usuario', 'notcred', 'notcred_total', 'rechazados'));
    return $v;
    }


    public function dame_cajeros(){
        $user_id = Auth::user()->id;
        // No muestra a Francisco Rojo (programador)
        // No mostrar al mismo usuario en sesión
        // Mostrar solo los usuarios que sean administradores de ventas o cajeros
        $cajeros = user::where('role_id',20)->where('id','<>',$user_id)->where('id','<>',16)->get();
        $nuevos_cajeros = [];
        foreach ($cajeros as $c) {
            if($c->activo == 1) array_push($nuevos_cajeros,$c);
        }
        return $nuevos_cajeros;
    }

    public function pedidos(){
        return view('ventas.pedidos');
    }

    public function revisar_pedidos(){
    try {
        // Obtén la fecha actual en formato 'Y-m-d'
        $fechaActual = Carbon::now()->format('Y-m-d');

        // Resta 7 días a la fecha actual
        $fechaResultadoTerrestre = Carbon::parse($fechaActual)->subDays(21);

        // Resta 3 días a la fecha actual
        $fechaResultadoAereo = Carbon::parse($fechaActual)->subDays(14);

        // Formatea la fecha en el formato Y-m-d (año-mes-día)
        $fechaFormateadaTerrestre = $fechaResultadoTerrestre->format('Y-m-d');
        $fechaFormateadaAereo = $fechaResultadoAereo->format('Y-m-d');

        $pedidos_terrestre = abono::select('abono.*','users.name as responsable')
                            ->where('abono.por_encargo','terrestre')
                            ->join('users','abono.id_responsable','users.id')
                            ->where('abono.fecha_emision','>=',$fechaFormateadaTerrestre)
                            ->where('abono.activo',1)
                            ->get();
        $pedidos_aereos = abono::select('abono.*','users.name as responsable')
                            ->where('abono.por_encargo','aereo')
                            ->join('users','abono.id_responsable','users.id')
                            ->where('abono.fecha_emision','>=',$fechaFormateadaAereo)
                            ->where('abono.activo',1)
                            ->get();

        // Le cambiamos el formato de las fechas
        foreach($pedidos_terrestre as $p){
            $p->fecha_emision = Carbon::parse($p->fecha_emision)->format('d-m-Y');
        }

        foreach($pedidos_aereos as $p){
            $p->fecha_emision = Carbon::parse($p->fecha_emision)->format('d-m-Y');
        }

        if(Auth::user()->rol->nombrerol == "Administrador"){
            return [$pedidos_terrestre, $pedidos_aereos,$fechaFormateadaTerrestre,$fechaFormateadaAereo];
        }else{
            return [];
        }

    } catch (\Exception $e) {
        return $e->getMessage();
    }

    }

    public function pedidos_nuevo(){
        $permisos = permissions_detail::all();
        $usuarios = User::where('activo',1)->where('role_id','<>',20)->where('id','<>',16)->get();
        $proveedores = proveedor::where('activo',1)->get();
        foreach ($permisos as $p) {
            if($p->usuarios_id == Auth::user()->id && $p->path_ruta == '/ventas/pedidos_nuevo'){
                return view('ventas.pedidos_nuevo',[
                    'usuarios' => $usuarios,
                    'proveedores' => $proveedores
                ]);
            }
        }

        if(Auth::user()->rol->nombrerol == "Administrador"){
            return view('ventas.pedidos_nuevo',[
                'usuarios' => $usuarios,
                'proveedores' => $proveedores
            ]);
        }else return redirect('home');

    }

    public function nuevo_abono(Request $req){
        $existe = $this->existeabono($req);

        if($existe == "nuevo"){
            $abono = new abono;
            // $abono->num_abono = $abono->id;
            $abono->usuarios_id = Auth::user()->id;
            $abono->nombre_cliente = $req->nombre;
            $abono->telefono = $req->telefono;
            $abono->email = $req->email;
            $abono->id_responsable = $req->responsable;
            $abono->save();
            return $abono->id;
        }else{
            return 'no';
        }

    }

    public function nueva_consignacion(Request $req){
        // $existe = $this->existeconsignacion($req);

        // if($existe == "nuevo"){
        //     $correlativo = correlativo::find(12);
        //     $vale_consignacion = new vale_consignacion;
        //     // $vale_consignacion->num_vale_consignacion = $vale_consignacion->id;
        //     $vale_consignacion->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha de hoy
        //     $vale_consignacion->fecha_expira = Carbon::today()->addDays(7)->toDateString();
        //     $vale_consignacion->rut_cliente = $req->rut;
        //     $vale_consignacion->usuarios_id = Auth::user()->id;
        //     $vale_consignacion->nombre_cliente = $req->nombre;
        //     $vale_consignacion->telefono_cliente = $req->telefono;
        //     $vale_consignacion->numero_boucher = $correlativo->correlativo+1;
        //     $vale_consignacion->activo = 1;
        //     $vale_consignacion->save();
        //     //Actualizamos el correlativo de los vales de consignacion
        //     $correlativo->correlativo = $correlativo->correlativo + 1;
        //     $correlativo->save();
        //     return $vale_consignacion;
        // }else{
        //     return 'no';
        // }

        $correlativo = correlativo::find(12);
            $vale_consignacion = new vale_consignacion;
            // $vale_consignacion->num_vale_consignacion = $vale_consignacion->id;
            $vale_consignacion->fecha_emision = Carbon::today()->toDateString(); //Solo la fecha de hoy
            $vale_consignacion->fecha_expira = Carbon::today()->addDays(7)->toDateString();
            $vale_consignacion->rut_cliente = $req->rut;
            $vale_consignacion->usuarios_id = Auth::user()->id;
            $vale_consignacion->nombre_cliente = $req->nombre;
            $vale_consignacion->telefono_cliente = $req->telefono;
            $vale_consignacion->numero_boucher = $correlativo->correlativo+1;
            $vale_consignacion->activo = 1;
            $vale_consignacion->save();
            //Actualizamos el correlativo de los vales de consignacion
            $correlativo->correlativo = $correlativo->correlativo + 1;
            $correlativo->save();
            return $vale_consignacion;

    }

    public function cerrar_consignacion($id_vale){
        $vale = vale_consignacion::find($id_vale);
        $vale->activo = 0;
        $vale->save();
        return 'OK';
    }

    public function existeconsignacion($data){
        $vales = vale_consignacion::all();
        try {
            foreach($vales as $vale){
                if(strtolower($vale->nombre_cliente) == strtolower($data->nombre)){
                    return "existe";
                }
            }

            return "nuevo";
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function existeabono($data){
        $abonos = abono::all();
        try {
            foreach($abonos as $abono){
                if(strtolower($abono->nombre_cliente) == strtolower($data->nombre)){
                    return "existe";
                }
            }

            return "nuevo";
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }


    public function eliminaritem(Request $r){
        try {
            $borrados=abono::where('id',$r->id)->delete();
            $abonos = abono::where('id_cliente',$r->idc)->get();
            return $abonos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }


    }

    public function eliminarpedido($idrep,$id_abono){

        try {
            $pedido= abono_detalle::where('id_repuesto',$idrep)->delete();
            $repuesto = repuesto::where('id',$idrep)->delete();

            $pedidos = abono_detalle::select('abono_detalle.*','abono_estado.descripcion_estado','repuestos.id as idrep')
                ->where('abono_detalle.id_abono',$id_abono)
                ->join('abono_estado','abono_estado.id','abono_detalle.estado')
                ->join('repuestos','repuestos.id','abono_detalle.id_repuesto')
                ->get();
            //Si no existen pedidos se elimina el cliente para no cargar la BD
            if($pedidos->count() == 0){
                $abono = abono::where('id',$id_abono)->delete();
            }
            return $pedidos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function nuevo_pedido($id_cliente){
        try {
            $user_id = Auth::user()->id;
            $abono = new abono;
            $abono->usuarios_id = $user_id;
            $abono->id_cliente = $id_cliente;
            $abono->save();
            return $abono->id;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function revisar_carrito(){
        try {
            $user_id = Auth::user()->id;
            $hoy = Carbon::now()->format('Y-m-d');

            // Optimizar la consulta eliminando el uso de distinct y groupBy si no es necesario
            $carrito_transferido = carrito_transferido::where('cajeros_id', $user_id)
                ->whereDate('fecha_emision', $hoy)
                ->select('carrito_id')
                ->distinct()
                ->count('carrito_id');

            return $carrito_transferido;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function agregar_repuesto_valemercaderia(Request $req){

        try {
            $idrep = $req->idrep;
            $num_boucher = $req->num_boucher;
            $origen = $req->origen;
            $cantidad = $req->cantidad;

            $repuesto = repuesto::find($idrep);

            if($origen == 1 && ($cantidad > $repuesto->stock_actual)){
                return 'error';
            }elseif($origen == 3 && ($cantidad > $repuesto->stock_actual_dos)){
                return 'error';
            }elseif($origen == 4 && ($cantidad > $repuesto->stock_actual_tres)){
                return 'error';
            }


            //Fecha ultima actualización del precio del repuesto
            $firstDate = $repuesto->fecha_actualiza_precio;
            $secondDate = date('d-m-Y H:i:s');

            $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

            $years  = floor($dateDifference / (365 * 60 * 60 * 24));
            $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
            $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

            $minutos = floor(abs($dateDifference / 60));
            $horas = floor($minutos / 60);
            $dias = floor($horas / 24);

            if($dias <= 60 ){
                $vale = $vale = vale_mercaderia::select('vale_mercaderia.*','users.name')
                ->join('users','vale_mercaderia.usuarios_id','users.id')
                ->where('vale_mercaderia.numero_boucher',$num_boucher)
                ->first();

                $detalle = new vale_mercaderia_detalle;
                $detalle->vale_mercaderia_id = $vale->id;
                $detalle->repuesto_id = $idrep;
                $detalle->local_id = $origen;
                $detalle->cantidad = $cantidad;
                $detalle->activo = 1;
                $detalle->usuario_id = Auth::user()->id;

                $detalle->save();

                $detalles = $this->dame_repuestos_valemercaderia($num_boucher);
                $v = view('fragm.busqueda_vale_mercaderia',compact('detalles','vale'))->render();
                return $v;
            }else{
                return 'viejo';
            }



        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function agregar_repuesto_valeconsignacion(Request $req){
        try {
            $idrep = $req->idrep;
            $id_vale = intval($req->id_vale);
            $origen = $req->origen;
            $cantidad = $req->cantidad;

            $repuesto = repuesto::find($idrep);

            if($origen == 1 && ($cantidad > $repuesto->stock_actual)){
                return 'error';
            }elseif($origen == 3 && ($cantidad > $repuesto->stock_actual_dos)){
                return 'error';
            }elseif($origen == 4 && ($cantidad > $repuesto->stock_actual_tres)){
                return 'error';
            }

            //Fecha ultima actualización del precio del repuesto
            $firstDate = $repuesto->fecha_actualiza_precio;
            $secondDate = date('d-m-Y H:i:s');

            $dateDifference = abs(strtotime($secondDate) - strtotime($firstDate));

            $years  = floor($dateDifference / (365 * 60 * 60 * 24));
            $months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
            $days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

            $minutos = floor(abs($dateDifference / 60));
            $horas = floor($minutos / 60);
            $dias = floor($horas / 24);

            if($dias <= 60 ){

                $vale = vale_consignacion::select('vale_consignacion.*','users.name')
                ->join('users','vale_consignacion.usuarios_id','users.id')
                ->where('vale_consignacion.id',$id_vale)
                ->first();

                $detalle = new vale_consignacion_detalle;
                $detalle->id_doc = $id_vale;
                $detalle->id_repuestos = $idrep;
                $detalle->cantidad = $cantidad;
                $detalle->id_local = $origen;
                $detalle->save();

                $detalles = $this->dame_repuestos_valeconsignacion($id_vale);
                $cargar = false;
                //Descontamos el stock del repuesto en consignacion
                $repuesto = repuesto::find($idrep);
                if($origen == 1){
                    $repuesto->stock_actual -= $cantidad;
                }else if($origen == 3){
                    $repuesto->stock_actual_dos -= $cantidad;
                }else{
                    $repuesto->stock_actual_tres -= $cantidad;
                }

                $repuesto->save();
                $v = view('fragm.busqueda_vale_consignacion',compact('detalles','vale','cargar'))->render();
                return $v;
            }else{
                return 'viejo';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function dame_vale_consignacion($num_consignacion){
        try {
            $vale = consignacion::where('num_consignacion',$num_consignacion)->first();
            $detalles = $this->dame_repuestos_valeconsignacion($vale->id);

            $cargar = true;
            $v = view('fragm.busqueda_vale_consignacion',compact('detalles','vale','cargar'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function eliminar_repuesto_valemercaderia($id,$num_boucher){

        try {

            $vale = vale_mercaderia::select('vale_mercaderia.*','users.name')
                            ->join('users','vale_mercaderia.usuarios_id','users.id')
                            ->where('vale_mercaderia.numero_boucher',$num_boucher)
                            ->first();
            $value = vale_mercaderia_detalle::find($id);
            //Devolvemos el stock del repuesto en consignacion
            //$repuesto = repuesto::find($value->id_repuestos);
            // if($value->id_local == 1){
            //     $repuesto->stock_actual += $cantidad;
            // }else if($value->id_local == 3){
            //     $repuesto->stock_actual_dos += $cantidad;
            // }else{
            //     $repuesto->stock_actual_tres += $cantidad;
            // }
            $value->delete();

            $detalles = $this->dame_repuestos_valemercaderia($num_boucher);
            $v = view('fragm.busqueda_vale_mercaderia',compact('detalles','vale'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function eliminar_repuesto_valeconsignacion($numero_boucher,$id_repuesto){
       try {
        $vale = vale_consignacion::where('numero_boucher',$numero_boucher)->first();
        $detalle = vale_consignacion_detalle::select('vale_consignacion_detalle.*','repuestos.codigo_interno','repuestos.descripcion')
                                                ->where('vale_consignacion_detalle.id_doc',$vale->id)
                                                ->join('repuestos','vale_consignacion_detalle.id_repuestos','repuestos.id')
                                                ->where('vale_consignacion_detalle.id_repuestos',$id_repuesto)
                                                ->first();
        //Devolvemos el stock del repuesto a su ubicación original.
        $repuesto = repuesto::find($id_repuesto);
        if($detalle->id_local == 1){
            $repuesto->stock_actual += $detalle->cantidad;
        }else if($detalle->id_local == 3){
            $repuesto->stock_actual_dos += $detalle->cantidad;
        }else{
            $repuesto->stock_actual_tres += $detalle->cantidad;
        }
        //Guardamos la ultima información del repuesto
        $repuesto->save();
        $detalle->delete();
        $detalles = $this->dame_repuestos_valeconsignacion($vale->id);
        $cargar = false;
        $v = view('fragm.busqueda_vale_consignacion',compact('detalles','vale','cargar'))->render();
        return $v;
       } catch (\Exception $e) {
        return $e->getMessage();
       }

    }


    public function devolver_repuesto_valeconsignacion($num_consignacion,$id_repuesto){
        try {
            $vale = consignacion::where('num_consignacion',$num_consignacion)->first();
            $detalle = consignacion_detalle::select('consignaciones_detalle.*','repuestos.codigo_interno','repuestos.descripcion')
                                                    ->where('consignaciones_detalle.id_consignacion',$vale->id)
                                                    ->join('repuestos','consignaciones_detalle.id_repuestos','repuestos.id')
                                                    ->where('consignaciones_detalle.id_repuestos',$id_repuesto)
                                                    ->first();
            //Se le cambia el estado al detalle que contiene información del repuesto a 1
            $detalle->devuelto = 1;
            //Buscamos el repuesto y le devolvemos la cantidad a su stock original
            $repuesto = repuesto::find($id_repuesto);
            if($detalle->id_local == 1){
                $repuesto->stock_actual += $detalle->cantidad;
            }else if($detalle->id_local == 3){
                $repuesto->stock_actual_dos += $detalle->cantidad;
            }else{
                $repuesto->stock_actual_tres += $detalle->cantidad;
            }
            $repuesto->save();
            $detalle->save();
            $detalles = $this->dame_repuestos_valeconsignacion($vale->id);
            if(count($detalles) == 0){
                $vale->activo = 0;
                $vale->save();
            }
            $cargar = true;
            $v = view('fragm.busqueda_vale_consignacion',compact('detalles','vale','cargar'))->render();
            return $v;
           } catch (\Exception $e) {
            return $e->getMessage();
           }
    }

    public function dame_repuestos_valeconsignacion($id_vale){
        try {
            $repuestos = consignacion_detalle::select('consignaciones_detalle.id','repuestos.*','consignaciones_detalle.cantidad','consignaciones.num_consignacion','locales.local_nombre')
                                                    ->join('consignaciones','consignaciones.id','consignaciones_detalle.id_consignacion')
                                                    ->join('repuestos','consignaciones_detalle.id_repuestos','repuestos.id')
                                                    ->join('locales','locales.id','consignaciones_detalle.id_local')
                                                    ->where('consignaciones_detalle.id_consignacion',$id_vale)
                                                    ->where('consignaciones_detalle.devuelto',0)
                                                    ->get();
            return $repuestos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function dame_repuestos_valemercaderia($num_boucher){
        try {
            $vale = vale_mercaderia::where('numero_boucher',$num_boucher)->first();
            $repuestos = vale_mercaderia_detalle::select('vale_mercaderia_detalle.*','users.name','locales.local_nombre','repuestos.id as idrep','repuestos.precio_venta','repuestos.codigo_interno')
                                            ->join('users','vale_mercaderia_detalle.usuario_id','users.id')
                                            ->join('locales','vale_mercaderia_detalle.local_id','locales.id')
                                            ->join('repuestos','vale_mercaderia_detalle.repuesto_id','repuestos.id')
                                            ->where('vale_mercaderia_detalle.vale_mercaderia_id',$vale->id)
                                            ->get();
            return $repuestos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function ventas_online(){
        try {
            $carritos_virtuales = carrito_virtual::where('activo',0)->get();
            $detalle = carrito_virtual_detalle::select('carrito_virtual_detalle.*','repuestos.descripcion','repuestos.ubicacion','carrito_virtual.fecha_emision')
                                            ->join('repuestos','carrito_virtual_detalle.repuesto_id','repuestos.id')
                                            ->join('carrito_virtual','carrito_virtual_detalle.carrito_numero','carrito_virtual.numero_carrito')
                                            ->orderBy('carrito_virtual_detalle.carrito_numero','asc')
                                            ->get();
            $usuarios_web = User::where('role_id',20)->get();
            return view('ventas.ventas_online',['carritos_virtuales'=>$carritos_virtuales,'detalles' => $detalle,'usuarios' => $usuarios_web]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function dame_fecha_actual(){
        $hoy = getdate();
        $dia = $hoy['mday'];
        $mes = $hoy['mon'];
        $year = $hoy['year'];
        $contador = 0;

        $fecha = $year.'-'.$mes.'-'.$dia;

        $hoy=Carbon::today();
        $fecha_hoy=$hoy->toDateString();

        return $fecha_hoy;
    }

    public function ofertas(){
        try {

            $fecha_hoy = $this->dame_fecha_actual();

            $ofertas = oferta_pagina_web::select('ofertas_pagina_web.*','repuestos.descripcion','repuestos.codigo_interno','users.name')
                                            ->join('repuestos','ofertas_pagina_web.id_repuesto','repuestos.id')
                                            ->join('users','ofertas_pagina_web.usuario_id','users.id')
                                            ->orderBy('ofertas_pagina_web.activo','desc')
                                            ->get();
            /*Si la fecha del inicio de la fecha es despues que la fecha de hoy y la fecha final
             es antes que la fecha de hoy se mantiene activa.          */
            foreach($ofertas as $o){
                if($fecha_hoy >= $o->desde && $fecha_hoy <= $o->hasta){
                    $o->activo = 1;
                }else{
                    $o->activo = 0;
                }
                $o->save();
                $o->desde =  Carbon::parse($o->desde)->format("d-m-Y");
                $o->hasta =  Carbon::parse($o->hasta)->format("d-m-Y");
                //Revisamos si hay oferta disponible el dia de hoy

            }

            $descuentos = descuento::all();
            foreach($descuentos as $d){
                if($fecha_hoy >= $d->desde && $fecha_hoy <= $d->hasta){
                    $d->activo = 1;
                }else{
                    $d->activo = 0;
                }
                $d->save();
                //Revisamos si hay oferta disponible el dia de hoy

            }


            $oferta = true;
            $confirma = $this->confirmaringreso('/ventas/ofertas');
            if($confirma){
                return view('ventas.ofertas',['ofertas' => $ofertas,'oferta' => $oferta]);
            }else return redirect('home');

        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function editar_nueva_familia(Request $request){
        try {

          // recuperar la imagen y guardarla en el storage
            $nombre = '';
            if($request->hasFile('imagen')){
                $file = $request->file('imagen');
                $nombre = $file->getClientOriginalName();
                $file->move(public_path().'/imagenes/familias/',$nombre);
                $ruta_origen = "C:/xampp/htdocs/repuestos/public/imagenes/familias/".$nombre;
                $ruta_destino = "C:/xampp/htdocs/repuestos/public_original/storage/imagenes/familias/".$nombre;;
                copy($ruta_origen, $ruta_destino);
            }

           // dame el porcentaje nuevo de descuento
            $porcentaje = $request->porcentaje_nuevo;
            $id = $request->idfamilia;
            $descuento = descuento::where('id_familia',$id)->first();
            $descuento->porcentaje = $porcentaje;
            $descuento->desde = $request->fecha_inicio;
            $descuento->hasta = $request->fecha_fin;
            if($request->hasFile('imagen')){
                $descuento->image_path = $nombre;
            }

            $descuento->update();
            return 'ok';
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function modal_editar_descuento_familia($id){
        try {
            $descuento = descuento::select('descuentos.*','familias.nombrefamilia','familias.id as idfamilia')
                                    ->where('descuentos.id',$id)
                                    ->join('familias','familias.id','descuentos.id_familia')
                                    ->orderBy('id','desc')
                                    ->first();



            $opcionesLocales = ['Solo Local','Solo Web','Local y Web'];

            // Como puedo hacer un array con objetos que sean locales que tengan un id y un nombre


            return view('fragm.modal_editar_descuento_familia',[
                'descuento' => $descuento,
                'familia' => $descuento->idfamilia,
                'opcionesLocales' => $opcionesLocales
            ]);

        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function damerepuesto_oferta(Request $req){
        $id = $req->codigo_repuesto;
        $opt = $req->option;

        if($opt === 'cod_int'){
            $encontrados=repuesto::where('repuestos.codigo_interno',$id)
            ->where('repuestos.activo',1)
            // ->join('repuestos_fotos','repuestos.id','repuestos_fotos.id_repuestos')
            ->select('repuestos.*')
            ->get();


            if($encontrados->count()>0){
                $existe = oferta_pagina_web::where('id_repuesto', $encontrados[0]->id)->first();
                if($existe){
                    return ["error","Repuesta ya se encuentra en oferta"];
                }
                $urlfoto = repuestofoto::select('urlfoto')->where('id_repuestos',$encontrados[0]->id)->first();
                $repuesto=$encontrados;
                if($urlfoto){
                    return [$repuesto,$urlfoto];
                }else{
                    $urlfoto = 'fotozzz/notfound.png';

                    return [$repuesto,$urlfoto];
                }

            }else{
                return ["error","Verifique el codigo interno"];
            }

        }else if($opt === 'oem'){

                $oem = oem::select('id_repuestos')
                            ->where('oems.codigo_oem','LIKE','%'.$id.'%')
                            ->where('oems.activo',1)
                            ->get()
                            ->toArray();

                if(count($oem)>0){
                    $encontrados=repuesto::wherein('repuestos.id',$oem)
                        ->where('repuestos.activo',1)
                        ->select( 'repuestos.*')
                        ->get();

                }else{
                    return ["error","Verifique el codigo OEM"];
                }


            if($encontrados->count()>0){
                $repuesto=$encontrados;
                return [$repuesto,''];
            }else{
                return ["error","Verifique el codigo OEM"];
            }

        }else{
            try {
                $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$id . '%')
                ->where('repuestos.activo',1)
                ->select('repuestos.*')
                ->get();

                if($repuestos->count() > 0){
                    $urlfoto = repuestofoto::select('urlfoto')->where('id_repuestos',$repuestos[0]->id)->first();
                    return [$repuestos,$urlfoto];
                }else{
                    return ["error","Verifique el codigo de proveedor"];
                }

            } catch (\Exception $e) {
                return $e->getMessage();
            }

        }
    }

    public function damerepuesto_kit(Request $req){
        $id = $req->codigo_repuesto;
        $opt = $req->option;
        //Ocupamos este valor para saber si es una busqueda de kit o una busqueda de repuesto
        // 1 = busqueda de kit
        // 2 = busqueda de repuesto
        $value = $req->value;
        $locales = local::all();
        if($opt === 'cod_int'){
            $encontrados=repuesto::where('repuestos.codigo_interno',$id)
            ->where('repuestos.activo',1)
            ->where('repuestos.codigo_OEM_repuesto','!=','XPRESS')
            ->join('paises', 'repuestos.id_pais', 'paises.id')
            ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
            ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
            // ->join('repuestos_fotos','repuestos.id','repuestos_fotos.id_repuestos')
            ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
            ->get();


            if($encontrados->count()>0){
                $rep = repuesto::where('codigo_interno',$id)->first();
                //Consultamos si la familia corresponde a un kit armado 442 = kit panchorepuestos
                if ($value == 1 && $rep->id_familia !== 442){
                    return ["error","No es un kit"];
                }
                $urlfoto = repuestofoto::select('urlfoto')->where('id_repuestos',$encontrados[0]->id)->first();
                $detalle_kit = armado_kit_detalle::select('armado_kit_detalle.*','repuestos.codigo_interno','repuestos.descripcion','repuestos.precio_venta')
                                                    ->join('repuestos','armado_kit_detalle.id_repuesto','repuestos.id')
                                                    ->where('armado_kit_detalle.id_kit',$rep->id)
                                                    ->get();
                $precio_venta = 0;
                foreach ($detalle_kit as $d) {
                    $precio_venta += intval($d->precio_venta) * intval($d->cantidad);
                }
                $repuesto=$encontrados;
                //Al repuesto le pasamos el valor del total de todos los repuestos agregados al kit
                //$repuesto[0]->precio_venta = $precio_venta;
                //Guardamos el nuevo valor del kit
                //$repuesto[0]->save();
                if($urlfoto){
                    return [$repuesto,$urlfoto,$detalle_kit, $locales];
                }else{
                    $urlfoto = 'fotozzz/notfound.png';

                    return [$repuesto,$urlfoto,$detalle_kit, $locales];
                }

            }else{
                return ["error","Verifique el codigo interno"];
            }

        }else if($opt === 'oem'){

                $oem = oem::select('id_repuestos')
                            ->where('oems.codigo_oem','LIKE','%'.$id.'%')
                            ->where('oems.activo',1)
                            ->get()
                            ->toArray();

                if(count($oem)>0){
                    $encontrados=repuesto::wherein('repuestos.id',$oem)
                        ->where('repuestos.activo',1)
                        ->where('repuestos.codigo_OEM_repuesto','!=','XPRESS')
                        ->join('paises', 'repuestos.id_pais', 'paises.id')
                        ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                        ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                        ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                        ->get();

                }else{
                    return ["error","Verifique el codigo OEM"];
                }


            if($encontrados->count()>0){
                $repuesto=$encontrados;
                return [$repuesto,''];
            }else{
                return ["error","Verifique el codigo OEM"];
            }

        }else{
            try {
                $repuestos = repuesto::where('repuestos.cod_repuesto_proveedor', 'LIKE', '%'.$id . '%')
                ->where('repuestos.activo',1)
                ->where('repuestos.codigo_OEM_repuesto','!=','XPRESS')
                ->join('paises', 'repuestos.id_pais', 'paises.id')
                ->join('proveedores', 'repuestos.id_proveedor', 'proveedores.id')
                ->join('marcarepuestos', 'repuestos.id_marca_repuesto', 'marcarepuestos.id')
                ->select('proveedores.empresa_nombre_corto', 'paises.nombre_pais', 'marcarepuestos.marcarepuesto', 'repuestos.*')
                ->get();

                if($repuestos->count() > 0){
                    $urlfoto = repuestofoto::select('urlfoto')->where('id_repuestos',$repuestos[0]->id)->first();
                    return [$repuestos,$urlfoto];
                }else{
                    return ["error","Verifique el codigo de proveedor"];
                }

            } catch (\Exception $e) {
                return $e->getMessage();
            }

        }
    }

    public function aplicar_descuento(Request $req){
        try {
            $id_repuesto = $req->id_repuesto;
            $precio_antiguo = $req->precio_antiguo;
            $precio_actualizado = $req->precio_actualizado;
            $dcto = $req->descuento;
            $desde = $req->desde;
            $hasta = $req->hasta;
            $usuario = Auth::user();

            $repuesto = repuesto::find($id_repuesto);
            $repuesto->oferta = 1;
            $repuesto->save();

            $oferta = new oferta_pagina_web;
            $oferta->id_repuesto = $id_repuesto;
            $oferta->precio_antiguo = $precio_antiguo;
            $oferta->precio_actualizado = $precio_actualizado;
            $oferta->descuento = $dcto;
            $oferta->usuario_id = $usuario->id;
            $oferta->desde = $desde;
            $oferta->hasta = $hasta;
            $oferta->activo = 1;

            $oferta->save();

            $nuevas_ofertas = $this->dameofertas();
            return ['OK',$nuevas_ofertas];
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function dameofertas(){
        $ofertas = oferta_pagina_web::select('ofertas_pagina_web.*','repuestos.descripcion','repuestos.codigo_interno','users.name')
                                    ->join('repuestos','ofertas_pagina_web.id_repuesto','repuestos.id')
                                    ->join('users','ofertas_pagina_web.usuario_id','users.id')
                                    ->get();
        foreach($ofertas as $o){

                $o->desde =  Carbon::parse($o->desde)->format("d-m-Y");
                $o->hasta =  Carbon::parse($o->hasta)->format("d-m-Y");

        }
        return $ofertas;
    }

    public function seleccionar_kit($idrep, $local_id){

        $rep = repuesto::find($idrep);
        if($local_id == 1 && $rep->stock_actual == 0){
            return ['error','no hay stock en bodega'];
        }
        if($local_id == 3 && $rep->stock_actual_dos == 0){
            return ['error','no hay stock en tienda'];
        }
        if($local_id == 4 && $rep->stock_actual_tres == 0){
            return ['error','no hay stock en casa matriz'];
        }

        return 'OK';
    }

    public function eliminar_oferta($id){
        try {
            $oferta = oferta_pagina_web::where('id',$id)->first();
            $idrep = $oferta->id_repuesto;
            $rep = repuesto::where('id',$idrep)->first();
            $rep->oferta = 0;
            $rep->save();
            $oferta->delete();
            $ofertas = $this->dameofertas();
            return $ofertas;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function vale_consignacion(){
        try {
            $permisos = permissions_detail::all();
            foreach ($permisos as $p) {
                if($p->usuarios_id == Auth::user()->id && $p->path_ruta == '/ventas/vale_consignacion'){
                    return view('ventas.vale_por_consignacion');
                }
            }

            if(Auth::user()->rol->nombrerol == "Administrador"){
                return view('ventas.vale_por_consignacion');
            }else return redirect('home');
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }


    }

    public function estadisticas(){
        try {
            // return $rep_mas_vendidos_boleta;
            return view('ventas.estadisticas');
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function estadisticas_resumen($data)
    {
        [$fechaInicial, $fechaFinal, $horarioInicial, $horarioFinal] = explode('&', $data);

        try {
            // ── Repuestos más vendidos ────────────────────────────────────────────
            $rep_mas_vendidos_boleta = boleta_detalle::selectRaw(
                    'repuestos.descripcion, repuestos.codigo_interno,
                    COUNT(boletas_detalle.id_repuestos) AS total,
                    SUM(boletas_detalle.cantidad) AS cantidad'
                )
                ->join('repuestos', 'boletas_detalle.id_repuestos', 'repuestos.id')
                ->join('boletas', 'boletas_detalle.id_boleta', 'boletas.id')
                ->whereBetween('boletas.fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('boletas.estado_sii', 'ACEPTADO')
                ->groupBy('boletas_detalle.id_repuestos', 'repuestos.descripcion', 'repuestos.codigo_interno')
                ->get();

            $rep_mas_vendidos_factura = factura_detalle::selectRaw(
                    'repuestos.descripcion, repuestos.codigo_interno,
                    COUNT(facturas_detalle.id_repuestos) AS total,
                    SUM(facturas_detalle.cantidad) AS cantidad'
                )
                ->join('repuestos', 'facturas_detalle.id_repuestos', 'repuestos.id')
                ->join('facturas', 'facturas_detalle.id_factura', 'facturas.id')
                ->whereBetween('facturas.fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('facturas.estado_sii', 'ACEPTADO')
                ->groupBy('facturas_detalle.id_repuestos', 'repuestos.descripcion', 'repuestos.codigo_interno')
                ->get();

            $rep_mas_vendidos = $rep_mas_vendidos_boleta
                ->concat($rep_mas_vendidos_factura)
                ->groupBy('codigo_interno')
                ->map(fn($g) => (object)[
                    'codigo_interno' => $g->first()->codigo_interno,
                    'descripcion'    => $g->first()->descripcion,
                    'cantidad'       => $g->sum('cantidad'),
                    'total'          => $g->sum('total'),
                ])
                ->sortByDesc('cantidad')
                ->take(30)
                ->values();

            // ── Mejores clientes ──────────────────────────────────────────────────
            $mejores_clientes_boleta = boleta::selectRaw(
                    'clientes.nombres, clientes.apellidos, clientes.rut,
                    COUNT(boletas.id_cliente) AS total,
                    SUM(boletas.total) AS total_pesos,
                    clientes.razon_social'
                )
                ->leftJoin('clientes', 'boletas.id_cliente', 'clientes.id')
                ->whereBetween('boletas.fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('boletas.id_cliente', '<>', 4)
                ->where('boletas.estado_sii', 'ACEPTADO')
                ->groupBy('clientes.nombres', 'clientes.apellidos', 'clientes.rut', 'clientes.razon_social')
                ->get();

            $mejores_clientes_factura = factura::selectRaw(
                    'clientes.nombres, clientes.apellidos, clientes.rut,
                    COUNT(facturas.id_cliente) AS total,
                    SUM(facturas.total) AS total_pesos,
                    clientes.razon_social'
                )
                ->leftJoin('clientes', 'facturas.id_cliente', 'clientes.id')
                ->whereBetween('facturas.fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('facturas.id_cliente', '<>', 4)
                ->where('facturas.estado_sii', 'ACEPTADO')
                ->groupBy('clientes.nombres', 'clientes.apellidos', 'clientes.rut', 'clientes.razon_social')
                ->get();

            $mejores_clientes = $mejores_clientes_boleta
                ->concat($mejores_clientes_factura)
                ->groupBy('rut')
                ->map(fn($g) => (object)[
                    'rut'          => $g->first()->rut,
                    'nombres'      => $g->first()->nombres,
                    'apellidos'    => $g->first()->apellidos,
                    'razon_social' => $g->first()->razon_social,
                    'total'        => $g->sum('total'),
                    'total_pesos'  => $g->sum('total_pesos'),
                ])
                ->sortByDesc('total_pesos')
                ->take(30)
                ->values();

            // ── Vendedores boleta ────────────────────────────────────────────────
            $mejores_vendedores_boleta = boleta::selectRaw(
                    'users.id, users.name AS nombres, users.rut,
                    COUNT(boletas.usuarios_id) AS total,
                    SUM(boletas.total) AS total_pagos'
                )
                ->join('users', 'boletas.usuarios_id', 'users.id')
                ->whereBetween('boletas.fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('boletas.usuarios_id', '<>', 4)
                ->where('boletas.estado_sii', 'ACEPTADO')
                ->groupBy('users.id', 'users.name', 'users.rut')
                ->get();

            // ── Notas de crédito ──────────────────────────────────────────────────
            // Regla: las NC se descuentan por la fecha_emision de la nota de crédito.
            // La referencia fa*43*2024-06-25 solo se usa para ubicar vendedor original.
            $notas_credito = nota_de_credito::whereBetween('fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('usuarios_id', '<>', 4)
                ->where('estado_sii', 'ACEPTADO')
                ->get(['fecha_emision', 'docum_referencia', 'total']);

            $numeros_nc = $notas_credito
                ->map(fn($n) => explode('*', $n->docum_referencia))
                ->filter(fn($p) => isset($p[1]) && is_numeric($p[1]))
                ->map(fn($p) => (int) $p[1])
                ->unique()
                ->values()
                ->toArray();

            $facturas_nc = empty($numeros_nc)
                ? collect()
                : factura::whereIn('num_factura', $numeros_nc)
                    ->get(['num_factura', 'usuarios_id'])
                    ->keyBy('num_factura');

            $totales_nc_por_usuario = [];
            foreach ($notas_credito as $nota) {
                $partes = explode('*', $nota->docum_referencia);
                if (isset($partes[1]) && is_numeric($partes[1])) {
                    $fac = $facturas_nc->get((int) $partes[1]);
                    if ($fac) {
                        $uid = $fac->usuarios_id;
                        $totales_nc_por_usuario[$uid] = ($totales_nc_por_usuario[$uid] ?? 0) + (float) $nota->total;
                    }
                }
            }

            // ── Vendedores factura ───────────────────────────────────────────────
            $mejores_vendedores_factura = factura::selectRaw(
                    'users.name AS nombres, users.id, users.rut,
                    COUNT(facturas.usuarios_id) AS total,
                    SUM(facturas.total) AS total_pagos'
                )
                ->join('users', 'facturas.usuarios_id', 'users.id')
                ->whereBetween('facturas.fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('facturas.usuarios_id', '<>', 4)
                ->where('facturas.estado_sii', 'ACEPTADO')
                ->groupBy('users.id', 'users.name', 'users.rut')
                ->get();

            foreach ($mejores_vendedores_factura as $vendedor) {
                $descuento = $totales_nc_por_usuario[$vendedor->id] ?? 0;
                $vendedor->total_pagos_con_descuento = (float) $vendedor->total_pagos - (float) $descuento;
            }

            $ranking_vendedores = $mejores_vendedores_boleta
                ->map(fn($v) => (object)[
                    'rut'           => $v->rut,
                    'nombres'       => $v->nombres,
                    'total_boleta'  => (float) $v->total_pagos,
                    'total_factura' => 0.0,
                    'n_boletas'     => (int) $v->total,
                    'n_facturas'    => 0,
                ])
                ->concat(
                    $mejores_vendedores_factura->map(fn($vf) => (object)[
                        'rut'           => $vf->rut,
                        'nombres'       => $vf->nombres,
                        'total_boleta'  => 0.0,
                        'total_factura' => (float) $vf->total_pagos_con_descuento,
                        'n_boletas'     => 0,
                        'n_facturas'    => (int) $vf->total,
                    ])
                )
                ->groupBy('rut')
                ->map(fn($g) => (object)[
                    'rut'           => $g->first()->rut,
                    'nombres'       => $g->first()->nombres,
                    'total_boleta'  => $g->sum('total_boleta'),
                    'total_factura' => $g->sum('total_factura'),
                    'total_ventas'  => $g->sum('total_boleta') + $g->sum('total_factura'),
                    'n_boletas'     => $g->sum('n_boletas'),
                    'n_facturas'    => $g->sum('n_facturas'),
                ])
                ->sortByDesc('total_ventas')
                ->take(30)
                ->values();

            // ── Marcas más vendidas ───────────────────────────────────────────────
            $marcas_mas_vendidas = boleta_detalle::selectRaw(
                    'marcarepuestos.marcarepuesto, COUNT(repuestos.id_marca) AS total'
                )
                ->join('repuestos', 'boletas_detalle.id_repuestos', 'repuestos.id')
                ->join('marcarepuestos', 'repuestos.id_marca', 'marcarepuestos.id')
                ->join('boletas', 'boletas_detalle.id_boleta', 'boletas.id')
                ->whereBetween('boletas.fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('boletas.estado_sii', 'ACEPTADO')
                ->groupBy('marcarepuestos.marcarepuesto')
                ->orderByDesc('total')
                ->take(30)
                ->get();

            // ── Días con ventas: boletas + facturas - NC por fecha de NC ─────────
            $dias_boletas = boleta::selectRaw(
                    'fecha_emision AS fecha,
                    COUNT(id) AS total_dte,
                    SUM(total) AS total_pago'
                )
                ->whereBetween('fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('estado_sii', 'ACEPTADO')
                ->groupBy('fecha_emision')
                ->get();

            $dias_facturas = factura::selectRaw(
                    'fecha_emision AS fecha,
                    COUNT(id) AS total_dte,
                    SUM(total) AS total_pago'
                )
                ->whereBetween('fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('estado_sii', 'ACEPTADO')
                ->groupBy('fecha_emision')
                ->get();

            $dias_notas_credito = nota_de_credito::selectRaw(
                    'fecha_emision AS fecha,
                    COUNT(id) AS total_dte,
                    SUM(total) AS total_pago'
                )
                ->whereBetween('fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('estado_sii', 'ACEPTADO')
                ->groupBy('fecha_emision')
                ->get();

            $dias_mas_vendido = $dias_boletas
                ->map(fn($d) => (object)[
                    'fecha'      => $d->fecha,
                    'total_dte'  => (int) $d->total_dte,
                    'total_pago' => (float) $d->total_pago,
                ])
                ->concat($dias_facturas->map(fn($d) => (object)[
                    'fecha'      => $d->fecha,
                    'total_dte'  => (int) $d->total_dte,
                    'total_pago' => (float) $d->total_pago,
                ]))
                ->concat($dias_notas_credito->map(fn($d) => (object)[
                    'fecha'      => $d->fecha,
                    'total_dte'  => 0,
                    'total_pago' => -1 * (float) $d->total_pago,
                ]))
                ->groupBy('fecha')
                ->map(fn($g) => (object)[
                    'fecha'      => Carbon::parse($g->first()->fecha)->format('d-m-Y'),
                    'total'      => $g->sum('total_dte'),
                    'total_pago' => $g->sum('total_pago'),
                ])
                ->sortByDesc('total_pago')
                ->take(30)
                ->values();

            // ── Totales resumen ejecutivo ─────────────────────────────────────────
            $total_boletas = boleta::whereBetween('fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('estado_sii', 'ACEPTADO')
                ->sum('total');

            $cantidad_boletas = boleta::whereBetween('fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('estado_sii', 'ACEPTADO')
                ->count();

            $total_facturas = factura::whereBetween('fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('estado_sii', 'ACEPTADO')
                ->sum('total');

            $cantidad_facturas = factura::whereBetween('fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('estado_sii', 'ACEPTADO')
                ->count();

            $total_notas_credito = nota_de_credito::whereBetween('fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('estado_sii', 'ACEPTADO')
                ->sum('total');

            $cantidad_notas_credito = nota_de_credito::whereBetween('fecha_emision', [$fechaInicial, $fechaFinal])
                ->where('estado_sii', 'ACEPTADO')
                ->count();

            $total_boletas = (float) $total_boletas;
            $total_facturas = (float) $total_facturas;
            $total_notas_credito = (float) $total_notas_credito;

            $total_vendido_bruto = $total_boletas + $total_facturas;
            $total_general = $total_vendido_bruto - $total_notas_credito;
            $porcentaje_nc = $total_vendido_bruto > 0 ? ($total_notas_credito / $total_vendido_bruto) * 100 : 0;

            $dias_con_movimiento = $dias_mas_vendido->where('total_pago', '!=', 0)->count();
            $promedio_diario = $dias_con_movimiento > 0 ? $total_general / $dias_con_movimiento : 0;
            $ticket_promedio_boleta = $cantidad_boletas > 0 ? $total_boletas / $cantidad_boletas : 0;
            $ticket_promedio_factura = $cantidad_facturas > 0 ? $total_facturas / $cantidad_facturas : 0;

            $top_vendedores_boleta = $mejores_vendedores_boleta
                ->sortByDesc('total_pagos')
                ->take(30)
                ->values();

            $top_vendedores_factura = $mejores_vendedores_factura
                ->sortByDesc('total_pagos_con_descuento')
                ->take(30)
                ->values();

            $mejor_cliente = $mejores_clientes->first();
            $mejor_vendedor = $ranking_vendedores->first();
            $repuesto_lider = $rep_mas_vendidos->first();
            $mejor_dia = $dias_mas_vendido->first();

            return view('fragm.estadisticas', [
                'rep_mas_vendidos'           => $rep_mas_vendidos,
                'mejores_clientes'           => $mejores_clientes,
                'mejores_vendedores_boleta'  => $top_vendedores_boleta,
                'mejores_vendedores_factura' => $top_vendedores_factura,
                'ranking_vendedores'         => $ranking_vendedores,
                'marcas_mas_vendidas'        => $marcas_mas_vendidas,
                'dias_mas_vendidos'          => $dias_mas_vendido,

                'total_boletas'              => $total_boletas,
                'cantidad_boletas'           => $cantidad_boletas,
                'total_facturas'             => $total_facturas,
                'cantidad_facturas'          => $cantidad_facturas,
                'total_notas_credito'        => $total_notas_credito,
                'cantidad_notas_credito'     => $cantidad_notas_credito,
                'total_vendido_bruto'        => $total_vendido_bruto,
                'total_general'              => $total_general,
                'porcentaje_nc'              => $porcentaje_nc,
                'dias_con_movimiento'        => $dias_con_movimiento,
                'promedio_diario'            => $promedio_diario,
                'ticket_promedio_boleta'     => $ticket_promedio_boleta,
                'ticket_promedio_factura'    => $ticket_promedio_factura,
                'mejor_cliente'              => $mejor_cliente,
                'mejor_vendedor'             => $mejor_vendedor,
                'repuesto_lider'             => $repuesto_lider,
                'mejor_dia'                  => $mejor_dia,

                'chart_repuestos' => json_encode([
                    'labels' => $rep_mas_vendidos->pluck('codigo_interno')->values()->toArray(),
                    'data'   => $rep_mas_vendidos->pluck('cantidad')->map(fn($v) => (float) $v)->values()->toArray(),
                ], JSON_NUMERIC_CHECK),

                'chart_clientes' => json_encode([
                    'labels' => $mejores_clientes->map(function ($c) {
                        $nombreCompleto = trim(
                            ($c->nombres ?? '') . ' ' . ($c->apellidos ?? '')
                        );

                        return $nombreCompleto !== ''
                            ? $nombreCompleto
                            : ($c->razon_social ?: 'Sin cliente');
                    })->values()->toArray(),

                    'data' => $mejores_clientes
                        ->pluck('total_pesos')
                        ->map(fn($v) => (float) $v)
                        ->values()
                        ->toArray(),
                ], JSON_NUMERIC_CHECK),

                'chart_vendedores_boleta' => json_encode([
                    'labels' => $top_vendedores_boleta->pluck('nombres')->values()->toArray(),
                    'data'   => $top_vendedores_boleta->pluck('total_pagos')->map(fn($v) => (float) $v)->values()->toArray(),
                ], JSON_NUMERIC_CHECK),

                'chart_vendedores_factura' => json_encode([
                    'labels' => $top_vendedores_factura->pluck('nombres')->values()->toArray(),
                    'data'   => $top_vendedores_factura->pluck('total_pagos_con_descuento')->map(fn($v) => (float) $v)->values()->toArray(),
                ], JSON_NUMERIC_CHECK),

                'chart_ranking_vendedores' => json_encode([
                    'labels' => $ranking_vendedores->pluck('nombres')->values()->toArray(),
                    'data'   => $ranking_vendedores->pluck('total_ventas')->map(fn($v) => (float) $v)->values()->toArray(),
                ], JSON_NUMERIC_CHECK),

                'chart_dias' => json_encode([
                    'labels' => $dias_mas_vendido->pluck('fecha')->values()->toArray(),
                    'data'   => $dias_mas_vendido->pluck('total_pago')->map(fn($v) => (float) $v)->values()->toArray(),
                ], JSON_NUMERIC_CHECK),

                'chart_composicion' => json_encode([
                    'labels' => ['Boletas', 'Facturas', 'Notas de crédito'],
                    'data'   => [$total_boletas, $total_facturas, $total_notas_credito],
                ], JSON_NUMERIC_CHECK),
            ])->render();

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }



    public function dame_ventas_vendedor(Request $req){
        $id_vendedor = $req->id_vendedor;
        $mes = $req->mes;
        $año_actual = Carbon::now()->year;

        $sus_ventas = factura::selectRaw("
                                users.name as nombres_vendedor,
                                clientes.id,
                                clientes.rut,
                                COUNT(facturas.usuarios_id) as total,
                                SUM(facturas.total) as total_pagos,
                                facturas.num_factura,
                                clientes.nombres
                            ")
                            ->whereMonth('facturas.fecha_emision', $mes)
                            ->whereYear('facturas.fecha_emision', $año_actual)
                            ->where('facturas.usuarios_id', $id_vendedor)
                            ->where('facturas.estado_sii', 'ACEPTADO')
                            ->where('facturas.usuarios_id',"<>",4)
                            ->join('users', 'facturas.usuarios_id', 'users.id')
                            ->join('clientes','facturas.id_cliente','clientes.id')
                            ->groupBy('users.name', 'users.id', 'users.rut', 'facturas.num_factura')
                            ->orderByDesc('total_pagos') // o 'total'

                            ->get();

        $carritos_transferidos_fantasmas = carrito_transferido::selectRaw("numero_carrito,fecha_emision,cliente_id,usuarios_id,fantasma_id, SUM(pu * cantidad) as total")
                                                ->whereMonth('fecha_emision', $mes)
                                                ->whereYear('fecha_emision', $año_actual)
                                                ->where('usuarios_id','<>',238) // que el vendedor no sea felipe barrera
                                                ->where('fantasma_id',238) // que el que envia la nota de venta sea felipe barrera
                                                ->where('estado',0)
                                                ->groupBy('numero_carrito')
                                                ->orderByDesc('total')
                                                ->get();

        foreach($carritos_transferidos_fantasmas as $c){
            $c->total = $c->total * 1.19;
            $cliente = cliente_modelo::find($c->cliente_id);
            $c->cliente = $cliente;
            $cliente_vendedor = cliente_vendedor::where('id_cliente',$c->cliente_id)->where('activo',1)->first();
            if($cliente_vendedor)
                $c->vendedor = User::find($cliente_vendedor->id_vendedor);
            else
                $c->vendedor = 'desconocido';
        }


        return ['sus_ventas' => $sus_ventas, 'ventas_ajenas' => $carritos_transferidos_fantasmas];
    }



    public function guardar_dcto_familia(Request $req){
        try {

            $fecha_hoy = $this->dame_fecha_actual();

            $id_familia = $req->id_familia;
            $porcentaje = $req->porcentaje;
            $desde = $req->desde;
            $hasta = $req->hasta;
            $local_id = $req->local_id;

            $repuestos = repuesto::where('id_familia',$id_familia)->get();
            //Consultamos si el descuento ya existe para esa familia
            $existe = $this->existe_descuento($id_familia);
            if($existe) return 'error';

            //Guardamos registro de las familias con descuento
            $descuento = new descuento;
            $descuento->id_cliente = 4;
            $descuento->id_familia = $id_familia;
            $descuento->porcentaje = $porcentaje;
            if($fecha_hoy >= $desde && $fecha_hoy <= $hasta){
                $descuento->activo = 1;
            }else{
                $descuento->activo = 0;
            }

            $descuento->usuarios_id = Auth::user()->id;
            $descuento->desde = $desde;
            $descuento->hasta = $hasta;
            $descuento->id_local = $local_id;
            $descuento->save();
            $fc = new familiacontrolador();
            $descuentos = $fc->dame_familias_con_descuento()[1];
            return $descuentos;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }


    public function editar_item_carrito_transferido(Request $req){
        try {
            $id = $req->id;
            $cantidad = $req->cantidad;
            $pu = $req->pu;
            $descuento = $req->descuento;
            $tipo_documento = $req->tipo_documento;
            $item = carrito_transferido::where('id',$id)->first();
            $item->cantidad = $cantidad;
            $item->pu = $pu;
            // aplicar descuento
            $descuento_porcentaje = $descuento / 100;
            $item->descuento_item = $descuento_porcentaje * $item->cantidad * $item->pu;
            $item->subtotal_item = $cantidad * $pu;
            $item->total_item = $item->subtotal_item - $item->descuento_item;

            $item->update();

            return $this->dame_carrito_transferido_vista_cliente($item->carrito_id, $tipo_documento);
            // $total = $this->dametotalcarritotransferido($item->cliente_id);
            // return $total;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function editar_item_carrito(Request $req){
        try {
            $id = $req->id;
            $cantidad = $req->cantidad;
            $pu = $req->pu;
            $descuento = $req->descuento;
            $tipo_documento = $req->tipo_documento;
            $item = carrito_compra::where('id',$id)->first();
            $item->cantidad = $cantidad;
            $item->pu = $pu;
            // aplicar descuento
            $descuento_porcentaje = $descuento / 100;
            $item->descuento_item = $descuento_porcentaje * $item->cantidad * $item->pu;
            $item->subtotal_item = $cantidad * $pu;
            $item->total_item = $item->subtotal_item - $item->descuento_item;

            $item->update();

            return $this->dame_carrito_vista();
            // $total = $this->dametotalcarritotransferido($item->cliente_id);
            // return $total;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function existe_descuento($id_familia){
        $descuentos = descuento::all();
        foreach($descuentos as $d){
            if($d->id_familia == $id_familia){
                return true;
            }
        }

        return false;
    }

    public function eliminar_descuento_familia($id){
        descuento::destroy($id);
        $fc = new familiacontrolador();
        $descuentos = $fc->dame_familias_con_descuento()[1];
        return $descuentos;
    }

    public function agregar_producto_carro(Request $req){
        try {
            $idproducto = $req->id;
            $cantidad = $req->cantidad;
            $precio_custom = $req->precio_custom; // Nuevo precio personalizado

            $repuesto = repuesto::find($idproducto);
            // preguntar si existe el producto en el carro de compras
            $existe = carrito_compra::where('usuarios_id',Auth::user()->id)
                                    ->where('id_repuestos',$idproducto)
                                    ->first();
            if($existe){
                return 'error';
            }

            if($repuesto->id_local == 1 && $repuesto->stock_actual < $cantidad){
                return 'error';
            }else if($repuesto->id_local == 2 && $repuesto->stock_actual_mermas < $cantidad){
                return 'error';
            }else if($repuesto->id_local == 3 && $repuesto->stock_actual_preventa < $cantidad){
                return 'error';
            }

            // Determinar el precio a usar
            $precio_a_usar = $repuesto->precio_venta;

            // Si hay precio personalizado, validar que esté dentro del rango permitido
            if($precio_custom && $precio_custom > 0){
                $tope = intval($repuesto->precio_venta * 0.10);
                $precio_venta = intval($repuesto->precio_venta);
                $precio_custom = intval($precio_custom);

                // Validar que el precio personalizado no exceda el 10% de variación
                if($precio_custom > ($precio_venta + $tope) || $precio_custom < ($precio_venta - $tope)){
                    return json_encode(['error' => 'El precio debe estar entre ' . number_format($precio_venta - $tope, 0) . ' y ' . number_format($precio_venta + $tope, 0)]);
                }
                $precio_a_usar = $precio_custom;
            } else {
                // verificamos si el repuesto esta en oferta
                $oferta = oferta_pagina_web::where('id_repuesto',$idproducto)->where('activo',1)->first();
                if($oferta){
                    $precio_a_usar = $oferta->precio_actualizado;
                }
            }

            // agregar a carro compras
            $c = new carrito_compra;
            $c->usuarios_id = Auth::user()->id;
            $c->item = $this->dameultimoitem(Auth::user()->id) + 1;
            $c->id_repuestos = $idproducto;
            $c->id_local = $req->idlocal ?? 1;
            $c->id_unidad_venta = 0;
            $c->cantidad = $cantidad;
            $c->pu = $precio_a_usar;
            $c->pu_neto = round($c->pu/(1+Session::get('PARAM_IVA')),2);
            $c->descuento_item = 0.00;
            $c->subtotal_item = $cantidad * $c->pu;
            $c->total_item = $c->subtotal_item - $c->descuento_item;
            $c->save();

            $total = $this->dametotalcarrito(Auth::user()->id);

            return $total;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function agregar_producto_carro_transferido(Request $req){
        try {
            $idrep = $req->id;
            $cantidad = (int) $req->cantidad;
            $cliente_id = $req->cliente_id;
            $idlocal = (int) $req->idlocal;

            $repuesto = repuesto::find($idrep);
            if (!$repuesto) {
                return response()->json([
                    'error' => 'repuesto_no_encontrado',
                    'message' => 'El repuesto seleccionado no existe.'
                ], 404);
            }

            if ($cantidad <= 0) {
                return response()->json([
                    'error' => 'cantidad_invalida',
                    'message' => 'La cantidad debe ser mayor que cero.'
                ], 422);
            }

            // Cada local mantiene su existencia en una columna distinta.
            $columnas_stock = [
                1 => 'stock_actual',
                2 => 'stock_actual_mermas',
                3 => 'stock_actual_preventa',
            ];

            if (!isset($columnas_stock[$idlocal])) {
                return response()->json([
                    'error' => 'local_invalido',
                    'message' => 'El local seleccionado no es válido.'
                ], 422);
            }

            $stock_disponible = (int) ($repuesto->{$columnas_stock[$idlocal]} ?? 0);
            if ($cantidad > $stock_disponible) {
                return response()->json([
                    'error' => 'stock_insuficiente',
                    'message' => 'Stock insuficiente en el local seleccionado. Disponible: '.$stock_disponible.'.',
                    'stock_disponible' => $stock_disponible
                ], 422);
            }
            // preguntar si existe el producto en el carro de compras
            //Buscar si artículo ya esta en el carrito
            $esta = carrito_transferido::where('carrito_id', $cliente_id)->where('id_repuestos',$idrep)->first();
            if (!is_null($esta)) {
                return "existe";
            }

            $cantidad_items = carrito_transferido::where('carrito_id', $cliente_id)->count();
            if ($cantidad_items >= 20) {
                return "limite";
            }

            // buscamos el ultimo carrito_transferido hacia el cajero
         $carrito_transferido = carrito_transferido::where('carrito_id', $cliente_id)->orderBy('item', 'desc')->first();


            $cajero_id = $carrito_transferido->cajeros_id;
            // buscar el repuesto
            $repuesto = repuesto::find($idrep);

            $el_id_usuario = Auth::user()->id;
            $ct=new carrito_transferido;
            $ct->usuarios_id = $carrito_transferido->usuarios_id;

            $ct->cajeros_id = $cajero_id;
            $ct->numero_carrito = $cliente_id;
            $ct->cliente_id = $carrito_transferido->cliente_id;
            $ct->carrito_id = $cliente_id;
            // avanzar el item en 1 del carrito_transferido
            $ct->item=$carrito_transferido->item + 1;
            $ct->id_repuestos=$idrep;

            $ct->id_local=$idlocal;
            $ct->id_unidad_venta=1;
            $ct->cantidad=$cantidad;

            $ct->pu=$repuesto->precio_venta;
            $ct->subtotal_item=$ct->pu * $ct->cantidad;
            $ct->descuento_item=0.00;
            $ct->total_item=$ct->subtotal_item - $ct->descuento_item;
            $ct->titulo = $carrito_transferido->titulo;

            $ct->save();
            $this->re_enumera_items_carrito_transferido($cliente_id);
            return $this->dametotalcarritotransferido($cliente_id);
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }


    }

    public function nota_venta_vista()
    {
        $iva = Session::get('PARAM_IVA', 0.19);
        return view('ventas.nota_venta', compact('iva'));
    }

    public function nota_venta_buscar($numero_carrito)
    {
        try {
            $items = carrito_transferido::select(
                        'carrito_transferido.*',
                        'clientes.nombres as cliente',
                        'repuestos.codigo_interno',
                        'repuestos.descripcion'
                    )
                    ->where('carrito_transferido.numero_carrito', $numero_carrito)
                    ->leftJoin('clientes', 'carrito_transferido.cliente_id', 'clientes.id')
                    ->leftJoin('repuestos', 'carrito_transferido.id_repuestos', 'repuestos.id')
                    ->orderBy('carrito_transferido.item')
                    ->get();

            if ($items->isEmpty()) {
                $estado = ['estado' => 'ERROR', 'mensaje' => 'No existe la Nota de Venta N° ' . $numero_carrito];
                return json_encode($estado);
            }

            $primero = $items->first();
            $estado_nv = $primero->estado; // estado del primer ítem (todos tienen el mismo)

            $nota_venta = [
                'numero_carrito' => $primero->numero_carrito,
                'cliente'        => $primero->cliente,
                'fecha_emision'  => $primero->fecha_emision,
            ];

            $estado = [
                'estado'      => 'OK',
                'nota_venta'  => $nota_venta,
                'detalle'     => $items,
                'estado_nv'   => $estado_nv,
            ];

            return json_encode($estado);

        } catch (\Exception $e) {
            $estado = ['estado' => 'ERROR', 'mensaje' => substr($e->getMessage(), 0, 300)];
            return json_encode($estado);
        }
    }

    public function nota_venta_cambiar_estado(Request $r)
    {
        try {
            $numero_carrito = $r->numero_carrito;
            $nuevo_estado   = intval($r->estado); // 0 o 1

            if (!in_array($nuevo_estado, [0, 1])) {
                $estado = ['estado' => 'ERROR', 'mensaje' => 'Estado inválido'];
                return json_encode($estado);
            }

            $items = carrito_transferido::where('numero_carrito', $numero_carrito)->get();

            if ($items->isEmpty()) {
                $estado = ['estado' => 'ERROR', 'mensaje' => 'No existe la Nota de Venta N° ' . $numero_carrito];
                return json_encode($estado);
            }

            foreach ($items as $item) {
                $item->estado = $nuevo_estado;
                $item->save();
            }

            $accion = $nuevo_estado == 1 ? 'habilitada' : 'deshabilitada';
            $estado = ['estado' => 'OK', 'mensaje' => 'Nota de Venta N° ' . $numero_carrito . ' ' . $accion . ' correctamente'];
            return json_encode($estado);

        } catch (\Exception $e) {
            $estado = ['estado' => 'ERROR', 'mensaje' => substr($e->getMessage(), 0, 300)];
            return json_encode($estado);
        }
    }
}
