@extends('plantillas.app')



@section('titulo','Devolución de mercadería')

@section('javascript')
<script type="text/javascript">

function mostrarError(mensaje){
  Vue.swal({
    icon: 'error',
    title: 'No se pudo continuar',
    text: mensaje
  });
}

function mostrarCargando(mensaje){
  Vue.swal({
    title: mensaje || 'Procesando...',
    text: 'Espere un momento.',
    icon: 'info',
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => {
      Vue.swal.showLoading();
    }
  });
}

function obtenerMensajeError(xhr, mensajeDefecto) {
    if (xhr && xhr.responseJSON) {
        const respuesta = xhr.responseJSON;

        if (respuesta.errors) {
            const mensajes = Object.values(respuesta.errors)
                .flat()
                .filter(Boolean);

            if (mensajes.length > 0) {
                return mensajes.join('\n');
            }
        }

        return respuesta.error ||
            respuesta.mensaje ||
            respuesta.message ||
            mensajeDefecto;
    }

    return mensajeDefecto;
}

function cargar_documento(tipo_busqueda){
  let numeroDocumento = '';
  let tipoDocumento = '';
  let url = '';

  if(tipo_busqueda === 1){
    numeroDocumento = $.trim($('#numero_nc').val());

    if(numeroDocumento === ''){
      mostrarError('Debe ingresar un número de nota de crédito.');
      $('#numero_nc').focus();
      return;
    }

    url = '/guiadespacho/detalle_nc_nueva/' + encodeURIComponent(numeroDocumento);
  }else{
    numeroDocumento = $.trim($('#numero_doc').val());
    tipoDocumento = $('input[name="exampleRadios"]:checked').val();

    if(numeroDocumento === ''){
      mostrarError('Debe ingresar un número de boleta o factura.');
      $('#numero_doc').focus();
      return;
    }

    if(!tipoDocumento){
      mostrarError('Debe seleccionar el tipo de documento.');
      return;
    }

    url = '/guiadespacho/detalle_doc_nuevo/' + encodeURIComponent(numeroDocumento) + '/' + encodeURIComponent(tipoDocumento);
  }

  $.ajax({
    type: 'get',
    url: url,
    beforeSend: function(){
      mostrarCargando('Cargando documento');
      $('#contenido').empty();
      $('#btn-imprimir').prop('disabled', true);
    },
    success: function(resp){
      Vue.swal.close();

      if(resp && resp.error){
        mostrarError(resp.error);
        return;
      }

      let contenido = (resp && typeof resp === 'object' && resp.html !== undefined)
        ? resp.html
        : resp;

      $('#contenido').html(contenido);
    },
    error: function(xhr){
      Vue.swal.close();
      $('#contenido').empty();
      mostrarError(obtenerMensajeError(xhr, 'No fue posible cargar el documento.'));
    }
  });
}

function soloNumeros(e){
  const key = e.which || e.keyCode;
  return (key >= 48 && key <= 57) || key === 8 || key === 9 || key === 13;
}

function obtenerNombreDestino(select){
  if(!select || !select.length){
    return 'No especificado';
  }

  const texto = select.find('option:selected').text();
  return $.trim(texto) !== '' ? $.trim(texto) : 'No especificado';
}

