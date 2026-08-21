@php
    $num_doc="0";
@endphp
@if($dtes->count()>0)
    <table class="table table-sm table-hover">
        <thead>
            <th>FECHA</th>
            <th>NUM</th>
            <th>RUT</th>
            <th style="width: 30%">RAZON SOCIAL</th>
            <th>TRACKID</th>
            <th>TOTAL</th>
            <th>ESTADO SII</th>
            <th>USUARIO</th>
            <th></th><!-- imprimir -->
            <th></th><!-- enviar email -->
            <th></th><!-- ver estado -->
        </thead>
        <tbody>
            @foreach($dtes as $dte)
            <tr>
                <td>{{\Carbon\Carbon::parse($dte->fecha_emision)->format("d-m-Y")}} {{\Carbon\Carbon::parse($dte->created_at)->format("H:i")}}</td>
                <td>
                    @php
                        if($tipodte=='33'){
                            $num_doc=$dte->num_factura;
                        }
                        if($tipodte=='39'){
                            $num_doc=$dte->num_boleta;
                        }
                        if($tipodte=='52'){
                            $num_doc=$dte->num_guia_despacho;
                        }
                        if($tipodte=='56'){
                            $num_doc=$dte->num_nota_debito;
                        }
                        if($tipodte=='61'){
                            $num_doc=$dte->num_nota_credito;
                        }
                        echo $num_doc;
                    @endphp

                </td>
                <td>{{$dte->rut}}</td>
                @if($dte->tipo_cliente==0)
                    <td>{{$dte->nombres}} {{$dte->apellidos}}</td>
                @else
                    <td>{{$dte->razon_social}}</td>
                @endif
                <td>{{$dte->trackid}}</td>
                <td>{{number_format($dte->total,0,',','.')}}</td>
                <td>
                    @if($dte->estado_sii=='ACEPTADO')
                        <p style="color:blue">{{$dte->estado_sii}}</p>
                    @elseif($dte->estado_sii=='RECIBIDO')
                        <p style="color:orange">{{$dte->estado_sii}}</p>
                    @else
                        <p style="color:red">{{$dte->estado_sii}}</p>
                    @endif

                </td>

                @if($dte->estado_sii=="ACEPTADO")
                    <td>{{$dte->name}}</td>
                    <td><button class="btn btn-info btn-sm" onclick="imprimir('{{$dte->url_xml}}')">Imprimir</button></td>
                    <td><button class="btn btn-warning btn-sm" onclick="enviar_correo('{{$num_doc}}&{{$dte->email}}')">Enviar Correo</button></td>

                @elseif($dte->estado_sii=="RECIBIDO" || $dte->estado_sii=="EN REVISION")
                    <td>{{$dte->name}}</td>
                    <td><button class="btn btn-success btn-sm" onclick="ver_estado('{{$dte->trackid}}')">Ver Estado</button></td>
                    <td></td>
                @else
                    <td>{{$dte->name}}</td>
                    <td><button class="btn btn-success btn-sm" onclick="ver_estado('{{$dte->trackid}}')">Ver Estado</button></td>
                    <td></td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        function devolver_stock_rechazado(tipodte, num_doc){
            Vue.swal({
                title: '¿Esta seguro de devolver los productos?',
                text: "¡No podrá revertir esta acción!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si, devolver!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        confirmar_devolucion_stock(tipodte, num_doc);
                    }
                });

        }

        function confirmar_devolucion_stock(tipodte, num_doc){
            console.log(tipodte, num_doc);
            let url = "{{ ROUTE('ventas.devolver_stock_rechazado') }}";

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type:'post',
                url: url,
                data:{
                    tipodte: tipodte,
                    num_doc: num_doc
                },
                success: function(resp){
                    console.log(resp);
                    let codigos_internos = resp.codigos_internos;
                    let mensajeDetalle = `
                        <table style="width:100%; border-collapse: collapse; text-align:left;">
                            <thead>
                                <tr>
                                    <th style="border-bottom: 1px solid #ccc;">Código</th>
                                    <th style="border-bottom: 1px solid #ccc;">Cantidad</th>
                                    <th style="border-bottom: 1px solid #ccc;">Destino</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    if (Array.isArray(codigos_internos)) {
                        codigos_internos.forEach(item => {
                            mensajeDetalle += `
                                <tr>
                                    <td>${item.codigo_interno}</td>
                                    <td>${item.cantidad_devuelta}</td>
                                    <td>${item.destino || 'Bodega'}</td>
                                </tr>
                            `;
                        });
                    }

                    mensajeDetalle += `
                            </tbody>
                        </table>
                    `;
                    if(resp.estado == 'ok'){
                            Vue.swal({
                                title: 'Listo',
                                html: `<strong>SUS PRODUCTOS FUERON DEVUELTOS</strong><br><br>${mensajeDetalle}`,
                                icon: 'success',
                            });
                    }else{
                        Vue.swal({
                            title: 'ERROR',
                            text: resp.msj,
                            icon: 'error',
                        });
                    }
                },
                error: function(error){
                    console.log(error.responseText);
                }
            });
        }
    </script>

@else
<center><h3><p style="color:red">NO SE ENCONTRARON DOCUMENTOS</p></h3></center>

@endif
