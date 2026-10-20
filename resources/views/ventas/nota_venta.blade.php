@extends('plantillas.app')
@section('titulo','Nota de Venta')
@section('javascript')
<script>
    const PARAM_IVA = {{ $iva }};

    function escapeHtml(valor) {
        return String(valor ?? '').replace(/[&<>'"]/g, caracter => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
        })[caracter]);
    }

    function buscar_nota_venta() {
        let num = document.getElementById('numero_nv').value.trim();
        if (num === '' || isNaN(num)) {
            Vue.swal({ icon: 'error', text: 'Ingrese un número de nota de venta válido', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
            return;
        }

        var url = '{{ url("ventas/nota_venta/buscar") }}/' + num;
        $.ajax({
            type: 'GET',
            beforeSend: function () {
                Vue.swal({ title: 'Buscando...', icon: 'info', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false });
            },
            url: url,
            success: function (data) {
                Vue.swal.close();
                let rpta;
                try { rpta = JSON.parse(data); } catch (e) {
                    Vue.swal({ icon: 'error', text: 'Respuesta inválida del servidor', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                    return;
                }

                if (rpta.estado === 'OK') {
                    mostrar_nota_venta(rpta);
                } else {
                    $('#resultado_nv').html('');
                    Vue.swal({ icon: 'error', title: rpta.estado, text: rpta.mensaje });
                }
            },
            error: function (err) {
                Vue.swal.close();
                Vue.swal({ icon: 'error', title: 'ERROR', text: String(err.responseText).substring(0, 300) });
            }
        });
    }

    function mostrar_nota_venta(rpta) {
        let nv = rpta.nota_venta;
        let detalle = rpta.detalle;
        let seguimiento = rpta.seguimiento || {};
        let estado_actual = rpta.estado_nv; // 1=activo, 0=deshabilitado
        let btn_color = estado_actual == 1 ? 'btn-danger' : 'btn-success';
        let btn_texto = estado_actual == 1 ? 'Deshabilitar Nota de Venta' : 'Habilitar Nota de Venta';
        let badge = estado_actual == 1
            ? '<span class="badge badge-success">ACTIVA</span>'
            : '<span class="badge badge-secondary">DESHABILITADA</span>';

        let html = `
        <div class="card mt-2">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><strong>Nota de Venta N° ${nv.numero_carrito}</strong> &nbsp; ${badge}</span>
                <span>Cliente: <strong>${nv.cliente ?? '-'}</strong></span>
                <span>Fecha: <strong>${nv.fecha_emision ?? '-'}</strong></span>
                <button class="btn btn-sm ${btn_color}" onclick="confirmar_cambio_estado(${nv.numero_carrito}, ${estado_actual})">
                    ${btn_texto}
                </button>
            </div>
            <div class="card-body">
                <div class="alert alert-light border mb-3">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="font-weight-bold">Estado del pedido</label>
                            <select id="estado_pedido_nv" class="form-control">${opcionesEstado(rpta.opciones_estados.pedido, seguimiento.estado_pedido)}</select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="font-weight-bold"><i class="fas fa-credit-card mr-1"></i> Estado del pago</label>
                            <select id="estado_pago_nv" class="form-control" onchange="actualizarVistaEstadoPago()">${opcionesEstado(rpta.opciones_estados.pago, seguimiento.estado_pago)}</select>
                            <div id="resumen_estado_pago_nv" class="mt-2"></div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="font-weight-bold">Estado de entrega</label>
                            <select id="estado_entrega_nv" class="form-control">${opcionesEstado(rpta.opciones_estados.entrega, seguimiento.estado_entrega)}</select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label>Tipo de entrega</label>
                            <select id="tipo_entrega_nv" class="form-control">
                                <option value="">Sin especificar</option>
                                <option value="domicilio" ${seguimiento.tipo_entrega === 'domicilio' ? 'selected' : ''}>Envío a domicilio</option>
                                <option value="retiro" ${seguimiento.tipo_entrega === 'retiro' ? 'selected' : ''}>Retiro en tienda</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label>Transportista</label>
                            <input id="transportista_nv" class="form-control" maxlength="100" value="${escapeHtml(seguimiento.transportista)}">
                        </div>
                        <div class="col-md-5 mb-2">
                            <label>Número de seguimiento</label>
                            <input id="numero_seguimiento_nv" class="form-control" maxlength="150" value="${escapeHtml(seguimiento.numero_seguimiento)}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Mensaje visible para el cliente</label>
                            <textarea id="observacion_cliente_nv" class="form-control" rows="2">${escapeHtml(seguimiento.observacion_cliente)}</textarea>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Observación interna</label>
                            <textarea id="observacion_interna_nv" class="form-control" rows="2">${escapeHtml(seguimiento.observacion_interna)}</textarea>
                        </div>
                    </div>
                    <div class="text-right mt-2">
                        <button class="btn btn-primary" onclick="guardar_seguimiento_nota(${Number(nv.numero_carrito)})"><i class="fas fa-save"></i> Guardar seguimiento</button>
                    </div>
                </div>
                <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Ítem</th>
                            <th>Cód. Interno</th>
                            <th>Descripción</th>
                            <th class="text-right">Cant.</th>
                            <th class="text-right">P.U.</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>`;

        let total_nv = 0;
        detalle.forEach(d => {
            let total_fmt = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(d.total_item);
            let pu_fmt = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(d.pu);
            html += `
                        <tr>
                            <td>${d.item}</td>
                            <td>${d.codigo_interno ?? '-'}</td>
                            <td>${d.descripcion ?? '-'}</td>
                            <td class="text-right">${d.cantidad}</td>
                            <td class="text-right">${pu_fmt}</td>
                            <td class="text-right">${total_fmt}</td>
                        </tr>`;
            total_nv += parseFloat(d.total_item);
        });

        let neto     = Math.round(total_nv);
        let iva      = Math.round(neto * PARAM_IVA);
        let total_con_iva = neto + iva;
        let fmt = v => new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(v);

        html += `
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right">Neto</td>
                            <td class="text-right">${fmt(neto)}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-right">IVA (${Math.round(PARAM_IVA*100)}%)</td>
                            <td class="text-right">${fmt(iva)}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-right"><strong>Total</strong></td>
                            <td class="text-right"><strong>${fmt(total_con_iva)}</strong></td>
                        </tr>
                    </tfoot>
                </table>
                </div>
            </div>
        </div>`;

        $('#resultado_nv').html(html);
        actualizarVistaEstadoPago();
    }

    function etiquetaEstado(codigo) {
        const etiquetas = {
            recibido: 'Solicitud recibida', revision: 'En revisión', confirmado: 'Confirmado',
            rechazado: 'Rechazado', cancelado: 'Cancelado', pendiente: 'Pendiente',
            validando: 'En validación', pagado: 'Pagado', reembolsado: 'Reembolsado',
            preparando: 'Preparando pedido', listo_retiro: 'Listo para retiro',
            despachado: 'Despachado', entregado: 'Entregado', incidencia: 'Incidencia'
        };
        return etiquetas[codigo] || codigo;
    }

    function opcionesEstado(opciones, actual) {
        return (opciones || []).map(codigo =>
            `<option value="${codigo}" ${codigo === actual ? 'selected' : ''}>${etiquetaEstado(codigo)}</option>`
        ).join('');
    }

    function actualizarVistaEstadoPago() {
        const estado = $('#estado_pago_nv').val() || 'pendiente';
        const estilos = {
            pendiente: ['badge-warning', 'fa-clock', 'Pendiente de confirmación'],
            validando: ['badge-info', 'fa-search-dollar', 'Comprobante o transacción en revisión'],
            pagado: ['badge-success', 'fa-check-circle', 'Pago confirmado'],
            rechazado: ['badge-danger', 'fa-times-circle', 'Pago rechazado'],
            reembolsado: ['badge-secondary', 'fa-undo', 'Pago reembolsado']
        };
        const configuracion = estilos[estado] || estilos.pendiente;
        $('#resumen_estado_pago_nv').html(`<span class="badge ${configuracion[0]} p-2"><i class="fas ${configuracion[1]} mr-1"></i>${configuracion[2]}</span>`);
    }

    function guardar_seguimiento_nota(numeroCarrito) {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        $.ajax({
            type: 'POST',
            url: '{{ url("ventas/nota_venta/actualizar_seguimiento") }}',
            data: {
                numero_carrito: numeroCarrito,
                estado_pedido: $('#estado_pedido_nv').val(),
                estado_pago: $('#estado_pago_nv').val(),
                estado_entrega: $('#estado_entrega_nv').val(),
                tipo_entrega: $('#tipo_entrega_nv').val(),
                transportista: $('#transportista_nv').val().trim(),
                numero_seguimiento: $('#numero_seguimiento_nv').val().trim(),
                observacion_cliente: $('#observacion_cliente_nv').val().trim(),
                observacion_interna: $('#observacion_interna_nv').val().trim()
            },
            beforeSend: function () {
                Vue.swal({ title: 'Guardando seguimiento...', allowOutsideClick: false, showConfirmButton: false });
            },
            success: function (rpta) {
                Vue.swal({ icon: 'success', text: rpta.mensaje, toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                buscar_nota_venta();
            },
            error: function (xhr) {
                const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No se pudo actualizar el seguimiento.';
                Vue.swal({ icon: 'error', title: 'No se pudo guardar', text: mensaje });
            }
        });
    }

    function confirmar_cambio_estado(numero_carrito, estado_actual) {
        let nueva_accion = estado_actual == 1 ? 'deshabilitar' : 'habilitar';
        Vue.swal({
            title: '¿Está seguro?',
            text: 'Va a ' + nueva_accion + ' la Nota de Venta N° ' + numero_carrito,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ' + nueva_accion,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                cambiar_estado_nota_venta(numero_carrito, estado_actual == 1 ? 0 : 1);
            }
        });
    }

    function cambiar_estado_nota_venta(numero_carrito, nuevo_estado) {
        var url = '{{ url("ventas/nota_venta/cambiar_estado") }}';
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        $.ajax({
            type: 'POST',
            url: url,
            data: { numero_carrito: numero_carrito, estado: nuevo_estado },
            beforeSend: function () {
                Vue.swal({ title: 'Procesando...', icon: 'info', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false });
            },
            success: function (data) {
                Vue.swal.close();
                let rpta;
                try { rpta = JSON.parse(data); } catch (e) {
                    Vue.swal({ icon: 'error', text: 'Respuesta inválida del servidor', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                    return;
                }
                if (rpta.estado === 'OK') {
                    Vue.swal({ icon: 'success', text: rpta.mensaje, toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                    buscar_nota_venta(); // recargar la vista
                } else {
                    Vue.swal({ icon: 'error', title: rpta.estado, text: rpta.mensaje });
                }
            },
            error: function (err) {
                Vue.swal.close();
                Vue.swal({ icon: 'error', title: 'ERROR', text: String(err.responseText).substring(0, 300) });
            }
        });
    }

    function soloNumeros(e) {
        var key = window.Event ? e.which : e.keyCode;
        return (key >= 48 && key <= 57) || key == 8;
    }
</script>
@endsection

@section('contenido_titulo_pagina')
    @include('fragm.breadcrumb', ['titulo' => "Nota de Venta", 'titulo_modulo' => "Ventas", 'ruta_modulo' => "/ventas"])
@endsection

@section('contenido_ingresa_datos')
<div class="row ml-1 w-100 mt-2">
    <div class="col-sm-5 col-md-4 col-lg-3">
        <div class="input-group">
            <input type="text" id="numero_nv" class="form-control" placeholder="N° Nota de Venta"
                onKeyPress="return soloNumeros(event)"
                onKeyDown="if(event.keyCode==13) buscar_nota_venta()">
            <div class="input-group-append">
                <button class="btn btn-primary" onclick="buscar_nota_venta()">Buscar</button>
            </div>
        </div>
    </div>
</div>

<div class="row ml-1 w-100 mt-2">
    <div class="col-12" id="resultado_nv">
    </div>
</div>
@endsection