function confirmar_devolucion(repuesto_id, doc_index){
  const prefijo = (typeof doc_index !== 'undefined' && doc_index !== null && doc_index !== '')
    ? doc_index + '-'
    : '';

  const cantidadInput = $('#cantidad-' + prefijo + repuesto_id);
  const localSelect = $('#local_id-' + prefijo + repuesto_id);
  const cantidadVendidaInput = $('#merma-' + prefijo + repuesto_id);

  const cantidad = Number(cantidadInput.val());
  const cantidadVendida = Number(cantidadVendidaInput.val());
  const local_id = localSelect.val();

  if(!Number.isInteger(cantidad) || cantidad <= 0){
    mostrarError('Debe ingresar una cantidad válida mayor a cero.');
    cantidadInput.focus();
    return;
  }

  if(!Number.isFinite(cantidadVendida) || cantidadVendida <= 0){
    mostrarError('No fue posible determinar la cantidad vendida del repuesto.');
    return;
  }

  if(cantidad > cantidadVendida){
    mostrarError('No puede devolver una cantidad mayor a la vendida.');
    cantidadInput.focus();
    return;
  }

  if(!local_id){
    mostrarError('Debe seleccionar una ubicación de destino.');
    localSelect.focus();
    return;
  }

  const numNcOculto = $('#num_nc').length ? $.trim($('#num_nc').val()) : '';
  const numDocOculto = $('#num_doc').length ? $.trim($('#num_doc').val()) : '';
  const numeroDocumentoBusqueda = $.trim($('#numero_doc').val());
  const tipoDocumentoSeleccionado = $('input[name="exampleRadios"]:checked').val();

  let numeroDocumento = '';
  let tipoDocumento = '';

  if(numNcOculto !== ''){
    numeroDocumento = numNcOculto;
    tipoDocumento = 'nc';
  }else if(numDocOculto !== ''){
    numeroDocumento = numDocOculto;
    tipoDocumento = tipoDocumentoSeleccionado;
  }else{
    numeroDocumento = numeroDocumentoBusqueda;
    tipoDocumento = tipoDocumentoSeleccionado;
  }

  if(numeroDocumento === '' || !tipoDocumento){
    mostrarError('No fue posible identificar el documento asociado a la devolución.');
    return;
  }

  const nombreDestino = obtenerNombreDestino(localSelect);
  const diferencia = cantidadVendida - cantidad;
  const tipoDocumentoTexto = tipoDocumento === 'nc'
    ? 'Nota de crédito'
    : (tipoDocumento === 'fa' ? 'Factura' : 'Boleta');

  Vue.swal({
    title: '¿Confirmar devolución?',
    icon: 'warning',
    html: `
      <div class="text-left">
        <table class="table table-sm table-bordered mb-3">
          <tbody>
            <tr><th>Documento</th><td>${tipoDocumentoTexto} N° ${numeroDocumento}</td></tr>
            <tr><th>Cantidad vendida</th><td>${cantidadVendida}</td></tr>
            <tr><th>Cantidad a devolver</th><td><strong>${cantidad}</strong></td></tr>
            <tr><th>Saldo posterior</th><td>${diferencia}</td></tr>
            <tr><th>Destino</th><td>${nombreDestino}</td></tr>
          </tbody>
        </table>
        <div class="alert alert-warning py-2 mb-0">
          La devolución actualizará inmediatamente el stock del repuesto.
        </div>
      </div>
    `,
    showCancelButton: true,
    confirmButtonColor: '#198754',
    cancelButtonColor: '#dc3545',
    confirmButtonText: 'Sí, devolver',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  }).then((result) => {
    if(!result.isConfirmed){
      return;
    }

    const data = {
      repuesto_id: repuesto_id,
      cantidad: cantidad,
      local_id: local_id,
      num_nc: numeroDocumento,
      tipo_doc: tipoDocumento,
      doc_index: (typeof doc_index !== 'undefined' ? doc_index : null)
    };

    console.log(data);

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $.ajax({
      type: 'post',
      data: data,
      url: '/guiadespacho/devolucion',
      beforeSend: function(){
        mostrarCargando('Procesando devolución');
      },
      success: function(resp){
        console.log('servidor '+resp);
        Vue.swal.close();

        if(resp && resp.error){
          mostrarError(resp.error);
          return;
        }

        const html = resp && resp.html !== undefined ? resp.html : resp;
        const tableId = prefijo !== ''
          ? '#table_devolucion_' + String(doc_index)
          : '#table_devolucion';

        if($(tableId).length){
          $(tableId).html(html);
        }else{
          $('#contenido').html(html);
        }

        cantidadVendidaInput.val(resp.cantidad_disponible);
        cantidadInput.attr('max', resp.cantidad_disponible).val(1);

        Vue.swal({
          icon: 'success',
          title: 'Devolución registrada',
          text: 'El stock fue actualizado correctamente.',
          timer: 2500,
          showConfirmButton: false
        });
      },
      error: function(xhr){
        Vue.swal.close();
        mostrarError(obtenerMensajeError(xhr, 'Error al procesar la devolución.'));
      }
    });
  });
}

