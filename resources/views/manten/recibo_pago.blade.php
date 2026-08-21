@extends('plantillas.app')
@section('titulo','Recibo de Pago')
@section('javascript')
<script>
    window.onload = function(){
        $('#rut_Cliente').focus();
        let xpress_precio = document.getElementById('monto_pago');
        Inputmask({
            alias: 'numeric',
            groupSeparator: '.',
            autoGroup: true,
            digitsOptional: false,
            prefix: '',
            placeholder: '0',
            rightAlign: false,
            removeMaskOnSubmit: true
        }).mask(xpress_precio);
        Vue.swal({
            title: 'Bienvenido',
            text: 'Recibos de Pago EN CONSTRUCCION',
            icon: 'info',
        });
    }
    function confirmacion(){
      if (confirm('Esta seguro de eliminar el registro?')==true) {
        //alert('El registro ha sido  eliminado correctamente!! !');
        return true;
      }else{
        //alert('Cancelo la eliminacion');
        return false;
      }
    }

    function enter_buscar_cliente(event){
        if(event.keyCode==13){
            buscar_cliente();
        }
    }

    function buscar_cliente(){
        // validar el rut del cliente
        var rut_cliente = $('#rut_Cliente').val();
        if(rut_cliente.length<8){
            alert('El rut del cliente debe tener al menos 8 caracteres');
            return;
        }
        // validar que ingrese un rut
        if(rut_cliente.length==0){
            alert('Debe ingresar un rut de cliente');
            return;
        }

        // validar que ingrese un rut valido
        if(!$.isNumeric(rut_cliente)){
            alert('El rut del cliente debe ser numerico');
            return;
        }

        // buscar el cliente
        $.ajax({
            url: "{{ route('clientes.buscar_por_rut') }}",
            type: 'POST',
            data: {
                rut_cliente: rut_cliente,
                _token: "{{ csrf_token() }}"
            },
            beforeSend: function(){
                Vue.swal({
                    title: 'Buscando cliente',
                    text: 'Espere un momento',
                    onOpen: () => {
                        Vue.swal.showLoading();
                    }
                })
            },
            success: function(data){
                Vue.swal.close();
                console.log(data);
                if(data.cliente){
                    $('#nombre_cliente').html(data.cliente.empresa);
                    $('#rut_cliente').html(data.cliente.rut);
                    $('#razon_social_cliente').html(data.cliente.razon_social);
                    $('#direccion_cliente').html(data.cliente.direccion);
                    $('#comuna_cliente').html(data.cliente.comuna);
                    $('#ciudad_cliente').html(data.cliente.ciudad);
                    $('#telefono_cliente').html(data.cliente.telf1);
                    $('#giro_cliente').html(data.cliente.giro);

                    let pagos = data.pagos;
                    $('#tbody_recibos').html('');
                    pagos.forEach(pago => {
                        // dar formato de numero a los montos
                        pago.pago = parseFloat(pago.pago).toFixed(0);
                        pago.deuda = parseFloat(pago.deuda).toFixed(0);

                        // separador de miles
                        pago.pago = pago.pago.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        pago.deuda = pago.deuda.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        $('#tbody_recibos').append(`
                            <tr>
                                <td>${pago.referencia}</td>
                                <td>${pago.dte_tipo}</td>
                                <td>${pago.dte_fecha}</td>
                                <td>$ ${pago.pago}</td>
                                <td>${pago.tipo_pago}</td>
                                <td>${pago.nro_pago}</td>
                                <td>${pago.fecha_operacion}</td>
                                <td>$ ${pago.deuda}</td>
                                <td>
                                    <a href="#" class="btn btn-outline-danger btn-sm" onclick="return confirmacion()"><i class="fas fa-trash"></i> Eliminar</a>
                                </td>
                            </tr>
                        `);
                    });
                }else{
                    alert('Cliente no encontrado');
                }
            }
        });
    }

    function registrar_pago(){
        Vue.swal({
            title: '¿Registrar pago?',
                    text: "Se registrará el pago asociado al cliente",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, ¡Confirmar!'
        }).then((result) => {
            if (result.isConfirmed) {
                registrar_pago_confirmar();
            }
        });
    }

    function registrar_pago_confirmar(){
        // validar que ingrese un monto
        var monto_pago = document.getElementById('monto_pago');
        var rut = document.getElementById('rut_Cliente');
        console.log(monto_pago.value);

        var valido = 1;
        var mensaje = '';

        // validar que ingrese un rut
        if(rut.value.length==0){
            mensaje += '<li>Debe buscar cliente</li>';
            valido = 0;
        }

        var tipo_dte = $('input[name="tipo_dte"]:checked').val();

        // validar que seleccione un tipo de documento
        if(!tipo_dte){
            mensaje += '<li>Debe seleccionar un tipo de documento</li>';
            valido = 0;
        }

        var num_dte = $('#numero_dte').val();

        // validar que ingrese un numero de documento
        if(num_dte.length==0){
            mensaje += '<li>Debe ingresar un numero de documento</li>';
            valido = 0;
        }

        if(monto_pago.value.length==0){
            mensaje += '<li>Debe ingresar un monto de pago</li>';
            valido = 0;
        }
        let prec = 0;
        if (monto_pago.inputmask) {
            prec = monto_pago.inputmask.unmaskedvalue();
        } else {
            prec = monto_pago.value.trim();
        }
        // eliminar el punto final si lo tiene
        if(prec[prec.length-1]=='.'){
            prec = prec.substring(0, prec.length-1);
        }

        // validar que ingrese un monto valido
        if(!$.isNumeric(prec)){
            mensaje += '<li>El monto de pago debe ser numerico</li>';
            valido = 0;
        }

        if(valido==0){
            Vue.swal({
                title: 'Error',
                html: mensaje,
                icon: 'error'
            });
            return;
        }

        let data = {
            rut_cliente: $('#rut_Cliente').val(),
            monto_pago: prec,
            tipo_dte: tipo_dte,
            num_dte: num_dte,
            forma_pago: $('#formas_pago').val(),
            _token: "{{ csrf_token() }}"
        }

        let url = "{{ route('clientes.agregacuenta_vendedor') }}";

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            beforeSend: function(){
                Vue.swal({
                    title: 'Registrando pago',
                    text: 'Espere un momento',
                    onOpen: () => {
                        Vue.swal.showLoading();
                    }
                })
            },
            success: function(data){
                Vue.swal.close();
                console.log(data);
                if(data.mensaje == 'OK'){
                    Vue.swal({
                        title: 'Pago registrado',
                        text: 'El pago ha sido registrado correctamente',
                        icon: 'success'
                    });
                    $('#id_pago').val(data.cuenta.id);
                    //buscar_cliente();
                    // abrir modal de impresion
                    $('#modal_imprimir').modal('show');
                }else{
                    Vue.swal({
                        title: 'Error',
                        text: 'Ha ocurrido un error al registrar el pago',
                        icon: 'error'
                    });
                }
            }
        });
    }

    function imprimir_recibo_pago(){
        let id_pago = $('#id_pago').val();
        let url = "{{ route('clientes.imprimir_recibo_pago', ['id' => 'IDPAGO']) }}";
        url = url.replace('IDPAGO', id_pago);

        $.ajax({
            url: url,
            type: 'GET',
            beforeSend: function(){
                Vue.swal({
                    title: 'Imprimiendo recibo',
                    text: 'Espere un momento',
                    onOpen: () => {
                        Vue.swal.showLoading();
                    }
                })
            },
            success: function(data){
                Vue.swal.close();
                console.log(data);
                let r = JSON.parse(data);
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

                    // cerrar modal
                    $('#modal_imprimir').modal('hide');
            }
        });
    }
