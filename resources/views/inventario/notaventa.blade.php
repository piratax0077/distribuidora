@extends('plantillas.app')

@section('javascript')
    <script>
        var TrackID = 0;
        var cliente = [];
        var ref1 = [];
        var ref2 = [];
        var ref3 = [];
        var num_doc_dte = 0;
        var tipo_dte_dte = '';
        var correo_dte = '';
        setInterval(() => {
            console.log('revisando');
            let url = '/revisar_carrito';
            $.ajax({
                type: 'get',
                url: url,
                beforeSend: function() {

                },
                success: function(resp) {
                    if (resp > 0) {
                        $('#numero_notas').empty();
                        $('#numero_notas').append(`
                        <a href='javascript:dame_carrito()'>
                            ` + resp + `
                        </a>
                        `);
                    } else {
                        $('#numero_notas').empty();
                        $('#numero_notas').append(resp);
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            })
        }, 15000);

        window.onload = function() {
            dame_carrito();
            dame_formas_pago();
            cargar_documentos_referencia();
        }

        function estado_carga_notas(mostrar, mensaje) {
            $('#texto_carga_notas').text(mensaje || 'Cargando...');
            $('#carga_notas_ventas').toggleClass('d-none', !mostrar);
            $('#btn_agregar_repuesto_express_nota').prop('disabled', mostrar);
        }

        function mensaje_estado_notas(tipo, mensaje) {
            let iconos = {
                success: 'fa-check-circle',
                danger: 'fa-exclamation-circle',
                info: 'fa-info-circle'
            };
            $('#estado_notas_ventas')
                .removeClass('d-none alert-success alert-danger alert-info')
                .addClass('alert-' + tipo)
                .html('<i class="fas ' + (iconos[tipo] || iconos.info) + '"></i> ' + mensaje);
        }

        function ocultar_estado_notas() {
            $('#estado_notas_ventas').addClass('d-none').empty();
        }

        function dame_carrito() {
            estado_carga_notas(true, 'Cargando notas de venta pendientes...');
            ocultar_estado_notas();
            $('#btn_agregar_repuesto_express_nota').addClass('d-none');
            fetch("{{ url('ventas/dame_carrito') }}")
                .then(response => {
                    if (!response.ok) {
                        throw new Error('No fue posible cargar las notas de venta');
                    }
                    return response.text();
                })
                .then(data => {
                    $('#notas_ventas').html(data);
                    mensaje_estado_notas('info', 'Seleccione una nota de venta para revisar su detalle.');
                    $('#info_nota_venta').html('');
                    $('#info_titulo_nota_venta').html('');
                    borrar_cliente_vista();
                    borrar_venvedor_vista();
                    desactivar_formas_pago();
                })
                .catch(error => {
                    mensaje_estado_notas('danger', error.message + '. Intente nuevamente.');
                    $('#notas_ventas').html(
                        '<div class="estado-vacio-notas">' +
                        '<i class="fas fa-exclamation-triangle"></i>' +
                        '<p>No se pudo mostrar el listado.</p>' +
                        '<button class="btn btn-outline-danger btn-sm" onclick="dame_carrito()">Reintentar</button>' +
                        '</div>'
                    );
                })
                .finally(() => {
                    estado_carga_notas(false);
                });
        }

        function desactivar_formas_pago(){
            for(let i = 1; i <= 10; i++) {
                document.getElementById(`formita-${i}`).disabled = true;
                document.getElementById(`monto-${i}`).disabled = true;
                document.getElementById(`referencia-${i}`).disabled = true;

                // todos los que esten desactivados dejar su valor en 0
                document.getElementById(`monto-${i}`).value = 0;

                // sacar todos los checked de los radio buttons
                document.getElementById(`formita-${i}`).checked = false;
            }
        }

        function borrar_cliente_vista() {
            $('#cliente_rut').html('SIN INFORMACION');
            $('#cliente_nombres').html('SIN INFORMACION');
            $('#id_cliente').val(0);
            $('#cliente_direccion select').empty();
            $('#cliente_giro select').empty();
        }

        function borrar_venvedor_vista(){
            $('#info_vendedor').empty();
        }

        function borrar_carrito_transferido(cliente_id) {
            Vue.swal({
                title: '¿Está seguro de borrar el carrito?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, borrar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = '/ventas/borrar_carrito_cliente/' + cliente_id;
                    $.ajax({
                        type: 'get',
                        url: url,
                        beforeSend: function() {
                            console.log();
                        },
                        success: function(resp) {
                            console.log(resp);
                            if (resp.mensaje === 'ok') {
                                dame_carrito();
                                $('#btn_procesar_').removeClass('d-block');
                                $('#btn_procesar_').addClass('d-none');
                            }
                        },
                        error: function(err) {
                            Vue.swal({
                                icon: 'error',
                                title: 'Error',
                                text: err.responseText
                            });
                        }
                    });
                }
            });
        }

        function abrir_carrito_transferido(cliente_id) {
            console.log(cliente_id);
            //Se tienen que limpiar los descuentos al inicio

            let url = '/ventas/dame_carrito_transferido_cliente/' + cliente_id;
            $('#cliente_carrito_transferido').val(cliente_id);
            $.ajax({
                type: 'get',
                url: url,
                beforeSend: function(){
                    estado_carga_notas(true, 'Abriendo nota de venta...');
                    ocultar_estado_notas();
                },
                success: function(resp) {
                    console.log(resp);
                    let html = resp.vista;
                    let cliente = resp.cliente;
                    let direcciones = resp.direcciones;
                    let giros = resp.giros;
                    let formas_pago = resp.formas_pago;
                    let carrito_transferido = resp.carrito_transferido;
                    let vendedor = carrito_transferido[0].vendedor;
                    let titulo = carrito_transferido[0].titulo;
                    console.log(vendedor);
                    console.log(carrito_transferido);
                    $('#info_vendedor').html(vendedor+" - Vendedor");
                    $('#info_titulo_nota_venta').html(titulo);
                    $('#info_nota_venta').html(`
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title">N° Nota de Venta `+carrito_transferido[0].numero_carrito+` <br>(`+carrito_transferido[0].fecha_emision+`) </h6>
                            </div>
                        </div>
                    `);
                    console.log(carrito_transferido[0]);
                    if(carrito_transferido[0].image_path){
                        $('#imagenes_pago img').attr('src', '{{ asset('') }}' + carrito_transferido[0].image_path);
                    }else{
                        $('#imagenes_pago img').attr('src', '{{ asset("imagenes/comprobantes_pago/not-found.png") }}');
                    }

                    $('#cliente_rut').html(cliente.rut);
                    $('#carrito_id_').val(carrito_transferido[0].carrito_id);
                    $('#cliente_nombres').html(cliente.nombres+' '+cliente.apellidos);
                    $('#id_cliente').val(cliente.id);
                    $('#cliente_id').val(cliente.id);
                    $('#cliente_direccion select').empty();
                    if(giros.length == 0){
                        $('#cliente_giro select').empty();
                        $('#cliente_giro select').append(`
                            <option value="0">No aplica</option>
                        `);
                    }else{
                        giros.forEach(giro => {
                            $('#cliente_giro select').append(`
                                <option value="` + giro.id + `">` + giro.giro + `</option>
                            `);
                        });
                    }


                    $('#cliente_direccion select').empty();
                    direcciones.forEach(direccion => {
                        console.log(direccion);
                        $('#cliente_direccion select').append(`
                            <option value="` + direccion.id + `">` + direccion.direccion + `,# `+direccion.numero+`, `+direccion.nombre_ciudad+`</option>
                        `);
                    });

                    console.log(carrito_transferido[0].id_direccion);
                    if(carrito_transferido[0].id_direccion){
                        $('#cliente_direccion select').val(carrito_transferido[0].id_direccion);
                    }

                    //aplicar_descuentos_carrito_transferido(0,id_cliente);
                    $('#btn_procesar_').removeClass('d-none');
                    $('#btn_procesar_').addClass('d-block');
                    //console.log(html);
                    $('#notas_ventas').empty();
                    $('#notas_ventas').append(html);
                    $('#btn_agregar_repuesto_express_nota').removeClass('d-none');
                    actualizar_limite_repuestos_nota();
                    mensaje_estado_notas(
                        'success',
                        'Nota de venta N° ' + carrito_transferido[0].numero_carrito + ' cargada correctamente.'
                    );

                    console.log(formas_pago);

                    desactivar_formas_pago();

                    // desactivar todas las formas de pago y activar solo las que estén en formas_pago

                    formas_pago.forEach(forma => {
                        console.log(forma.id_forma_pago);

                        // Activar solo los elementos que están en formas_pago
                        document.getElementById(`formita-${forma.id_forma_pago}`).disabled = false;
                        document.getElementById(`formita-${forma.id_forma_pago}`).checked = true;
                        document.getElementById(`monto-${forma.id_forma_pago}`).disabled = false;
                        document.getElementById(`referencia-${forma.id_forma_pago}`).disabled = false;
                    });
                    // se active el radio button tipo_documento factura
                    document.getElementById('radio_factura').checked = true;
                    document.getElementById('credito').checked = true;
                },
                error: function(error) {
                    mensaje_estado_notas(
                        'danger',
                        'No se pudo abrir la nota. Vuelva a seleccionarla o recargue el listado.'
                    );
                    console.log(error.responseText);
                },
                complete: function() {
                    estado_carga_notas(false);
                }
            })
        }

        function abrir_repuesto_express_nota() {
            let cantidadItems = Number($('#items_carrito').val() || 0);
            if (cantidadItems >= 20) {
                Vue.swal({
                    icon: 'warning',
                    title: 'Límite alcanzado',
                    text: 'No se pueden agregar más de 20 productos a una nota de venta.'
                });
                return;
            }

            $('#codigo_repuesto_express_nota').val('');
            $('#resultado_repuesto_express_nota').html(
                '<span class="text-muted">Ingrese un código interno para buscar.</span>'
            );
            $('#cantidad_repuesto_express_nota').val(1);
            $('#local_repuesto_express_nota').empty();
            $('#id_repuesto_express_nota').val('');
            $('#btn_confirmar_repuesto_express_nota').prop('disabled', true);
            $('#repuesto-express-nota-modal').modal('show');
            $('#repuesto-express-nota-modal').one('shown.bs.modal', function() {
                $('#codigo_repuesto_express_nota').focus();
            });
        }

        function actualizar_limite_repuestos_nota() {
            let cantidadItems = Number($('#items_carrito').val() || 0);
            let limiteAlcanzado = cantidadItems >= 20;
            let boton = $('#btn_agregar_repuesto_express_nota');

            boton
                .toggleClass('btn-success', !limiteAlcanzado)
                .toggleClass('btn-secondary', limiteAlcanzado)
                .attr('title', limiteAlcanzado
                    ? 'Este carrito ya tiene el máximo de 20 productos'
                    : 'Agregar un repuesto al carrito abierto')
                .html(limiteAlcanzado
                    ? '<i class="fas fa-ban"></i> Límite de 20 productos'
                    : '<i class="fas fa-plus-circle"></i> Agregar repuesto express');
        }

        function buscar_repuesto_express_nota() {
            let codigo = $('#codigo_repuesto_express_nota').val().trim();
            if (codigo === '') {
                Vue.swal({ icon: 'warning', text: 'Debe ingresar un código interno' });
                return;
            }

            $.ajax({
                type: 'GET',
                url: '/repuesto/buscarcodigo/1' + encodeURIComponent(codigo),
                beforeSend: function() {
                    $('#resultado_repuesto_express_nota').html(
                        '<span class="text-muted"><i class="fas fa-spinner fa-spin"></i> Buscando...</span>'
                    );
                    $('#btn_confirmar_repuesto_express_nota').prop('disabled', true);
                },
                success: function(resp) {
                    if (resp == -1 || !resp[0]) {
                        $('#resultado_repuesto_express_nota').html(
                            '<span class="text-danger">No se encontró el repuesto.</span>'
                        );
                        return;
                    }

                    let repuestos = typeof resp[0] === 'string' ? JSON.parse(resp[0]) : resp[0];
                    if (!repuestos.length) {
                        $('#resultado_repuesto_express_nota').html(
                            '<span class="text-danger">No se encontró el repuesto.</span>'
                        );
                        return;
                    }

                    let repuesto = repuestos[0];
                    $('#id_repuesto_express_nota').val(repuesto.id);
                    $('#resultado_repuesto_express_nota').html(
                        '<strong>' + repuesto.codigo_interno + '</strong><br>' +
                        $('<div>').text(repuesto.descripcion).html() + '<br>' +
                        '<span class="text-success">Precio: $' +
                        Number(repuesto.precio_venta).toLocaleString('es-CL') + '</span>'
                    );

                    let locales = resp[3] || [];
                    $('#local_repuesto_express_nota').empty();
                    if (locales.length) {
                        locales.forEach(function(local) {
                            $('#local_repuesto_express_nota').append(
                                $('<option>', {
                                    value: local.id_local,
                                    text: local.local_nombre + ' (' + local.stock_actual + ')'
                                })
                            );
                        });
                    } else {
                        $('#local_repuesto_express_nota').append(
                            $('<option>', { value: 1, text: 'Bodega (' + repuesto.stock_actual + ')' })
                        );
                    }
                    $('#btn_confirmar_repuesto_express_nota').prop('disabled', false);
                },
                error: function(error) {
                    $('#resultado_repuesto_express_nota').html(
                        '<span class="text-danger">No fue posible realizar la búsqueda.</span>'
                    );
                    console.log(error.responseText);
                }
            });
        }

        function agregar_repuesto_express_nota() {
            let idRepuesto = $('#id_repuesto_express_nota').val();
            let cantidad = Number($('#cantidad_repuesto_express_nota').val());
            let carritoId = $('#carrito_id_').val();

            if (!idRepuesto || !carritoId || !Number.isFinite(cantidad) || cantidad <= 0) {
                Vue.swal({ icon: 'warning', text: 'Revise el repuesto y la cantidad ingresada' });
                return;
            }

            $.ajax({
                type: 'POST',
                url: "{{ url('ventas/agregar-producto-carro-transferido') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    id: idRepuesto,
                    idlocal: $('#local_repuesto_express_nota').val(),
                    cantidad: cantidad,
                    cliente_id: carritoId
                },
                beforeSend: function() {
                    $('#btn_confirmar_repuesto_express_nota')
                        .prop('disabled', true)
                        .html('<i class="fas fa-spinner fa-spin"></i> Agregando...');
                    $('#codigo_repuesto_express_nota, #cantidad_repuesto_express_nota, #local_repuesto_express_nota')
                        .prop('disabled', true);
                },
                success: function(resp) {
                    if (resp === 'limite') {
                        $('#repuesto-express-nota-modal').modal('hide');
                        actualizar_limite_repuestos_nota();
                        Vue.swal({
                            icon: 'warning',
                            title: 'Límite alcanzado',
                            text: 'No se pueden agregar más de 20 productos a una nota de venta.'
                        });
                        return;
                    }
                    if (resp === 'existe') {
                        Vue.swal({ icon: 'warning', text: 'El repuesto ya está en la nota de venta' });
                        $('#btn_confirmar_repuesto_express_nota').prop('disabled', false);
                        return;
                    }
                    if (resp === 'error' || resp === 0 || resp === '0') {
                        Vue.swal({ icon: 'error', text: 'No se pudo agregar el repuesto' });
                        $('#btn_confirmar_repuesto_express_nota').prop('disabled', false);
                        return;
                    }

                    $('#repuesto-express-nota-modal').modal('hide');
                    abrir_carrito_transferido(carritoId);
                    Vue.swal({
                        icon: 'success',
                        text: 'Repuesto agregado a la nota de venta',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500
                    });
                },
                error: function(error) {
                    $('#btn_confirmar_repuesto_express_nota').prop('disabled', false);
                    let mensaje = error.responseJSON && error.responseJSON.message
                        ? error.responseJSON.message
                        : 'No se pudo agregar el repuesto';
                    Vue.swal({ icon: 'error', text: mensaje });
                    console.log(error.responseText);
                },
                complete: function() {
                    $('#btn_confirmar_repuesto_express_nota')
                        .prop('disabled', false)
                        .html('<i class="fas fa-plus"></i> Agregar al carrito');
                    $('#codigo_repuesto_express_nota, #cantidad_repuesto_express_nota, #local_repuesto_express_nota')
                        .prop('disabled', false);
                }
            });
        }

        function contado_credito(op) {
            if (op == 1) //click en contado
            {
                //dame_formas_pago();
                document.getElementById("zona_formas_pago").disabled = false;
            } else { // click en crédito
                //$("#formas_pago").html("<br>");
                //document.getElementById("zona_formas_pago").disabled = true;
            }
        }

        function dame_formas_pago() {
            var url = '{{ url('ventas/dame_forma_pago') }}';
            $.ajax({
                type: 'GET',
                beforeSend: function() {
                    $('#mensajes').html("Cargando Formas de Pago...");
                },
                url: url,
                success: function(formas) {
                    $('#mensajes').html("&nbsp;");
                    $("#formas_pago").html(formas);
                },
                error: function(error) {
                    console.log(formatear_error(error.responseText));
                    Vue.swal({
                        title: 'ERROR',
                        text: formatear_error(error.responseText),
                        icon: 'error',
                    });
                }

            });

        }
        function activar_forma_pago(id) {
            if ((document.getElementById("formita-2").checked) && id == 2) {
                $('#modalNuevaFormaPagoCredito').modal('show'); // abrir
            }

            if ((document.getElementById("formita-5").checked) && id == 5) {
                $('#modalNuevaFormaPagoDebito').modal('show'); // abrir
            }
            let dcto = document.getElementById('forma_descuento').checked;
            if ((dcto)) {
                if (id == 999) {
                    Vue.swal({
                        title: 'Ingrese Contraseña',
                        input: 'password',
                        confirmButtonText: 'Verificar',
                        showConfirmButton: true,
                        showCancelButton: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let clave = result.value;
                            let url = '{{ url('/clave_descuento') }}';
                            let parametros = {
                                clave: clave,
                            };
                            $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });

                            $.ajax({
                                type: 'POST',
                                url: url,
                                data: parametros,
                                success: function(resp) {
                                    console.log(resp);
                                    Vue.swal.close();
                                    if (resp == "OK") {
                                        document.getElementById("formita-descuento").disabled = !dcto;
                                        document.getElementById("btn_aplicar_dcto").disabled = !dcto;
                                    } else {
                                        Vue.swal({
                                            title: 'ERROR',
                                            text: 'Contraseña incorrecta',
                                            icon: 'error',
                                        });
                                        $("#forma_descuento").prop("checked", !dcto);
                                    }
                                },
                                error: function(error) {
                                    Vue.swal.close();
                                    Vue.swal({
                                        title: 'ERROR',
                                        text: error.responseText,
                                        icon: 'error',
                                    });
                                }
                            });

                        } else {
                            console.log('cancelando');
                            $("#forma_descuento").prop("checked", !dcto);
                        }


                    });
                }
            } else {
                let marca = document.getElementById("formita-" + id).checked;
                document.getElementById("monto-" + id).disabled = !marca;
                document.getElementById("referencia-" + id).disabled = !marca;
            }
        }

        function que_documento(tipo_doc) {
            let bb = document.getElementById("radio_boleta");
            let ff = document.getElementById("radio_factura");

            let idc = document.getElementById("id_cliente").value;

            if(tipo_doc == 'boleta'){
                let carrito_id = $('#carrito_id_').val();
                console.log(carrito_id);
                let url = '/ventas/precios_boleta/'+carrito_id;
                $.ajax({
                    type: 'GET',
                    url: url,
                    beforeSend: function(){
                        Vue.swal({
                            title: 'Cargando...',
                            text: 'Espere un momento por favor',
                            icon: 'info',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            showCancelButton: false,
                        });
                    },
                    success: function(html) {
                        Vue.swal.close();
                        console.log(html);
                        $('#notas_ventas').empty();
                        $('#notas_ventas').append(html);
                    },
                });
            }

            if (tipo_doc == 'factura') {
                let carrito_id = $('#carrito_id_').val();
                console.log(carrito_id);
                let url = '/ventas/precios_factura/'+carrito_id;
                $.ajax({
                    type: 'GET',
                    url: url,
                    beforeSend: function(){
                        Vue.swal({
                            title: 'Cargando...',
                            text: 'Espere un momento por favor',
                            icon: 'info',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            showCancelButton: false,
                        });
                    },
                    success: function(html) {
                        Vue.swal.close();
                        console.log(html);
                        $('#notas_ventas').empty();
                        $('#notas_ventas').append(html);
                    },
                });
            }

            if (cliente["credito"] == undefined);
            {
                if (tipo_doc == "boleta" || tipo_doc == "factura") {
                    if(tipo_doc == 'factura') document.getElementById("credito").checked = true;
                    document.getElementById("contado").disabled = false;
                    document.getElementById("contado").checked = true;
                    // document.getElementById("credito").disabled = true;
                    // document.getElementById("dias_expira").disabled = true;
                    // document.getElementById("nombre_cotizacion").disabled = true;
                    // document.getElementById("nombre_cotizacion").value = "";
                    // document.getElementById("nombre_consignacion").disabled = true;
                    // document.getElementById("nombre_consignacion").value = "";
                    //dame_formas_pago();
                    document.getElementById("zona_formas_pago").disabled = false;
                    //Si no existe el permiso de solo carritos transferidos se debe mostrar el boton de procesar
                    let procesar = $('#procesar').val();
                    if (procesar == 0) {
                        $('#btn_procesar_').removeClass('d-block');
                        $('#btn_procesar_').addClass('d-none');
                    }

                }

                if (tipo_doc == "cotizacion") {
                    document.getElementById("nombre_cotizacion").disabled = false;
                    document.getElementById("nombre_consignacion").disabled = true;
                    document.getElementById("contado").disabled = true;
                    document.getElementById("credito").disabled = true;
                    document.getElementById("contado").checked = false;
                    document.getElementById("credito").checked = false;
                    // document.getElementById("dias_expira").disabled = false;
                    document.getElementById("nombre_cotizacion").focus();
                    //$("#formas_pago").html("<br><strong>No corresponde Formas de Pago</strong><br>");
                    document.getElementById("zona_formas_pago").disabled = true;
                    document.getElementById("nombre_consignacion").value = "";
                    let procesar = $('#procesar').val();
                    if (procesar == 0) {
                        $('#btn_procesar_').removeClass('d-none');
                        $('#btn_procesar_').addClass('d-block');
                    }
                }

                if (tipo_doc == "consignacion") {
                    document.getElementById("nombre_consignacion").disabled = false;
                    document.getElementById("nombre_cotizacion").disabled = true;
                    document.getElementById("contado").disabled = true;
                    document.getElementById("credito").disabled = true;
                    document.getElementById("contado").checked = false;
                    document.getElementById("credito").checked = false;
                    // document.getElementById("dias_expira").disabled = false;
                    document.getElementById("nombre_consignacion").focus();
                    //$("#formas_pago").html("<br><strong>No corresponde Formas de Pago</strong><br>");
                    document.getElementById("zona_formas_pago").disabled = true;
                    document.getElementById("nombre_cotizacion").value = "";
                    $('#btn_procesar_').removeClass('d-none');
                    $('#btn_procesar_').addClass('d-block');
                }

            }

            if (cliente["credito"] == 1) //Cliente tiene crédito
            {
                if (tipo_doc == "boleta" || tipo_doc == "factura") {
                    document.getElementById("contado").disabled = false;
                    document.getElementById("credito").disabled = false;
                    document.getElementById("contado").checked = true;
                    document.getElementById("credito").checked = false;
                    // document.getElementById("dias_expira").disabled = true;
                    if (document.getElementById("contado").checked) {
                        //dame_formas_pago();
                        document.getElementById("zona_formas_pago").disabled = false;
                    } else {
                        //$("#formas_pago").html("<br><strong>No corresponde Formas de Pago</strong><br>");
                        document.getElementById("zona_formas_pago").disabled = true;
                    }
                }

                if (tipo_doc == "cotizacion") {
                    document.getElementById("contado").disabled = true;
                    document.getElementById("contado").checked = false;
                    document.getElementById("credito").disabled = true;
                    // document.getElementById("dias_expira").disabled = false;
                    // document.getElementById("dias_expira").focus();
                    //$("#formas_pago").html("<br><strong>No corresponde Formas de Pago</strong><br>");
                    document.getElementById("zona_formas_pago").disabled = true;
                }

            }

            if (cliente["credito"] == 0) // Cliente no tiene crédito
            {
                if (tipo_doc == "boleta" || tipo_doc == "factura") {
                    document.getElementById("contado").disabled = false;
                    document.getElementById("contado").checked = true;
                    document.getElementById("credito").disabled = true;
                    document.getElementById("credito").checked = false;
                    // document.getElementById("dias_expira").disabled = true;
                    //dame_formas_pago();
                    document.getElementById("zona_formas_pago").disabled = false;
                }

                if (tipo_doc == "cotizacion") {
                    document.getElementById("contado").disabled = true;
                    document.getElementById("contado").checked = false;
                    document.getElementById("credito").disabled = true;
                    document.getElementById("credito").checked = false;
                    // document.getElementById("dias_expira").disabled = false;
                    // document.getElementById("dias_expira").focus();
                    //$("#formas_pago").html("<br><strong>No corresponde Formas de Pago</strong><br>");
                    document.getElementById("zona_formas_pago").disabled = true;
                }

                if (tipo_doc == "consignacion") {
                    document.getElementById("contado").disabled = true;
                    document.getElementById("contado").checked = false;
                    document.getElementById("credito").disabled = true;
                    document.getElementById("credito").checked = false;
                    // document.getElementById("dias_expira").disabled = false;
                    document.getElementById("dias_expira").focus();
                    //$("#formas_pago").html("<br><strong>No corresponde Formas de Pago</strong><br>");
                    document.getElementById("zona_formas_pago").disabled = true;
                }
            }

        }

        function ver_estadoUP() {
            //let TrackID=document.getElementById("trackID").value;
            document.getElementById("btn-verestado").disabled = true;
            let docu = $('input[name="tipo_documento"]:checked').val().trim();
            let tipoDTE = "??";
            if (docu == "boleta") {
                tipoDTE = '39';
            }
            if (docu == "factura") {
                tipoDTE = '33';
            }

            var url = '{{ url('sii/verestado') }}' + "/" + tipoDTE + "&" +
                TrackID; //Controlador sii_controlador@ver_estadoUP
            $.ajax({
                type: 'GET',
                beforeSend: function() {

                    $("#mensajes").html("<b>Revisando Estado...</b>");
                    $("#envio-txt").html("<small>Revisando estado...</small>");
                },
                url: url,
                success: function(rs) {
                    console.log(rs);
                    rs = JSON.parse(rs);
                    if (rs.estado == 'ACEPTADO') {
                        $("#envio-txt").html("<small>Envío ACEPTADO... puede imprimir</small>");
                        document.getElementById("btn-enviarsii").disabled = true;
                        document.getElementById("btn-verestado").disabled = true;
                        document.getElementById("btn-imprimir").disabled = false;
                        // Envío automático del XML al correo del cliente
                        if (correo_dte && num_doc_dte && tipo_dte_dte) {
                            enviar_correo_automatico(correo_dte, tipo_dte_dte, num_doc_dte);
                        }
                    } else {
                        $("#envio-txt").html("<small>" + rs.estado + ": " + rs.mensaje + "</small>");
                        document.getElementById("btn-verestado").disabled = false;
                    }


                },
                error: function(error) {
                    $('#mensajes').html(formatear_error(error.responseText));
                }
            });

        }

        function borrar_item_carrito_transferido(item_id, codigo_interno) {
            Vue.swal({
                title: '¿Está seguro de borrar el item '+codigo_interno+'?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, borrar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {

                    var url = "{{ url('ventas') }}" + "/" + item_id + "/borrar_item_carrito_transferido"; //petición
                    $.ajax({
                        type: 'GET',
                        beforeSend: function() {
                            //$("#carrito").html("Borrando Item...");
                        },
                        url: url,
                        success: function(resp) {
                            console.log(resp);
                            let t = new Intl.NumberFormat('es-CL').format(resp[0]);
                            document.getElementsByName("forma_pago_monto")[0].value = t.replace(/\./g, "");
                            abrir_carrito_transferido(resp[1]);
                            //calcular_sumatoria();
                        },
                        error: function(error) {
                            $('#carrito').html(formatear_error(error.responseText));
                        }

                    }); //Fin petición
                }
            });
        }

        function confirmar_documento() {
            let docu = $('input[name="tipo_documento"]:checked').val().trim();
            docu = docu.toUpperCase();
            Vue.swal({
                text: "Desea continuar con " + docu + ' ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'CONTINUAR',
                cancelButtonText: 'CANCELAR'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (docu == 'COTIZACION') {
                        cotizar();
                    }
                    if (docu == 'CONSIGNACION') {
                        consignar();
                    }
                    if (docu == 'BOLETA' || docu == 'FACTURA') {
                        generar_xml();
                    }
                } else {
                    /*
                    let bb=document.getElementById("radio_boleta");
                    let ff=document.getElementById("radio_factura");
                    bb.checked=true;
                    ff.checked=false;
                    */
                }
            });
        }

        function imprimir_ticket_para_bodega(id_cliente){
        let id_direccion = document.getElementById('cliente_direccion_select').value;

        if(id_direccion == ''){
            id_direccion = 0;
        }

        let url = '/ventas/imprimir_ticket_para_bodega/' + id_cliente;

        $.ajax({
            type: 'GET',
            url: url,
            beforeSend: function(){
                Vue.swal({
                    title: 'Imprimiendo...',
                    text: 'Espere un momento por favor',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    showCancelButton: false,
                })
            },
            success: function(resp) {
                console.log(resp);
                Vue.swal.close();
                let r = JSON.parse(resp);
                if (r.estado == 'OK') {
                    var config = "location=yes,height=570,width=720,scrollbars=yes,status=yes";
                    var w = window.open(r.mensaje, '_blank', config);
                    w.focus();
                } else {
                    Vue.swal({
                        title: r.estado,
                        text: r.mensaje,
                        icon: 'error',
                    });
                }
            },
            error: function(error) {
                Vue.swal({
                    title: 'Error',
                    text: error.responseText + " .Recargue la pagina porfavor",
                    icon: 'error',
                });
            }
        });
    }

    function imprimir_para_bodega(){
            Vue.swal({
                    title: '¿Está seguro de imprimir?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, imprimir',
                    cancelButtonText: 'Cancelar',
            }).then((result) => {
                    if (result.isConfirmed) {
                        let carrito_id = document.getElementById('carrito_id_').value;
                        let carrito_direccion = document.getElementById('cliente_direccion_select').value;
                        let timestamp = new Date().getTime(); // Agregar un parámetro único
                        let url = '/ventas/imprimir_para_bodega/' + carrito_id + '/' + carrito_direccion + '?_=' + timestamp;

                        $.ajax({
                            type: 'GET',
                            url: url,
                            beforeSend: function(){
                                Vue.swal({
                                    title: 'Imprimiendo...',
                                    text: 'Espere un momento por favor',
                                    icon: 'info',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    showConfirmButton: false,
                                    showCancelButton: false,
                                })
                            },
                            success: function(resp) {
                                Vue.swal.close();
                                let r = JSON.parse(resp);
                                if (r.estado == 'OK') {
                                    var config = "location=yes,height=570,width=720,scrollbars=yes,status=yes";
                                    var w = window.open(r.mensaje, '_blank', config);
                                    w.focus();
                                } else {
                                    Vue.swal({
                                        title: r.estado,
                                        text: r.mensaje,
                                        icon: 'error',
                                    });
                                }
                            },
                            error: function(error) {
                                Vue.swal({
                                    title: 'Error',
                                    text: error.responseText + " .Recargue la pagina porfavor",
                                    icon: 'error',
                                });
                            }
                        });
                    }
            });


    }

        function formatear_error(error) {
            let max = 300;
            let rpta = error.substring(0, max);
            return rpta;
        }

        function generar_xml() {
            //Se crea un div con id es_oferta donde se guarda si tiene algun repuesto en oferta.
            let es_oferta = $('#es_oferta').val();
            let id_cliente = $('#id_cliente').val();
            //Si es oferta se debe consultar si desea mantener la oferta o colocar el precio normal.
            if (es_oferta == 1) {
                Vue.swal({
                    title: 'Oferta, ¿Como desea Cancelar?',
                    text: "¡Solo si paga con efectivo o transferencia se respeta el precio oferta!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Precio Oferta',
                    cancelButtonText: 'Precio Normal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        //Dejamos disabled las formas de pago que no sean efectivo o transferencia
                        $('#formita-2').attr('disabled', 'disabled');
                        $('#formita-3').attr('disabled', 'disabled');
                        $('#formita-5').attr('disabled', 'disabled');
                        $('#formita-6').attr('disabled', 'disabled');
                        $('#es_oferta').val('0');
                        $('#confirmado').val('ofertado');
                        //Volvemos la oferta a 0 ya que se selecciono alguna opción.
                        es_oferta = 0;
                    } else {
                        //Funcion creada para cambiarle el valor al carrito de compras y quitarle el precio oferta
                        let si_carrito_transferido = $('#es_transferido').val();
                        if (si_carrito_transferido == 1) {
                            recargar_carrito_transferido();
                        } else {
                            recargar_carrito();

                        }
                        es_oferta = 0;
                        $('#confirmado').val('normal');

                    }

                });
            } else {
                console.log('no tiene ofertas');

                //Verificar si seleccionó documento de venta
                let bb = document.getElementById("radio_boleta");
                let ff = document.getElementById("radio_factura");

                let nombre_cotizacion = "";

                if (!bb.checked && !ff.checked) {
                    Vue.swal({
                        text: 'Seleccione un documento de Venta',
                        position: 'top-end',
                        icon: 'warning',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                    });
                    return false;
                }


                //Verificar si existe el div items_carrito
                let items_en_carrito = document.getElementById("items_carrito").value;

                if (items_en_carrito == 0) {
                    Vue.swal({
                        text: 'Carrito de Compras Vacio...',
                        position: 'top-end',
                        icon: 'error',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                    });
                    return false;
                }



                //var parametros={idcliente:idc,docu:docu,venta:venta,forma_pago:id_forma_pago,monto:monto_forma_pago,referencia:referencia_forma_pago};
                let docu = $('input[name="tipo_documento"]:checked').val().trim();
                let idc = document.getElementById("id_cliente").value;


                if (idc == 0 && docu == 'factura') // Solo exige cliente si es factura
                {
                    Vue.swal({
                        text: 'No ha seleccionado cliente...',
                        position: 'top-end',
                        icon: 'error',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                    });
                    return false;
                }

                var venta = "nada";

                if (docu != "cotizacion") {
                    venta = $('input[name="tipo_venta"]:checked').val().trim();
                }

                if (venta == "contado") {
                    //Forma de pago, monto y referencia
                    //formita-1, monto-1, referencia-1

                    let paguitos = $('input[name="forma_pago"]:checked');
                    //Si no selecciona ningún check??
                    let seleccionados = paguitos.length;
                    if (seleccionados == 0) {
                        Vue.swal({
                            text: 'Debe seleccionar al menos una forma de pago',
                            position: 'top-end',
                            icon: 'error',
                            toast: true,
                            showConfirmButton: false,
                            timer: 3000,
                        });
                        return false;
                    }

                    //inicio paguitos

                    var id_forma_pago = [];
                    var monto_forma_pago = [];
                    var referencia_forma_pago = [];
                    var m = "";
                    var r = "";
                    var monto = "";
                    var referencia_pago = "";
                    let error = false;
                    paguitos.each(function() {
                        //Si los checks seleccionados no tienen monto ni referencia??
                        var texto_seleccionado = $(this)[0].nextSibling.nodeValue.toUpperCase().trim();
                        m = "monto-" + $(this).val();
                        monto = document.getElementById(m).value;
                        //console.log("Checkbox " + $(this).prop("id") +  " (" + $(this).val() + ") Seleccionado MONTO-m: "+m+" TEXTO: "+texto_seleccionado+" monto-valor: "+Number(monto));
                        if (isNaN(monto) || Number(monto) <= 0) {
                            Vue.swal({
                                text: texto_seleccionado + ": Monto vacio, cero o no es un número válido.",
                                position: 'top-end',
                                icon: 'error',
                                toast: true,
                                showConfirmButton: false,
                                timer: 4000,
                            });
                            error = true;
                            return false; //sale del bucle jquery paguitos.each
                        }

                        r = "referencia-" + $(this).val();
                        referencia_pago = document.getElementById(r).value.toUpperCase().trim();
                        if (referencia_pago.length == 0) {
                            Vue.swal({
                                text: texto_seleccionado +
                                    ": Referencia vacia, cero o no es un número válido.",
                                position: 'top-end',
                                icon: 'error',
                                toast: true,
                                showConfirmButton: false,
                                timer: 4000,
                            });
                            error = true;
                            return false; //sale del bucle jquery paguitos.each
                        }

                        id_forma_pago.push($(this).val());
                        monto_forma_pago.push(monto);
                        referencia_forma_pago.push(referencia_pago);
                    }); // fin de paguitos

                    if (error) {
                        return false; //sale de la funcion...
                    }

                    var error_pago = total_pagado();
                    if (error_pago == "SI") {
                        mensaje_error = "Total a Pagar no coincide con Total Venta";
                        Vue.swal({
                            title: "ATENCIÓN!!!",
                            text: mensaje_error,
                            position: 'center',
                            icon: 'error',
                            showConfirmButton: false,
                            timer: 5000,
                        });
                        return false;
                    }

                    //var parametros={idcliente:idc,docu:docu,venta:venta,forma_pago:id_forma_pago,monto:monto_forma_pago,referencia:referencia_forma_pago};

                } else { //Venta al crédito o cotización, no hay formas de pago.
                    if (venta == "credito" || venta == "delivery") {
                        //var parametros={idcliente:idc,docu:docu,venta:venta};
                    } else { //Si no es crédito ni contado, entonces es cotización porque así lo programé... ja ja ja
                        var d_ex = document.getElementById("dias_expira").value;
                        //var parametros={idcliente:idc,docu:docu,dias_expira:d_ex};
                    }
                }

                let descuento = $('#formita-descuento').val();
                //Verificamos si esta en oferta
                let o = $('#es_oferta').val();
                let confirmado = $('#confirmado').val();

                let url = "{{ url('ventas/generarxml') }}";
                if ((document.getElementById('vendedor_id')) !== null) {
                    var parametros = {
                        docu: docu,
                        idcliente: idc,
                        fmapago: venta,
                        cliente_id: document.getElementById('cliente_id').value,
                        carrito_id: document.getElementById('carrito_id_').value,
                        vendedor_id: document.getElementById('vendedor_id').value,
                        ref1: JSON.stringify(ref1),
                        ref2: JSON.stringify(ref2),
                        ref3: JSON.stringify(ref3),
                        dcto: descuento,
                        oferta: o,
                        confirmado: confirmado,
                        id_direccion: $('#cliente_direccion select').val(),
                        id_giro: $('#cliente_giro select').val(),
                    };
                } else {
                    var parametros = {
                        docu: docu,
                        idcliente: idc,
                        fmapago: venta,
                        ref1: JSON.stringify(ref1),
                        ref2: JSON.stringify(ref2),
                        ref3: JSON.stringify(ref3),
                        dcto: descuento,
                        oferta: o,
                        confirmado: confirmado,
                        id_direccion: $('#cliente_direccion select').val(),
                        id_giro: $('#cliente_giro select').val(),
                    };
                }


                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    type: 'POST',
                    beforeSend: function() {
                        $("#mensajes").html("<b>Generando XML...</b>");
                        $("#envio-txt").html("<b>Generando XML...</b>");
                    },
                    url: url,
                    data: parametros,
                    beforeSend: function(){
                        Vue.swal({
                            title: 'Generando XML...',
                            text: 'Espere un momento por favor',
                            icon: 'info',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            showCancelButton: false,
                        });
                    },
                    success: function(rs) { //llega un JSON
                        console.log(rs);
                        Vue.swal.close();
                        let rpta = JSON.parse(rs);

                        if (rpta.estado == 'GENERADO') {
                            $("#mensajes").html("Generando XML...<b> Listo!!!</b>");
                            $("#envio-txt").html("<b>Generando XML...</b> Listo!!!");
                            documento_procesado = rpta
                                .mensaje; //tiene el tipo y número de documento que se está procesando.
                            //document.getElementById("dcmto_en_envio").value = documento_procesado;

                            abrir_procesar_envio();

                            // Vue.swal({
                            //     title: '<h3><i><strong>PROCESANDO '+documento_procesado.toUpperCase()+'</strong></i></h3>',
                            //     html:'<h3 id="envio-txt">Generando XML...</h3>'+
                            //             '<button  class="btn btn-outline-info form-control-sm" onclick="enviar_sii()" id="btn-enviarsii"><small>Enviar al SII</small></button>'+
                            //             '<button  class="btn btn-outline-warning form-control-sm" disabled onclick="ver_estadoUP()" id="btn-verestado"><small>Ver Estado</small></button>'+
                            //             '<button  class="btn btn-outline-success form-control-sm" disabled onclick="imprimir()" id="btn-imprimir">Imprimir</button>',
                            //     allowOutsideClick:false,
                            //     allowEscapeKey:false,
                            //     showConfirmButton:false,
                            //     showCancelButton:true,
                            //     cancelButtonText:"CERRAR" //si apreta cancel, borrar todas las variables generadas en enviar_sii() y ver_estado()
                            //     });




                        } else {
                            Vue.swal({
                                title: rpta.estado,
                                text: rpta.mensaje,
                                icon: 'error',
                            });
                        }
                    },
                    error: function(error) {
                        console.log(error.responseText);
                        Vue.swal({
                            title: 'ERROR',
                            text: formatear_error(error.responseText),
                            icon: 'error',
                            });
                    }
                });
            }
        }



        function abrir_procesar_envio() {
            $("#titulo_procesar_envio_modal").html("PROCESANDO " + documento_procesado.toUpperCase());
            $('#procesar-envio-modal').on('shown.bs.modal', function() {

            });
            $("#procesar-envio-modal").modal("show");
            //enviar_sii();
        }

        function cerrar_procesar_envio() {
            // cliente_xpress_cancelar();
            $("#procesar-envio-modal").modal("hide");
            // abrir_cliente_xpress();
        }

        function total_pagado() {
            var total_pago = calcular_sumatoria();

            //Comparar con el total del carrito
            let dcto = Number(document.getElementById('formita-descuento').value);
            let total_carrito = Number(document.getElementById("total_carrito").value);
            // El descuento se divide por 100 para sacar el porcentaje y ese resultado se multiplica por el total del carrito.
            // Con esto calculamos el valor a restar con el descuento aplicado.
            // El resultado anterior se resta al total del carrito.
            let total_carrito_descuento = Number(total_carrito) - Number((dcto / 100) * Number(total_carrito));
            let total_pagado = Number(total_pago);
            let diferencia = Math.abs(total_pagado - total_carrito_descuento);
            console.log(diferencia);
            if (diferencia > 2) {
                $("#aqui_mensaje").html("Total a Pagar es menor que Total Venta");
                return "SI";
            } else {
                return "NO"
            }

        }

        function calcular_sumatoria() {
            var total_pago = 0;
            var paguitos = $('input[name="forma_pago"]:checked');

            var monto = 0;
            var dcto = 0;
            var m = "";
            var d = "formita-descuento";
            var dcto = Number(document.getElementById(d).value.replace(/\./g, ""));
            if (dcto >= 100) {
                Vue.swal({
                    icon: 'error',
                    text: 'NO PUEDE INGRESAR UN PORCENTAJE MAYOR O IGUAL A 100',
                    toast: true,
                    timer: 3000,
                    position: 'center',
                    showConfirmButton: false
                });
                return false;
            }

            paguitos.each(function() {
                //Aqui se obtiene el valor del id de los pagos seleccionados
                m = "monto-" + $(this).val();
                monto = Number(document.getElementById(m).value.replace(/\./g, ""));

                let dcto_x_item = Number((dcto / 100) * monto);
                console.log(monto - dcto_x_item);
                total_pago = total_pago + monto - dcto_x_item;
                //Le asignamos los nuevos valores a los pagos con el descuento para no descuadrar el reporte diario
                document.getElementById(m).value = monto - dcto_x_item;
            });
            var t = new Intl.NumberFormat('es-CL').format(total_pago);
            $("#total_forma_pago").html("<b><p style='color:blue'>" + t + "</p></b>");

            return total_pago;
        }

        function abrir_referencias() {
            $('#referencias-modal').on('shown.bs.modal', function() {

            });
            $("#referencias-modal").modal("show");
        }

        function activar_referencia(cual) {
            if (cual == 1) {
                referencia1 = !referencia1;
                document.getElementById("referencia1").disabled = referencia1;
            }
            if (cual == 2) {
                referencia2 = !referencia2;
                document.getElementById("referencia2").disabled = referencia2;
            }
            if (cual == 3) {
                referencia3 = !referencia3;
                document.getElementById("referencia3").disabled = referencia3;
            }
        }

        function borrar_item_carrito(item_id) {
            Vue.swal({
                title: '¿Está seguro?',
                text: "Si ACEPTA se borrará el item seleccionado!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'CANCELAR',
                confirmButtonText: 'ACEPTAR'
            }).then((result) => {
                if (result.value) {
                    var url = "{{ url('ventas') }}" + "/" + item_id + "/borrar_item_carrito"; //petición
                    $.ajax({
                        type: 'GET',
                        beforeSend: function() {
                            //$("#carrito").html("Borrando Item...");
                        },
                        url: url,
                        success: function(resp) {
                            let t = new Intl.NumberFormat('es-CL').format(resp);
                            dame_carrito();
                            //calcular_sumatoria();
                        },
                        error: function(error) {
                            $('#carrito').html(formatear_error(error.responseText));
                        }

                    }); //Fin petición
                }
            });
        }

        function enviar_sii() {

let marca_credito = document.getElementById("formita-2").checked;
let marca_debito = document.getElementById("formita-5").checked;
let consignacion = $('#local_id_consignacion').val();

var nfp_credito = 0;
var nfp_debito = 0;
if (marca_credito) {
    nfp_credito = $('input:radio[name=flexRadioDefault_credito]:checked').val();
}

if (marca_debito) {
    nfp_debito = $('input:radio[name=flexRadioDefault_debito]:checked').val();
}

if (nfp_credito == undefined) nfp_credito = 0;
if (nfp_debito == undefined) nfp_debito = 0;

document.getElementById("btn-enviarsii").disabled = true;
let vendedor_id = $('#vendedor_id').val();
let carrito_id = $('#carrito_id').val();
let cliente_id = $('#id_cliente').val();
let num_abono = $('#input_num_abono').val();

let docu = $('input[name="tipo_documento"]:checked').val().trim();

if (docu != "cotizacion") {
    venta = $('input[name="tipo_venta"]:checked').val().trim();
}

var id_forma_pago = [];
var monto_forma_pago = [];
var referencia_forma_pago = [];
var m = "";
var r = "";
var monto = "";
var referencia_pago = "";
let error = false;
let parametros = {};
if (venta == 'contado') {
    var paguitos = $('input[name="forma_pago"]:checked');
    paguitos.each(function() {
        //Si los checks seleccionados no tienen monto ni referencia??
        var texto_seleccionado = $(this)[0].nextSibling.nodeValue.toUpperCase().trim();
        m = "monto-" + $(this).val();
        monto = document.getElementById(m).value;

        //console.log("monto: "+monto);
        //console.log("Checkbox " + $(this).prop("id") +  " (" + $(this).val() + ") Seleccionado MONTO-m: "+m+" TEXTO: "+texto_seleccionado+" monto-valor: "+Number(monto));
        if (isNaN(monto) || Number(monto) <= 0) {
            Vue.swal({
                text: texto_seleccionado + ": Monto vacio, cero o no es un número válido.",
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 4000,
            });
            error = true;
            return false; //sale del bucle jquery paguitos.each
        }

        r = "referencia-" + $(this).val();
        referencia_pago = document.getElementById(r).value.toUpperCase().trim();
        if (referencia_pago.length == 0) {
            Vue.swal({
                text: texto_seleccionado + ": Referencia vacia, cero o no es un número válido.",
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 4000,
            });
            error = true;
            return false; //sale del bucle jquery paguitos.each
        }

        id_forma_pago.push($(this).val());
        monto_forma_pago.push(monto);
        referencia_forma_pago.push(referencia_pago);
    }); // fin de paguitos

    let descuento = $('#formita-descuento').val();

    if (!vendedor_id) {
        parametros = {
            idcliente: cliente_id,
            carrito_id: document.getElementById('carrito_id_').value,
            docu: docu,
            venta: venta,
            forma_pago: id_forma_pago,
            nfp_credito: 2,
            nfp_debito: 2,
            monto: monto_forma_pago,
            referencia: referencia_forma_pago,
            num_abono: num_abono,
            dcto: descuento,
            consignacion: consignacion
        };
    } else {
        parametros = {
            idcliente: cliente_id,
            docu: docu,
            venta: venta,
            forma_pago: id_forma_pago,
            nfp_credito: 2,
            nfp_debito: 2,
            monto: monto_forma_pago,
            referencia: referencia_forma_pago,
            vendedor_id: vendedor_id,
            carrito_id: document.getElementById('carrito_id_').value,
            cliente_id: cliente_id,
            dcto: descuento
        };
    }

} else { //es crédito o delivery
    parametros = {
        idcliente: cliente_id,
        carrito_id: document.getElementById('carrito_id_').value,
        vendedor_id: vendedor_id,
        docu: docu,
        venta: venta
    };
}


let url = "{{ url('ventas/enviarsii') }}";
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$.ajax({
    type: 'POST',
    beforeSend: function() {
        $("#mensajes").html("<b>Enviando al SII...</b>");
        $("#envio-txt").html("<small>Enviando al SII...</small>");

    },
    url: url,
    data: parametros,
    success: function(rs) {
         console.log(rs);
        let carrito_id = $('#carrito_id').val();
        let cliente_id = $('#cliente_id').val();
        let rpta = JSON.parse(rs);

        if (rpta.estado == 'OK') {
            $("#mensajes").html("Enviando al SII...<b>Recibido !!!</b>");
            $("#envio-txt").html("<small>" + rpta.mensaje + " TrackID: " + rpta.trackid +
                "</small>");
            //document.getElementById("btn-enviarsii").disabled = true;
            document.getElementById("btn-verestado").disabled = false;
            document.getElementById("btn-imprimir").disabled = false;

            TrackID = rpta.trackid;
            num_doc_dte = rpta.num_doc || 0;
            tipo_dte_dte = rpta.tipo_dte || '';
            correo_dte = rpta.correo_cliente || '';

            //borrar_carrito_transferido(cliente_id);
            //limpiar_todo();
            //borrar_carrito('actual');
            // Limpiamos el input de monto_efectivo
            document.getElementById("monto_efectivo").value = '';
            reiniciar_descuento();
        } else {
            document.getElementById("btn-enviarsii").disabled = false;
            $("#mensajes").html("Enviando al SII...<b>No recibido !!! Reintente el envío</b>");
            $("#envio-txt").html("<small>" + rpta.estado + ". " + rpta.mensaje + "</small>");
            TrackID = 0;
        }

    },
    error: function(error) {
        console.log(error.responseText);
        $('#envio-txt').html(error.responseText);
    }
});


}