function eliminar_devolucion(idDevolucion, docIndex){
  Vue.swal({
    title: '¿Quitar producto?',
    text: 'El producto se eliminará de la lista de esta devolución.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sí, quitar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  }).then((result) => {
    if(!result.isConfirmed){
      return;
    }

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $.ajax({
      type: 'post',
      url: '/guiadespacho/eliminar_devolucion',
      data: {
        id_devolucion: idDevolucion,
        doc_index: docIndex
      },
      beforeSend: function(){
        mostrarCargando('Quitando producto');
      },
      success: function(resp){
        Vue.swal.close();

        if(!resp || resp.success !== true){
          mostrarError((resp && resp.message) || 'No fue posible quitar el producto.');
          return;
        }

        const tieneIndice = docIndex !== null && typeof docIndex !== 'undefined';
        const tableId = tieneIndice
          ? '#table_devolucion_' + String(docIndex)
          : '#table_devolucion';

        $(tableId).html(resp.html);

        const prefijo = tieneIndice ? String(docIndex) + '-' : '';
        const disponibleInput = $('#merma-' + prefijo + resp.repuesto_id);
        const cantidadInput = $('#cantidad-' + prefijo + resp.repuesto_id);
        const nuevaDisponibilidad = Number(resp.cantidad_disponible || 0);

        disponibleInput.val(nuevaDisponibilidad);
        cantidadInput.attr('max', nuevaDisponibilidad).val(1).prop('disabled', false);

        Vue.swal({
          icon: 'success',
          title: 'Producto quitado',
          text: resp.message,
          timer: 1800,
          showConfirmButton: false
        });
      },
      error: function(xhr){
        Vue.swal.close();
        mostrarError(obtenerMensajeError(xhr, 'No fue posible quitar el producto.'));
      }
    });
  });
}

function cerrar_devolucion(num_nc){
  const ncField = $('#num_nc');
  const docField = $('#num_doc');
  const tipoDocumento = $('input[name="exampleRadios"]:checked').val();

  let url = '';

  if(ncField.length && $.trim(ncField.val()) !== ''){
    url = '/guiadespacho/cerrar_devolucion/' + encodeURIComponent($.trim(ncField.val()));
  }else if(docField.length && $.trim(docField.val()) !== ''){
    url = '/guiadespacho/cerrar_devolucion_/' + encodeURIComponent($.trim(docField.val())) + '/' + encodeURIComponent(tipoDocumento);
  }else if(typeof num_nc !== 'undefined' && $.trim(String(num_nc)) !== ''){
    url = '/guiadespacho/cerrar_devolucion/' + encodeURIComponent($.trim(String(num_nc)));
  }else{
    const numeroDocumento = $.trim($('#numero_doc').val());

    if(numeroDocumento === '' || !tipoDocumento){
      mostrarError('No fue posible identificar el documento que desea cerrar.');
      return;
    }

    url = '/guiadespacho/cerrar_devolucion_/' + encodeURIComponent(numeroDocumento) + '/' + encodeURIComponent(tipoDocumento);
  }

  Vue.swal({
    title: '¿Cerrar devolución?',
    text: 'Se finalizará el proceso y se preparará el comprobante.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, cerrar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#198754',
    cancelButtonColor: '#6c757d',
    reverseButtons: true
  }).then((result) => {
    if(!result.isConfirmed){
      return;
    }

    $.ajax({
      type: 'get',
      url: url,
      beforeSend: function(){
        mostrarCargando('Cerrando devolución');
      },
      success: function(resp){
        Vue.swal.close();

        const estadoOk = Array.isArray(resp)
          ? resp[0] === 'OK'
          : resp && (resp.estado === 'OK' || resp.estado === 1);

        if(!estadoOk){
          mostrarError((resp && resp.mensaje) ? resp.mensaje : 'No existen repuestos ingresados.');
          return;
        }

        const contenido = Array.isArray(resp) ? resp[1] : (resp.html || resp.mensaje || '');
        $('#listado_repuestos_devolucion').html(contenido);
        $('#exampleModal').modal('show');
      },
      error: function(xhr){
        Vue.swal.close();
        mostrarError(obtenerMensajeError(xhr, 'No fue posible cerrar la devolución.'));
      }
    });
  });
}

function imprimir_devolucion(num_nc){
  if(!num_nc){
    mostrarError('No fue posible identificar la devolución que desea imprimir.');
    return;
  }

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  $.ajax({
    type: 'post',
    data: { num_nc: num_nc },
    url: '/imprimir_devolucion',
    beforeSend: function(){
      mostrarCargando('Generando comprobante');
    },
    success: function(resp){
      Vue.swal.close();

      let resultado = resp;
      if(typeof resp === 'string'){
        try{
          resultado = JSON.parse(resp);
        }catch(e){
          mostrarError('La respuesta del servidor no tiene un formato válido.');
          return;
        }
      }

      if(resultado.estado === 'OK'){
        const config = 'location=yes,height=570,width=720,scrollbars=yes,status=yes';
        const ventana = window.open(resultado.mensaje, '_blank', config);

        if(ventana){
          ventana.focus();
        }else{
          mostrarError('El navegador bloqueó la ventana de impresión. Permita las ventanas emergentes e intente nuevamente.');
        }
      }else{
        mostrarError(resultado.mensaje || 'No fue posible generar el comprobante.');
      }
    },
    error: function(xhr){
      Vue.swal.close();
      mostrarError(obtenerMensajeError(xhr, 'No fue posible generar el comprobante.'));
    }
  });
}

