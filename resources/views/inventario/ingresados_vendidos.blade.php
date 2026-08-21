@extends('plantillas.app')
@section('contenido_titulo_pagina')
@include('fragm.breadcrumb',['titulo'=>'Ingresados y vendidos','titulo_modulo'=>'Inventario','ruta_modulo'=>'Inventario'])
@endsection
@section('javascript')
<script>
    function espere(mensaje)
    {
        Vue.swal({
                title: mensaje,
                icon: 'info',
                showConfirmButton: true,
                showCancelButton: false,
                allowOutsideClick:false,
            });
    }
    function enter_press(e)
    {
        var keycode = e.keyCode;
        if(keycode=='13')
        {
            dame_info();
        }
    }

    function dame_info(){

        let codigo_repuesto = document.getElementById('codigo_ips_detalle').value;
        let opt = $('input:radio[name=flexRadioDefault]:checked').val();
        let desde = 'ingresados_vendidos';
        let data = {codigo_repuesto: codigo_repuesto, option: opt,desde: desde};

        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
        type:'POST',
            url:'/repuesto/damestockrepuesto',
            data: data,
            beforeSend: function(){
            $('#mensajes').empty();
            $('#mensajes').append('Buscando ...');
            espere('Buscando ...');
            },
            success: function(resp){

            Vue.swal.close();
            console.log(resp);
            let total_vendido = 0;
            let traspasos = resp[3];
            let modificaciones = resp[4];
            let devoluciones = resp[5];
            let total_devoluciones = resp[6];
            let fotos = resp[7];

            let cantidad_ingresada_stock = 0;
            let total_vendido_stock = 0;
            let total_modificaciones = 0;


            $('#ingresados').empty();
            if(resp[0] === "error"){

                Vue.swal({

                title:'Error!',

                text:resp[1],

                icon:'error'

                });
                // limpiar los campos
                $('#info_repuesto').empty();
                $('#ingresados').empty();
                $('#vendidos').empty();
                $('#traspasos').empty();
                //$('#seccion_detalle_repuesto').hide();
                return false;
            }
            if(modificaciones.length > 0){
                // Calcular el total de modificaciones de stock (diferencia entre stock nuevo y anterior)
                modificaciones.forEach(mod => {
                    let diferencia = parseFloat(mod.stock) - parseFloat(mod.stock_anterior);
                    total_modificaciones += diferencia;
                });
            }
            resp[0].forEach(item => {
                cantidad_ingresada_stock += item.cantidad;
            });


            resp[1].forEach(element => {
                    total_vendido_stock += element.cantidad;
            });

            console.log(total_vendido_stock);

            console.log((resp[2].stock_actual  + resp[2].stock_actual_preventa));
            let total_stock = (resp[2].stock_actual  + resp[2].stock_actual_preventa);
            $('#id_repuesto').val(resp[2].id);
            ver_devoluciones(resp[2].id);

            // Calcular cantidad ingresada por facturas
            let cantidad_ingresada = 0;

            if(resp[0].length == 0){
                // No hay ingresos por factura, pero puede haber modificaciones
                $('#ingresados').empty();
                $('#ingresados').append(`
                    <tr>
                        <td colspan="6"><em>No se ha agregado por factura</em></td>
                    </tr>
                `);
            }else{
                $('#ingresados').empty();
                resp[0].forEach(item => {
                    let fecha = item.created_at;
                    let pu = new Intl.NumberFormat("de-DE").format(item.pu);
                    cantidad_ingresada += item.cantidad;
                    $('#ingresados').append(`
                            <tr>
                                <td scope="row">`+item.fecha+`</td>
                                <td>`+item.cantidad+`</td>
                                <td>`+item.factura_numero+`</td>
                                <td>$`+pu+`</td>
                                <td>`+item.empresa_nombre_corto+`</td>
                                <td style="display: flex;"><button class="btn btn-outline-danger btn-sm" onclick="modificar_traspaso_repuesto(`+item.id_repuesto+`,`+item.id_solicitud+`,`+item.cantidad+`,`+pu+`)"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="eliminar_ingreso(`+item.id+`,`+item.id_solicitud+`)"><i class="fas fa-times"></i></button>
                            </tr>
                    `);
                });
            }

            // Agregar totales (facturas + modificaciones) SIEMPRE, independiente de si hay o no facturas
            let total_final = cantidad_ingresada + total_modificaciones;
            let signo_modificaciones = total_modificaciones >= 0 ? '+' : '';
            let color_modificaciones = total_modificaciones >= 0 ? 'text-success' : 'text-danger';

            // Solo mostrar total de facturas si hay facturas
            if(cantidad_ingresada > 0){
                $('#ingresados').append(`
                <tr>
                        <td scope="row"><strong>Total Facturas</strong></td>
                        <td><strong>`+cantidad_ingresada+`</strong></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                `);
            }

            // Siempre mostrar modificaciones si existen
            if(modificaciones.length > 0){
                $('#ingresados').append(`
                <tr class="`+color_modificaciones+`">
                        <td scope="row"><strong>Modificaciones (`+modificaciones.length+`)</strong></td>
                        <td><strong>`+signo_modificaciones+total_modificaciones+`</strong></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                `);
            }

            // Mostrar total final siempre
            $('#ingresados').append(`
                <tr class="table-info">
                        <td scope="row"><strong>TOTAL INGRESADO</strong></td>
                        <td><strong>`+total_final+`</strong></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
            `);

            if(resp[1].length == 0){
                $('#vendidos').empty();
                $('#vendidos').append(`
                    <p>No se han vendido productos </p>
                `);
            }else{
                $('#vendidos').empty();
                resp[1].forEach(element => {
                    let clase = '';
                    if(element.estado == 2){
                        clase = 'table-danger';
                    }
                    // if(element.estado == 1){
                    //     total_vendido += element.cantidad;
                    // }
                      total_vendido += element.cantidad;

                    let pv = new Intl.NumberFormat("de-DE").format(element.precio_venta);
                    $('#vendidos').append(`
                    <tr class='`+clase+`'>
                        <td class='text-center'>`+element.tipo_doc+`</td>
                        <td class='text-center'>`+element.num_doc+`</td>
                        <td class='text-center'>`+element.cantidad+`</td>
                        <td class='text-center'>$`+pv+`</td>
                        <td class='text-center'>`+element.fecha_emision+`</td>
                        <td class='text-center'>`+element.name+`</td>
                        <td class='text-center'>`+element.nombre_cliente+`</td>
                        <td class='text-center'> `+element.local_nombre+` </td>
                    </tr>
                    `);
                });


            }
            if(traspasos.length == 0){
                $('#traspasos').empty();
                $('#traspasos').append(`
                    <p>No se han traspasado productos </p>
                `);
            }else{
                $('#traspasos').empty();
                traspasos.forEach(element => {
                    let locaciones;
                    let estado;
                    if(element.locaciones == 1){
                        locaciones = "Bodega a Tienda";
                    }else if(element.locaciones == 2){
                        locaciones = "Bodega a Casa Matríz";
                    }else{
                        locaciones = "Casa Matríz a Tienda";
                    }

                    if(element.estado == 1){
                        estado = 'ACEPTADO';
                    }else if(element.estado == 0){
                        estado = 'RECHAZADO';
                    }else{
                        estado = 'ESPERANDO';
                    }
                    $('#traspasos').append(`
                    <tr>
                        <td>`+element.cantidad+`</td>
                        <td>`+element.fecha_emision+`</td>
                        <td> `+locaciones+` </td>
                        <td> `+estado+` </td>
                        <td>`+element.name+` </td>
                    </tr>
                    `);
                });


            }
            $('#info_repuesto').empty();
            $('#info_repuesto').css('padding-top','10px');
            // dar formato de numero al precio de venta
            let precio_venta = new Intl.NumberFormat("de-DE").format(resp[2].precio_venta);
            // dar formato de numero al precio antiguo
            let precio_antiguo = new Intl.NumberFormat("de-DE").format(resp[2].precio_antiguo);
            let base_url_fotos = "{{ asset('storage') }}";
            let placeholder_img = "https://placehold.co/100x100";

            let foto = placeholder_img;

            if (Array.isArray(fotos) && fotos.length > 0 && fotos[0].urlfoto && fotos[0].urlfoto !== '') {
                foto = `${base_url_fotos}/${fotos[0].urlfoto}`;
            }
            $('#info_repuesto').append(`
                <tr>
                        <td><img src="`+foto+`" alt="image" style="width: 160px;"></td>
                        <td>`+resp[2].descripcion+`</td>
                        <td> `+total_stock+` </td>
                        <td> `+total_vendido+` </td>
                        <td><a id="total_devoluciones_display" href="javascript:void(0)" style="color: red;" onclick="ver_devoluciones(`+resp[2].id+`)"> -`+total_devoluciones+` </a></td>

                    </tr>
            `);
            $('#precio_rep').val('$'+precio_venta);

            // Mostrar y llenar la sección de detalle del repuesto
            //$('#seccion_detalle_repuesto').show();
            $('#codigo_ips_detalle').val(resp[2].codigo_interno || '');
            $('#stock_detalle').val(total_stock || 0);
            $('#modelo_detalle').val(resp[2].modelo_vehiculo || '');
            $('#motor_detalle').val(resp[2].motor || '');
            $('#descripcion_detalle').val(resp[2].descripcion || '');
            $('#precio_venta_detalle').val('$' + precio_venta);
            $('#cod_fabricante_detalle').val(resp[2].codigo_barras || '');
            $('#anio_inicio_detalle').val(resp[2].anio_inicio || '');
            $('#anio_termino_detalle').val(resp[2].anio_termino || '');
            $('#cod_mexico_detalle').val(resp[2].codigo_mexico || '');
            $('#fob_detalle').val(resp[2].fob || '');
            $('#cilindrada_detalle').val(resp[2].cilindrada || '');

            // Formatear precio de compra si existe
            let precio_compra_formateado = resp[2].precio_compra ? new Intl.NumberFormat("de-DE").format(resp[2].precio_compra) : '0';
            $('#precio_compra_detalle').val('$' + precio_compra_formateado);

            $('#cif_detalle').val(resp[2].cif || '');
            $('#ft_detalle').val(resp[2].ft || '');
            $('#precio_prorrateado_detalle').val(resp[2].precio_prorrateado || '0');
            $('#cu_detalle').val(resp[2].cu || '');
            $('#tc_aduana_detalle').val(resp[2].tc_aduana || '');
            $('#factor_peso_detalle').val(resp[2].factor_peso || '');
            $('#factor_dolar_detalle').val(resp[2].factor_dolar || '');
            $('#precio_minimo_detalle').val(resp[2].precio_minimo || '0');
            $('#modificaciones').empty();
            if(modificaciones.length == 0){
                $('#modificaciones').append(`
                    <p>No se han modificado el stock </p>
                `);
            }else{
                modificaciones.forEach(element => {
                    let stock_anterior = element.stock_anterior;
                    let stock_ingresado = element.stock;
                    let usuario = element.name;
                    let fecha = element.fecha_modificacion;
                    let codigo = element.id_repuesto;
                    let observacion = element.observacion ? element.observacion : '-';
                    $('#modificaciones').append(`
                    <tr>
                        <td>`+fecha+`</td>
                        <td>`+stock_anterior+`</td>
                        <td>`+stock_ingresado+`</td>
                        <td>`+observacion+`</td>
                        <td>`+usuario+`</td>
                    </tr>
                    `);
                });
            }
            },
            error: function(err){
                console.log(err.responseText);
                Vue.swal({

                title:'Error!',

                text:err.responseText,

                icon:'error'

                });
            }
        });
    }

    function buscar_solicitud(){
        let num_solicitud = $('#num_solicitud').val();
        if(num_solicitud.trim() == 0 || num_solicitud == ''){
            Vue.swal({
                icon:'error',
                title:'Error',
                text:'Debe ingresar un número de solicitud'
            });
        return false;
        }

        let url = '/guiadespacho/buscarsolicitud/'+num_solicitud;

        $.ajax({
            type:'get',
            url: url,
            beforeSend: function(){
                Vue.swal({
                    icon:'info',
                    title:'Cargando ...'
                });
            },
            success: function(html){
                Vue.swal.close();
                if(html == 'error'){
                    Vue.swal({
                        icon:'error',
                        text:'No existen solicitudes o ya fue procesada'
                    });
                return false;
                }


                $('#panel_resultado_solicitudes').empty();
                $('#panel_resultado_solicitudes').append(html);
            },
            error: function(error){
                Vue.swal({
                    icon:'error',
                    title:'Error',
                    text:error.responseText
                });
            }
        })

    }

    function ver_devoluciones(id){
        let url = "{{ ROUTE('inventario.dame_devoluciones') }}";
        let data = {
            id_repuesto: id,
            _token: $('meta[name="csrf-token"]').attr('content')
        }

        $.ajax({
            type:'post',
            data: data,
            url: url,
            success: function(resp){
                console.log(resp);
                // abrir modal
                // $('#modalDevolucionMercaderia').modal('show');
                let devoluciones = resp;
                $('#info_devoluciones').empty();
                var totalCantidad = 0;
                devoluciones.forEach(element => {
                    let esMerma = element.local_nombre === 'Mermas';
                    if(!esMerma){
                        totalCantidad += parseFloat(element.cantidad);
                    }
                    let filaEstilo = esMerma ? 'style="color:#aaa; font-style:italic;"' : '';
                    let badge = esMerma ? `<span class="badge bg-warning text-dark ms-1" style="font-size:0.7em;">Merma</span>` : '';
                    $('#info_devoluciones').append(`
                    <tr ${filaEstilo}>
                        <td>${element.fecha_emision.split(' ')[0]}</td>
                        <td>${element.num_nc}${badge}</td>
                        <td>${element.cantidad}</td>
                        <td>${element.local_nombre}</td>
                    </tr>
                `);
                });

                // Agregar la última fila con el total (solo devoluciones a bodega, excluye mermas)
                $('#info_devoluciones').append(`
                    <tr style="font-weight: bold; background: #eee;" >
                        <td colspan="2" class="text-end">Total devuelto a bodega:</td>
                        <td>${totalCantidad}</td>
                        <td></td>
                    </tr>
                `);

                // Actualizar el total mostrado en el resumen del repuesto
                $('#total_devoluciones_display').text('-' + totalCantidad);
            },
            error: function(error){
                console.log(error.responseText);
            }
        })
    }

    function aceptar_traspaso_repuesto(repuesto_id, solicitud_id,cantidad){
        let params = {repuesto_id: repuesto_id, solicitud_id: solicitud_id,cantidad: cantidad};
        let url = '/guiadespacho/aceptar_traspaso_repuesto';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'post',
            url: url,
            data: params,
            success: function(html){
                $('#panel_resultado_solicitudes').empty();
                $('#panel_resultado_solicitudes').append(html);
            },
            error: function(error){
                Vue.swal({
                    icon:'error',
                    text: error.responseText
                });
            }
        })
    }

    function rechazar_traspaso_repuesto(id, solicitud_id){
        let params = {id: id, solicitud_id: solicitud_id};
        let url = '/guiadespacho/rechazar_traspaso_repuesto';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'post',
            url: url,
            data: params,
            success: function(html){
                console.log(html);
                $('#panel_resultado_solicitudes').empty();
                $('#panel_resultado_solicitudes').append(html);
            },
            error: function(error){
                Vue.swal({
                    icon:'error',
                    text: error.responseText
                });
            }
        })
    }

    function buscar_info_fechas(){
        let id_repuesto = $('#id_repuesto').val();
        let fecha_inicio = $('#fecha_inicio_busqueda').val();
        let fecha_fin = $('#fecha_fin_busqueda').val();

        if(fecha_inicio == ''){
            Vue.swal({
                icon:'info',
                title:'info',
                text:'Debe ingresar fecha de inicio'
            });
            return false;
        }

        if(fecha_fin == ''){
            Vue.swal({
                icon:'info',
                title:'info',
                text:'Debe ingresar fecha de fin'
            });
            return false;
        }

        let data = {
            id_repuesto: id_repuesto,
            fecha_inicio: fecha_inicio,
            fecha_fin : fecha_fin,
            _token: $('meta[name="csrf-token"]').attr('content')
        }

        console.log(data);
        let url = "{{ ROUTE('inventario.dame_info_repuesto_fechas') }}";
        $.ajax({
            type:'post',
            data: data,
            url: url,
            beforeSend: function(){
                Vue.swal({
                    icon:'info',
                    title:'info',
                    text:'Buscando ...'
                });
            },
            success: function(resp){
                console.log(resp);

                Vue.swal.close();
                console.log(resp);
                let total_vendido = 0;
                let traspasos = resp[3];
                let modificaciones = resp[4];
                let devoluciones = resp[5];
                let total_devoluciones = resp[6];

                if(resp[0] === "error"){
                    Vue.swal({
                        title:'Error!',
                        text:resp[1],
                        icon:'error'
                    });
                    // limpiar los campos
                    $('#info_repuesto').empty();
                    $('#ingresados').empty();
                    $('#vendidos').empty();
                    $('#traspasos').empty();
                    return false;
                }

                let total_modificaciones = 0;
                // Calcular el total de modificaciones de stock (diferencia entre stock nuevo y anterior)
                modificaciones.forEach(mod => {
                    let diferencia = parseFloat(mod.stock) - parseFloat(mod.stock_anterior);
                    total_modificaciones += diferencia;
                });
                let total_stock = resp[2].stock_actual;
                $('#id_repuesto').val(resp[2].id);
                let cantidad_ingresada = 0;
                $('#ingresados').empty();
                if(resp[0].length == 0){
                    $('#ingresados').append(`
                        <tr><td colspan="6"><em>No se ha agregado por factura</em></td></tr>
                    `);
                }else{
                    resp[0].forEach(item => {
                        let pu = new Intl.NumberFormat("de-DE").format(item.pu);
                        cantidad_ingresada += item.cantidad;
                        $('#ingresados').append(`
                        <tr>
                            <td scope="row">`+item.fecha+`</td>
                            <td>`+item.cantidad+`</td>
                            <td>`+item.factura_numero+`</td>
                            <td>$`+pu+`</td>
                            <td>`+item.empresa_nombre_corto+`</td>
                            <td></td>
                        </tr>
                  `);
                    });

                    // Total de facturas solo si hay facturas
                    $('#ingresados').append(`
                    <tr>
                            <td scope="row"><strong>Total Facturas</strong></td>
                            <td><strong>`+cantidad_ingresada+`</strong></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    `);
                }

                // Calcular el total final — siempre, independiente de si hay facturas
                let total_final = cantidad_ingresada + total_modificaciones;
                let signo_modificaciones = total_modificaciones >= 0 ? '+' : '';
                let color_modificaciones = total_modificaciones >= 0 ? 'text-success' : 'text-danger';

                // Mostrar modificaciones si existen
                if(modificaciones.length > 0){
                    $('#ingresados').append(`
                    <tr class="`+color_modificaciones+`">
                            <td scope="row"><strong>Modificaciones (`+modificaciones.length+`)</strong></td>
                            <td><strong>`+signo_modificaciones+total_modificaciones+`</strong></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    `);
                }

                // Siempre mostrar total final
                $('#ingresados').append(`
                <tr class="table-info">
                        <td scope="row"><strong>TOTAL INGRESADO</strong></td>
                        <td><strong>`+total_final+`</strong></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                `);
                if(resp[1].length == 0){
                    $('#vendidos').empty();
                    $('#vendidos').append(`
                        <p>No se han vendido productos </p>
                    `);
                }else{
                    $('#vendidos').empty();
                    resp[1].forEach(element => {
                        total_vendido += element.cantidad;
                        let pv = new Intl.NumberFormat("de-DE").format(element.precio_venta);
                        $('#vendidos').append(`
                        <tr>
                            <td class='text-center'>`+element.tipo_doc+`</td>
                            <td class='text-center'>`+element.num_doc+`</td>
                            <td class='text-center'>`+element.cantidad+`</td>
                            <td class='text-center'>$`+pv+`</td>
                            <td class='text-center'>`+element.fecha_emision+`</td>
                            <td class='text-center'>`+element.name+`</td>
                            <td class='text-center'>`+element.nombre_cliente+`</td>
                            <td class='text-center'> `+element.local_nombre+` </td>
                        </tr>
                        `);
                    });


                }
                if(traspasos.length == 0){
                    $('#traspasos').empty();
                    $('#traspasos').append(`
                        <p>No se han traspasado productos </p>
                    `);
                }else{
                    $('#traspasos').empty();
                    traspasos.forEach(element => {
                        let locaciones;
                        let estado;
                        if(element.locaciones == 1){
                            locaciones = "Bodega a Tienda";
                        }else if(element.locaciones == 2){
                            locaciones = "Bodega a Casa Matríz";
                        }else{
                            locaciones = "Casa Matríz a Tienda";
                        }

                        if(element.estado == 1){
                            estado = 'ACEPTADO';
                        }else if(element.estado == 0){
                            estado = 'RECHAZADO';
                        }else{
                            estado = 'ESPERANDO';
                        }
                        $('#traspasos').append(`
                        <tr>
                            <td>`+element.cantidad+`</td>
                            <td>`+element.fecha_emision+`</td>
                            <td> `+locaciones+` </td>
                            <td> `+estado+` </td>
                            <td>`+element.name+` </td>
                        </tr>
                        `);
                    });


                }
                $('#info_repuesto').empty();
                $('#info_repuesto').css('padding-top','10px');
                // dar formato de numero al precio de venta
                let precio_venta = new Intl.NumberFormat("de-DE").format(resp[2].precio_venta);
                // dar formato de numero al precio antiguo
                let precio_antiguo = new Intl.NumberFormat("de-DE").format(resp[2].precio_antiguo);
                let fotos = resp[7];
                let base_url_fotos = "{{ asset('storage') }}";
                let placeholder_img = "https://placehold.co/100x100";
                let foto = placeholder_img;
                if (Array.isArray(fotos) && fotos.length > 0 && fotos[0].urlfoto && fotos[0].urlfoto !== '') {
                    foto = `${base_url_fotos}/${fotos[0].urlfoto}`;
                }
                let stock_periodo = total_final - total_vendido;
                $('#info_repuesto').append(`
                    <tr>
                            <td><img src="`+foto+`" alt="image" style="width: 160px;"></td>
                            <td>`+resp[2].descripcion+`</td>
                            <td><span title="Stock del período: ingresado - vendido"> `+stock_periodo+` </span></td>
                            <td> `+total_vendido+` </td>
                            <td><a id="total_devoluciones_display" href="javascript:void(0)" style="color:red;" onclick="ver_devoluciones(`+resp[2].id+`)"> -`+total_devoluciones+` </a></td>

                        </tr>
                `);
                ver_devoluciones(resp[2].id);
                $('#modificaciones').empty();
                if(modificaciones.length == 0){
                    $('#modificaciones').append(`
                        <p>No se han modificado el stock </p>
                    `);
                }else{
                    modificaciones.forEach(element => {
                        let stock_anterior = element.stock_anterior;
                        let stock_ingresado = element.stock;
                        let usuario = element.name;
                        let fecha = element.fecha_modificacion;
                        let observacion = element.observacion ? element.observacion : '-';
                        $('#modificaciones').append(`
                        <tr>
                            <td>`+fecha+`</td>
                            <td>`+stock_anterior+`</td>
                            <td>`+stock_ingresado+`</td>
                            <td>`+observacion+`</td>
                            <td>`+usuario+`</td>
                        </tr>
                        `);
                    });
                }
            },
            error: function(error){
                console.log(error.responseText);
            }
        })
    }

    function modificar_traspaso_repuesto(id_repuesto, id_solicitud, cantidad = 0, precio = 0){
        // abrir modal
        $('#modalModificarTraspaso').modal('show');
        $('#id_repuesto_modificar').val(id_repuesto);
        $('#id_solicitud_modificar').val(id_solicitud);
        $('#cantidad_actual').val(cantidad);
        $('#precio_compra').val(precio);
        $('#motivo_modificacion').val('');
    }

    function eliminar_ingreso(id, solicitud_id){
        Vue.swal({
            title: '¿Está seguro de eliminar este ingreso?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminar_ingreso_confirmar(id, solicitud_id);
            }
        })
    }

    function eliminar_ingreso_confirmar(id, solicitud_id){
        let params = {id: id, solicitud_id: solicitud_id};
        let url = '/guiadespacho/eliminar_ingreso';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'post',
            url: url,
            data: params,
            success: function(resp){
                console.log(resp);
                if(resp.estado == 'OK'){
                    Vue.swal({
                        icon:'success',
                        text: resp.mensaje
                    }).then(() => {
                        dame_info();
                    });
                }else{
                    Vue.swal({
                        icon:'error',
                        text: resp.mensaje
                    });
                }
            },
            error: function(error){
                Vue.swal({
                    icon:'error',
                    text: error.responseText
                });
            }
        })
    }

