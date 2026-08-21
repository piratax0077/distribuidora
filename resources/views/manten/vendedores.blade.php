@extends('plantillas.app')
@section('titulo','Vendedores')
@section('javascript')
<script>

</script>
@endsection

@section('style')
 <style>
    .scrollable-modal-body {
        max-height: 400px; /* Altura máxima del modal-body */
        overflow-y: auto; /* Mostrar barra de scroll vertical si el contenido excede la altura */
    }
 </style>
@endsection

@section('contenido_ingresa_datos')
@include('fragm.breadcrumb',['titulo'=>'Vendedores','titulo_modulo'=>'Ventas','ruta_modulo'=>'Clientes'])
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 border p-2 tabla-scroll-y-500">

                @foreach($vendedores as $vendedor)
               <div class="d-flex letra_pequeña mb-2" style="color: gray">
                    <img src="{{url('usuarios/avatar/'.$vendedor->image_path)}}" alt="profile" style="width: 80px">
                    <a href="javascript:void(0)" onclick="cargar_vendedor({{ $vendedor->id }})" class="text-decoration-none">
                        <div class="ml-3 w-100 p-1" style="background: #eee">
                            <span>{{ $vendedor->name }}</span><br>
                            <span>{{ $vendedor->rut }}</span><br>
                            <span>{{ $vendedor->email }}</span><br>
                            <span>{{ $vendedor->telefono }}</span>
                        </div>
                    </a>

               </div>


                @endforeach

        </div>
        <div class="col-md-9">
            <div id="problemas"></div>
            <div id="contenedor_vendedor">

            </div>

        </div>
    </div>
</div>

<!-- modalConfirmacion -->

<div class="modal fade" id="modalConfirmacion" tabindex="-1" role="dialog" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmacionLabel">Confirmar Solicitud de Cambio de Precio</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="text-danger">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                    <div class="form-group floating">
                        <input type="time" name="duracion" id="duracion" value="{{ date('H:i', strtotime('+30 minutes')) }}" class="form-control floating">
                        <label for="duracion" class="floating">Hora finalizacion</label>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-outline-success" onclick="reconfirmarSolicitudCambioPrecio()">Aceptar</button>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="id_solicitud" id="id_solicitud" value="">