</script>
@endsection
@section('contenido_titulo_pagina')
    <center><h4 class="titulazo">Recibos de Pago</h4></center><br>
@endsection

@section('contenido_ver_datos')
<div class="container">
    <div class="row">
        <div class="col-md-3">
            <div class="mb-3">
                <h5 class="titulazo">Cliente</h5>
                <div class="form-group floating">
                    <input type="text" name="rut_Cliente" id="rut_Cliente" class="form-control floating" onkeydown="enter_buscar_cliente(event)">
                    <label for="rut_Cliente" class="floating">RUT</label>
                </div>
                <button class="btn btn-outline-success btn-sm w-100 my-2" onclick="buscar_cliente()"><i class="fas fa-search"></i> Buscar</button>
            </div>
            <div class="mb-3">
                    <h5 class="titulazo">Tipo DTE</h5>
                    @foreach($tipo_documento as $td)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_dte" id="tipo_dte{{ $td->id }}" value="{{ $td->id }}" >
                        <label class="form-check-label" for="tipo_dte{{ $td->id }}">
                            {{ $td->nombre_documento }}
                        </label>
                    </div>
                    @endforeach
                    <h5 class="titulazo mt-2">Detalle</h5>
                    <div class="form-group floating my-3">
                        <input type="text" name="numero_dte" id="numero_dte" class="form-control floating">
                        <label for="numero_dte" class="floating">N° DTE</label>
                    </div>
                    <div class="form-group floating mb-3">
                        <input type="text" name="monto_pago" id="monto_pago" class="form-control floating">
                        <label for="monto_pago" class="floating">Monto</label>
                    </div>
                    <div class="form-group floating mt-3">

                        <select class="form-control floating" id="formas_pago">
                            @foreach($formas_pago as $fp)
                            <option value="{{ $fp->id }}">{{ $fp->formapago }}</option>
                            @endforeach
                        </select>
                        <label for="formas_pago" class="floating">Formas Pago</label>
                    </div>
                    <button class="btn btn-outline-success btn-sm w-100" onclick="registrar_pago()">Registrar Pago</button>
            </div>

        </div>
        <div class="col-md-9">
            <h5 class="titulazo">Detalle</h5>
            <table class="table mb-2">
                <tbody>
                    <tr>
                        <td>RUT</td>
                        <td class="text-right" id="rut_cliente"></td>
                    </tr>
                    <tr>
                        <td>Nombre</td>
                        <td class="text-right" id="nombre_cliente"></td>
                    </tr>
                    <tr>
                        <td>Razón Social</td>
                        <td class="text-right" id="razon_social_cliente"></td>
                    </tr>
                    <tr>
                        <td>Dirección</td>
                        <td class="text-right" id="direccion_cliente"></td>
                    </tr>
                    <tr>
                        <td>Comuna</td>
                        <td class="text-right" id="comuna_cliente"></td>
                    </tr>
                    <tr>
                        <td>Ciudad</td>
                        <td class="text-right" id="ciudad_cliente"></td>
                    </tr>
                    <tr>
                        <td>Telefono</td>
                        <td class="text-right" id="telefono_cliente"></td>
                    </tr>
                    <tr>
                        <td>Giro</td>
                        <td class="text-right" id="giro_cliente"></td>
                    </tr>
                </tbody>
            </table>
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">N° DTE</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Monto</th>
                        <th scope="col">Tipo Pago</th>
                        <th scope="col">N° Pago</th>
                        <th scope="col">Fecha Pago</th>
                        <th scope="col">Saldo</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody_recibos">
                    @if(isset($recibos))
                        @foreach($recibos as $recibo)
                            <tr>
                                <td>{{ $recibo->dte_numero }}</td>
                                <td>{{ $recibo->dte_tipo }}</td>
                                <td>{{ $recibo->dte_fecha }}</td>
                                <td>{{ $recibo->dte_monto }}</td>
                                <td>{{ $recibo->tipo_pago }}</td>
                                <td>{{ $recibo->nro_pago }}</td>
                                <td>{{ $recibo->fecha_pago }}</td>
                                <td>{{ $recibo->saldo }}</td>
                                <td>
                                    <a href="#" class="btn btn-outline-warning btn-sm active" role="button"> <i class="fas fa-edit"></i>Editar</a>
                                    <a href="#" class="btn btn-outline-danger btn-sm" onclick="return confirmacion()"><i class="fas fa-trash"></i> Eliminar</a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- MODAL IMPRIMIR -->
<div class="modal fade" id="modal_imprimir" tabindex="-1" aria-labelledby="modal_imprimir" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h5 class="titulazo">Imprimir Recibo</h5>
                <p>¿Desea imprimir el recibo de pago?</p>
                <button class="btn btn-outline-success btn-sm" onclick="imprimir_recibo_pago()"><i class="fas fa-print"></i> Imprimir</button>
                <button class="btn btn-outline-danger btn-sm" data-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
            </div>
        </div>
    </div>
</div>
<!-- DATOS DE VITAL IMPORTANCIA -->
<input type="hidden" id="id_pago">
@endsection