function imprimir() {
            var url = "{{ url('imprimir/0') }}";
            $.ajax({
                type: 'GET',
                beforeSend: function() {
                    $("#mensajes").html("<b>Imprimiendo...</b>");
                },
                url: url,
                success: function(resp) {
                    //console.log(resp);
                    let r = JSON.parse(resp);
                    if (r.estado == 'OK') {
                        $("#mensajes").html("Imprimiendo... <b>Listo...</b>");
                        $('#envio-txt').html("PDF Generado...");
                        var config = "location=yes,height=570,width=720,scrollbars=yes,status=yes";
                        var w = window.open(r.mensaje, '_blank', config);
                        w.focus();
                        //limpiar toda la pantalla y variables para una nueva venta...
                        // limpiar_todo();
                        Vue.swal.close();
                    } else {
                        $('#envio-txt').html(r.estado + ": " + r.mensaje);
                    }

                },
                error: function(error) {
                    $('#envio-txt').html(formatear_error(error.responseText));
                    /*
                    Vue.swal({
                        title: 'ERROR',
                        text: error.responseText,
                        icon: 'error',
                        });
                        */
                }

            });

            //location.href="{{ url('ventas') }}"; //
        }

        function referencias_aceptar() {
            let i = 0;
            let ref1_docu = document.getElementById("ref1_docu").value;
            let ref1_folio = document.getElementById("ref1_folio").value;
            let ref1_fecha = document.getElementById("ref1_fecha").value;
            let ref1_razon = document.getElementById("ref1_razon").value;
            let ref1_check = document.getElementById("ref1_check").checked;

            let ref2_docu = document.getElementById("ref2_docu").value;
            let ref2_folio = document.getElementById("ref2_folio").value;
            let ref2_fecha = document.getElementById("ref2_fecha").value;
            let ref2_razon = document.getElementById("ref2_razon").value;
            let ref2_check = document.getElementById("ref2_check").checked;

            let ref3_docu = document.getElementById("ref3_docu").value;
            let ref3_folio = document.getElementById("ref3_folio").value;
            let ref3_fecha = document.getElementById("ref3_fecha").value;
            let ref3_razon = document.getElementById("ref3_razon").value;
            let ref3_check = document.getElementById("ref3_check").checked;

            let mensaje_error_ref = "";

            if (ref1_check === true) {
                if (ref1_docu == 0) {
                    mensaje_error_ref = "Elija un documento para Referencia 1";
                } else if (ref1_folio.length == 0 || isNaN(parseInt(ref1_folio))) {
                    mensaje_error_ref = "Escriba Número de Folio para Referencia 1";
                } else if (ref1_fecha.length == 0) {
                    //ref1_fecha = Carbon.now().toDateString();
                    //mensaje_error_ref = "Elija Fecha para Referencia 1";
                } else if (ref1_razon.length == 0) {
                    //mensaje_error_ref = "Escriba Razón para Referencia 1";
                }

                if (mensaje_error_ref != "") {
                    Vue.swal({
                        icon: 'error',
                        text: mensaje_error_ref,
                        position: 'top-end',
                        toast: true,
                        showConfirmButton: false,
                        timer: 4000,
                    });
                    return false;
                }
                ref1.push({
                    docu: ref1_docu,
                    folio: ref1_folio,
                    fecha: ref1_fecha,
                    razon: ref1_razon
                });
                i++;
            } else {
                ref1 = [];
            }

            mensaje_error_ref = "";
            if (ref2_check === true) {
                if (ref2_docu == 0) {
                    mensaje_error_ref = "Elija un documento para Referencia 2";
                } else if (ref2_folio.length == 0 || isNaN(parseInt(ref2_folio))) {
                    mensaje_error_ref = "Escriba Número de Folio para Referencia 2";
                } else if (ref2_fecha.length == 0) {
                    //mensaje_error_ref = "Elija Fecha para Referencia 2";
                } else if (ref2_razon.length == 0) {
                    //mensaje_error_ref = "Escriba Razón para Referencia 2";
                }
                if (mensaje_error_ref != "") {
                    Vue.swal({
                        icon: 'error',
                        text: mensaje_error_ref,
                        position: 'top-end',
                        toast: true,
                        showConfirmButton: false,
                        timer: 4000,
                    });
                    return false;
                }
                ref2.push({
                    docu: ref2_docu,
                    folio: ref2_folio,
                    fecha: ref2_fecha,
                    razon: ref2_razon
                });
                i++;
            } else {
                ref2 = [];
            }

            mensaje_error_ref = "";
            if (ref3_check === true) {
                if (ref3_docu == 0) {
                    mensaje_error_ref = "Elija un documento para Referencia 3";
                } else if (ref3_folio.length == 0 || isNaN(parseInt(ref3_folio))) {
                    mensaje_error_ref = "Escriba Número de Folio para Referencia 3";
                } else if (ref3_fecha.length == 0) {
                    //mensaje_error_ref = "Elija Fecha para Referencia 3";
                } else if (ref3_razon.length == 0) {
                    //mensaje_error_ref = "Escriba Razón para Referencia 3";
                }
                if (mensaje_error_ref != "") {
                    Vue.swal({
                        icon: 'error',
                        text: mensaje_error_ref,
                        position: 'top-end',
                        toast: true,
                        showConfirmButton: false,
                        timer: 4000,
                    });
                    return false;
                }
                ref3.push({
                    docu: ref3_docu,
                    folio: ref3_folio,
                    fecha: ref3_fecha,
                    razon: ref3_razon
                });
                i++;
            } else {
                ref3 = [];
            }

            if (i > 0) {
                document.getElementById("referencias").innerHTML = "<b>REFERENCIAS:</b> " + i;
            } else {
                document.getElementById("referencias").innerHTML = "<b>REFERENCIAS:</b> Ninguna";
            }
            $("#referencias-modal").modal("hide");
        }

        function referencias_cancelar() {
            document.getElementById("referencias").innerHTML = "<b>REFERENCIAS:</b> Ninguna";

            referencia1 = true;
            document.getElementById("referencia1").disabled = referencia1;
            document.getElementById("ref1_docu").value = 0;
            document.getElementById("ref1_folio").value = "";
            document.getElementById("ref1_fecha").value = "";
            document.getElementById("ref1_razon").value = "";
            document.getElementById("ref1_check").checked = false;
            ref1 = [];

            referencia2 = true;
            document.getElementById("referencia2").disabled = referencia2;
            document.getElementById("ref2_docu").value = 0;
            document.getElementById("ref2_folio").value = "";
            document.getElementById("ref2_fecha").value = "";
            document.getElementById("ref2_razon").value = "";
            document.getElementById("ref2_check").checked = false;
            ref2 = [];

            referencia3 = true;
            document.getElementById("referencia3").disabled = referencia3;
            document.getElementById("ref3_docu").value = 0;
            document.getElementById("ref3_folio").value = "";
            document.getElementById("ref3_fecha").value = "";
            document.getElementById("ref3_razon").value = "";
            document.getElementById("ref3_check").checked = false;
            ref3 = [];
        }

        function enviar_correo_automatico(correo, tipo_doc, num_doc) {
            var url = "{{url('/enviarcorreo')}}";
            var parametros = { correo_destino: correo, tipo_doc: tipo_doc, num_doc: num_doc };

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            $.ajax({
                type: 'POST',
                url: url,
                data: parametros,
                success: function(resp) {
                    console.log('Correo automático: ' + resp);
                    Vue.swal({
                        text: 'Documento enviado al correo: ' + correo,
                        position: 'top-end',
                        icon: 'success',
                        toast: true,
                        showConfirmButton: false,
                        timer: 5000,
                    });
                },
                error: function(error) {
                    console.log('Error envío correo automático: ' + error.responseText);
                }
            });
        }

        function cargar_documentos_referencia() {
            var url = "{{ url('/dame_tipo_documentos') }}";
            $.ajax({
                type: 'GET',
                url: url,
                success: function(resp) {
                    resp.forEach(function(r) {
                        $("#ref1_docu").append('<option value="' + r.codigo_documento + '">' + r
                            .nombre_documento + '</option>');
                        $("#ref2_docu").append('<option value="' + r.codigo_documento + '">' + r
                            .nombre_documento + '</option>');
                        $("#ref3_docu").append('<option value="' + r.codigo_documento + '">' + r
                            .nombre_documento + '</option>');
                    });
                },
                error: function(error) {
                    console.log(formatear_error(error.responseText));
                    Vue.swal({
                        title: 'ERROR',
                        text: formatear_error(error.responseText),
                        icon: 'error',
                    });
                }

            });
        }
    </script>