<input type="hidden" name="id_vendedor" id="id_vendedor" value="">
<script>
    function confirmarSolicitudCambioPrecio(id){
        console.log('aceptar solicitud '+id);
        // guardar el id de solicitud en un campo oculto
        $('#id_solicitud').val(id);
        // abrir modal de confirmacion
        $('#modalConfirmacion').modal('show');
    }

    function rechazarSolicitudCambioPrecio(id){
        console.log('rechazar solicitud '+id);
        let data = {
            id: id
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "{{url('repuesto/rechazar_solicitud_cambio_precio')}}",
            method: 'POST',
            data: data,
            success: function(response){
                console.log(response);
                let solicitud = response.solicitud;
                console.log(solicitud);
                $('#mensajes'+id).html('<div class="alert alert-danger">Solicitud rechazada</div>');

                var numero = "1234567890"; // reemplaza esto con el número de teléfono al que deseas enviar el mensaje
                var mensaje = "Hola, se ha rechazado la solicitud de cambio de precio."; // reemplaza esto con el mensaje que deseas enviar

                // codifica el mensaje para usarlo en una URL
                var mensajeCodificado = encodeURIComponent(mensaje);

                // crea la URL de WhatsApp
                var urlWhatsApp = "https://wa.me/" + numero + "?text=" + mensajeCodificado;

                // abre la URL de WhatsApp en una nueva ventana
                window.open(urlWhatsApp);

            },
            error: function(error){
                console.log(error);
                $('#mensajes').html('<div class="alert alert-danger">Error al rechazar la solicitud '+error.responseText+'</div>');
            }
        })
    }

    function reconfirmarSolicitudCambioPrecio(){
        let id = $('#id_solicitud').val();
        let duracion = $('#duracion').val();

        let data = {
            id: id,
            duracion: duracion
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "{{url('repuesto/confirmar_solicitud_cambio_precio')}}",
            method: 'POST',
            data: data,
            success: function(response){
                console.log(response.solicitud);
                let solicitud = response.solicitud;
                $('#modalConfirmacion').modal('hide');
                $('#mensajes'+id).html('<div class="alert alert-success">Solicitud confirmada hasta '+solicitud.duracion+'</div>');

                var numero = "1234567890"; // reemplaza esto con el número de teléfono al que deseas enviar el mensaje
                var mensaje = "Hola, este es un mensaje de prueba"; // reemplaza esto con el mensaje que deseas enviar

                // codifica el mensaje para usarlo en una URL
                var mensajeCodificado = encodeURIComponent(mensaje);

                // crea la URL de WhatsApp
                var urlWhatsApp = "https://wa.me/" + numero + "?text=" + mensajeCodificado;

                // abre la URL de WhatsApp en una nueva ventana
                window.open(urlWhatsApp);
            },
            error: function(error){
                console.log(error);
                $('#mensajes').html('<div class="alert alert-danger">Error al confirmar la solicitud '+error.responseText+'</div>');
            }
        })
    }

    function buscar_clientes(){
        var ax=document.getElementById("buscaxnombres").checked;
        var quien = "vendedor";
        if(ax)
        {
        var nombres=document.getElementById("buscado").value.trim();
        if(nombres.length==0)
        {
            Vue.swal({
                        text: 'Criterio Búsqueda Vacío. Ingrese NOMBRES',
                        position: 'top-end',
                        icon: 'error',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                        });
            $("#buscado").focus();
            return false;
        }
        var parametros={buscax:"nombres",buscado:nombres,quien:quien};
        }


        var bx=document.getElementById("buscaxrut").checked;

        if(bx) //Elegido el rut
        {
        //DEJAMOS SOLO NÚMEROS
        var ruut=document.getElementById("buscado");
        var rut=ruut.value.replace(/[^\d]/g, '');
        ruut.value=rut;
        if(rut.length==0)
        {
            Vue.swal({
                        text: 'Criterio Búsqueda Vacío. Ingrese RUT',
                        position: 'top-end',
                        icon: 'error',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                        });
            $("#buscado").focus();
            return false;
        }
        parametros=parametros={buscax:"rut",buscado:rut,quien:quien};
        }


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "{{url('clientes/buscar')}}",
            method: 'POST',
            data: parametros,
            beforeSend: function(){
                $('#problemas').html('<div class="alert alert-info">Buscando...</div>');
                Vue.swal({
                        text: 'Buscando...',
                        position: 'center',
                        icon: 'info',
                        showConfirmButton: false,
                });
            },
            success: function(response){
                Vue.swal.close();
                console.log(response);
                $('#problemas').html('');
                $('contenedor_clientes').empty();
                $('#contenedor_clientes').html(response);
            },
            error: function(error){
                console.log(error);
                $('#problemas').html(error.responseText);
            }
        })
    }

    function enter_buscar_clientes(){
        if(event.key === 'Enter'){
            buscar_clientes();
        }
    }

    function buscar_clientes_vendedor(){
        var ax=document.getElementById("buscaxnombres_").checked;
        var id_vendedor = $('#id_vendedor').val();
        var quien = "vendedor";
        if(ax)
        {
        var nombres=document.getElementById("buscado_").value.trim();
        if(nombres.length==0)
        {
            Vue.swal({
                        text: 'Criterio Búsqueda Vacío. Ingrese NOMBRES',
                        position: 'top-end',
                        icon: 'error',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                        });
            $("#buscado_").focus();
            return false;
        }
        var parametros={buscax:"nombres",buscado:nombres,quien:quien,id_vendedor:id_vendedor};
        }


        var bx=document.getElementById("buscaxrut_").checked;

        if(bx) //Elegido el rut
        {
        //DEJAMOS SOLO NÚMEROS
        var ruut=document.getElementById("buscado_");
        var rut=ruut.value.replace(/[^\d]/g, '');
        ruut.value=rut;
        if(rut.length==0)
        {
            Vue.swal({
                        text: 'Criterio Búsqueda Vacío. Ingrese RUT',
                        position: 'top-end',
                        icon: 'error',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                        });
            $("#buscado_").focus();
            return false;
        }
        parametros=parametros={buscax:"rut",buscado:rut,quien:quien,id_vendedor:id_vendedor};
        }


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "{{url('clientes/buscar')}}",
            method: 'POST',
            data: parametros,
            beforeSend: function(){
                $('#problemas').html('<div class="alert alert-info">Buscando...</div>');
                Vue.swal({
                        text: 'Buscando...',
                        position: 'center',
                        icon: 'info',
                        showConfirmButton: false,
                });
            },
            success: function(response){
                Vue.swal.close();
                console.log(response);
                $('#problemas').html('');
                $('contenedor_clientes_vendedor').empty();
                $('#contenedor_clientes_vendedor').html(response);
            },
            error: function(error){
                console.log(error);
                $('#problemas').html(error.responseText);
            }
        })
    }

    function enter_buscar_clientes_vendedor(){
        if(event.key === 'Enter'){
            buscar_clientes_vendedor();
        }
    }

    function cargar_vendedor(id){
        console.log('cargar vendedor '+id);
        // guardar el id del vendedor en un campo oculto
        $('#id_vendedor').val(id);
        let data = {
            id: id
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "{{url('vendedores/cargar')}}",
            method: 'POST',
            data: data,
            beforeSend: function(){
                $('#contenedor_vendedor').html('<div class="alert alert-info">Cargando...</div>');
            },
            success: function(response){
                console.log(response);
                $('#contenedor_vendedor').html(response);
            },
            error: function(error){
                console.log(error);
                $('#contenedor_vendedor').html('<div class="alert alert-danger">Error al cargar el vendedor '+error.responseText+'</div>');
            }
        })
    }

    function buscar_clientes_por_region(){
        var region = $('#region').val();
        var data = {
            region: region
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "{{url('clientes/buscar_por_region')}}",
            method: 'POST',
            data: data,
            beforeSend: function(){
                $('#contenedor_clientes').html('<div class="alert alert-info">Buscando...</div>');
            },
            success: function(response){
                console.log(response);
                $('#contenedor_clientes').html(response);
            },
            error: function(error){
                console.log(error);
                $('#contenedor_clientes').html('<div class="alert alert-danger">Error al buscar los clientes '+error.responseText+'</div>');
            }
        })
    }
</script>
@endsection
