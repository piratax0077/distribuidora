<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\compras_cab;
use App\compras_det;
use App\repuesto;
use App\Imports\RepuestosImport;
use App\Imports\RepuestosEdit;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DetalleComprasExport;
use App\Helpers\ExcelValidator;
use App\proveedor;
use App\proveedor_ips;
use App\local;
use App\saldo;
use App\familia;
use App\marcavehiculo;
use App\marcarepuesto;
use App\modelovehiculo;
use App\pais;
use App\pagos_factura_compra;
use App\permissions_detail;
use Carbon\Carbon; // para tratamiento de fechas
use Session;

use Illuminate\Support\Facades\Auth;

class compras_controlador extends Controller
{

    public function dame_factura($id)
    {
//25310-3k090
//rmo24 58000
//gwm-52a
//llamar x cod_int
    try {
        $cabecera=compras_cab::find($id);
// id_factura_cab
        $items=compras_det::select('compras_det.cantidad','repuestos.descripcion','compras_det.pu','compras_det.costos as flete','compras_det.subtotal','compras_det.precio_sugerido','repuestos.id')
            ->where('compras_det.id_factura_cab',$id)
            ->join('repuestos','compras_det.id_repuestos','repuestos.id')
            ->get();

        $suma_flete=0;
        foreach($items as $item){
            $suma_flete+=$item->cantidad*$item->flete;
        }

        $pagos_factura = pagos_factura_compra::select('formapago.formapago','compras_cab.factura_numero','users.name','pagos_factura_compra.*')
                                                ->join('compras_cab','compras_cab.id','pagos_factura_compra.id_factura')
                                                ->join('formapago','formapago.id','pagos_factura_compra.forma_pago')
                                                ->leftjoin('users','users.id','pagos_factura_compra.usuario_id')
                                                ->where('id_factura',$id)
                                                ->get();

            $permiso_para_editar = permissions_detail::where('usuarios_id',Auth::user()->id)->where('path_ruta','/editar_factura')->first();
            if($permiso_para_editar){
                $value=1;
            }else{
                $value = 0;
            }


            $v=view('fragm.compras_listar_factura',compact('cabecera','items','suma_flete','pagos_factura','value'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }


    }

    public function dame_facturas_por_proveedor($idproveedor)
    {
        $facturas=compras_cab::select('id','factura_fecha','factura_numero','pagada','updated_at')
        ->where('factura_id_proveedor',$idproveedor)
        ->orderBy('factura_numero','desc')
        ->get();
        $totalfacturas=$facturas->count();
        $v=view('fragm.facturas_por_proveedor',compact('facturas','totalfacturas'))->render();
        return $v;
    }

    public function dame_facturas_por_proveedor_json($idproveedor){
        $facturas=compras_cab::select('id','factura_fecha','factura_numero','pagada','updated_at')
        ->where('factura_id_proveedor',$idproveedor)
        ->orderBy('factura_numero','desc')
        ->get();
        // pasar facturas a json
        return response()->json($facturas);
    }

    public function eliminar_repuesto_factura(Request $req){
        try {
            $cabecera = compras_cab::where('factura_numero',$req->numero_factura)->first();
            $repuesto = repuesto::find($req->id_repuesto);
            $detalle = compras_det::where('id_repuestos',$repuesto->id)->where('id_factura_cab', $cabecera->id)->first();
            //$repuesto->delete();
            $detalle->delete();
            // $detalle->save();
            $this->recalcular($detalle->id_factura_cab);
            $items = $this->dame_factura($cabecera->id);
            return $items;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    private function recalcular($id_factura){
        try{
            $subt=compras_det::where('id_factura_cab',$id_factura)->sum('subtotal');
            $iva=$subt*Session::get('PARAM_IVA'); //parametro iva
            $tot=$subt+$iva;

            $cc=compras_cab::find($id_factura);
            $cc->factura_subtotal=$subt;
            $cc->factura_iva=$iva;
            $cc->factura_total=$tot;
            $cc->save();

        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();

            return $v;
        }
    }

    private function dameultimoitem($id_factura)
    {

        $ultimo=compras_det::where('id_factura_cab',$id_factura)->latest()->value('item');
        if(is_null($ultimo))
        {
            $ultimo=0;
        }
        return $ultimo;
    }


    private function dame_items($id_factura)
    {
        $items=compras_det::select('compras_det.id',
                                    'compras_det.item',
                                    'repuestos.codigo_interno',
                                    'repuestos.descripcion',
                                    'marcavehiculos.marcanombre',
                                    'modelovehiculos.modelonombre',
                                    'repuestos.anios_vehiculo',
                                    'compras_det.cantidad',
                                    'compras_det.pu',
                                    'compras_det.subtotal',
                                    'compras_det.costos',
                                    'compras_det.precio_sugerido',
                                    'locales.local_nombre')
                                ->where('compras_det.id_factura_cab','=',$id_factura)
                                ->join('repuestos','compras_det.id_repuestos','repuestos.id')
                                ->join('marcavehiculos','repuestos.id_marca_vehiculo','marcavehiculos.idmarcavehiculo')
                                ->join('modelovehiculos','repuestos.id_modelo_vehiculo','modelovehiculos.id')
                                ->join('locales','compras_det.id_local','locales.id')
                                ->get();

        return $items;

    }

    private function damerepuestos($f,$m,$n)
    {
        $r=repuesto::where('id_familia',$f)
                    ->where('id_marca_vehiculo',$m)
                    ->where('id_modelo_vehiculo',$n)
                    ->orderByraw('substr(codigo_interno,1,3)')
                    ->get();
        return $r;
    }

    private function damerepuestosprov($codprov)
    {
        $r=repuesto::where('cod_repuesto_proveedor','LIKE','%'.$codprov.'%')
                    ->orWhere('version_vehiculo','LIKE','%'.$codprov.'%')
                    ->get();
        return $r;
    }

    private function damefamilias()
    {
        $f=familia::orderBy('nombrefamilia')->get();
        return $f;
    }

    private function damemarcas()
    {
        $m=marcavehiculo::where('activo','=',1)->select('idmarcavehiculo','marcanombre','urlfoto')->orderBy('marcanombre')->get();
        return $m;
    }

    private function damemodelos()
    {
        $m=modelovehiculo::where('activo','=',1)->get();
        return $m;
    }

    private function dameproveedores()
    {
        $p=proveedor::where('activo',1)
                    ->where('es_transportista',0)
                    ->orderBy('empresa_nombre_corto')
                    ->get();
        return $p;
    }

    private function damelocales()
    {
    	$l=local::all();
    	return $l;
    }

    public function dameporcentaje($id_familia)
    {

        $f=familia::find($id_familia);
        $porcentaje=$f->porcentaje;
        return $porcentaje;
    }

    public function crear()
    {

    	$proveedores=$this->dameproveedores();
    	$locales=$this->damelocales();
        $familias=$this->damefamilias();
        $marcas=$this->damemarcas();
        $modelos=$this->damemodelos();

    	//No envío los repuestos pues en la búsqueda del repuesto a ingresar,
    	//será con AJAX para no recargar mucho la página puesto que la cantidad
    	//de registros de repuestos es y será bastante.
    	return view('inventario.compras_ingreso',compact('proveedores','locales','familias','marcas','modelos'));
    }


    public function buscarepuestos(Request $r)
    {

        $fam=$r->idFa;
        $mar=$r->idMa;
        $mod=$r->idMo;

        $repuestos=$this->damerepuestos($fam,$mar,$mod);
        $vista=view('fragm.dame_repuesto',compact('repuestos'))->render();
        return $vista;
    }

    public function buscarepuestosprov($codprov)
    {
        //
        $repuestos=$this->damerepuestosprov($codprov);
        $vista=view('fragm.dame_repuesto',compact('repuestos'))->render();
        return $vista;
    }

    public function guardarcabecera(Request $r)
    {


        if(!$r->id_cabecera){
            $cabecera=new compras_cab;
            $cabecera->factura_id_proveedor=$r->idproveedor;
            $cabecera->factura_numero=$r->numerofactura;
            $cabecera->factura_fecha=$r->fechafactura;
            $cabecera->factura_es_credito=($r->escredito=="true") ? 1:0; //checkbox

            if($cabecera->factura_es_credito==1)
            {
                $cabecera->factura_fecha_venc=$r->vencefactura;
            }else{
                $cabecera->factura_fecha_venc=null;
            }

            $cabecera->factura_subtotal=0.0;
            $cabecera->factura_iva=0.0;
            $cabecera->factura_total=0.0;
            $cabecera->factura_observaciones="";
            $cabecera->activo=1;
            $cabecera->usuarios_id=Auth::user()->id;

            try{
                $cabecera->save();
            }catch (\Exception $error){
                $debug=$error;
                $v=view('errors.debug_ajax',compact('debug'))->render();
                return $v;
            }



            $r=$cabecera->id;
            return $r;
            }else{
                $id_cabecera = $r->id_cabecera;
                $cabecera = compras_cab::find($id_cabecera);
                $cabecera->factura_id_proveedor=$r->idproveedor;
                $cabecera->factura_numero=$r->numerofactura;
                $cabecera->factura_fecha=$r->fechafactura;
                $cabecera->factura_es_credito=($r->escredito=="true") ? 1:0; //checkbox

                if($cabecera->factura_es_credito==1)
                {
                    $cabecera->factura_fecha_venc=$r->vencefactura;
                }else{
                    $cabecera->factura_fecha_venc=null;
                }

                $cabecera->factura_subtotal=0.0;
                $cabecera->factura_iva=0.0;
                $cabecera->factura_total=0.0;
                $cabecera->factura_observaciones="";
                $cabecera->activo=1;
                $cabecera->usuarios_id=Auth::user()->id;

                try{
                    $cabecera->save();
                }catch (\Exception $error){
                    $debug=$error;
                    $v=view('errors.debug_ajax',compact('debug'))->render();
                    return $v;
                }



                $r=$cabecera->id;
                return $r;
                }

    }

    public function guardaritem(Request $r)
    {



// Validaciones : https://styde.net/como-trabajar-con-form-requests-en-laravel/
        $reglas=array(
            'pu'=>'required',
            'cantidad'=>'required'


                    );

        $mensajes=array(
            'pu.required'=>'Debe elegir un repuesto',
            'cantidad.required'=>'Ingrese la cantidad'
                    );

        try{
            $this->validate($r,$reglas,$mensajes);
        }catch (\Exception $error){
            $errores=$error->validator->getMessageBag();
            $v=view('errors.validacion',compact('errores'))->render();
            return $v;
        }

        $detalle=new compras_det;
        $detalle->id_factura_cab=$r->idFactura;
        $item=$this->dameultimoitem($r->idFactura);
        $detalle->item=$item+1;
        $detalle->id_repuestos=$r->idrepuesto;
        $detalle->cantidad=$r->cantidad;
        $detalle->pu=$r->pu;
        $detalle->subtotal=$r->subtotalitem;
        $detalle->costos=$r->costos;
        $detalle->costos_descripcion=$r->costosdesc;
        $detalle->precio_sugerido=$r->preciosug;
        $detalle->id_local=$r->idLocal;
        $detalle->activo=1;
        $detalle->usuarios_id=Auth::user()->id;

        try{
            $detalle->save();
        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }

        // Actualizar tabla saldos considerando el local
        // id_repuestos,id_local,saldo,activo,usuarios_id
        // En ves de llamar un controlador desde otro controlador,
        // es mejor usar traits: https://www.php.net/manual/es/language.oop5.traits.php
        $r=new repuestocontrolador();
        $r->actualiza_saldos('I',$detalle->id_repuestos,$detalle->id_local,$detalle->cantidad);


        //Calculamos para actualizar cabecera de factura con subtotal, iva y total

        try{
            $subt=compras_det::where('id_factura_cab',$detalle->id_factura_cab)->sum('subtotal');
            $iva=$subt*Session::get('PARAM_IVA'); //parametro iva
            $tot=$subt+$iva;

            $cc=compras_cab::find($detalle->id_factura_cab);
            $cc->factura_subtotal=$subt;
            $cc->factura_iva=$iva;
            $cc->factura_total=$tot;
            $cc->save();




            }catch (\Exception $error){
                $debug=$error;
                $v=view('errors.debug_ajax',compact('debug'))->render();

                return $v;
            }

        $resp="Guardado Item ".$detalle->item." Puede agregar más Items si desea";

        return $resp;

    }

    public function importar_repuestos(Request $request)
    {

        try {
            // Validar que el request tenga un archivo
            $request->validate([
                'archivo' => 'required|file',
            ]);

             // Importar los datos
             // Importar los datos desde el Excel
            $repuestosRaw = Excel::toArray(new RepuestosImport, $request->file('archivo'));

            // Filtramos las filas vacías
            $repuestos[0] = array_filter($repuestosRaw[0], function ($fila) {
                // Consideramos la fila vacía si todos sus campos son null o vacíos
                return collect($fila)->filter(function ($valor) {
                    return !is_null($valor) && $valor !== '';
                })->isNotEmpty();
            });
            // --- Pasada de validación completa antes de guardar nada ---
            $errores = [];
            foreach ($repuestos[0] as $index => $repuesto) {
                $resultado = ExcelValidator::validarFila($repuesto, [
                    'codigo', 'codigo_ips', 'descrip', 'costo',
                    'precio_neto', 'stock', 'marca_rep',
                    'pais_proc', 'proveedor'
                ], $index);

                if (!$resultado['valid']) {
                    $errores = array_merge($errores, $resultado['errores']);
                }
            }

            if (count($errores)) {
                return redirect()->back()->with('error', implode('<br>', $errores));
            }

            // preguntar si existe la factura por el numero de factura y por proveedor
            $factura_existente = compras_cab::where('factura_numero', strtolower($request->numerofactura_importada))->where('factura_id_proveedor', $request->proveedor_importado)->first();

            if(!$factura_existente){
                // guardar cabecera de la factura
                $cabecera=new compras_cab;
                $cabecera->factura_id_proveedor=$request->proveedor_importado; //proveedor por defecto
                $cabecera->factura_numero=$request->numerofactura_importada;
                $cabecera->factura_fecha=$request->fechafactura_importada;
                $cabecera->factura_es_credito=0; //checkbox
                $cabecera->factura_fecha_venc=null;
                $cabecera->factura_subtotal=0.0;
                $cabecera->factura_iva=0.0;
                $cabecera->factura_total=0.0;
                $cabecera->factura_observaciones="";
                $cabecera->activo=1;
                $cabecera->usuarios_id=Auth::user()->id;
                $cabecera->save();
            }else{
                $cabecera = $factura_existente;
            }


            $paises = array_unique(array_column($repuestos[0], 'pais_proc'));
            $marcas = array_unique(array_column($repuestos[0], 'marca_rep'));
            $proveedores = array_unique(array_column($repuestos[0], 'proveedor'));

            // Guardar cada repuesto en la base de datos
            foreach ($repuestos[0] as $index => $repuesto) {

                $pais = pais::where('nombre_pais', $repuesto['pais_proc'])->first();

                $marca = marcarepuesto::where('marcarepuesto', $repuesto['marca_rep'])->first();
                $proveedor = proveedor_ips::where('nombre_proveedor', $repuesto['proveedor'])->first();
                if(!$proveedor){
                    $proveedor = proveedor_ips::firstOrCreate(['nombre_proveedor' => $repuesto['proveedor']]);
                }
                // preguntar si existe el repuesto por el codigo_interno
                $repuesto_existente = repuesto::where('codigo_interno', $repuesto['codigo_ips'])->first();

                if($repuesto_existente){
                    $repuesto_existente->id_pais = $pais->id;
                    $repuesto_existente->id_proveedor = $proveedor->id;
                    $repuesto_existente->codigo = $repuesto['codigo'];
                    $repuesto_existente->codigo_fab = $repuesto['codigo'];
                    $repuesto_existente->precio_compra = $repuesto['costo'];
                    // si el bodega_destino es 1
                    if($request->bodega_destino == 1){

                        // sumar el stock
                        $repuesto_existente->id_local = 1;
                        $repuesto_existente->id_local_dos = 2;
                        $repuesto_existente->id_local_tres = 3;
                        $repuesto_existente->stock_actual += $repuesto['stock'];
                        $repuesto_existente->precio_venta = $repuesto['precio_neto'];
                        // limpiamos el nuevo estado
                        $repuesto_existente->estado = null;
                        $repuesto_existente->save();
                    }else if($request->bodega_destino == 2){

                        // actualizar el stock
                        $repuesto_existente->id_local = 1;
                        $repuesto_existente->id_local_dos = 2;
                        $repuesto_existente->id_local_tres = 3;
                        $repuesto_existente->stock_actual_mermas = $repuesto['stock'];
                        $repuesto_existente->precio_venta = $repuesto['precio_neto'];
                        $repuesto_existente->estado = null;
                        $repuesto_existente->save();
                    }else{

                        $repuesto_existente->id_local = 1;
                        $repuesto_existente->id_local_dos = 2;
                        $repuesto_existente->id_local_tres = 3;
                        $repuesto_existente->stock_actual_preventa = $repuesto['stock'];
                        $repuesto_existente->precio_venta = $repuesto['precio_neto'];
                        $repuesto_existente->estado = null;
                        $repuesto_existente->save();
                    }


                    // guardar el detalle de la compra del repuesto existente
                    $detalle = new compras_det;
                    $detalle->id_factura_cab = $cabecera->id;
                    $detalle->item = $this->dameultimoitem($cabecera->id) + 1;
                    $detalle->id_repuestos = $repuesto_existente->id;
                    $detalle->cantidad = $repuesto['stock'];
                    $detalle->pu = $repuesto['costo'];
                    $detalle->subtotal = $repuesto['costo'] * $repuesto['stock'];
                    $detalle->costos = 0;
                    $detalle->costos_descripcion = 'N/A';
                    $detalle->precio_sugerido = $repuesto['precio_neto'];
                    $detalle->id_local = $request->bodega_destino; // Local por defecto
                    $detalle->activo = 1;
                    $detalle->usuarios_id = Auth::user()->id;
                    $detalle->save();
                }else{

                    // Obtener o crear el país, marca y proveedor específicos de esta fila
                    $pais = pais::firstOrCreate(['nombre_pais' => $repuesto['pais_proc']]);
                    $marca = marcarepuesto::firstOrCreate(['marcarepuesto' => $repuesto['marca_rep']]);
                    $proveedor = proveedor_ips::firstOrCreate(['nombre_proveedor' => $repuesto['proveedor']]);

                    if($repuesto['descri_sof']==""){
                        $repuesto['descri_sof']="N/A";
                    }

                    if($request->bodega_destino == 1){
                        $rep = new repuesto([
                            'codigo' => $repuesto['codigo'],
                            'codigo_fab' => $repuesto['codigo'],
                            'codigo_interno' => $repuesto['codigo_ips'],
                            'descripcion' => $repuesto['descrip'],
                            'observaciones' => $repuesto['descri_sof'],
                            'precio_compra' => $repuesto['costo'],
                            'pu_neto' => $repuesto['costo'],
                            'precio_venta' => $repuesto['precio_neto'],
                            'stock_minimo' => 3,
                            'stock_maximo' => 100,
                            'stock_actual' => $repuesto['stock'],
                            'stock_actual_mermas' => 0,
                            'stock_actual_preventa' => 0,
                            'familia' => 'importado',
                            'id_marca' => $marca->id,
                            'id_local' => 1,
                            'id_local_dos' => 2,
                            'id_local_tres' => 3,
                            'id_proveedor' => $proveedor->id,
                            'id_pais' => $pais->id,
                            'cu' => $repuesto['cu'],
                            'ft' => $repuesto['ft'],
                            'st' => $repuesto['st'],
                            'linea' => $repuesto['linea'],
                            'cif' => $repuesto['cif'],
                            'usuarios_id' => Auth::user()->id,
                            'codigo_barras' => '*'.$repuesto['codigo'].'*', // Se agregan asteriscos al principio y al final del código
                            'activo' => 1,
                            'estado' => 1,
                        ]);
                    }else if($request->bodega_destino == 2){
                        $rep = new repuesto([
                            'codigo' => $repuesto['codigo'],
                            'codigo_interno' => $repuesto['codigo_ips'],
                            'descripcion' => $repuesto['descrip'],
                            'observaciones' => $repuesto['descri_sof'],
                            'precio_compra' => $repuesto['costo'],
                            'pu_neto' => $repuesto['costo'],
                            'precio_venta' => $repuesto['precio_neto'],
                            'stock_minimo' => 3,
                            'stock_maximo' => 100,
                            'stock_actual' => 0,
                            'stock_actual_mermas' => $repuesto['stock'],
                            'stock_actual_preventa' => 0,
                            'familia' => 'importado',
                            'id_marca' => $marca->id,
                            'id_local' => 1,
                            'id_local_dos' => 2,
                            'id_local_tres' => 3,
                            'id_proveedor' => $proveedor->id,
                            'id_pais' => $pais->id,
                            'cu' => $repuesto['cu'],
                            'ft' => $repuesto['ft'],
                            'st' => $repuesto['st'],
                            'linea' => $repuesto['linea'],
                            'cif' => $repuesto['cif'],
                            'usuarios_id' => Auth::user()->id,
                            'codigo_barras' => '*'.$repuesto['codigo'].'*', // Se agregan asteriscos al principio y al final del código
                            'activo' => 1,
                            'estado' => 1,
                        ]);
                    }else{
                        $rep = new repuesto([
                            'codigo' => $repuesto['codigo'],
                            'codigo_interno' => $repuesto['codigo_ips'],
                            'descripcion' => $repuesto['descrip'],
                            'observaciones' => $repuesto['descri_sof'],
                            'precio_compra' => $repuesto['costo'],
                            'pu_neto' => $repuesto['costo'],
                            'precio_venta' => $repuesto['precio_neto'],
                            'stock_minimo' => 3,
                            'stock_maximo' => 100,
                            'stock_actual' => 0,
                            'stock_actual_mermas' => 0,
                            'stock_actual_preventa' => $repuesto['stock'],
                            'familia' => 'importado',
                            'id_marca' => $marca->id,
                            'id_local' => 1,
                            'id_local_dos' => 2,
                            'id_local_tres' => 3,
                            'id_proveedor' => $proveedor->id,
                            'id_pais' => $pais->id,
                            'cu' => $repuesto['cu'],
                            'ft' => $repuesto['ft'],
                            'st' => $repuesto['st'],
                            'linea' => $repuesto['linea'],
                            'cif' => $repuesto['cif'],
                            'usuarios_id' => Auth::user()->id,
                            'codigo_barras' => '*'.$repuesto['codigo'].'*', // Se agregan asteriscos al principio y al final del código
                            'activo' => 1,
                            'estado' => 1,
                        ]);
                    }

                    $rep->save();

                    // guardar el detalle de la compra
                    $detalle = new compras_det;
                    $detalle->id_factura_cab = $cabecera->id;
                    $detalle->item = $this->dameultimoitem($cabecera->id) + 1;
                    $detalle->id_repuestos = $rep->id;
                    $detalle->cantidad = $repuesto['stock'];
                    $detalle->pu = $repuesto['costo'];
                    $detalle->subtotal = $repuesto['costo'] * $repuesto['stock'];
                    $detalle->costos = 0;
                    $detalle->costos_descripcion = 'N/A';
                    $detalle->precio_sugerido = $repuesto['precio_neto'];
                    $detalle->id_local = $request->bodega_destino; // Local por defecto
                    $detalle->activo = 1;
                    $detalle->usuarios_id = Auth::user()->id;
                    $detalle->save();
                }
            }

            // Redirigir al usuario con un mensaje de éxito
            return redirect()->back()->with('success', 'Importación exitosa.');
        } catch (\Exception $e) {
            //throw $th;
            return redirect()->back()->with('error', $e->getMessage());
        }

    }

    public function detalle_compras(){
        $proveedores = $this->dameproveedores();
        return view('inventario.detalle_compras', compact('proveedores'));
    }

    public function dame_compras_por_fechas($fecha_inicial, $fecha_final){
        try {
            //code...
            $compras = compras_cab::select('compras_cab.*','proveedores.empresa_nombre_corto','users.name as usuario_nombre')
                    ->whereBetween('factura_fecha', [$fecha_inicial, $fecha_final])
                    ->leftJoin('proveedores','compras_cab.factura_id_proveedor','proveedores.id')
                    ->leftJoin('users','compras_cab.usuarios_id','users.id')
                    ->orderBy('factura_fecha','asc')
                    ->get();

            $total_compras = $compras->count();
            $suma_total = $compras->sum('factura_total');

            $v = view('fragm.compras_por_fechas', compact('compras','total_compras','suma_total'))->render();
            return $v;
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }

    }

    public function traspasar_bodega_preventa(){
        $repuestos = repuesto::where('id_local', 1)
            ->orWhere('id_local_dos', 2)
            ->orWhere('id_local_tres', 3)
            ->where('stock_actual_preventa', '>=', 0)
            ->get();
        // recorrer los repuestos y al stock_actual sumarle el stock_actual_preventa
        foreach ($repuestos as $repuesto) {
            $repuesto->stock_actual += $repuesto->stock_actual_preventa;
            $repuesto->stock_actual_preventa = 0; // Limpiar el stock de preventa
            $repuesto->save();
        }

        return ['estado' => 'ok', 'mensaje' => 'Traspaso de bodega a preventa realizado correctamente.'];
    }

    public function editar_repuestos(Request $request)
    {
        try {
            $request->validate([
                'archivo' => 'required|file',
            ]);

            $repuestos = Excel::toArray(new RepuestosEdit, $request->file('archivo'));
            $totalActualizados = 0;

            foreach ($repuestos[0] as $item) {
                $codigo = $item['codigo_interno'] ?? null;
                $codigo_fab = $item['codigo_mexico'] ?? null;
                $nombrePais = $item['nueva_procedencia'] ?? null;
                $nombreMarca = $item['marcas'] ?? null;

                if (!$codigo || !$nombrePais || !$nombreMarca) {
                    continue;
                }

                $repuesto = repuesto::where('codigo_interno', $codigo)->first();

                if ($repuesto) {
                    $pais = pais::firstOrCreate(['nombre_pais' => $nombrePais]);
                    $marca = marcarepuesto::firstOrCreate(['marcarepuesto' => $nombreMarca]);

                    $repuesto->id_pais = $pais->id;
                    $repuesto->id_marca = $marca->id;
                    $repuesto->codigo_fab = $codigo_fab;
                    $repuesto->save();

                    $totalActualizados++;
                }
            }

            return response()->json([
                'mensaje' => "Se actualizaron $totalActualizados repuestos correctamente.",
                'actualizados' => $totalActualizados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }


    public function edicion_repuestos(){
        return view('inventario.edicion_repuestos');
    }

    public function moveritem(Request $req){
        try {

            $item = compras_det::where('id_factura_cab', $req->factura_origen)->where('id_repuestos',$req->idrep)->first();
            $item->id_factura_cab = $req->factura_destino;
            $item->save();

            $subt=compras_det::rightjoin('repuestos','compras_det.id_repuestos','repuestos.id')->where('id_factura_cab',$req->factura_destino)->sum('subtotal');
            $iva=$subt*Session::get('PARAM_IVA'); //parametro iva
            $tot=$subt+$iva;

            $cc=compras_cab::find($req->factura_destino);

            $cc->factura_subtotal=$subt;
            $cc->factura_iva=$iva;
            $cc->factura_total=$tot;
            $cc->save();

            $subt_origen = compras_det::rightjoin('repuestos','compras_det.id_repuestos','repuestos.id')->where('id_factura_cab', $req->factura_origen)->sum('subtotal');
            $iva_origen = $subt_origen*Session::get('PARAM_IVA'); //parametro iva
            $tot_origen = $subt_origen+$iva_origen;

            $cc_origen = compras_cab::find($req->factura_origen);

            $cc_origen->factura_subtotal=$subt_origen;
            $cc_origen->factura_iva=$iva_origen;
            $cc_origen->factura_total=$tot_origen;
            $cc_origen->save();

            return $this->dame_factura($req->factura_origen);
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }

    }

    public function damefacturacab($idfactura){
        try {
            $cabecera = compras_cab::select('compras_cab.*','proveedores.empresa_nombre','proveedores.empresa_nombre_corto','users.name')
                            ->join('proveedores','compras_cab.factura_id_proveedor','proveedores.id')
                            ->leftjoin('users','compras_cab.usuarios_id','users.id')
                            ->where('compras_cab.id',$idfactura)
                            ->first();

            $v = view('fragm.factuprodu_cab',compact('cabecera'))->render();
            return $v;
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }

    }

    public function dameitemsfactura($id_factura)
    {


        $items=$this->dame_items($id_factura);
        $st=0.0;

        foreach($items as $item)
        {
            $st=$st+$item->subtotal;
        }
        $iva=$st*Session::get('PARAM_IVA');
        $total=$st+$iva;
        $view=view('fragm.compras_items',compact('items','st','iva','total'))->render();
        return $view;
    }

    public function eliminaritem($id) //Es el id de la tabla compras_det, no es de la factura.
    {


        try{
            $c=compras_det::find($id);
            $operacion="E"; //Egreso
            $idrep=$c->id_repuestos;
            $idlocal=$c->id_local;
            $cantidad=$c->cantidad;

            $r=new repuestocontrolador();
            $r->actualiza_saldos($operacion,$idrep,$idlocal,$cantidad);


            compras_det::destroy($id);
            return "<strong>Item Eliminado...</strong>";

        }catch (\Exception $error){
            $debug=$error;
            $v=view('errors.debug_ajax',compact('debug'))->render();
            return $v;
        }

    }

    public function listar()
    {
        $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 4 && $permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == '/compras/listar'){
                        $proveedores=$this->dameproveedores();
                        return view('inventario/compras_listar',compact('proveedores'));
                    }
            }
        $user = Auth::user();
        if ($user->rol->nombrerol == "Administrador") {
                $proveedores=$this->dameproveedores();
                return view('inventario.compras_listar',compact('proveedores'));
        } else {
            return redirect('home');
        }

    }

    public function listar_por_factura(){
        $permisos_detalles = permissions_detail::all();
            foreach($permisos_detalles as $permiso_detalle){
                if($permiso_detalle->permission_id == 4 && $permiso_detalle->usuarios_id == Auth::user()->id && $permiso_detalle->path_ruta == '/compras/listar_por_factura'){

                        return view('inventario/compras_listar_factura');
                    }
            }
        $user = Auth::user();
        if ($user->rol->nombrerol == "Administrador") {
            return view('inventario/compras_listar_factura');
        } else {
            return redirect('home');
        }

    }

    public function listar_factura_numero($num_factura){

        try {
                $factura = compras_cab::where('compras_cab.factura_numero',$num_factura)
                                            ->first();
                $cabecera =  $factura;

                $items=compras_det::select('compras_det.cantidad','repuestos.cod_repuesto_proveedor','repuestos.descripcion','compras_det.pu','compras_det.costos as flete','compras_det.subtotal','compras_det.precio_sugerido')
                    ->where('compras_det.id_factura_cab',intval($cabecera->id))
                    ->join('repuestos','compras_det.id_repuestos','repuestos.id')
                    ->get();

                $suma_flete=0;
                foreach($items as $item){
                    $suma_flete+=$item->cantidad*$item->flete;
                }

                $v=view('fragm.compras_listar_factura',compact('cabecera','items','suma_flete'))->render();
                return $v;

        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function listar_factura_id($id){

        try {
                $factura = compras_cab::where('compras_cab.id',$id)
                                            ->first();
                $cabecera =  $factura;

                $items=compras_det::select('compras_det.cantidad','repuestos.codigo_interno','repuestos.descripcion','compras_det.pu','compras_det.costos as flete','compras_det.subtotal','compras_det.precio_sugerido')
                    ->where('compras_det.id_factura_cab',intval($cabecera->id))
                    ->join('repuestos','compras_det.id_repuestos','repuestos.id')
                    ->get();

                $suma_flete=0;
                foreach($items as $item){
                    $suma_flete+=$item->cantidad*$item->flete;
                }

                $v=view('fragm.compras_listar_factura',compact('cabecera','items','suma_flete'))->render();
                return $v;

        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function exportar_compras_excel(Request $request){
        try {
            $fechaInicial = $request->query('fechainicial');
            $fechaFinal = $request->query('fechafinal');
            $filename = 'repuestos_comprados_'.$fechaInicial.'_'.$fechaFinal.'.xls';

            return Excel::download(new DetalleComprasExport($fechaInicial, $fechaFinal), $filename);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function pagar_factura(Request $req){
        $idfactura = $req->idfactura;
        try {
            $factura = compras_cab::where('compras_cab.id',$idfactura)
                                    ->first();
            $factura->pagada = 1;

            $factura->save();

            for ($i = 0; $i < count($req->forma_pago); $i++) {
                $p = new pagos_factura_compra;
                $p->id_factura = $idfactura;
                $p->forma_pago = $req->forma_pago[$i];
                $p->fecha_pago = Carbon::today()->toDateString(); //Solo la fecha de hoy
                $p->monto = $req->monto[$i];
                $p->referencia = $req->referencia[$i];
                $p->activo = 1;
                $p->usuario_id = Auth::user()->id;

                $p->save();
            }


        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return ['OK',$idfactura];
    }

    public function dame_item_factura($id_repuesto, $id_factura){
        try {
            $compras_cab = compras_cab::select('compras_cab.*','proveedores.empresa_nombre','users.name')
                                        ->join('proveedores','compras_cab.factura_id_proveedor','proveedores.id')
                                        ->leftjoin('users','compras_cab.usuarios_id','users.id')
                                        ->where('compras_cab.id',$id_factura)
                                        ->first();

            $fecha_formateada = Carbon::createFromFormat('Y-m-d', $compras_cab->factura_fecha)->format('d-m-Y');
            $compras_cab->factura_fecha = $fecha_formateada;
            $repuesto = compras_det::select('repuestos.descripcion','repuestos.id', 'compras_det.id_factura_cab','compras_det.cantidad','compras_cab.factura_numero','compras_det.pu','compras_det.precio_sugerido')
                    ->join('repuestos','compras_det.id_repuestos','repuestos.id')
                    ->join('compras_cab','compras_det.id_factura_cab','compras_cab.id')
                    ->where('id_factura_cab', $compras_cab->id)
                    ->where('id_repuestos',$id_repuesto)
                    ->first();

            $v = view('fragm.compras_detalle_item',compact('repuesto','compras_cab'))->render();
            return $v;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function actualizar_item_factura(Request $req){
        try {
            $factura_id = $req->factura_id;
            $repuesto_id = $req->repuesto_id;
            $cantidad = $req->nueva_cantidad;
            $pu = $req->nuevo_pu;
            $ps = $req->nuevo_ps;
            $nuevo_numero_factura = $req->nuevo_numero_factura;
            $flete = $req->flete;
            $compras_cab = compras_cab::find($factura_id);
            $item = compras_det::where('id_repuestos',$repuesto_id)->where('id_factura_cab', $compras_cab->id)->first();

            $item->cantidad = intval($cantidad);
            $item->pu = intval($pu);
            $item->subtotal = intval($cantidad) * intval($pu);
            if($flete < 0){
                $item->costos = 0;
            }else{
                $item->costos = $flete;
            }

            $item->precio_sugerido = $ps;


           $item->save();

            $subt = compras_det::where('id_factura_cab', $factura_id)->sum('subtotal');

            $iva = $subt *Session::get('PARAM_IVA'); //parametro iva
            $tot = $subt + $iva;

            $compras_cab->factura_subtotal = $subt;
            $compras_cab->factura_iva = $iva;
            $compras_cab->factura_total = $tot;
            $compras_cab->factura_numero = $nuevo_numero_factura;

            $compras_cab->update();
            return ['OK', $compras_cab];
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

}