@endsection

@section('style')
    <style>
        #numero_notas{
            font-size: 25px;
            padding: 10px;
        }

        #numero_notas a{
            color: white;
        }

        .contenedor-notas-venta {
            position: relative;
            min-height: 260px;
        }

        .carga-notas-ventas {
            position: absolute;
            z-index: 20;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .9);
            border: 1px solid #dee2e6;
            border-radius: .25rem;
        }

        .carga-notas-contenido {
            text-align: center;
            color: #495057;
        }

        .carga-notas-contenido i {
            display: block;
            margin-bottom: .75rem;
            font-size: 2rem;
            color: #28a745;
        }

        .estado-vacio-notas {
            min-height: 240px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            text-align: center;
        }

        .estado-vacio-notas > i {
            margin-bottom: .75rem;
            font-size: 2.5rem;
        }

        .panel-lateral-nota {
            padding: 16px;
            background: #f4f6f8;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        .panel-lateral-nota hr {
            margin: 14px 0;
            border-color: #dde3e9;
        }

        #el_cliente {
            margin: 0;
            padding: 0;
            background: #fff;
            border: 1px solid #e1e6eb;
            border-radius: 8px;
            overflow: hidden;
        }

        #el_cliente .titulazo {
            margin: 0 -15px 10px;
            padding: 12px 15px;
            color: #243447;
            background: #eef5fb;
            border-bottom: 1px solid #d9e7f2;
            font-size: 15px;
            font-weight: 700;
            text-align: left;
        }

        .tabla-cliente-nota {
            margin-bottom: 8px;
        }

        .tabla-cliente-nota td {
            padding: 8px 4px;
            vertical-align: middle;
            border-top-color: #edf0f2;
        }

        .tabla-cliente-nota td:first-child {
            width: 28%;
            color: #657180;
            font-weight: 600;
            background: transparent !important;
        }

        .tabla-cliente-nota select {
            min-height: 34px;
            border-color: #ced6de;
            border-radius: 6px;
            background: #fff;
        }

        .resumen-lateral {
            display: flex;
            align-items: center;
            min-height: 76px;
            padding: 10px;
            background: #fff;
            border: 1px solid #e1e6eb;
            border-radius: 8px;
        }

        .resumen-lateral img {
            width: 52px !important;
            height: 52px;
            object-fit: contain;
            margin-right: 10px;
        }

        .resumen-titulo-nota #info_titulo_nota_venta {
            min-height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff3bf;
            border-radius: 6px;
            color: #514500;
        }

        .resumen-numero-nota #info_nota_venta,
        .resumen-vendedor #info_vendedor {
            background: transparent !important;
        }

        .resumen-numero-nota .card,
        .resumen-numero-nota .card-header {
            width: 100%;
            border: 0;
            background: transparent;
        }

        .resumen-numero-nota .card-header {
            padding: 4px;
        }

        .resumen-numero-nota .card-title {
            margin: 0;
            line-height: 1.45;
        }

        .acciones-nota {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .acciones-nota .btn {
            min-height: 38px;
            border-radius: 6px;
        }

        .selector-documento-nota {
            margin: 0;
            padding: 12px 8px;
            background: #fff;
            border: 1px solid #e1e6eb;
            border-radius: 8px;
        }

        .formas-pago-nota {
            margin-top: 12px;
            padding: 12px;
            background: #fff;
            border: 1px solid #e1e6eb;
            border-radius: 8px;
        }

        .titulo-seccion-lateral {
            margin-bottom: 10px;
            color: #34495e;
            font-size: 13px;
            font-weight: 700;
        }

        .modal-editar-item-content {
            overflow: hidden;
            border: 0;
            border-radius: 12px;
            box-shadow: 0 20px 55px rgba(22, 35, 50, .25);
        }

        .modal-editar-item-header {
            align-items: center;
            padding: 18px 22px;
            color: #fff;
            background: linear-gradient(135deg, #2374ab, #55a8df);
            border-bottom: 0;
        }

        .modal-editar-item-header .close {
            color: #fff;
            opacity: .85;
            text-shadow: none;
        }

        .modal-editar-item-eyebrow {
            margin-bottom: 2px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .8;
        }

        .modal-editar-item-content .modal-body {
            padding: 22px;
        }

        .modal-editar-label {
            color: #455465;
            font-size: 12px;
            font-weight: 700;
        }

        .modal-editar-label i {
            width: 16px;
            color: #287fb8;
        }

        .modal-editar-item-content .form-control {
            height: 42px;
            border-color: #d5dce3;
            border-radius: 7px;
        }

        .modal-editar-item-content .form-control:focus {
            border-color: #4b9bd3;
            box-shadow: 0 0 0 .15rem rgba(75, 155, 211, .18);
        }

        .modal-editar-iva {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            background: #f7f9fb;
            border: 1px solid #e2e7ec;
            border-radius: 8px;
        }

        .modal-editar-item-content .modal-footer {
            padding: 14px 22px;
            background: #f8fafc;
            border-top-color: #e6ebef;
        }

        @media (max-width: 767px) {
            .panel-lateral-nota {
                margin-bottom: 16px;
            }

            .modal-editar-iva {
                align-items: flex-start;
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
@endsection

@section('contenido_titulo_pagina')
    @include('fragm.breadcrumb', ['titulo' => 'Ventas', 'titulo_modulo' => 'Ventas'])

@endsection
@section('contenido_ingresa_datos')
    <div class="container-fluid">
        <div class="nav d-flex justify-content-end">
            <div id="referencias"></div>
            <button type="button" class="btn btn-outline-primary" onclick="abrir_referencias()">Referencias</button>
            <button type="button" class="btn btn-danger" onclick="dame_carrito()">
                <i class="fa-solid fa-cart-shopping"></i>
                <span id="numero_notas"></span>
            </button>
        </div>
        <div class="row my-3">
            <div class="col-sm-12 col-md-3 panel-lateral-nota">
                <div class="row row-15" id="el_cliente">
                    <div id="mensaje-cliente"></div>

                    <div class="col-sm-12">
                        <p class="titulazo"><i class="fas fa-user mr-2"></i>Datos del cliente</p>

                        <input type="hidden" id="id_cliente" value="0">
                        <input type="hidden" id="cliente_id" value="0">
                        <input type="hidden" id="carrito_id_" value="0">
                        <table class="table letra_pequeña tabla-cliente-nota">

                            <tr>
                                <td style="background: #eee; ">Rut</td>
                                <td id="cliente_rut" style="color: gray;">SIN INFORMACION</td>
                            </tr>
                            <tr>
                                <td style="background: #eee; ">Nombre</td>
                                <td id="cliente_nombres" style="color: gray;">SIN INFORMACION</td>
                            </tr>
                            <tr>
                                <td style="background: #eee; ">Direccion</td>
                                <td id="cliente_direccion"><select name="cliente_direccion_select" id="cliente_direccion_select"
                                        class="form-control-sm w-100">
                                        <option value="0">Seleccione</option>
                                    </select></td>
                            </tr>
                            <tr>
                                <td style="background: #eee; ">Giro</td>
                                <td id="cliente_giro"><select name="" id="" class="form-control-sm w-100">
                                        <option value="0">Seleccione</option>
                                    </select></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="resumen-lateral resumen-titulo-nota">
                    <img src="{{ asset('storage/imagenes/listado.png') }}" alt="" style="width: 70px;">
                    <div id="info_titulo_nota_venta" class="p-2 text-center letra_pequeña w-100 badge badge-warning text-wrap"></div>
                </div>
                <hr>
                @if(Auth::user()->rol->nombrerol == 'Secretario' || Auth::user()->rol->nombrerol == 'Administrador')
                <div class="acciones-nota">
                    <button class="btn btn-primary btn-sm" onclick="confirmar_documento()"><i class="fas fa-check mr-1"></i> Procesar</button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="imprimir_para_bodega()"><i class="fas fa-print mr-1"></i> Imprimir</button>
                </div>

                @else
                <button class="btn btn-outline-dark btn-sm w-100 my-3" onclick="enviarNotaVenta()">ENVIAR</button>
                @endif
                <hr>
                <div class="resumen-lateral resumen-numero-nota">
                    <img src="{{ asset('storage/imagenes/caja_registradora.png') }}" alt="" style="width: 70px;">
                    <div id="info_nota_venta" class="p-2 text-center letra_pequeña w-100" style="background: #eee;"></div>
                </div>
                <hr>
                @if(Auth::user()->rol->nombrerol == 'Secretario' || Auth::user()->rol->nombrerol == 'Administrador')
                    <div class="row container-fluid selector-documento-nota">
                        <div class="col-sm-4" style="padding-left:2px"><strong>Documento:</strong></div>
                        <div class="col-sm-4" class="form-check form-check-inline" style="padding-left:2px">
                            <input type="radio" class="form-check-input" name="tipo_documento" id="radio_boleta"
                                value="boleta" onclick="que_documento('boleta')" checked>
                            <label for="radio_boleta" class="form-check-label">Boleta</label>
                        </div>
                        <div class="col-sm-4" class="form-check form-check-inline" style="padding-left:2px">
                            <input type="radio" class="form-check-input" name="tipo_documento" id="radio_factura"
                                value="factura" onclick="que_documento('factura')">
                            <label for="radio_factura" class="form-check-label">Factura</label>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3 text-center" style="padding-left:1px;padding-right:2px">
                            <strong>Venta:</strong>
                        </div>
                        <div class="col-sm-3 text-center" style="padding-left:1px;padding-right:2px"><input
                                type="radio" name="tipo_venta" value="contado" id="contado"
                                onclick="contado_credito(1)" checked><small>Contado</small></div>
                        <div class="col-sm-3 text-center" style="padding-left:1px;padding-right:2px"><input
                                type="radio" name="tipo_venta" value="credito" id="credito"
                                onclick="contado_credito(2)"><small>Crédito</small></div>
                        @if (Auth::user()->name == 'Matias Alfaro' || Auth::user()->rol->nombrerol === 'Administrador')
                            <div class="col-sm-3 text-center" style="padding-left:1px;padding-right:2px"><input
                                    type="radio" name="tipo_venta" value="delivery" id="delivery"
                                    onclick="contado_credito(3)"><small>Delivery</small></div>
                        @endif
                    </div>
                    <fieldset id="zona_formas_pago" class="formas-pago-nota">
                        <div class="titulo-seccion-lateral"><i class="fas fa-credit-card mr-2"></i>Formas de pago</div>
                        <div id="formas_pago"></div>
                    </fieldset>

                    <div class="row row-15" id="zona_descuento_input">
                        <div class="col-md-5">
                            <input type="checkbox" name="forma_descuento" id="forma_descuento"
                                onclick="activar_forma_pago(999)"> Descuento
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="" id="formita-descuento" class=""
                                style="width: 100%;" value="0" placeholder="% DCTO"
                                onkeypress="return soloNumeros(event)" disabled>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-sm" id="btn_aplicar_dcto"
                                onclick="calcular_sumatoria()" disabled>Aplicar</button>
                        </div>


                    </div>

                @endif

                <div class="resumen-lateral resumen-vendedor">
                    <img src="{{ asset('storage/imagenes/vendedor2.png') }}" alt="" style="width: 70px;">
                    <div id="info_vendedor" class="p-2 text-center letra_pequeña w-100" style="background: #eee; font-weight: bold; color: forestgreen;"></div>
                </div>



                <fieldset id="imagenes_pago">
                    <img src="" alt="" class="w-100">
                </fieldset>
            </div>
            <div class="col-sm-12 col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div id="estado_notas_ventas" class="alert py-2 px-3 mb-0 d-none" role="status"></div>
                    <div class="ml-auto">
                        <button type="button"
                            id="btn_agregar_repuesto_express_nota"
                            class="btn btn-success btn-sm d-none"
                            onclick="abrir_repuesto_express_nota()">
                            <i class="fas fa-plus-circle"></i> Agregar repuesto express
                        </button>
                    </div>
                </div>
                <div class="contenedor-notas-venta">
                    <div id="carga_notas_ventas" class="carga-notas-ventas d-none" aria-live="polite">
                        <div class="carga-notas-contenido">
                            <i class="fas fa-circle-notch fa-spin"></i>
                            <strong id="texto_carga_notas">Cargando...</strong>
                            <div class="small text-muted mt-1">Esto puede tardar unos segundos</div>
                        </div>
                    </div>
                    <div id="notas_ventas" class="tabla-scroll-y-600">
                        <div class="estado-vacio-notas">
                            <i class="fas fa-receipt"></i>
                            <p>Preparando notas de venta...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- MODAL REFERENCIAS -->
    <div class="modal fade" tabindex="-1" id="referencias-modal" data-backdrop="static"
    data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">REFERENCIAS</h5>
            </div>
            <div class="modal-body">
                <!-- el estilo de las clases referencia1,2 y 3 estan definidas en GuiaDespachoComponente por que lo compila hacia app.css laravel-mix -->
                <input type="checkbox" class="form-control form-control-sm ref_check" id="ref1_check"
                    onclick="activar_referencia(1)">
                <fieldset class="form-group referencia1" id="referencia1" disabled>
                    <select class="form-control form-control-sm ref_docu" id="ref1_docu">
                        <option value="0">Seleccione Documento</option>
                    </select>
                    <input class="form-control form-control-sm ref_folio" type="text" id="ref1_folio"
                        placeholder="Folio">
                    <input class="form-control form-control-sm ref_fecha" type="date" id="ref1_fecha">
                    <input class="form-control form-control-sm ref_razon" type="text" id="ref1_razon"
                        placeholder="Razón">
                </fieldset>
                <input type="checkbox" class="form-control form-control-sm ref_check" id="ref2_check"
                    onclick="activar_referencia(2)">
                <fieldset class="form-group referencia2" id="referencia2" disabled>
                    <select class="form-control form-control-sm ref_docu" id="ref2_docu">
                        <option value="0">Seleccione Documento</option>
                    </select>
                    <input class="form-control form-control-sm ref_folio" type="text" id="ref2_folio"
                        placeholder="Folio">
                    <input class="form-control form-control-sm ref_fecha" type="date" id="ref2_fecha">
                    <input class="form-control form-control-sm ref_razon" type="text" id="ref2_razon"
                        placeholder="Razón">
                </fieldset>
                <input type="checkbox" class="form-control form-control-sm ref_check" id="ref3_check"
                    onclick="activar_referencia(3)">
                <fieldset class="form-group referencia3" id="referencia3" disabled>
                    <select class="form-control form-control-sm ref_docu" id="ref3_docu">
                        <option value="0">Seleccione Documento</option>
                    </select>
                    <input class="form-control form-control-sm ref_folio" type="text" id="ref3_folio"
                        placeholder="Folio">
                    <input class="form-control form-control-sm ref_fecha" type="date" id="ref3_fecha">
                    <input class="form-control form-control-sm ref_razon" type="text" id="ref3_razon"
                        placeholder="Razón">
                </fieldset>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" data-dismiss="modal"
                    onclick="referencias_cancelar()">Cancelar</button>
                <button type="button" class="btn btn-outline-primary"
                    onclick="referencias_aceptar()">Aceptar</button>
            </div>
        </div>
    </div>
</div> <!-- FIN MODAL REFERENCIAS -->
<!-- MODAL AGREGAR REPUESTO EXPRESS A NOTA DE VENTA -->
<div class="modal fade" tabindex="-1" id="repuesto-express-nota-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-barcode"></i> Agregar repuesto express</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id_repuesto_express_nota">
                <label for="codigo_repuesto_express_nota">Código interno</label>
                <div class="input-group input-group-sm mb-3">
                    <input type="search"
                        class="form-control"
                        id="codigo_repuesto_express_nota"
                        placeholder="Ingrese código interno"
                        onkeyup="if (event.key === 'Enter') buscar_repuesto_express_nota()">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-success" onclick="buscar_repuesto_express_nota()">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
                <div id="resultado_repuesto_express_nota" class="border rounded p-3 mb-3">
                    <span class="text-muted">Ingrese un código interno para buscar.</span>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-7">
                        <label for="local_repuesto_express_nota">Ubicación</label>
                        <select id="local_repuesto_express_nota" class="form-control form-control-sm"></select>
                    </div>
                    <div class="form-group col-md-5">
                        <label for="cantidad_repuesto_express_nota">Cantidad</label>
                        <input type="number" min="1" value="1" id="cantidad_repuesto_express_nota"
                            class="form-control form-control-sm">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn_confirmar_repuesto_express_nota"
                    class="btn btn-success btn-sm" onclick="agregar_repuesto_express_nota()" disabled>
                    <i class="fas fa-plus"></i> Agregar al carrito
                </button>
            </div>
        </div>
    </div>
</div>
<!-- MODAL PROCESAR ENVIO -->
<div class="modal" tabindex="-1" id="procesar-envio-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="titulo_procesar_envio_modal"></h5>
            </div>
            <div class="modal-body">
                <h3 id="envio-txt">Generando XML...</h3>
                <button class="btn btn-outline-info form-control-sm" onclick="enviar_sii()"
                    id="btn-enviarsii"><small>Enviar al SII</small></button>
                <button class="btn btn-outline-dark form-control-sm" disabled onclick="ver_estadoUP()"
                    id="btn-verestado"><small>Ver Estado</small></button>
                <button class="btn btn-outline-success form-control-sm" disabled onclick="imprimir()"
                    id="btn-imprimir">Imprimir</button>
                <div id="cliente_xpress_div" style="display:none">
                    <!-- ESTE MISMO CODIGO ESTA EN EL MODAL DE CLIENTE XPRESS MAS ARRIBA -->
                    <br>
                    <hr>
                    <h4>CLIENTE XPRESS</h4>
                    <div class="form-group referencia1">
                        <label for="dcmto">Documento:</label>
                        <input class="form-control form-control-sm" type="text" id="dcmto_en_envio"
                            style="width:150px" maxlength="20" disabled>
                        <button type="button" class="btn btn-outline-primary form-control-sm"
                            onclick="cliente_xpress_guardar()">Guardar Cliente Xpress</button>
                    </div>
                    <fieldset class="form-group referencia1" id="cliente_xpress">
                        <input class="form-control form-control-sm ref_razon" style="width:120px"
                            type="text" id="cliente_xpress_rut_en_envio" placeholder="RUT solo números"
                            onKeyPress="return soloNumeros(event)">
                        <input class="form-control form-control-sm ref_razon" style="width:100px"
                            type="text" id="cliente_xpress_nombres_en_envio" placeholder="nombres">
                        <input class="form-control form-control-sm ref_razon" style="width:100px"
                            type="text" id="cliente_xpress_apellidos_en_envio" placeholder="apellidos">
                        <input class="form-control form-control-sm ref_razon" style="width:100px"
                            type="text" id="cliente_xpress_celular_en_envio" placeholder="Celular">
                        <input class="form-control form-control-sm ref_razon" style="width:100px"
                            type="email" id="cliente_xpress_correo_en_envio"
                            placeholder="Correo Electrónico" title="Holitaaas...">
                    </fieldset>
                    <div id="cliente_xpress_mensaje"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-success form-control-sm" onclick="abrir_cliente_xpress()"
                    id="boton_cliente_xpress_en_envio">Abrir Cliente Xpress</button>
                <button type="button" class="btn btn-outline-danger"
                    onclick="cerrar_procesar_envio()">Cerrar
                    Envio</button>
            </div>
        </div>
    </div>
</div> <!-- FIN MODAL PROCESAR ENVIO -->
<input type="hidden" name="items_carrito_" value="0">
@endsection
