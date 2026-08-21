<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\cliente_modelo;
use App\cliente_vendedor;
use App\cliente_direcciones;
use App\solicitudes_cambio_precio;
use App\regiones;

class vendedores_controlador extends Controller
{
    //
    public function index(){
        try {
            $vendedores = User::where('activo',1)->whereIn('role_id',[10,13])->get();
            
            $clientes = cliente_modelo::select('clientes.*','cliente_vendedor.id_vendedor','cliente_vendedor.id as id_vendedor_cliente')
                                    ->join('cliente_vendedor', 'clientes.id', 'cliente_vendedor.id_cliente')
                                    ->where('cliente_vendedor.activo', 1)
                                    ->get();

            // saber las direcciones de los clientes
            foreach ($clientes as $cliente) {
                $cliente_direccion = cliente_direcciones::select('cliente_direcciones.*','regiones.nombre as region')->where('cliente_direcciones.id_cliente', $cliente->id)->join('regiones', 'cliente_direcciones.id_region', 'regiones.id')->first();
                $cliente->region = $cliente_direccion->region;
            }

            $regiones = regiones::all();


            $solicitudes = solicitudes_cambio_precio::select('solicitudes_cambio_precio.*','users.name as vendedor', 'repuestos.descripcion as repuesto','repuestos.codigo_interno')
                                                    ->join('users', 'solicitudes_cambio_precio.id_usuario', 'users.id')
                                                    ->join('repuestos', 'solicitudes_cambio_precio.id_repuesto', 'repuestos.id')
                                                    ->where('solicitudes_cambio_precio.estado', 3)
                                                    ->get();



            return view('manten.vendedores', compact('vendedores', 'clientes', 'solicitudes', 'regiones'));
        } catch (\Exception $e) {
            //throw $th;
            return $e->getMessage();
        }

    }

    public function cargar(Request $req){
        $id = $req->id;
        $vendedor = User::find($id);

        $clientes = $this->dame_clientes_vendedor($id);

        $regiones = regiones::all();


        $solicitudes = solicitudes_cambio_precio::select('solicitudes_cambio_precio.*','users.name as vendedor', 'repuestos.descripcion as repuesto','repuestos.codigo_interno')
                                                    ->join('users', 'solicitudes_cambio_precio.id_usuario', 'users.id')
                                                    ->join('repuestos', 'solicitudes_cambio_precio.id_repuesto', 'repuestos.id')
                                                    ->where('solicitudes_cambio_precio.estado', 3)
                                                    ->orWhere('solicitudes_cambio_precio.estado', 1)
                                                    ->where('solicitudes_cambio_precio.id_usuario', $id)
                                                    ->get();

        return view('fragm.contenedor_vendedor', compact('vendedor', 'clientes', 'solicitudes', 'regiones'));
    }

    public function dame_clientes_vendedor($id){
        if($id == 238){
            $clientes = cliente_modelo::select('clientes.*','cliente_vendedor.id_vendedor','cliente_vendedor.id as id_vendedor_cliente')
            ->join('cliente_vendedor', 'clientes.id', 'cliente_vendedor.id_cliente')
            ->where('cliente_vendedor.activo', 1)
            ->get();
        }else{

            $clientes = cliente_modelo::select('clientes.*','cliente_vendedor.id_vendedor','cliente_vendedor.id as id_vendedor_cliente')
            ->join('cliente_vendedor', 'clientes.id', 'cliente_vendedor.id_cliente')
            ->where('cliente_vendedor.activo', 1)
            ->where('cliente_vendedor.id_vendedor', $id)
            ->get();
        }

        // $clientes = cliente_modelo::select('clientes.*','cliente_vendedor.id_vendedor','cliente_vendedor.id as id_vendedor_cliente')
        //     ->join('cliente_vendedor', 'clientes.id', 'cliente_vendedor.id_cliente')
        //     ->where('cliente_vendedor.activo', 1)
        //     ->where('cliente_vendedor.id_vendedor', $id)
        //     ->get();


            // saber las direcciones de los clientes
            foreach ($clientes as $cliente) {
                $cliente_direccion = cliente_direcciones::select('cliente_direcciones.*','regiones.nombre as region')->where('cliente_direcciones.id_cliente', $cliente->id)->join('regiones', 'cliente_direcciones.id_region', 'regiones.id')->first();
                $cliente->region = $cliente_direccion->region;
            }

        return $clientes;
    }
}
