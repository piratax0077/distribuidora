@extends('plantillas.app')
@section('titulo','Stock de repuestos')

@section('contenido_titulo_pagina')
  @include('fragm.breadcrumb',["titulo"=>"Stock de repuestos","titulo_modulo"=>"Repuestos","ruta_modulo"=>"/repuesto"])
@endsection

 @section('javascript')
  <script>

      window.onload = function(){

          $('#campo_modificar').css('display','none');
          $('#campo_ubicacion').css('display','none');
          $('#mensaje_stock').css('display','none');
      }

      function enter_press(event){
        if(event.keyCode === 13 || event.which === 13){
          buscar_stock_repuesto();
        }
      }

      function espere(mensaje)
    {
        Vue.swal({
                title: mensaje,
                icon: 'info',
                showConfirmButton: true,
                showCancelButton: false,
                allowOutsideClick:false,
            });

            document.getElementById('codigo_repuesto').focus();
    }

      function buscar_stock_repuesto(){

          let codigo_repuesto = document.getElementById('codigo_repuesto').value;
          let opt = $('input:radio[name=flexRadioDefault]:checked').val();
          let data = {codigo_repuesto: codigo_repuesto, option: opt};

          if(codigo_repuesto.trim() == 0 || codigo_repuesto == ''){
            Vue.swal({
              icon:'error',
              position:'top-end',
              text:'Debe ingresar un codigo',
              timer: 3000,
              toast:true,
              showConfirmButton: false
            });
            return false;
          }

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
                $('#mensajes').show();
                $('#mensajes').html('<i class="fas fa-spinner fa-spin"></i> Buscando...');
                espere('Buscando ...');
              },
              success: function(resp){

                Vue.swal.close();
                console.log(resp);

                $('#mensajes').hide();
                $('#tbody_resultados').empty();
                $('#info_rep').empty();
                if(resp[0] === "error"){

                    Vue.swal({

                    title:'Error!',

                    text:resp[1],

                    icon:'error'

                    });
                }else{

                 resp[0].forEach(element => {
                    // eliminar las comillas dobles en la descripcion y la \n
                    element.descripcion = element.descripcion.replace(/"/g, '').replace(/\n/g, ' ');
                    console.log(element);
                    let base_url_fotos = "{{ asset('storage') }}";
                    let placeholder_img = "https://placehold.co/600x400";

                    let foto = (resp[1]['urlfoto'] && resp[1]['urlfoto'] !== '') ? `${base_url_fotos}/${resp[1]['urlfoto']}` : placeholder_img;
                    $('#tbody_resultados').append(`
                        <tr>
                            <td><img src="${foto}" class="logoHeader"  /> </td>
                            <td class="font-monospace">`+element.codigo_interno+`</td>
                            <td style="width: 30%">
                                <div class="text-truncate" title="`+element.descripcion+`">
                                    `+element.descripcion+`
                                </div>
                            </td>
                            <td>
                                <span id="stock-`+element.id+`" class="stock-badge stock-normal">`+element.stock_actual+`</span>
                                <button class='btn btn-modify btn-action btn-sm ms-2' data-toggle="modal" onclick='abrir_campos(`+element.id+`,"`+element.descripcion+`","`+element.marca+`","`+element.pais+`","`+element.responsable+`")' data-target="#modal_modificar_stock" title="Modificar stock">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                            <td>`+element.marca+`</td>
                            <td>`+element.pais+`</td>
                        </tr>
                    `);
                    $('#info_rep').append(`
                        <tr>
                            <td>`+element.codigo_interno+`</td>
                            <td>${element.stock_actual}</td>
                            <td>${element.stock_actual_mermas}</td>
                            <td>${element.stock_actual_preventa}</td>
                        </tr>
                    `);
                 });

                 let responsables = resp[2];
                 $('#select_stock').empty();
                 $('#select_stock').append(`
                    <option value="">Seleccione responsable</option>
                 `);
                 responsables.forEach(e => {

                    $('#select_stock').append(`
                        <option value="`+e.id+`">`+e.name+`</option>
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

      function abrir_campos(id_repuesto,nombre_repuesto,marca,pais,responsable){

          if(id_repuesto !== ""){
            $('#modal_modificar_stock_Label').empty();
            $('#modal_modificar_stock_Label').append(nombre_repuesto);
            $('#modal_body').empty();
            $('#modal_body').append(`
                <div class="form-group floating">
                    <input type="number" class="form-control floating" value="" id="nuevo_stock" min="0">
                    <label class="floating">Nuevo Stock</label>
                </div>
                <input type="hidden" id="id_repuesto_stock" value="`+id_repuesto+`">
            `);
          }else{
            Vue.swal({

            title:'Error!',

            text:'Debe buscar un repuesto',

            icon:'error'

            });
          }

      }

      function abrir_campos_dos(id_repuesto,nombre_repuesto,marca,pais,responsable){
        if(id_repuesto !== ""){
            $('#modal_modificar_stock_dos_Label').empty();
            $('#modal_modificar_stock_dos_Label').append(nombre_repuesto);
            $('#modal_body_dos').empty();
            $('#modal_body_dos').append(`
            <p>Marca: `+marca+` </p>
            <p>País: `+pais+` </p>
            <p>Responsable: `+responsable+` </p>
            <span>Ingrese nuevo stock</span>
                <div class="form-group">
                    <input type="number" class="form-control" value="" id="nuevo_stock_dos" min="0">
                    <input type="hidden" id="id_repuesto_stock_dos" value="`+id_repuesto+`">
                </div>
            `);
          }else{
            Vue.swal({

            title:'Error!',

            text:'Debe buscar un repuesto',

            icon:'error'

            });
          }
      }

      function abrir_campos_tres(id_repuesto,nombre_repuesto,marca,pais,responsable){
        if(id_repuesto !== ""){
            $('#modal_modificar_stock_tres_Label').empty();
            $('#modal_modificar_stock_tres_Label').append(nombre_repuesto);
            $('#modal_body_tres').empty();
            $('#modal_body_tres').append(`
            <p>Marca: `+marca+` </p>
            <p>País: `+pais+` </p>
            <p>Responsable: `+responsable+` </p>
            <span>Ingrese nuevo stock</span>
                <div class="form-group">
                    <input type="number" class="form-control" value="" id="nuevo_stock_tres" min="0">
                    <input type="hidden" id="id_repuesto_stock_tres" value="`+id_repuesto+`">
                </div>
            `);
          }else{
            Vue.swal({

            title:'Error!',

            text:'Debe buscar un repuesto',

            icon:'error'

            });
          }
      }

      function guardar_stock(){
          let id_repuesto = $('#id_repuesto_stock').val();
          let stock = $('#nuevo_stock').val();
          let id_local = $('#local_id').val();
          let idresponsable = $('#select_stock').val();
          let observacion = $('#observacion_stock').val().trim();

          let nombreRepuesto = $('#modal_modificar_stock_Label').text().trim();
          let nombreLocal = $('#local_id option:selected').text().trim();
          let nombreResponsable = $('#select_stock option:selected').text().trim();
          let stockActualTexto = $('#stock-' + id_repuesto).text().trim();
          let stockActual = parseInt(stockActualTexto, 10);
          let nuevoStock = parseInt(stock, 10);

          if(!id_repuesto){
              Vue.swal({
                  title: 'Error',
                  text: 'No se pudo identificar el repuesto.',
                  icon: 'error'
              });
              return false;
          }

          if(stock === '' || isNaN(nuevoStock) || nuevoStock < 0){
              Vue.swal({
                  title: 'Stock inválido',
                  text: 'Debe ingresar un stock válido, igual o mayor que cero.',
                  icon: 'warning'
              });
              $('#nuevo_stock').focus();
              return false;
          }

          if(!id_local || id_local === '0'){
              Vue.swal({
                  title: 'Local requerido',
                  text: 'Debe seleccionar el local donde se modificará el stock.',
                  icon: 'warning'
              });
              $('#local_id').focus();
              return false;
          }

          if(!idresponsable || idresponsable === '0'){
              Vue.swal({
                  title: 'Responsable requerido',
                  text: 'Debe seleccionar al responsable de la modificación.',
                  icon: 'warning'
              });
              $('#select_stock').focus();
              return false;
          }

          if(observacion === ''){
              Vue.swal({
                  title: 'Observación requerida',
                  text: 'Debe indicar el motivo de la modificación de stock.',
                  icon: 'warning'
              });
              $('#observacion_stock').focus();
              return false;
          }

          function escaparHtml(texto){
              return $('<div>').text(texto ?? '').html();
          }

          let diferencia = isNaN(stockActual) ? null : nuevoStock - stockActual;
          let diferenciaTexto = diferencia === null
              ? 'No disponible'
              : (diferencia > 0 ? '+' + diferencia : diferencia.toString());

          let diferenciaClase = diferencia === null
              ? 'text-muted'
              : (diferencia > 0 ? 'text-success' : (diferencia < 0 ? 'text-danger' : 'text-secondary'));

          Vue.swal({
              title: '¿Confirmar modificación de stock?',
              icon: 'warning',
              html: `
                  <div class="text-left">
                      <div class="alert alert-warning py-2">
                          <i class="fas fa-exclamation-triangle"></i>
                          Este cambio modificará inmediatamente el inventario.
                      </div>
                      <table class="table table-sm table-bordered mb-2">
                          <tbody>
                              <tr>
                                  <th style="width:42%">Repuesto</th>
                                  <td>${escaparHtml(nombreRepuesto)}</td>
                              </tr>
                              <tr>
                                  <th>Stock actual</th>
                                  <td><strong>${isNaN(stockActual) ? 'No disponible' : stockActual}</strong></td>
                              </tr>
                              <tr>
                                  <th>Nuevo stock</th>
                                  <td><strong>${nuevoStock}</strong></td>
                              </tr>
                              <tr>
                                  <th>Diferencia</th>
                                  <td><strong class="${diferenciaClase}">${diferenciaTexto}</strong></td>
                              </tr>
                              <tr>
                                  <th>Local</th>
                                  <td>${escaparHtml(nombreLocal)}</td>
                              </tr>
                              <tr>
                                  <th>Responsable</th>
                                  <td>${escaparHtml(nombreResponsable)}</td>
                              </tr>
                              <tr>
                                  <th>Observación</th>
                                  <td>${escaparHtml(observacion)}</td>
                              </tr>
                          </tbody>
                      </table>
                      <small class="text-muted">Revise cuidadosamente los datos antes de continuar.</small>
                  </div>
              `,
              showCancelButton: true,
              confirmButtonText: '<i class="fas fa-save"></i> Sí, guardar',
              cancelButtonText: 'Cancelar',
              confirmButtonColor: '#198754',
              cancelButtonColor: '#6c757d',
              reverseButtons: true,
              focusCancel: true,
              allowOutsideClick: false
          }).then((resultado) => {
              if(!resultado.isConfirmed){
                  return;
              }

              let data = {
                  stock: nuevoStock,
                  id_repuesto: id_repuesto,
                  idresponsable: idresponsable,
                  id_local: id_local,
                  observacion: observacion
              };

              $.ajaxSetup({
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  }
              });

              $.ajax({
                  type: 'post',
                  data: data,
                  url: '/repuesto/guardarstock',
                  beforeSend: function(){
                      Vue.swal({
                          title: 'Guardando cambio...',
                          text: 'Por favor, no cierre esta ventana.',
                          icon: 'info',
                          allowOutsideClick: false,
                          showConfirmButton: false,
                          didOpen: () => {
                              Vue.swal.showLoading();
                          }
                      });
                  },
                  success: function(resp){
                      console.log(resp);

                      if(resp === 'nopermiso'){
                          Vue.swal({
                              title: 'Cambio no permitido',
                              text: 'No puede realizar dos cambios de stock durante el mismo día.',
                              icon: 'warning'
                          });
                          return false;
                      }

                      if(resp && resp[0] === 'OK'){
                          let idRepuestoRespuesta = resp[2];
                          let stockRespuesta = parseInt(resp[1], 10);

                          if(stockRespuesta < 10){
                              $('#stock-' + idRepuestoRespuesta).html(
                                  '<span class="stock_bajo">' + stockRespuesta + '</span>'
                              );
                          }else{
                              $('#stock-' + idRepuestoRespuesta).html(
                                  '<span class="stock-badge stock-normal">' + stockRespuesta + '</span>'
                              );
                          }

                          $('#modal_modificar_stock').modal('hide');
                          $('#nuevo_stock').val('');
                          $('#local_id').val('0');
                          $('#select_stock').val('');
                          $('#observacion_stock').val('');

                          Vue.swal({
                              title: 'Stock actualizado',
                              html: `
                                  <p class="mb-1">El stock fue modificado correctamente.</p>
                                  <strong>Nuevo stock: ${stockRespuesta}</strong>
                              `,
                              icon: 'success',
                              timer: 3000,
                              showConfirmButton: false
                          });

                          actualizar_stock(idRepuestoRespuesta);
                      }else{
                          Vue.swal({
                              title: 'No se pudo actualizar',
                              text: 'El servidor no devolvió una respuesta válida.',
                              icon: 'error'
                          });
                      }
                  },
                  error: function(error){
                      console.log(error.responseText);

                      let mensaje = 'Ocurrió un error al guardar la modificación de stock.';

                      if(error.responseJSON && error.responseJSON.message){
                          mensaje = error.responseJSON.message;
                      }

                      Vue.swal({
                          title: 'Error',
                          text: mensaje,
                          icon: 'error'
                      });
                  }
              });
          });
      }

      function guardar_stock_dos(){
        let id_repuesto = document.getElementById('id_repuesto_stock_dos').value;
          let stock = document.getElementById('nuevo_stock_dos').value;
          let idresponsable = document.getElementById('select_stock_dos').value;
          let opt = 'stock2';
          let data = {stock: stock, id_repuesto: id_repuesto,opt: opt, idresponsable: idresponsable};

          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type:'post',
                data: data,
                url:'/repuesto/guardarstock',
                success: function(resp){
                  if(resp == 'nopermiso'){
                    alert('NO PUEDE HACER CAMBIOS 2 VECES EN EL DÍA');
                    return false;
                  }
                    let id_repuesto = resp[2];
                    if(resp[3] === 'stock2'){
                      console.log('stock 2 actualizado');
                      if(resp[1] < 10){
                            $('#stock_dos-'+id_repuesto).empty();
                            $('#stock_dos-'+id_repuesto).append('<span class="stock_bajo">'+resp[1]+'</span>');
                        }else{
                            $('#stock_dos-'+id_repuesto).empty();
                            $('#stock_dos-'+id_repuesto).append('<span>'+resp[1]+'</span>');
                        }
                        Vue.swal({
                          text: "Stock actualizado",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                          $('#modal_modificar_stock_dos').modal('hide');
                    }else{
                      if(resp[0] === 'OK'){
                        console.log('stock actualizado');
                        if(resp[1] < 10){
                            $('#stock-'+id_repuesto).empty();
                            $('#stock-'+id_repuesto).append('<span class="stock_bajo">'+resp[1]+'</span>');
                        }else{
                            $('#stock-'+id_repuesto).empty();
                            $('#stock-'+id_repuesto).append('<span>'+resp[1]+'</span>');
                        }

                        Vue.swal({
                          text: "Stock actualizado",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                            $('#modal_modificar_stock_dos').modal('hide');
                        actualizar_stock(id_repuesto);
                    }else{
                        console.log(resp);
                    }
                    }


                },
                error: function(error){
                    console.log(error.responseText);
                }
            });
      }

      function guardar_stock_tres(){
        let id_repuesto = document.getElementById('id_repuesto_stock_tres').value;
          let stock = document.getElementById('nuevo_stock_tres').value;
          let idresponsable = document.getElementById('select_stock_tres').value;
          let opt = 'stock3';
          let data = {stock: stock, id_repuesto: id_repuesto,opt: opt, idresponsable: idresponsable};

          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type:'post',
                data: data,
                url:'/repuesto/guardarstock',
                success: function(resp){
                  if(resp == 'nopermiso'){
                    alert('NO PUEDE HACER CAMBIOS 2 VECES EN EL DÍA');
                    return false;
                  }
                    let id_repuesto = resp[2];
                    if(resp[3] === 'stock2'){
                      console.log('stock 2 actualizado');
                      if(resp[1] < 10){
                            $('#stock_dos-'+id_repuesto).empty();
                            $('#stock_dos-'+id_repuesto).append('<span class="stock_bajo">'+resp[1]+'</span>');
                        }else{
                            $('#stock_dos-'+id_repuesto).empty();
                            $('#stock_dos-'+id_repuesto).append('<span>'+resp[1]+'</span>');
                        }
                        Vue.swal({
                          text: "Stock actualizado",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                          $('#modal_modificar_stock_tres').modal('hide');
                    }else if(resp[3] === 'stock3'){
                      console.log('stock 3 actualizado');
                      if(resp[1] < 10){
                            $('#stock_tres-'+id_repuesto).empty();
                            $('#stock_tres-'+id_repuesto).append('<span class="stock_bajo">'+resp[1]+'</span>');
                        }else{
                            $('#stock_tres-'+id_repuesto).empty();
                            $('#stock_tres-'+id_repuesto).append('<span>'+resp[1]+'</span>');
                        }
                        Vue.swal({
                          text: "Stock actualizado",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                          $('#modal_modificar_stock_tres').modal('hide');
                    }else{
                      if(resp[0] === 'OK'){
                        console.log('stock actualizado');
                        if(resp[1] < 10){
                            $('#stock-'+id_repuesto).empty();
                            $('#stock-'+id_repuesto).append('<span class="stock_bajo">'+resp[1]+'</span>');
                        }else{
                            $('#stock-'+id_repuesto).empty();
                            $('#stock-'+id_repuesto).append('<span>'+resp[1]+'</span>');
                        }

                        Vue.swal({
                          text: "Stock actualizado",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                            $('#modal_modificar_stock_tres').modal('hide');
                        actualizar_stock(id_repuesto);
                    }else{
                        console.log(resp);
                    }
                    }


                },
                error: function(error){
                    console.log(error.responseText);
                }
            });
      }

      function guardar_ubicacion(){
          let ubicacion = document.getElementById('ubicacion').value;
          let piso = document.getElementById('piso').value;
          let estanteria = document.getElementById('estanteria').value;
          let bandeja = document.getElementById('bandeja').value;
          let pasillo = document.getElementById('pasillo').value;
          let id_repuesto = document.getElementById('id_repuesto_ubicacion').value;

          let params = {
              ubicacion: ubicacion,
              piso: piso,
              estanteria: estanteria,
              bandeja: bandeja,
              pasillo: pasillo,
              id_repuesto: id_repuesto
          }

          let url = '/repuesto/modificarubicacion';

          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type:'post',
                data: params,
                url: url,
                beforeSend: function(){
                    console.log('Enviando')
                },
                success: function(data){
                  console.log(data);

                   let id_repuesto = data[2];
                    if(data[0] === 'OK'){
                        console.log('stock actualizado');
                        // $('#ubi_uno').empty();
                        // $('$ubi_uno').append('<p>Ubicacion: '+data[1]+'  </p>');
                        $('#ubicacion-'+id_repuesto).empty();
                        $('#ubicacion-'+id_repuesto).append(data[1]);
                        Vue.swal({
                          text: "Ubicación guardada",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                          $("#modal_modificar_ubicacion").modal('hide');
                        actualizar_stock(id_repuesto);
                    }else{
                      Vue.swal({
                          text: "No se puede guardar la misma ubicacion",
                          position: 'top-end',
                          icon: 'error',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                          $("#modal_modificar_ubicacion").modal('hide');
                    }
                },
                error: function(err){
                    console.log(err);
                }
            })
      }

      function guardar_ubicacion_dos(){
          console.log('GUARDANDO');
          let ubicacion = document.getElementById('bodega_ubicacion_dos').value;
          let piso = document.getElementById('piso_ubicacion_dos').value;
          let estanteria = document.getElementById('estanteria_ubicacion_dos').value;
          let bandeja = document.getElementById('bandeja_ubicacion_dos').value;
          let pasillo = document.getElementById('pasillo').value;
          let id_repuesto = document.getElementById('id_repuesto_ubicacion').value;
          let opt = 'ubicacion2';

          let params = {
              ubicacion: ubicacion,
              piso: piso,
              estanteria: estanteria,
              bandeja: bandeja,
              id_repuesto: id_repuesto,
              pasillo: pasillo,
              opt: opt
          }

          let url = '/repuesto/modificarubicacion';

          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type:'post',
                data: params,
                url: url,
                beforeSend: function(){
                    console.log('Enviando')
                },
                success: function(data){

                   let id_repuesto = data[2];
                    if(data[0] === 'OK'){
                        console.log('stock actualizado');
                        $('#ubicacion_dos-'+id_repuesto).empty();
                        $('#ubicacion_dos-'+id_repuesto).append(data[1]);
                        Vue.swal({
                          text: "Ubicación actualizada",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                        actualizar_stock(id_repuesto);
                        $("#modal_modificar_ubicacion_dos").modal('hide');
                    }else{
                      Vue.swal({
                          text: "No se pueden guardar 2 ubicaciones iguales",
                          position: 'top-end',
                          icon: 'error',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                          $("#modal_modificar_ubicacion_dos").modal('hide');
                    }
                },
                error: function(err){
                    console.log(err);
                }
            })
      }

      function guardar_ubicacion_tres(){
          console.log('GUARDANDO 3');
          let ubicacion = document.getElementById('bodega_ubicacion_tres').value;
          let piso = document.getElementById('piso_ubicacion_tres').value;
          let estanteria = document.getElementById('estanteria_ubicacion_tres').value;
          let bandeja = document.getElementById('bandeja_ubicacion_tres').value;
          let pasillo = document.getElementById('pasillo').value;
          let id_repuesto = document.getElementById('id_repuesto_ubicacion').value;
          let opt = 'ubicacion3';

          let params = {
              ubicacion: ubicacion,
              piso: piso,
              estanteria: estanteria,
              bandeja: bandeja,
              id_repuesto: id_repuesto,
              pasillo: pasillo,
              opt: opt
          }

          let url = '/repuesto/modificarubicacion';

          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type:'post',
                data: params,
                url: url,
                beforeSend: function(){
                    console.log('Enviando')
                },
                success: function(data){

                   let id_repuesto = data[2];
                    if(data[0] === 'OK'){
                        console.log('stock actualizado');
                        $('#ubicacion_tres-'+id_repuesto).empty();
                        $('#ubicacion_tres-'+id_repuesto).append(data[1]);
                        Vue.swal({
                          text: "Ubicación actualizada",
                          position: 'top-end',
                          icon: 'success',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                        actualizar_stock(id_repuesto);
                        $("#modal_modificar_ubicacion_tres").modal('hide');
                    }else{
                      Vue.swal({
                          text: "No se pueden guardar 2 ubicaciones iguales",
                          position: 'top-end',
                          icon: 'error',
                          toast: true,
                          showConfirmButton: false,
                          timer: 4000,
                          });
                          $("#modal_modificar_ubicacion_tres").modal('hide');
                    }
                },
                error: function(err){
                    console.log(err);
                }
            })
      }

      function actualizar_stock(id_repuesto){

        let url = '/repuesto/actualizarstock/'+id_repuesto;
        $.ajax({
            type:'get',
            url: url,
            success: function(resp){

                if(resp[0].stock_actual < 10){

                    $('#mensaje_stock').empty();
                    $('#mensaje_stock').css('display','block');
                    $('#mensaje_stock').append('Stock acabandose')
                }else{
                    $('#mensaje_stock').css('display','none');
                    $('#mensaje_stock').empty();
                }

            },
            error: function(err){

            }
        })
      }

      function cerrar_div(){
        $('#campo_modificar').css('transition','all 300ms');
        $('#campo_modificar').css('display','none');
      }

      function cerrar_div_ubicacion(){
        $('#campo_ubicacion').css('display','none');
      }

      function enter_press(e)
    {
        var keycode = e.keyCode;
        if(keycode=='13')
        {
            buscar_stock_repuesto();
        }
    }

    function modificar_ubicacion(id_repuesto,nombre_repuesto,marca,ubicacion){

        if(id_repuesto !== ""){
            $('#modal_modificar_ubicacion_Label').empty();
            $('#modal_modificar_ubicacion_Label').append('<p class="title">'+nombre_repuesto+'</p>');
            $('#id_repuesto_ubicacion').val(id_repuesto);
            $('#marca').empty();
            $('#marca').append('<p>Marca: '+marca+' </p>');
            $('#ubi_uno').empty();
            $('#ubi_uno').append('<p>Ubicacion: '+ubicacion+' </p>');
          }else{
            Vue.swal({

            title:'Error!',

            text:'Debe buscar un repuesto',

            icon:'error'

            });
          }
    }

    function modificar_ubicacion_dos(id_repuesto,nombre_repuesto,marca,ubicacion){

        if(id_repuesto !== ""){
            $('#modal_modificar_ubicacion_dos_Label').empty();
            $('#modal_modificar_ubicacion_dos_Label').append('<p class="title">'+nombre_repuesto+'</p>');

            $('#id_repuesto_ubicacion').val(id_repuesto);
            $('#marca_dos').empty();
            $('#marca_dos').append('<p>Marca: '+marca+' </p>');

            $('#ubi_dos').empty();
            $('#ubi_dos').append('<p>Ubicación: '+ubicacion+' </p>');
          }else{
            Vue.swal({

            title:'Error!',

            text:'Debe buscar un repuesto',

            icon:'error'

            });
          }
    }

    function modificar_ubicacion_tres(id_repuesto,nombre_repuesto,marca,ubicacion){

        if(id_repuesto !== ""){
            $('#modal_modificar_ubicacion_tres_Label').empty();
            $('#modal_modificar_ubicacion_tres_Label').append('<p class="title">'+nombre_repuesto+'</p>');

            $('#id_repuesto_ubicacion').val(id_repuesto);
            $('#marca_tres').empty();
            $('#marca_tres').append('<p>Marca: '+marca+' </p>');

            $('#ubi_tres').empty();
            $('#ubi_tres').append('<p>Ubicación: '+ubicacion+' </p>');
          }else{
            Vue.swal({

            title:'Error!',

            text:'Debe buscar un repuesto',

            icon:'error'

            });
          }
    }

  </script>
 @endsection

 @section('style')
  <style>
    /* Estilos principales con paleta neutra */
    .container-fluid {
        background-color: #f8f9fa;
        min-height: 100vh;
        padding: 30px;
    }

    /* Cards elegantes para las tablas */
    .dashboard-card {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 25px;
        transition: all 0.3s ease;
    }

    .dashboard-card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }

    .card-header-custom {
        background: #6c757d;
        color: white;
        padding: 20px;
        font-weight: 600;
        font-size: 1.1em;
        border: none;
        display: flex;
        align-items: center;
    }

    .card-header-custom i {
        margin-right: 10px;
        font-size: 1.2em;
    }

    /* Tablas elegantes */
    .table-elegant {
        margin-bottom: 0;
        background: white;
        font-size: 13px;
    }

    .table-elegant thead th {
        background: #e9ecef;
        border: 1px solid #dee2e6;
        font-weight: 600;
        color: #212529;
        text-transform: uppercase;
        font-size: 0.8em;
        letter-spacing: 0.5px;
        padding: 15px 12px;
        border-bottom: 2px solid #6c757d;
    }

    .table-elegant tbody td {
        color: #212529;
        border: 1px solid #dee2e6;
        padding: 12px;
        vertical-align: middle;
    }

    .table-elegant tbody tr:hover {
        background-color: #f8f9fa;
        transition: all 0.2s ease;
    }

    /* Sección de búsqueda */
    .search-panel {
        background: white;
        border: 2px solid #6c757d;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        margin-top: 30px;
    }

    .search-panel h5 {
        color: #212529;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #6c757d;
        display: flex;
        align-items: center;
    }

    .search-panel h5 i {
        margin-right: 10px;
    }

    /* Sección de detalles */
    .details-panel {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        margin-top: 30px;
        min-height: 200px;
    }

    .details-panel h5 {
        color: #212529;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #6c757d;
        display: flex;
        align-items: center;
    }

    .details-panel h5 i {
        margin-right: 10px;
    }

    /* Controles de formulario */
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

    /* Radio buttons elegantes */
    .radio-group {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #dee2e6;
    }

    .form-check {
        display: flex;
        align-items: center;
    }

    .form-check-input:checked {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .form-check-label {
        color: #212529;
        font-weight: 500;
        margin-left: 8px;
    }

    /* Botones elegantes */
    .btn-search {
        background: #6c757d;
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-search:hover {
        background: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        color: white;
    }

    .btn-excel {
        background: #28a745;
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-left: 10px;
    }

    .btn-excel:hover {
        background: #218838;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        color: white;
    }

    .btn-transfer {
        background: #ffc107;
        border: none;
        color: #212529;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-left: 10px;
    }

    .btn-transfer:hover {
        background: #e0a800;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
        color: #212529;
    }

    /* Badges y etiquetas */
    .stock-badge {
        padding: 8px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.85em;
    }

    .stock-normal {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .stock_bajo {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        padding: 8px 12px;
        border-radius: 6px;
        font-weight: 600;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }

    /* Botones de acción */
    .btn-action {
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 0.8em;
        font-weight: 500;
        transition: all 0.3s ease;
        margin-left: 5px;
    }

    .btn-modify {
        background: #6c757d;
        border: 1px solid #6c757d;
        color: white;
    }

    .btn-modify:hover {
        background: #5a6268;
        border-color: #5a6268;
        transform: translateY(-1px);
        color: white;
    }

    /* Logo y imágenes */
    .logoHeader {
        width: 80px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #dee2e6;
    }

    .logo {
        width: 100px;
        border-radius: 10px;
    }

    /* Floating labels */
    .form-group.floating {
        position: relative;
        margin-bottom: 20px;
    }

    .form-group.floating .form-control.floating {
        height: 55px;
        padding: 20px 15px 8px 15px;
    }

    .form-group.floating label.floating {
        position: absolute;
        top: 15px;
        left: 15px;
        color: #6c757d;
        transition: all 0.3s ease;
        pointer-events: none;
        font-weight: 500;
    }

    .form-control.floating:focus + label.floating,
    .form-control.floating:not(:placeholder-shown) + label.floating {
        top: 5px;
        font-size: 0.8em;
        color: #212529;
        font-weight: 600;
    }

    /* Mensajes */
    #mensajes {
        background: #d1ecf1;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #bee5eb;
        color: #0c5460;
        font-weight: 500;
        margin-bottom: 20px;
    }

    /* Modal personalizado */
    .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .modal-header {
        background: #6c757d;
        color: white;
        border-radius: 15px 15px 0 0;
        border: none;
        padding: 20px;
    }

    .modal-body {
        background: white;
        padding: 25px;
    }

    .modal-footer {
        background: #f8f9fa;
        border-radius: 0 0 15px 15px;
        border: none;
        padding: 20px;
    }

    /* Botones de acción con espaciado */
    .d-flex.gap-2 > * {
        margin-right: 10px;
    }

    .d-flex.gap-2 > *:last-child {
        margin-right: 0;
    }

    /* Mejoras para móviles */
    @media (max-width: 768px) {
        .radio-group {
            flex-direction: column;
            gap: 10px;
        }

        .dashboard-card {
            margin-bottom: 15px;
        }

        .btn-excel, .btn-transfer {
            margin-left: 0;
            margin-top: 10px;
            width: 100%;
        }

        .d-flex.gap-2 {
            flex-direction: column;
        }

        .d-flex.gap-2 > * {
            margin-right: 0;
            margin-bottom: 10px;
            width: 100%;
        }
    }

    /* Estados especiales */
    .no-data {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .no-data i {
        font-size: 3em;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    /* Efectos de hover en cards */
    .dashboard-card .table-elegant tbody tr:hover td {
        background-color: #f1f3f4;
    }

    /* Inputs con foco */
    input[type="text"]:focus,
    input[type="number"]:focus,
    select:focus {
        background-color: white !important;
        color: #212529 !important;
    }

    /* Clases heredadas necesarias */
    .busqueda_principal {
        /* Se mantiene la funcionalidad existente */
    }

    .busqueda_resultado {
        /* Se mantiene la funcionalidad existente */
    }

    .campo_modificar {
        margin-top:30px;
        padding: 30px;
        border: 1px solid #6c757d;
        border-radius: 10px;
        background: white;
    }

    .alert-danger {
        padding: 15px;
        border: 1px solid #f5c6cb;
        background: #f8d7da;
        color: #721c24;
        border-radius: 8px;
    }

    .select-ubicacion {
        width: 100%;
    }

    .select-ubicacion select {
        width: 100%;
        margin-bottom: 10px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        padding: 10px;
    }

    #tbody_resultados {
      font-size: 13px;
    }

    .imagen_pequeña {
        width: 120px;
    }
  </style>
 @endsection

 @section('contenido_ingresa_datos')
 <div class="container-fluid">
  <script type="text/javascript">
    $('#modalArticulos').on('shown.bs.modal', function () {
        $('#codigoEscaneado').focus();
    });

    function buscarArticulo(){
        let codigo_escaneado = $('#codigoEscaneado').val();
        let url = '{{url("/buscar-barcode")}}';

        $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
        });
        $.ajax({
            type:'POST',
            url: url,
            data: {'codigo_escaneado': codigo_escaneado},
            beforeSend: function(){
                console.log('Enviando');
            },
            success: function(resp){

                $('#tbody_repuesto_escaneado').html(resp);
            },
            error: function(err){
                console.log(err.statusText);
            }
        })
    }

    function agregar(){
        console.log('Agregando');

        let codigo_repuesto = document.getElementById('codigoEscaneado').value;
          let opt = 'cod_int';
          let data = {codigo_repuesto: codigo_repuesto, option: opt};

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
                $('#mensajes').empty();
                $('#mensajes').append('Listo');
                $('#tbody_resultados').empty();
                if(resp[0] === "error"){

                    Vue.swal({

                    title:'Error!',

                    text:resp[1],

                    icon:'error'

                    });
                }else{
                 resp.forEach(element => {

                     if(element.stock_actual < 10){
                        $('#tbody_resultados').append(`
                        <tr>
                            <th scope="row" style="width: 30%">`+element.descripcion+`</th>
                            <td><span class="stock_bajo" id="stock-`+element.id+`">`+element.stock_actual+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='abrir_campos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.nombre_pais+`")' data-target="#modal_modificar_stock">M </button></td>
                            <td><span id="ubicacion-`+element.id+`">`+element.ubicacion+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion+`")' data-target="#modal_modificar_ubicacion">M </button></td>
                            <td>`+element.ubicacion_dos+`<button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion_dos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion_dos+`")' data-target="#modal_modificar_ubicacion_dos">M </button></td>
                            <td>`+element.cod_repuesto_proveedor+`</td>
                            <td>`+element.marcarepuesto+`</td>
                            <td>`+element.nombre_pais+`</td>
                            <td></td>
                        </tr>
                 `);
                     }else{
                        $('#tbody_resultados').append(`
                        <tr>
                            <th scope="row" style="width: 30%">`+element.descripcion+`</th>
                            <td><span id="stock-`+element.id+`">`+element.stock_actual+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='abrir_campos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.nombre_pais+`")' data-target="#modal_modificar_stock">M </button></td>
                            <td><span id="ubicacion-`+element.id+`">`+element.ubicacion+`</span><button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion+`")' data-target="#modal_modificar_ubicacion">M </button></td>
                            <td>`+element.ubicacion_dos+`<button class='btn btn-warning btn-sm' data-toggle="modal" onclick='modificar_ubicacion_dos(`+element.id+`,"`+element.descripcion+`","`+element.marcarepuesto+`","`+element.ubicacion_dos+`")' data-target="#modal_modificar_ubicacion_dos">M </button></td>
                            <td>`+element.cod_repuesto_proveedor+`</td>
                            <td>`+element.marcarepuesto+`</td>
                            <td>`+element.nombre_pais+`</td>
                            <td></td>
                        </tr>
                 `);
                     }

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
          })
    }

    function exportar_excel(){
        let url = '{{url("/exportar-excel")}}';
        $.ajax({
            type:'get',
            url: url,
            success: function(resp){
                console.log(resp);
            },
            error: function(err){
                console.log(err);
            }
        })
    }
</script>

<!-- Modal -->
<div class="modal fade bd-example-modal-xl" id="modalArticulos" tabindex="-1" role="dialog" aria-labelledby="modalArticulosLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
                <h4 class="modal-title" id="modalArticulosLabel">Ingreso de Artículos</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

			</div>
			<div class="modal-body">
				<div class="form-group">
					<label>Escanear Código de Barras</label>
					<div class="input-group">
						<div class="input-group-addon icon_barcode"><i class="fa fa-barcode"></i></div>
						<input type="text" class="form-control producto" name="codigoEscaneado" id="codigoEscaneado" autocomplete="off" onchange="buscarArticulo();">
					</div>
				</div>
				<div>
					<table class="table table-striped" id="tablaAgregarArticulos">
						<thead>
							<tr>
								<th>Producto</th>
								<th>Cantidad</th>
								<th>Familia</th>
                                <th>Observaciones</th>
                                <th>Pais</th>
                                <th>Empresa</th>
                                <th>Precio Venta</th>
							</tr>
						</thead>
						<tbody id="tbody_repuesto_escaneado">

						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal" id="btnCerrarModal">Cerrar</button>
				<button type="button" class="btn btn-primary" id="btnAgregar" onclick="agregar();">Agregar</button>
			</div>
		</div>
	</div>
</div>
<div class="row w-100">

  <!-- Últimos Repuestos Vendidos -->
  <div class="col-md-4">
    <div class="dashboard-card">
      <div class="card-header-custom">
        <i class="fas fa-shopping-cart"></i>
        Últimos Repuestos Vendidos
      </div>
      <div class="table-responsive">
        <table class="table table-elegant">
          <thead>
            <tr>
              <th scope="col"><i class="fas fa-barcode"></i> Código</th>
              <th scope="col"><i class="fas fa-tag"></i> Descripción</th>
              <th scope="col"><i class="fas fa-sort-numeric-up"></i> Cantidad</th>
            </tr>
          </thead>
          <tbody>
            @forelse($ultimos_repuestos_vendidos as $repuesto)
            <tr>
              <td class="font-monospace">{{$repuesto->codigo_interno}}</td>
              <td>
                <div class="text-truncate" style="max-width: 200px;" title="{{$repuesto->descripcion}}">
                  {{$repuesto->descripcion}}
                </div>
              </td>
              <td>
                <span class="stock-badge stock-normal">{{$repuesto->cantidad}}</span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="3" class="no-data">
                <i class="fas fa-inbox"></i><br>
                <small>No hay ventas registradas</small>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Últimas Ventas -->
  <div class="col-md-4">
    <div class="dashboard-card">
      <div class="card-header-custom">
        <i class="fas fa-cash-register"></i>
        Últimas Ventas
      </div>
      <div class="table-responsive">
        <table class="table table-elegant">
          <thead>
            <tr>
              <th scope="col"><i class="fas fa-calendar"></i> Fecha</th>
              <th scope="col"><i class="fas fa-file-alt"></i> Tipo</th>
              <th scope="col"><i class="fas fa-clock"></i> Hora</th>
              <th scope="col"><i class="fas fa-dollar-sign"></i> Total</th>
            </tr>
          </thead>
          <tbody>
            @forelse($ultimas_ventas as $venta)
            <tr>
              <td>
                <small class="text-muted">{{$venta->fecha_formateada}}</small>
              </td>
              <td>
                <span class="badge" style="background: #6c757d; color: white;">{{$venta->tipo_doc}}</span>
              </td>
              <td>
                <small class="font-monospace">{{$venta->hora}}</small>
              </td>
              <td>
                <strong class="text-success">${{number_format($venta->total)}}</strong>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="no-data">
                <i class="fas fa-chart-line"></i><br>
                <small>No hay ventas registradas</small>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Últimos Repuestos Ingresados -->
  <div class="col-md-4">
    <div class="dashboard-card">
      <div class="card-header-custom">
        <i class="fas fa-plus-circle"></i>
        Últimos Repuestos Ingresados
      </div>
      <div class="table-responsive">
        <table class="table table-elegant">
          <thead>
            <tr>
              <th scope="col"><i class="fas fa-barcode"></i> Código</th>
              <th scope="col"><i class="fas fa-tag"></i> Descripción</th>
              <th scope="col"><i class="fas fa-dollar-sign"></i> Valor</th>
            </tr>
          </thead>
          <tbody>
            @forelse($ultimos_repuestos as $repuesto)
            <tr>
              <td class="font-monospace">{{$repuesto->codigo_interno}}</td>
              <td>
                <div class="text-truncate" style="max-width: 200px;" title="{{$repuesto->descripcion}}">
                  {{$repuesto->descripcion}}
                </div>
              </td>
              <td>
                <strong class="text-primary">${{number_format($repuesto->precio_venta)}}</strong>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="3" class="no-data">
                <i class="fas fa-box-open"></i><br>
                <small>No hay repuestos ingresados</small>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<div class="row">

  <!-- Panel de Búsqueda -->
  <div class="col-md-4">
    <div class="search-panel">
      <h5><i class="fas fa-search"></i> Búsqueda de Repuestos</h5>

      <!-- Opciones de búsqueda -->
      <div class="radio-group">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1" value="cod_int" checked>
          <label class="form-check-label" for="flexRadioDefault1">
            <i class="fas fa-barcode"></i> Código interno
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2" value="oem" disabled>
          <label class="form-check-label" for="flexRadioDefault2">
            <i class="fas fa-cog"></i> OEM
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault4" value="cod_prov" disabled>
          <label class="form-check-label" for="flexRadioDefault4">
            <i class="fas fa-truck"></i> Cód. Proveedor
          </label>
        </div>
      </div>

      <!-- Campo de búsqueda -->
      <div class="form-group floating">
        <input type="text" class="form-control floating" name="codigo_repuesto" id="codigo_repuesto" onkeyup="enter_press(event)" placeholder=" ">
        <label for="codigo_repuesto" class="floating"><i class="fas fa-keyboard"></i> Ingrese el código del repuesto</label>
      </div>

      <!-- Mensaje de estado -->
      <div id="mensajes" style="display: none;">
        <i class="fas fa-info-circle"></i> Listo
      </div>

      <!-- Botones de acción -->
      <div class="d-flex flex-wrap gap-2 mb-3">
        <button type="button" class="btn btn-search" onclick="buscar_stock_repuesto()">
          <i class="fas fa-search"></i> Buscar
        </button>
        <a href="{{url('repuesto/descargar-repuestos')}}" class="btn btn-excel">
          <i class="fas fa-file-excel"></i> Excel
        </a>
        <button type="button" class="btn btn-transfer" onclick="confirmar_traspaso_bodega_preventa()">
          <i class="fas fa-exchange-alt"></i> Traspaso
        </button>
      </div>
    </div>
  </div>

  <!-- Panel de Detalles del Producto -->
  <div class="col-md-8">
    <div class="details-panel">
      <h5><i class="fas fa-info-circle"></i> Detalles del Producto</h5>

      <div class="table-responsive">
        <table class="table table-elegant" id="tabla_resultados">
          <thead>
            <tr>
              <th scope="col" style="width: 100px;"><i class="fas fa-image"></i> Imagen</th>
              <th scope="col"><i class="fas fa-barcode"></i> Código</th>
              <th scope="col"><i class="fas fa-tag"></i> Descripción</th>
              <th scope="col"><i class="fas fa-cubes"></i> Stock</th>
              <th scope="col"><i class="fas fa-industry"></i> Marca</th>
              <th scope="col"><i class="fas fa-globe"></i> País</th>
            </tr>
          </thead>
          <tbody id="tbody_resultados">
            <tr>
              <td colspan="6" class="no-data">
                <i class="fas fa-search fa-2x"></i><br>
                <strong>Ingrese un código para buscar</strong><br>
                <small class="text-muted">Utilice el panel de búsqueda para encontrar repuestos</small>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>



</div>
<script>
    document.getElementById('exportarExcel').addEventListener('click', function(event) {
        event.preventDefault(); // Previene la acción por defecto del enlace
        var confirmarDescarga = confirm('¿Realmente quieres descargar el archivo Excel?');
        if (confirmarDescarga) {
            window.location.href = this.href; // Si el usuario confirma, redirige a la URL del enlace
        }
    });
</script>
<!-- Modal -->
<div class="modal fade" id="modal_modificar_stock" tabindex="-1" role="dialog" aria-labelledby="modal_modificar_stock_Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <img src="{{asset('storage/imagenes/ips_logo.png')}}" alt="" srcset="" class="logo">
          <div class="modal-title" id="modal_modificar_stock_Label">Modal title</div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-12">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Codigo interno</th>
                                <th scope="col">Stock Bodega</th>
                                <th scope="col">Stock Mermas</th>
                                <th scope="col">Stock Preventa</th>
                            </tr>
                        </thead>
                        <tbody id="info_rep">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    <div id="modal_body">

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group floating">
                        <select name="local_id" id="local_id" class="form-control floating">
                            <option value="0">Seleccione local</option>
                            @foreach($locales as $local)
                                <option value="{{$local->id}}">{{$local->local_nombre}}</option>
                            @endforeach
                        </select>
                        <label for="" class="floating">Seleccione local</label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group floating">
                                <select name="select_stock" id="select_stock" class="form-control floating">
                                    <option value="0">Seleccione</option>
                                </select>
                                <label for="select_stock" class="floating">Responsable</label>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group floating">
                        <input type="text" class="form-control floating" name="observacion_stock" id="observacion_stock" placeholder=" ">
                        <label for="observacion_stock" class="floating">Ingrese una observación</label>
                    </div>
                </div>
            </div>


        </div>
        <div class="modal-footer">


          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fas fa-close"></i> Cerrar</button>
          <button type="button" class="btn btn-success btn-sm" onclick="guardar_stock()"><i class="fas fa-save"></i> Guardar</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal modificar stock 2 -->
<div class="modal fade" id="modal_modificar_stock_dos" tabindex="-1" role="dialog" aria-labelledby="modal_modificar_stock_dos_Label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <img src="{{asset('storage/imagenes/ips_logo.png')}}" alt="" srcset="" class="logo">
        <div class="modal-title" id="modal_modificar_stock_dos_Label">Modal title</div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal_body_dos">

      </div>
      <div class="modal-footer">
        <select name="select_stock_dos" id="select_stock_dos" class="form-control"></select>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" onclick="guardar_stock_dos()">Guardar</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal modificar stock 3 -->
<div class="modal fade" id="modal_modificar_stock_tres" tabindex="-1" role="dialog" aria-labelledby="modal_modificar_stock_tres_Label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <img src="{{asset('storage/imagenes/ips_logo.png')}}" alt="" srcset="" class="logo">
        <div class="modal-title" id="modal_modificar_stock_tres_Label">Modal title</div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal_body_tres">

      </div>
      <div class="modal-footer">
        <select name="select_stock_tres" id="select_stock_tres" class="form-control"></select>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" onclick="guardar_stock_tres()">Guardar</button>
      </div>
    </div>
  </div>
</div>
  <!-- Modal -->
<div class="modal fade" id="modal_modificar_ubicacion" tabindex="-1" role="dialog" aria-labelledby="modal_modificar_ubicacion_Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <img src="{{asset('storage/imagenes/ips_logo.png')}}" alt="" srcset="" class="logo">
          <div class="modal-title" id="modal_modificar_ubicacion_Label">Modal title</div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="modal_body_ubicacion">
          <input type="hidden" id="id_repuesto_ubicacion" value="">
            <p id="marca">Marca:</p>
            <p id="ubi_uno">Ubicación: </p>
            <span>Ingrese nueva ubicación</span>

                <div class="select-ubicacion">
                  <select name="ubicacion" id="ubicacion" class="form-control">
                    <option value="1">Bodega</option>
                    </select>
                    <select name="piso" id="piso" class="form-control">
                        <option value="p1">Piso 1</option>
                        <option value="p2">Piso 2</option>
                        <option value="p3">Piso 3</option>
                    </select>
                    <select name="estanteria" id="estanteria" class="form-control">
                        <option value="0">Estanteria</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                        <option value="F">F</option>
                        <option value="G">G</option>
                        <option value="H">H</option>
                        <option value="I">I</option>
                        <option value="J">J</option>
                        <option value="K">K</option>
                        <option value="L">L</option>
                        <option value="M">M</option>
                        <option value="N">N</option>
                        <option value="Ñ">Ñ</option>
                        <option value="O">O</option>
                        <option value="P">P</option>
                        <option value="Q">Q</option>
                        <option value="R">R</option>
                        <option value="S">S</option>
                        <option value="T">T</option>
                        <option value="U">U</option>
                        <option value="V">V</option>
                        <option value="W">W</option>
                        <option value="X">X</option>
                        <option value="Y">Y</option>
                        <option value="Z">Z</option>
                    </select>
                    <select name="bandeja" id="bandeja" class="form-control">
                        <option value="b0">Bandeja</option>
                        <option value="bA">Bandeja A</option>
                        <option value="bB">Bandeja B</option>
                        <option value="bC">Bandeja C</option>
                        <option value="bD">Bandeja D</option>
                        <option value="bE">Bandeja E</option>
                        <option value="bF">Bandeja F</option>
                        <option value="bG">Bandeja G</option>
                        <option value="bH">Bandeja H</option>
                        <option value="bI">Bandeja I</option>
                        <option value="bJ">Bandeja J</option>
                        <option value="bK">Bandeja K</option>
                        <option value="bL">Bandeja L</option>
                        <option value="bM">Bandeja M</option>
                        <option value="bN">Bandeja N</option>
                        <option value="bÑ">Bandeja Ñ</option>
                    </select>
                    <select name="pasillo" id="pasillo" class="form-control">
                      <option value="p0">Pasillo</option>
                      <option value="p1">Pasillo 1</option>
                      <option value="p2">Pasillo 2</option>
                      <option value="p3">Pasillo 3</option>
                      <option value="p4">Pasillo 4</option>
                      <option value="p5">Pasillo 5</option>
                      <option value="p6">Pasillo 6</option>
                      <option value="p7">Pasillo 7</option>
                      <option value="p8">Pasillo 8</option>
                      <option value="p9">Pasillo 9</option>
                      <option value="p10">Pasillo 10</option>
                      <option value="p11">Pasillo 11</option>
                      <option value="p12">Pasillo 12</option>
                      <option value="p13">Pasillo 13</option>
                      <option value="p14">Pasillo 14</option>
                      <option value="p15">Pasillo 15</option>
                      <option value="p16">Pasillo 16</option>
                      <option value="p17">Pasillo 17</option>
                      <option value="p18">Pasillo 18</option>
                      <option value="p19">Pasillo 19</option>
                      <option value="p20">Pasillo 20</option>
                      <option value="oficina">Oficina</option>
                    </select>
                </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" onclick="guardar_ubicacion()">Guardar</button>
        </div>
      </div>
    </div>
  </div>
  <!--Modal ubicacion 2 -->
  <div class="modal fade" id="modal_modificar_ubicacion_dos" tabindex="-1" role="dialog" aria-labelledby="modal_modificar_ubicacion_dos_Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <img src="{{asset('storage/imagenes/ips_logo.png')}}" alt="" srcset="" class="logo">
          <div class="modal-title" id="modal_modificar_ubicacion_dos_Label">Modal title</div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="modal_body_ubicacion_dos">
          <input type="hidden" id="id_repuesto_ubicacion" value="">
            <p id="marca_dos">Marca: </p>
            <p id="ubi_dos">Ubicación:</p>
            <span>Ingrese nueva ubicación</span>
                <div class="select-ubicacion">
                  <select name="bodega_ubicacion_dos" id="bodega_ubicacion_dos" class="form-control">
                    <option value="3">Tienda</option>
                    </select>
                    <select name="piso" id="piso_ubicacion_dos" class="form-control">
                        <option value="p1">Piso 1</option>
                        <option value="p2">Piso 2</option>
                        <option value="p3">Piso 3</option>
                    </select>
                    <select name="estanteria" id="estanteria_ubicacion_dos" class="form-control">
                        <option value="0">Estanteria</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                        <option value="F">F</option>
                        <option value="G">G</option>
                        <option value="H">H</option>
                        <option value="I">I</option>
                        <option value="J">J</option>
                        <option value="K">K</option>
                        <option value="L">L</option>
                        <option value="M">M</option>
                        <option value="N">N</option>
                        <option value="Ñ">Ñ</option>
                        <option value="O">O</option>
                        <option value="P">P</option>
                        <option value="Q">Q</option>
                        <option value="R">R</option>
                        <option value="S">S</option>
                        <option value="T">T</option>
                        <option value="U">U</option>
                        <option value="V">V</option>
                        <option value="W">W</option>
                        <option value="X">X</option>
                        <option value="Y">Y</option>
                        <option value="Z">Z</option>
                    </select>
                    <select name="bandeja" id="bandeja_ubicacion_dos" class="form-control">
                      <option value="b0">Bandeja</option>
                      <option value="bA">Bandeja A</option>
                      <option value="bB">Bandeja B</option>
                      <option value="bC">Bandeja C</option>
                      <option value="bD">Bandeja D</option>
                      <option value="bE">Bandeja E</option>
                      <option value="bF">Bandeja F</option>
                      <option value="bG">Bandeja G</option>
                      <option value="bH">Bandeja H</option>
                      <option value="bI">Bandeja I</option>
                      <option value="bJ">Bandeja J</option>
                      <option value="bK">Bandeja K</option>
                      <option value="bL">Bandeja L</option>
                      <option value="bM">Bandeja M</option>
                      <option value="bN">Bandeja N</option>
                      <option value="bÑ">Bandeja Ñ</option>
                  </select>
                    <select name="pasillo" id="pasillo_ubicacion_dos" class="form-control">
                      <option value="p0">Pasillo</option>
                      <option value="p1">Pasillo 1</option>
                      <option value="p2">Pasillo 2</option>
                      <option value="p3">Pasillo 3</option>
                      <option value="p4">Pasillo 4</option>
                      <option value="p5">Pasillo 5</option>
                      <option value="p6">Pasillo 6</option>
                      <option value="p7">Pasillo 7</option>
                      <option value="p8">Pasillo 8</option>
                      <option value="p9">Pasillo 9</option>
                      <option value="p10">Pasillo 10</option>
                      <option value="p11">Pasillo 11</option>
                      <option value="p12">Pasillo 12</option>
                      <option value="p13">Pasillo 13</option>
                      <option value="p14">Pasillo 14</option>
                      <option value="p15">Pasillo 15</option>
                      <option value="p16">Pasillo 16</option>
                      <option value="p17">Pasillo 17</option>
                      <option value="p18">Pasillo 18</option>
                      <option value="p19">Pasillo 19</option>
                      <option value="p20">Pasillo 20</option>
                      <option value="oficina">Oficina</option>
                    </select>
                </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" onclick="guardar_ubicacion_dos()">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <!--Modal ubicacion 3 -->
  <div class="modal fade" id="modal_modificar_ubicacion_tres" tabindex="-1" role="dialog" aria-labelledby="modal_modificar_ubicacion_tres_Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <img src="{{asset('storage/imagenes/ips_logo.png')}}" alt="" srcset="" class="logo">
          <div class="modal-title" id="modal_modificar_ubicacion_tres_Label">Modal title</div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="modal_body_ubicacion_tres">
          <input type="hidden" id="id_repuesto_ubicacion" value="">
            <p id="marca_tres">Marca: </p>
            <p id="ubi_tres">Ubicación:</p>
            <span>Ingrese nueva ubicación</span>
                <div class="select-ubicacion">
                  <select name="bodega_ubicacion_tres" id="bodega_ubicacion_tres" class="form-control">
                    <option value="4">Casa Matríz</option>
                    </select>
                    <select name="piso" id="piso_ubicacion_tres" class="form-control">
                        <option value="p1">Piso 1</option>
                        <option value="p2">Piso 2</option>
                        <option value="p3">Piso 3</option>
                    </select>
                    <select name="estanteria" id="estanteria_ubicacion_tres" class="form-control">
                        <option value="0">Estanteria</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                        <option value="F">F</option>
                        <option value="G">G</option>
                        <option value="H">H</option>
                        <option value="I">I</option>
                        <option value="J">J</option>
                        <option value="K">K</option>
                        <option value="L">L</option>
                        <option value="M">M</option>
                        <option value="N">N</option>
                        <option value="Ñ">Ñ</option>
                        <option value="O">O</option>
                        <option value="P">P</option>
                        <option value="Q">Q</option>
                        <option value="R">R</option>
                        <option value="S">S</option>
                        <option value="T">T</option>
                        <option value="U">U</option>
                        <option value="V">V</option>
                        <option value="W">W</option>
                        <option value="X">X</option>
                        <option value="Y">Y</option>
                        <option value="Z">Z</option>
                    </select>
                    <select name="bandeja" id="bandeja_ubicacion_tres" class="form-control">
                      <option value="b0">Bandeja</option>
                      <option value="bA">Bandeja A</option>
                      <option value="bB">Bandeja B</option>
                      <option value="bC">Bandeja C</option>
                      <option value="bD">Bandeja D</option>
                      <option value="bE">Bandeja E</option>
                      <option value="bF">Bandeja F</option>
                      <option value="bG">Bandeja G</option>
                      <option value="bH">Bandeja H</option>
                      <option value="bI">Bandeja I</option>
                      <option value="bJ">Bandeja J</option>
                      <option value="bK">Bandeja K</option>
                      <option value="bL">Bandeja L</option>
                      <option value="bM">Bandeja M</option>
                      <option value="bN">Bandeja N</option>
                      <option value="bÑ">Bandeja Ñ</option>
                  </select>
                    <select name="pasillo" id="pasillo_ubicacion_tres" class="form-control">
                      <option value="p0">Pasillo</option>
                      <option value="p1">Pasillo 1</option>
                      <option value="p2">Pasillo 2</option>
                      <option value="p3">Pasillo 3</option>
                      <option value="p4">Pasillo 4</option>
                      <option value="p5">Pasillo 5</option>
                      <option value="p6">Pasillo 6</option>
                      <option value="p7">Pasillo 7</option>
                      <option value="p8">Pasillo 8</option>
                      <option value="p9">Pasillo 9</option>
                      <option value="p10">Pasillo 10</option>
                      <option value="p11">Pasillo 11</option>
                      <option value="p12">Pasillo 12</option>
                      <option value="p13">Pasillo 13</option>
                      <option value="p14">Pasillo 14</option>
                      <option value="p15">Pasillo 15</option>
                      <option value="p16">Pasillo 16</option>
                      <option value="p17">Pasillo 17</option>
                      <option value="p18">Pasillo 18</option>
                      <option value="p19">Pasillo 19</option>
                      <option value="p20">Pasillo 20</option>
                      <option value="oficina">Oficina</option>
                    </select>
                </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" onclick="guardar_ubicacion_tres()">Guardar</button>
        </div>
      </div>
    </div>
  </div>

<script>
    function traspasar_bodega_preventa(){
        let url = '{{url("/compras/traspasar-bodega-preventa")}}';
        $.ajax({
            type:'get',
            url: url,
            success: function(resp){
                console.log(resp);
                if(resp.estado == 'ok'){
                    Vue.swal({
                        title:'Éxito!',
                        text:resp.mensaje,
                        icon:'success'
                    });
                }else{
                    Vue.swal({
                        title:'Error!',
                        text:resp.mensaje,
                        icon:'error'
                    });
                }
            },
            error: function(err){
                console.log(err);
                Vue.swal({
                    title:'Error!',
                    text:err.responseText,
                    icon:'error'
                });
            }
        })
    }

    function confirmar_traspaso_bodega_preventa(){
        Vue.swal({
            title: '¿Estás seguro?',
            text: "Esta acción traspasará los repuestos de la bodega de preventa a la bodega principal.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡Confirmar!'
        }).then((willDelete) => {
            if (willDelete.isConfirmed) {
                traspasar_bodega_preventa();
            }
        });
    }
</script>

 @endsection
