@extends('plantillas.app')
@section('javascript')
<script>
    function editar_repuestos(){
        var file = document.getElementById('file').files[0];
        var formData = new FormData();
        formData.append('archivo', file);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        formData.append('_token', '{{ csrf_token() }}');
        $.ajax({
            url: "{{ route('editar-repuestos') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function() {
                Vue.swal({
                    title: 'Cargando',
                    text: 'Por favor espere...',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            success: function(response) {
                console.log(response);
                Vue.swal({
                    title: 'Éxito',
                    text: response.mensaje,
                    icon: 'success',
                    timer: 2000
                });
            },
            error: function(error) {
                console.log(error);
                Vue.swal({
                    title: 'Error',
                    text: error.responseText,
                    icon: 'error',
                });
            }
        });
    }
</script>
@endsection
@section('titulo','Edición de Repuestos')
@section('contenido_titulo_pagina')
  <div class="row titulazo" style="width: 100%;">
    <div class="col-sm-11" style="width:95%"><center><h4>Edicion de repuestos</h4></center></div>
    <div class="col-sm-1" style="width:5%"><abbr title="Agregar Sugerencias" style="border-bottom:none" id="campana"><img src="{{asset('storage/imagenes/foco-notification.png')}}" width="30px"/></abbr></div>
  </div>
@endsection

@section('contenido_ingresa_datos')
<div class="form-group">

    <input type="file" name="file" id="file" class="form-control-file" accept=".xlsx, .xls, .csv">
    <button class="btn btn-primary" type="button" onclick="editar_repuestos()">Subir</button>
</div>
@endsection
