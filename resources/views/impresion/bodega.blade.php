<style>
    .container{
        width: 90%;
        margin: 0px auto;
        padding: 5px;
        
    }
    table {
        width: 100%;
        margin: 0px auto;
        border-collapse: collapse;
        font-family: Arial;
    }

    #table_, th, td {
        border: 1px solid #eee;
    }

    th, td {
        padding: 5px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    /* .footer{
        display: flex;
        margin-top: 15px;
        justify-content: space-around;
    } */

    /* .footer div{
        border: 1px solid black;
    } */

    #table_{
        min-height: 650px;
        height: 650px;
    }
    
</style>

<div class="container">
    <table style="margin-bottom: 15px;">
        <tbody>
            <tr>
                <td> <img src="https://intranet.ipschile.cl/storage/imagenes/ips_logo.png" alt="" style="width: 60px;"></td>
                <td style="text-align: center;"><span style="font-weight: bold;">{{Session::get('PARAM_RAZ_SOC')}}</span><br><span>{{Session::get('PARAM_GIRO')}}</span></td>
                <td style="text-align: center;"><span>R.U.T {{Session::get('PARAM_RUT')}}</span><br>
                    <span>NOTA DE VENTA </span><br>
                    <span>{{ $carrito[0]->numero_carrito == null ? $carrito[0]->id : $carrito[0]->numero_carrito }}</span></td>
            </tr>
        </tbody>
    </table>
    <div id="cliente">
        <table style="margin-bottom: 10px; font-size: 13px;">
            <tbody>
                <tr>
                    <td>Santiago , {{ date('d-m-Y') }}</td>
                    <td>Señor {{ $cliente->empresa }} RUT {{ $cliente->rut }}</td>
                </tr>
                <tr>
                    <td>Direccion {{ $cliente->direccion }} </td>
                    <td>Giro {{ $cliente->giro }}</td>
                </tr>
                <tr>
                    <td>Ciudad {{ $cliente->ciudad }}</td>
                    <td>Codigo Vendedor {{ $cliente->vendedor }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div id="table_">
        <table class="table" style="font-size: 13px;">
            <thead>
                <tr>
                    <th>Cod Int</th>
                    <th>Descripcion</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($carrito as $c)
                    <tr>
                        <td>{{ $c->codigo_interno }}</td>
                        <td>{{ $c->descripcion }}</td>
                        <td>{{ $c->cantidad }}</td>
                        <td>{{ number_format($c->pu, 0, ',', '.') }}</td>
                        <td>{{ number_format((intval($c->pu) * $c->cantidad), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="footer">
        <table style="font-size: 13px;">
            <tbody>
                <tr>
                    <td>
                        NOTA {{ $carrito[0]->titulo }} - Tipo Despacho: {{ $carrito[0]->descripcion_tipo_despacho }}<br>
                    </td>
                    <td style="text-align: right;">
                        <div>
                            <span style="font-weight: bold;">Subtotal $</span> <span> {{ number_format($total_neto,0,',','.') }}</span><br>
                            <span style="font-weight: bold;">IVA $</span> <span> {{ number_format($total_neto * 0.19,0,',','.') }}</span><br>
                            <span style="font-weight: bold;">Total $</span> <span> {{ number_format($total,0,',','.') }}</span>
                        </div>
                        
                    </td>
                </tr>
            </tbody>
        </table>
        <table >
            <tbody>
                <tr>
                    <td style="text-align: center;">
                        <div style="margin-top: 20px;">
                            <span>{{Session::get('PARAM_RAZ_SOC')}}</span> <br>
                            <span>{{Session::get('PARAM_DIR_NEG')}} {{Session::get('PARAM_DIR_COMUNA')}}, {{ Session::get('PARAM_DIR_CIUDAD') }} Fono {{Session::get('PARAM_FONO1')}}</span> <br>
                         </div>
                    </td>
                </tr>
            </tbody>
        </table>
        
    </div>
    
    
</div>