</script>
@endsection
@section('style')
<style>
.busqueda_principal{
    margin-top: 30px;
    background: #f8f9fa;
    border: 2px solid #6c757d;
    color: #212529;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(108, 117, 125, 0.15);
}

.busqueda_resultado{
    margin-top: 30px;
    min-height: 200px;
    padding: 0;
    border-radius: 15px;
}

.logo{
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    background: white;
    padding: 10px;
    border: 2px solid #6c757d;
}

.form-check-label {
    color: #212529;
    font-weight: 500;
}

.form-check-input:checked {
    background-color: #6c757d;
    border-color: #6c757d;
}

.card {
    border: 1px solid #dee2e6;
    border-radius: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background: white;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.card-header {
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 15px 15px 0 0 !important;
    padding: 20px;
}

.titulazo {
    color: #212529;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 3px solid #6c757d;
    display: inline-block;
}

.table {
    border-radius: 10px;
    overflow: hidden;
    background: white;
}

.table thead th {
    background: #e9ecef;
    border: 1px solid #dee2e6;
    font-weight: 600;
    color: #212529;
    text-transform: uppercase;
    font-size: 0.85em;
    letter-spacing: 0.5px;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
    transition: all 0.2s ease;
}

.table tbody td {
    color: #212529;
    border: 1px solid #dee2e6;
}

.btn-search-panel {
    background: #6c757d;
    border: none;
    color: white;
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.btn-search-panel:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    color: white;
}

.form-control {
    border-radius: 8px;
    border: 2px solid #dee2e6;
    background: white;
    color: #212529;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-control:focus {
    border-color: #6c757d;
    box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
    background: white;
    color: #212529;
}

.price-display {
    background: #e9ecef;
    border: 2px solid #6c757d;
    color: #212529;
    padding: 25px;
    border-radius: 15px;
    text-align: center;
}

.search-filters {
    background: rgba(255,255,255,0.8);
    border: 1px solid #dee2e6;
    padding: 20px;
    border-radius: 10px;
}

#panel_busqueda{
    display: block;
    width: 100%;
    margin: 20px 0px 20px 0px;
    border: 1px solid #dee2e6;
    padding: 20px;
    border-radius: 15px;
    background: white;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.stats-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    border-left: 4px solid #6c757d;
}

