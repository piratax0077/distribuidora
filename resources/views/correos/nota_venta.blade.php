<html>
    <head>
        <title>IPS CHILE</title>
    </head>
    <body>
        <h1>IPS CHILE</h1>
        <p>Buen día, {{$asunto}} con {{ $notaventa->count() }} productos, enviada por {{ $notaventa->vendedor }}</p>
        <hr>
        <table style="margin-bottom: 15px;">
            <tbody>
                <tr>
                    <td> <img src="https://intranet.ipschile.cl/storage/imagenes/ips_logo.png" alt="" style="width: 60px;"></td>
                    <td style="text-align: center;"><span style="font-weight: bold;">{{Session::get('PARAM_RAZ_SOC')}}</span><br><span>{{Session::get('PARAM_GIRO')}}</span></td>
                    <td style="text-align: center;"><span>R.U.T {{Session::get('PARAM_RUT')}}</span><br>
                        <span>NOTA DE VENTA </span><br>
                        <span>{{ $notaventa[0]->numero_carrito == null ? $notaventa[0]->id : $notaventa[0]->numero_carrito }}</span></td>
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
                        <td>Codigo Vendedor {{ Auth::user()->name }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <table>
            <tr>
                <th style="text-align: center">Codigo</th>
                <th style="text-align: center">Descripcion</th>
                <th style="text-align: center">Cantidad</th>
                <th style="text-align: center">Precio</th>
                <th style="text-align: center">Total</th>
            </tr>
            @foreach($notaventa as $d)
                <tr>
                    <td style="text-align: center;">{{$d->codigo_interno}}</td>
                    <td style="text-align: center;">{{$d->descripcion}}</td>
                    <td style="text-align: center;">{{$d->cantidad}}</td>
                    <td style="text-align: center;">{{number_format($d->pu,0,',','.')}}</td>
                    <td style="text-align: center;">{{number_format($d->total_item,0,',','.')}}</td>
                </tr>
            @endforeach
        </table>
        <footer>
            <table style="margin-top: 15px;">
                <tbody>
                    <tr>
                        <td>Neto</td>
                        <td>{{number_format($total_neto,0,',','.')}}</td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td>{{number_format($total,0,',','.')}}</td>
                    </tr>
                </tbody>
            </table>
        </footer>
        <i>Cordialmente,</i>
        <h5>SISTEMA FACTURACIÓN ELECTRÓNICA<br>
        IPS CHILE<br><br>
    </body>
</html>
