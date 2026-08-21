<div class="col-sm-12 tabla-scroll-y-300" style="padding:0px">
        @if($clientes->count()>0)
        <table class="table table-bordered table-hover table-sm">
            <thead class="letra-chica">
            <th width="18%" scope="col">RUT</th>
            <th width="30%" scope="col">NOMBRES</th>
            <th width="32%" scope="col">TIPOS PAGO</th>
            <th width="20%" scope="col">ESTADO</th>
        </thead>
        <tbody>
            @foreach($clientes as $c)
            <tr>
                @if(substr($c->rut,0,4)=='6666')
                    <td style="text-align: right;padding-right:8px"><small>{{$c->rut}}</small></td>
                    <td colspan="2"><small>Para boletas y cotizaciones sin especificar cliente</small></td>
                @else
                    <!-- $c envia todo el array asociativo a ventas_principal.blade para poder acceder a todos los datos del cliente-->
                    <td style="text-align: right;padding-right:8px"><small><a href="javascript:void(0);" @if($c->moroso !== 1) onclick="cargar_cliente({{$c}})" @endif id="rut{{$c->rut}}"><script>formatear_rut('{!!$c->rut!!}')</script></a></small></td>

                    <td><small>{{$c->nombres}} {{$c->apellidos}}</small></td>
                    <td><small>
                        @if($c->formas_pago->count()==0)
                        <span style="color:red">NO DEFINIDO, COMUNICARSE CON ADMINISTRADOR.</span>
                        @endif
                        <ul style="list-style: none;">
                            @foreach($c->formas_pago as $fp)
                            <li>{{$fp->formapago}}</li>
                            @endforeach
                        </ul>
                    </small></td>
                    <td>@if($c->moroso == 1) <span class="badge badge-danger">MOROSO <br> {{ $c->descripcion_activo }} </span> @else <span class="badge badge-success">ACTIVO</span> @endif</td>
                @endif
            </tr>
            @endforeach
        </tbody>
        </table>
        @else
        <div class="col-sm-12">
            <br>
            <h3 style="text-align: center;color:red">No se encontraron clientes</h3>
        <!--
            <h4>¿ Agregar cliente rápido ?:</h4>
            <table>
            <th width="20%" scope="col"></th><th width="40%" scope="col"></th>
            <tr>
                <td>RUT:</td><td><abbr title="Ingrese el RUT sin puntos ni guiones. Sólo números y letra K."><input type="text" id="rut_cliente_rapido" size="12" maxlength="10" onKeyPress="return soloNumeros(event)" placeholder="OBLIGATORIO"></abbr></td>
            </tr>
            <tr>
                <td>NOMBRES:</td><td><input type="text" id="nombres_cliente_rapido" size="20" placeholder="OBLIGATORIO"></td>
            </tr>
            <tr>
                <td>APELLIDOS:</td><td><input type="text" id="apellidos_cliente_rapido" size="20" placeholder="OBLIGATORIO"></td>
            </tr>
            <tr>
                <td></td><td><button class="btn btn-success btn-sm" onclick="agregar_cliente_rapido()">AGREGAR</button></td>
            </tr>
            </table>
            <p id="mensajes_cliente_rapido"></p>
        -->
        </div>
        @endif
</div>