$(document).on('keypress', '#numero_nc, #numero_doc', function(e){
  if(e.which === 13){
    e.preventDefault();
    cargar_documento(this.id === 'numero_nc' ? 1 : 2);
  }
});

</script>
@endsection



@section('style')
<style>
  .devolucion-card {
    border: 1px solid #dee2e6;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 3px 12px rgba(0, 0, 0, .08);
    height: 100%;
  }

  .devolucion-card__header {
    padding: 16px 18px;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
    background: #f8f9fa;
    border-radius: 12px 12px 0 0;
  }

  .devolucion-card__body {
    padding: 20px;
  }

  .devolucion-logo {
    width: 70px;
    height: 70px;
    object-fit: contain;
  }

  .document-type-group {
    display: flex;
    align-items: center;
    gap: 18px;
    min-height: 38px;
  }

  #contenido {
    margin-top: 24px;
    border-radius: 12px;
    min-height: 70px;
  }

  #contenido:not(:empty) {
    padding: 18px;
    border: 1px solid #bee5eb;
    background: #f4fcfd;
  }

  .modal-body-alto {
    max-height: calc(100vh - 160px);
    overflow-y: auto;
  }

  #info_devolucion {
    width: 100%;
    max-width: 400px;
    background: #eee;
  }

  @media (max-width: 767.98px) {
    .devolucion-card {
      margin-bottom: 18px;
      height: auto;
    }

    .devolucion-logo {
      margin-bottom: 12px;
    }

    .document-type-group {
      flex-wrap: wrap;
      margin-bottom: 12px;
    }
  }
</style>
@endsection



@section('contenido_titulo_pagina')



  @include('fragm.breadcrumb',['titulo'=>'Devolución de mercadería','titulo_modulo'=>'Inventario','ruta_modulo'=>'/inventario'])



@endsection

@section('mensajes')

  @include('fragm.mensajes')

@endsection

@section('contenido_ingresa_datos')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-6 mb-3">
      <div class="devolucion-card">
        <div class="devolucion-card__header">
          <i class="fas fa-file-invoice-dollar mr-2"></i>
          Devolución por Nota de Crédito
        </div>

        <div class="devolucion-card__body">
          <div class="row align-items-end">
            <div class="col-md-2 text-center">
              <img src="{{asset('storage/imagenes/ips_logo.png')}}" alt="Logo" class="devolucion-logo">
            </div>

            <div class="col-md-7">
              <div class="form-group mb-md-0">
                <label for="numero_nc" class="font-weight-bold">N° de Nota de Crédito</label>
                <input
                  type="text"
                  name="numero_nc"
                  id="numero_nc"
                  class="form-control"
                  inputmode="numeric"
                  autocomplete="off"
                  onkeypress="return soloNumeros(event)"
                  placeholder="Ingrese el folio"
                >
              </div>
            </div>

            <div class="col-md-3">
              <button type="button" class="btn btn-success btn-block" onclick="cargar_documento(1)">
                <i class="fas fa-search mr-1"></i> Buscar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 mb-3">
      <div class="devolucion-card">
        <div class="devolucion-card__header">
          <i class="fas fa-receipt mr-2"></i>
          Devolución por Documento de Venta
        </div>

        <div class="devolucion-card__body">
          <div class="row align-items-end">
            <div class="col-md-2 text-center">
              <img src="{{asset('storage/imagenes/ips_logo.png')}}" alt="Logo" class="devolucion-logo">
            </div>

            <div class="col-md-4">
              <label class="font-weight-bold d-block">Tipo de documento</label>
              <div class="document-type-group">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="bo" checked>
                  <label class="form-check-label" for="exampleRadios1">Boleta</label>
                </div>

                <div class="form-check">
                  <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="fa">
                  <label class="form-check-label" for="exampleRadios2">Factura</label>
                </div>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group mb-md-0">
                <label for="numero_doc" class="font-weight-bold">N° documento</label>
                <input
                  type="text"
                  name="numero_doc"
                  id="numero_doc"
                  class="form-control"
                  inputmode="numeric"
                  autocomplete="off"
                  onkeypress="return soloNumeros(event)"
                  placeholder="Ingrese el folio"
                >
              </div>
            </div>

            <div class="col-md-3">
              <button type="button" class="btn btn-success btn-block" onclick="cargar_documento(2)">
                <i class="fas fa-search mr-1"></i> Buscar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="contenido"></div>
</div>
@endsection