.floating {
    color: #212529 !important;
}

.floating:focus + label,
.floating:not(:placeholder-shown) + label {
    color: #6c757d !important;
}

.form-group.floating label {
    color: #6c757d;
}

/* Asegurar que el texto en inputs sea siempre visible */
input[type="text"], input[type="date"] {
    background-color: white !important;
    color: #212529 !important;
}

input[type="text"]:focus, input[type="date"]:focus {
    background-color: white !important;
    color: #212529 !important;
}

/* Mejorar contraste de títulos */
h3, h5, h6 {
    color: #212529 !important;
}

.search-filters h6 {
    color: #212529 !important;
}

/* Fix para labels flotantes con contenido pre-llenado */
.form-group.floating {
    position: relative;
    margin-bottom: 1.5rem;
}

.form-group.floating input.floating {
    padding-top: 1.5rem;
    padding-bottom: 0.5rem;
}

.form-group.floating label.floating {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    transition: all 0.2s ease;
    pointer-events: none;
    color: #6c757d;
    font-size: 1rem;
    background: white;
    padding: 0 0.25rem;
}

/* Cuando el input tiene valor o está en foco */
.form-group.floating input.floating:focus + label.floating,
.form-group.floating input.floating:not(:placeholder-shown) + label.floating,
.form-group.floating input.floating[readonly] + label.floating {
    top: -0.5rem;
    left: 0.75rem;
    font-size: 0.75rem;
    color: #6c757d;
    background: white;
    z-index: 1;
}

/* Para inputs readonly específicamente */
.form-group.floating input[readonly] {
    background-color: #f8f9fa !important;
    cursor: default;
}
</style>

@endsection

@section('contenido_ingresa_datos')
<div class="container-fluid">
    <!-- Panel de Búsqueda Mejorado -->
    <div class="busqueda_principal" style="display: none;">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <img src="{{asset('storage/imagenes/ips_logo.png')}}" alt="Logo IPS" class="logo mb-3">
            </div>
            <div class="col-md-10">
                <h3 class="mb-4"><i class="fas fa-search me-2"></i>Búsqueda de Repuestos</h3>
                <div class="row">
                    <div class="col-md-4">
                        <div class="search-filters">
                            <h6 class="mb-3"><i class="fas fa-filter me-2"></i>Filtros de Búsqueda</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1" value="cod_int" checked>
                                <label class="form-check-label" for="flexRadioDefault1">
                                    <i class="fas fa-barcode me-2"></i>Código interno
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2" value="oem" disabled>
                                <label class="form-check-label" for="flexRadioDefault2">
                                    <i class="fas fa-cog me-2"></i>OEM
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault4" value="cod_prov" disabled>
                                <label class="form-check-label" for="flexRadioDefault4">
                                    <i class="fas fa-truck me-2"></i>Código de proveedor
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group floating mb-3">
                                    <input type="text" class="form-control floating" name="codigo_repuesto" id="codigo_repuesto" onkeyup="enter_press(event)">
                                    <label for="codigo_repuesto" class="floating"><i class="fas fa-keyboard me-2"></i>Ingrese el código del repuesto</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-success btn-search-panel w-100" onclick="dame_info()">
                                    <i class="fas fa-search me-2"></i>Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Detalle del Repuesto -->
    <div class="row my-4" id="seccion_detalle_repuesto" >
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-database me-2"></i>Detalle General</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Primera columna -->
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" onkeypress="enter_press(event)" id="codigo_ips_detalle" placeholder=" ">
                                <label for="codigo_ips_detalle" class="floating" >Código IPS</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="stock_detalle" readonly placeholder=" ">
                                <label for="stock_detalle" class="floating">Stock</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="modelo_detalle" readonly placeholder=" ">
                                <label for="modelo_detalle" class="floating">Modelo</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="motor_detalle" readonly placeholder=" ">
                                <label for="motor_detalle" class="floating">Motor</label>
                            </div>
                        </div>

                        <!-- Segunda columna -->
                        <div class="col-md-6">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="descripcion_detalle" readonly placeholder=" ">
                                <label for="descripcion_detalle" class="floating">Descripción</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="precio_venta_detalle" readonly placeholder=" ">
                                <label for="precio_venta_detalle" class="floating">Precio Venta</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="cod_fabricante_detalle" readonly placeholder=" ">
                                <label for="cod_fabricante_detalle" class="floating">Cod Fabricante</label>
                            </div>
                        </div>

                        <!-- Tercera columna -->
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="anio_inicio_detalle" readonly placeholder=" ">
                                <label for="anio_inicio_detalle" class="floating">Año inicio</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="anio_termino_detalle" readonly placeholder=" ">
                                <label for="anio_termino_detalle" class="floating">Año Termino</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="cod_mexico_detalle" readonly placeholder=" ">
                                <label for="cod_mexico_detalle" class="floating">Cod México</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="fob_detalle" readonly placeholder=" ">
                                <label for="fob_detalle" class="floating">FOB</label>
                            </div>
                        </div>

                        <!-- Cuarta columna -->
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="cilindrada_detalle" readonly placeholder=" ">
                                <label for="cilindrada_detalle" class="floating">Cilindrada</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="precio_compra_detalle" readonly placeholder=" ">
                                <label for="precio_compra_detalle" class="floating">Precio Compra</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="cif_detalle" readonly placeholder=" ">
                                <label for="cif_detalle" class="floating">CIF</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="ft_detalle" readonly placeholder=" ">
                                <label for="ft_detalle" class="floating">FT</label>
                            </div>
                        </div>

                        <!-- Quinta columna -->
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="precio_prorrateado_detalle" readonly placeholder=" ">
                                <label for="precio_prorrateado_detalle" class="floating">Precio Prorrateado</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="cu_detalle" readonly placeholder=" ">
                                <label for="cu_detalle" class="floating">CU</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="tc_aduana_detalle" readonly placeholder=" ">
                                <label for="tc_aduana_detalle" class="floating">T/C Aduana</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="factor_peso_detalle" readonly placeholder=" ">
                                <label for="factor_peso_detalle" class="floating">Factor Peso</label>
                            </div>
                        </div>

                        <!-- Sexta columna -->
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="factor_dolar_detalle" readonly placeholder=" ">
                                <label for="factor_dolar_detalle" class="floating">Factor Dólar</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group floating">
                                <input type="text" class="form-control floating" id="precio_minimo_detalle" readonly placeholder=" ">
                                <label for="precio_minimo_detalle" class="floating">Precio Mínimo</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Resultados Mejorado -->
    <div class="busqueda_resultado letra_pequeña">
        <div class="row g-4">
            <!-- Detalle del Repuesto -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Detalle del Repuesto</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tabla_principal_repuesto">
                                <thead>
                                    <tr>
                                        <th scope="col"><i class="fas fa-image me-1"></i>Imagen</th>
                                        <th scope="col"><i class="fas fa-tag me-1"></i>Descripción</th>
                                        <th scope="col"><i class="fas fa-boxes me-1"></i>Stock Total</th>
                                        <th scope="col"><i class="fas fa-shopping-cart me-1"></i>Total vendido</th>
                                        <th scope="col"><i class="fas fa-undo me-1"></i>Total devuelto</th>
                                    </tr>
                                </thead>
                                <tbody id="info_repuesto"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Devoluciones -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-exchange-alt me-2"></i>Devoluciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-calendar me-1"></i>Fecha</th>
                                        <th><i class="fas fa-receipt me-1"></i>N° de crédito</th>
                                        <th><i class="fas fa-sort-numeric-down me-1"></i>Cantidad</th>
                                        <th><i class="fas fa-map-marker-alt me-1"></i>Destino</th>
                                    </tr>
                                </thead>
                                <tbody id="info_devoluciones"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de Filtros y Precio -->
            <div class="col-md-2">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="fas fa-filter me-2"></i>Filtros</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group floating mb-3">
                            <input type="date" name="fecha_inicio_busqueda" id="fecha_inicio_busqueda" class="form-control floating">
                            <label for="fecha_inicio_busqueda" class="floating">Fecha inicio</label>
                        </div>
                        <div class="form-group floating mb-3">
                            <input type="date" name="fecha_fin_busqueda" id="fecha_fin_busqueda" class="form-control floating">
                            <label for="fecha_fin_busqueda" class="floating">Fecha fin</label>
                        </div>
                        <button class="btn btn-outline-success btn-sm w-100 mb-4" onclick="buscar_info_fechas()">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                        <button class="btn btn-outline-primary btn-sm w-100 mb-4" onclick="dame_info()">
                            <i class="fas fa-search me-1"></i> Volver a cargar todo
                        </button>
                        <div class="price-display">
                            <div class="form-group floating">
                                <input type="text" name="precio_rep" id="precio_rep" onkeyup="enter_press(event)" class="form-control floating">
                                <label for="precio_rep" class="floating"><i class="fas fa-dollar-sign me-1"></i>Precio venta</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-5">

        <!-- Sección de Tablas de Datos -->
        <div class="row g-4">
            <!-- Ingresados y Modificaciones -->
            <div class="col-md-5">
                <!-- Ingresados -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-arrow-down me-2"></i>Ingresados</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive tabla-scroll-y-300">
                            <table class="table table-hover" style="font-size: 12px;">
                                <thead>
                                    <tr>
                                        <th scope="col"><i class="fas fa-calendar me-1"></i>Fecha de ingreso</th>
                                        <th scope="col"><i class="fas fa-sort-numeric-down me-1"></i>Cantidad</th>
                                        <th scope="col"><i class="fas fa-file-invoice me-1"></i>N° Factura</th>
                                        <th scope="col"><i class="fas fa-dollar-sign me-1"></i>Precio Compra</th>
                                        <th scope="col"><i class="fas fa-building me-1"></i>Proveedor</th>
                                        <th scope="col"><i class="fas fa-cogs me-1"></i>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="ingresados"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modificaciones -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i>Modificaciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col"><i class="fas fa-calendar me-1"></i>Fecha</th>
                                        <th scope="col"><i class="fas fa-history me-1"></i>Stock anterior</th>
                                        <th scope="col"><i class="fas fa-plus me-1"></i>Stock ingresado</th>
                                        <th scope="col"><i class="fas fa-sticky-note me-1"></i>Observación</th>
                                        <th scope="col"><i class="fas fa-user me-1"></i>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody id="modificaciones"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vendidos -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-arrow-up me-2"></i>Vendidos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive tabla-scroll-y-300">
                            <table class="table table-hover text-center" style="font-size: 12px;">
                                <thead>
                                    <tr>
                                        <th scope="col"><i class="fas fa-file me-1"></i>Doc</th>
                                        <th scope="col"><i class="fas fa-hashtag me-1"></i>N°</th>
                                        <th scope="col"><i class="fas fa-sort-numeric-down me-1"></i>Cantidad</th>
                                        <th scope="col"><i class="fas fa-dollar-sign me-1"></i>Precio venta</th>
                                        <th scope="col"><i class="fas fa-calendar me-1"></i>Fecha Emisión</th>
                                        <th scope="col"><i class="fas fa-user me-1"></i>Usuario</th>
                                        <th scope="col"><i class="fas fa-users me-1"></i>Cliente</th>
                                        <th scope="col"><i class="fas fa-map-marker-alt me-1"></i>Origen</th>
                                    </tr>
                                </thead>
                                <tbody id="vendidos"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalModificarTraspaso" tabindex="-1" aria-labelledby="modalModificarTraspasoLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalModificarTraspasoLabel">Modificar Traspaso</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="formModificarTraspaso">
            <div class="mb-3">
                <label for="cantidad_actual" class="form-label">Cantidad Actual</label>
                <input type="number" class="form-control" id="cantidad_actual" name="cantidad_actual" disabled>
            </div>
            <div class="mb-3">
                <label for="precio_compra" class="form-label">Precio Compra</label>
                <input type="text" class="form-control" id="precio_compra" name="precio_compra" disabled>
            </div>
            <div class="mb-3">
                <label for="cantidad_modificar" class="form-label">Cantidad a modificar</label>
                <input type="number" class="form-control" id="cantidad_modificar" name="cantidad_modificar" required>
            </div>
            <div class="mb-3">
                <label for="motivo_modificacion" class="form-label">Motivo de la modificación</label>
                <textarea class="form-control" id="motivo_modificacion" name="motivo_modificacion" rows="3" required></textarea>
            </div>
            <input type="hidden" name="id_repuesto_modificar" id="id_repuesto_modificar">
            <input type="hidden" name="id_solicitud_modificar" id="id_solicitud_modificar">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
          </form>
        </div>
      </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="modalDevolucionMercaderia" tabindex="-1" aria-labelledby="modalDevolucionMercaderiaLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalDevolucionMercaderiaLabel">Devoluciones</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" >
          <table class="table">
          <thead>
            <th>Fecha emisión</th>
            <th>N° de credito</th>
            <th>Cantidad</th>
            <th>Destino</th>
          </thead>
          <tbody id="info_devoluciones">
          </tbody>
          </table>
        </div>
        <div class="modal-footer">
        </div>
      </div>
    </div>
</div>

<input type="hidden" name="id_repuesto" id="id_repuesto">

@endsection
