@extends('plantillas.app')



@section('titulo','Traspaso de mercadería')


@section('javascript')

  <script>

    function traspasar(){
      let codigo_interno = document.getElementById('cod_int').value;
      let cantidad = document.getElementById('cantidad').value;
      let origen = document.getElementById('origen').value;
      let destino = document.getElementById('destino').value;

      if(codigo_interno.trim() == 0){
        Vue.swal({
          icon:'info',
          position: 'top-end',
          text:'Debe ingresar un codigo interno',
          toast: true,
          showConfirmButton: false,
          timer: 3000,
        });
        return false;
      }

      if(cantidad.trim() == 0){
        Vue.swal({
          icon:'info',
          position: 'top-end',
          text:'Debe ingresar una cantidad',
          toast: true,
          showConfirmButton: false,
          timer: 3000,
        });
        return false;
      }

      if(cantidad.trim() <= 0 || cantidad < 0){
        Vue.swal({
          icon:'error',
          position: 'top-end',
          text:'La cantidad no puede ser negativa',
          toast: true,
          showConfirmButton: false,
          timer: 3000,
        });

        return false;

      }

      let url = '/guiadespacho/traspasar_mercaderia';
      let params = {
        codigo_interno: codigo_interno,
        cantidad: cantidad,
        origen: origen,
        destino: destino
      }

      // validar que no sea el mismo origen y destino

        if(origen == destino){
            Vue.swal({
                icon:'error',
                position: 'top-end',
                text:'Origen y destino no pueden ser iguales',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }

      var texto;

        // Obtener nombres de origen y destino para mostrar en el mensaje
        let origenTexto = document.getElementById('origen').selectedOptions[0].text;
        let destinoTexto = document.getElementById('destino').selectedOptions[0].text;
        
        texto = `¿Desea traspasar ${cantidad} unidades del repuesto ${codigo_interno}?\n\n` +
               `• Desde: ${origenTexto}\n` +
               `• Hacia: ${destinoTexto}`;


        console.log(params);
      Vue.swal({
        title:'¿Confirmar Traspaso?',
        text:texto,
        icon:'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, Traspasar!',
        cancelButtonText: 'Cancelar'
      }).then((result) => {

        if(result.isConfirmed){
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
          Vue.swal({
            icon:'info',
            title:'Procesando traspaso...',
            showConfirmButton: false,
            allowOutsideClick: false
          });
        },
        success: function(resp){
          console.log(resp);
          Vue.swal.close();

          // Verificar si hay error en la respuesta
          if(resp.error){
            Vue.swal({
              icon:'error',
              title:'Error en el traspaso',
              text: resp.error,
              confirmButtonColor: '#d33'
            });
            return false;
          }

          // Si el traspaso fue exitoso
          if(resp.success){
            // Limpiar los campos del formulario
            document.getElementById('cod_int').value = '';
            document.getElementById('cantidad').value = '';
            
            // Mostrar mensaje de éxito con detalles
            let mensaje = `Traspaso realizado correctamente:\n` +
                         `• Cantidad: ${resp.cantidad_traspasada} unidades\n` +
                         `• Stock anterior en origen: ${resp.stock_origen_anterior}\n` +
                         `• Stock actual en origen: ${resp.stock_origen_actual}`;

            Vue.swal({
              icon:'success',
              title:'¡Traspaso Exitoso!',
              text: mensaje,
              confirmButtonColor: '#28a745',
              confirmButtonText: 'Continuar'
            }).then(() => {
              // Recargar la página para actualizar el historial
              window.location.reload();
            });
          }

        },
        error: function(xhr, status, error){
          Vue.swal.close();
          console.log(xhr.responseText);
          
          let errorMessage = 'Error desconocido';
          try {
            let errorResponse = JSON.parse(xhr.responseText);
            errorMessage = errorResponse.message || errorResponse.error || xhr.responseText;
          } catch(e) {
            errorMessage = xhr.responseText || 'Error de conexión con el servidor';
          }

          Vue.swal({
            icon:'error',
            title:'Error en la solicitud',
            text: errorMessage,
            confirmButtonColor: '#d33'
          });
        }
      });

        }

      });





    }



    function cerrarSolicitud(){

      let num_solicitud_ingreso = $('#num_solicitud_ingreso').val();

      let num_solicitud = $('#num_solicitud').val();

        if(num_solicitud == '' || num_solicitud == undefined || num_solicitud.trim() == 0  ){

          var value = num_solicitud_ingreso;

        }else{

          var value = num_solicitud;

        }





        if(value == ''){

          Vue.swal({

            icon:'error',

            showConfirmButton: false,

            toast: true,

            timer:3000,

            text:'No tiene repuesto/s agregados',

            position: 'top-end'

          });

          return false;

        }



        let url = '/repuesto/avanzarNumTraspaso';



        $.ajax({

          type:'get',

          url: url,

          beforeSend: function(){

            Vue.swal({

              icon:'info',

              title:'Cargando ...'

            });

          },

          success: function(resp){

            Vue.swal.close();



            if(resp == 'OK'){

              Vue.swal({

                icon:'success',

                title:'Exito',

                text:''

              });



            }

            var myTimeout = setTimeout(redireccionar, 1000);



            function redireccionar() {

              window.location.href = "http://panchoserver.ddns.net/guiadespacho/traspaso_mercaderia";

            }



          },

          error: function(error){

            Vue.swal({

              icon:'error',

              title: error.responseText

            });

          }



        });

    }



    function buscar_repuesto(){
      let quien = 1;
      let codigo_interno = document.getElementById('cod_int').value;

      if(codigo_interno.trim() == 0 || codigo_interno == ''){
        Vue.swal({
          icon:'info',
          text:'Debe ingresar un codigo interno',
          toast: true,
          showConfirmButton:false,
          timer:3000,
          position:'top-end'
        });

        return false;

      }

      let url = '{{url("repuesto/buscarcodigo")}}'+'/'+quien+codigo_interno;
      $.ajax({
        type:'get',
        url: url,
        beforeSend:function(){
          Vue.swal({
            icon:'info',
            title:'Cargando ...',
            showConfirmButton:false,
            toast:true,
            position:'top-end'
          });
        },
        success: function(resp){
          Vue.swal.close();
          if(resp == -1){
            Vue.swal({
              icon:'error',
              text:'No se encontró el repuesto',
            });
            return false;
          }

          let r = JSON.parse(resp[0]);

          $('#tbody_repuestos').empty();

          r.forEach(e => {

              $('#tbody_repuestos').append(`

                <tr>

                  <td class='letra_pequeña'>`+e.codigo_interno+` </td>

                  <td class='letra_pequeña'>`+e.descripcion+` </td>

                  <td class='letra_pequeña'>`+e.stock_actual+`</td>

                  <td><button class='btn btn-outline-info btn-sm' onclick="detalle_repuesto(`+e.id+`)"><i class='fas fa-search'></i>Detalle </button></td>

                </tr>

              `);

          });

        },

        error: function(error){

          console.log(error.responseText);

        }

      })

    }

    function devolucion_mercaderia(id){
        Vue.swal({
            title:'¿Revertir Traspaso?',
            text:'¿Está seguro de que desea revertir este traspaso? Esta acción devolverá la mercadería a su ubicación original.',
            icon:'warning',
            showCancelButton:true,
            confirmButtonColor:'#3085d6',
            cancelButtonColor:'#d33',
            confirmButtonText:'Sí, revertir!',
            cancelButtonText:'Cancelar'
        }).then((result) => {
            if(result.isConfirmed){
                confirmar_eliminar_traspaso(id);
            }
        })
    }

    function confirmar_eliminar_traspaso(id){
        let url = '/guiadespacho/eliminar_mercaderia/'+id;
        $.ajax({
            type:'get',
            url: url,
            beforeSend: function(){
                Vue.swal({
                    icon:'info',
                    title:'Procesando eliminación...',
                    showConfirmButton:false,
                    allowOutsideClick: false
                });
            },
            success: function(resp){
                console.log(resp);
                Vue.swal.close();
                
                // Verificar si hay error en la respuesta
                if(resp.error){
                    Vue.swal({
                        icon:'error',
                        title:'Error al eliminar',
                        text: resp.error,
                        confirmButtonColor: '#d33'
                    });
                    return false;
                }

                // Si la eliminación fue exitosa
                if(resp.success){
                    Vue.swal({
                        icon:'success',
                        title:'¡Traspaso Revertido!',
                        text: resp.message,
                        confirmButtonColor: '#28a745',
                        confirmButtonText: 'Continuar'
                    }).then(() => {
                        // Recargar la página para actualizar el historial
                        window.location.reload();
                    });
                } else {
                    // Para compatibilidad con respuestas antiguas
                    if(resp == 'OK'){
                        Vue.swal({
                            icon:'success',
                            title:'Éxito',
                            text:'Traspaso eliminado correctamente',
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                }
            },
            error: function(xhr, status, error){
                Vue.swal.close();
                console.log(xhr.responseText);
                
                let errorMessage = 'Error desconocido';
                try {
                    let errorResponse = JSON.parse(xhr.responseText);
                    errorMessage = errorResponse.message || errorResponse.error || xhr.responseText;
                } catch(e) {
                    errorMessage = xhr.responseText || 'Error de conexión con el servidor';
                }

                Vue.swal({
                    icon:'error',
                    title:'Error en la solicitud',
                    text: errorMessage,
                    confirmButtonColor: '#d33'
                });
            }
        });
    }

    function detalle_repuesto(id){

        let url = '/repuesto/buscaridrep/'+id;


        $.ajax({

          type:'get',

          url: url,

          beforeSend: function(){

            Vue.swal({

              icon:'info',

              title:'Cargando ...',

              showConfirmButton:false,

              toast:true,

              position:'top-end'

            });

          },

          success: function(resp){
            console.log(resp);

            Vue.swal.close();
            let repuesto = resp;
            // abrir modal
            $('#modal_informacion_repuesto').modal('show');
            $('#codigo_interno').val(repuesto.codigo_interno);
            $('#descripcion').val(repuesto.descripcion);
            $('#marca_').val(repuesto.marca);
            $('#stock_actual').val(repuesto.stock_actual);

          },

          error: function(error){

            console.log(error.responseText);

          }

        })
    }


    function cargar_info(id,marca, descripcion, ubicacion){



      $('#id_repuesto_ubicacion').val(id);

      $('#marca').empty();

      $('#modal_modificar_ubicacion_Label').empty();

      $('#ubi_uno').empty();



      $('#marca').append('<p>Marca: '+marca+'</p>');

      $('#modal_modificar_ubicacion_Label').append(descripcion);

      $('#ubi_uno').append('<p>Ubicación: '+ubicacion+'</p>');

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



                    }else{

                      Vue.swal({

                          text: "No se pueden guardar las dos ubicaciones en Tienda",

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



      function notificar(){

        let num_solicitud_ingreso = $('#num_solicitud_ingreso').val();

        let num_solicitud = $('#num_solicitud').val();

        if(num_solicitud == '' || num_solicitud == undefined || num_solicitud.trim() == 0  ){

          var value = num_solicitud_ingreso;

        }else{

          var value = num_solicitud;

        }



        if(value == ''){

          Vue.swal({

            icon:'error',

            showConfirmButton: false,

            toast: true,

            timer:3000,

            text:'Seleccione un repuesto',

            position: 'top-end'

          });

          return false;

        }



        let url = '/solicitud_traspaso';



        $.ajaxSetup({

                headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                }

            });



            $.ajax({

              type:'post',

              url: url,

              data: {num_solicitud: value},

              beforeSend: function(){

                Vue.swal({

                  icon:'info',

                  title:'Cargando ...',

                  showConfirmButton: false

                });



              },

              success: function(resp){

                Vue.swal.close();

                let r=JSON.parse(resp);

                            if(r.estado=='OK'){

                                var config="location=yes,height=570,width=720,scrollbars=yes,status=yes";

                                var w=window.open(r.mensaje,'_blank',config);

                                w.focus();

                            }else{

                                Vue.swal({

                                    title: r.estado,

                                    text: r.mensaje,

                                    icon: 'error',

                                });

                            }

              },

              error: function(error){

                Vue.swal({

                  icon:'error',

                  title:'error',

                  text: error.responseText

                });

              }

            })



      }





function enter_buscar_repuesto(event){
  if(event.keyCode == 13){
    buscar_repuesto();
  }
}

  </script>

@endsection



@section('style')
<style>
.letra_pequeña{
    font-size: 12px;
}

/* Estilos principales con paleta neutra */
.container-fluid {
    background-color: #f8f9fa;
    min-height: 100vh;
    padding: 30px;
}

.control-panel {
    background: white;
    border: 2px solid #6c757d;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.control-panel h5 {
    color: #212529;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #6c757d;
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

.form-group.floating label {
    color: #6c757d;
    font-weight: 500;
}

.floating:focus + label,
.floating:not(:placeholder-shown) + label {
    color: #212529 !important;
}

/* Botones elegantes */
.btn-search {
    background: #6c757d;
    border: none;
    color: white;
    padding: 8px 12px;
    border-radius: 8px;
    margin-left: 5px;
    transition: all 0.3s ease;
}

.btn-search:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    color: white;
}

.btn-confirm {
    background: #6c757d;
    border: none;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-confirm:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    color: white;
}

/* Cards para las tablas */
.table-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 20px;
}

.table-header {
    background: #6c757d;
    color: white;
    padding: 15px 20px;
    font-weight: 600;
}

.table {
    margin-bottom: 0;
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
    padding: 12px;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
    transition: all 0.2s ease;
}

.table tbody td {
    color: #212529;
    border: 1px solid #dee2e6;
    padding: 12px;
    vertical-align: middle;
}

/* Tabla de historial con fondo especial */
.history-table {
    background: #e3f2fd !important;
}

.history-table tbody {
    background: #e3f2fd !important;
}

.history-table tbody tr:hover {
    background: #bbdefb !important;
}

/* Botones de acción */
.btn-action {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.85em;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-action.btn-outline-danger {
    border-color: #6c757d;
    color: #6c757d;
}

.btn-action.btn-outline-danger:hover {
    background: #6c757d;
    border-color: #6c757d;
    color: white;
    transform: translateY(-1px);
}

/* Iconos */
.fas {
    margin-right: 5px;
}

/* Input de número */
input[type="number"], input[type="text"], select {
    background-color: white !important;
    color: #212529 !important;
}

input[type="number"]:focus, input[type="text"]:focus, select:focus {
    background-color: white !important;
    color: #212529 !important;
}

/* Responsive */
@media (max-width: 768px) {
    .control-panel {
        margin-bottom: 30px;
    }
    
    .table-responsive {
        border-radius: 10px;
    }
}

/* Estados de elementos */
.status-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}
</style>

@endsection



@section('contenido_titulo_pagina')
    @include('fragm.breadcrumb', ['titulo' => 'Inventario', 'titulo_modulo' => 'Traspaso'])

@endsection



@section('contenido_ingresa_datos')
<div class="container-fluid">
  <div class="row">
    <!-- Panel de Control -->
    <div class="col-md-3">
      <div class="control-panel">
        <h5><i class="fas fa-exchange-alt"></i>Panel de Traspaso</h5>
        
        <!-- Búsqueda de Producto -->
        <div class="mb-4">
          <label class="form-label text-muted mb-2"><i class="fas fa-search"></i> Búsqueda de Producto</label>
          <div class="d-flex align-items-end">
            <div class="form-group floating flex-grow-1">
              <input type="text" name="" id="cod_int" class="form-control floating" onkeyup="enter_buscar_repuesto(event)">
              <label for="" class="floating">Código interno</label>
            </div>
            <button class="btn btn-search" onclick="buscar_repuesto()">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>

        <!-- Configuración de Traspaso -->
        <div class="mb-4">
          <label class="form-label text-muted mb-2"><i class="fas fa-cogs"></i> Configuración de Traspaso</label>
          
          <div class="form-group floating mb-3">
            <select name="origen" id="origen" class="form-control floating">
              @foreach($locales as $b)
                <option value="{{$b->id}}">{{$b->local_nombre}}</option>
              @endforeach
            </select>
            <label for="" class="floating"><i class="fas fa-arrow-up"></i> Origen</label>
          </div>
          
          <div class="form-group floating mb-3">
            <select name="destino" id="destino" class="form-control floating">
              @foreach($locales as $b)
                <option value="{{$b->id}}">{{$b->local_nombre}}</option>
              @endforeach
            </select>
            <label for="" class="floating"><i class="fas fa-arrow-down"></i> Destino</label>
          </div>

          <div class="form-group floating mb-4">
            <input type="number" name="" id="cantidad" class="form-control floating" min="1">
            <label for="" class="floating"><i class="fas fa-sort-numeric-up"></i> Cantidad</label>
          </div>
        </div>

        <!-- Botón de Confirmación -->
        <button class="btn btn-confirm w-100" onclick="traspasar()">
          <i class="fas fa-check"></i> Confirmar Traspaso
        </button>
      </div>
    </div>

    <!-- Área de Contenido -->
    <div class="col-md-9">
      <div class="row">
        
        <!-- Tabla de Productos Disponibles -->
        <div class="col-md-8">
          <div class="table-card">
            <div class="table-header">
              <i class="fas fa-boxes"></i> Productos Disponibles
            </div>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th scope="col"><i class="fas fa-barcode"></i> Cod Int</th>
                    <th scope="col" style="width: 40%;"><i class="fas fa-tag"></i> Descripción</th>
                    <th scope="col"><i class="fas fa-cubes"></i> Stock</th>
                    <th scope="col"><i class="fas fa-cogs"></i> Acción</th>
                  </tr>
                </thead>
                <tbody id="tbody_repuestos">
                  <!-- Contenido dinámico -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Tabla de Historial de Traspasos -->
        <div class="col-md-4">
          <div class="table-card history-table">
            <div class="table-header">
              <i class="fas fa-history"></i> Historial de Traspasos
            </div>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th scope="col"><i class="fas fa-barcode"></i> Código</th>
                    <th scope="col"><i class="fas fa-sort-numeric-up"></i> Cant.</th>
                    <th scope="col"><i class="fas fa-arrow-up"></i> Desde</th>
                    <th scope="col"><i class="fas fa-arrow-down"></i> Hacia</th>
                    <th scope="col"><i class="fas fa-tools"></i> Acción</th>
                  </tr>
                </thead>
                <tbody id="tbody_detalle_repuestos">
                  @if(isset($historial))
                    @foreach($historial as $h)
                      <tr>
                        <td class="letra_pequeña">{{$h->codigo_interno}}</td>
                        <td class="letra_pequeña">
                          <span class="badge bg-secondary">{{$h->stock}}</span>
                        </td>
                        <td class="letra_pequeña">{{$h->origen_nombre}}</td>
                        <td class="letra_pequeña">{{$h->destino_nombre}}</td>
                        <td class="letra_pequeña">
                          <button class="btn btn-action btn-outline-danger btn-sm" onclick="devolucion_mercaderia({{$h->id}})">
                            <i class="fas fa-undo"></i>
                          </button>
                        </td>
                      </tr>
                    @endforeach
                  @else
                    <tr>
                      <td colspan="5" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                        No hay traspasos registrados
                      </td>
                    </tr>
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

  <!-- Modal Modificar Ubicación -->
<div class="modal fade" id="modal_modificar_ubicacion" tabindex="-1" role="dialog" aria-labelledby="modal_modificar_ubicacion_Label" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
      
      <div class="modal-header" style="background: #6c757d; color: white; border-radius: 15px 15px 0 0; border: none;">
        <div class="d-flex align-items-center">
          <img src="{{asset('storage/imagenes/ips_logo.png')}}" alt="Logo" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 15px; background: white; padding: 5px;">
          <h5 class="modal-title mb-0" id="modal_modificar_ubicacion_Label">
            <i class="fas fa-map-marker-alt me-2"></i>Modificar Ubicación
          </h5>
        </div>
        <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close" style="background: none; border: none; color: white; font-size: 1.5rem;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div class="modal-body p-4" id="modal_body_ubicacion" style="background: white;">
        <input type="hidden" id="id_repuesto_ubicacion" value="">
        
        <div class="row mb-4">
          <div class="col-12">
            <div class="alert alert-info" style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; border-radius: 8px;">
              <i class="fas fa-info-circle me-2"></i>
              <strong>Información del Producto</strong>
              <div class="mt-2">
                <p id="marca" class="mb-1"><strong>Marca:</strong> <span class="text-muted">Cargando...</span></p>
                <p id="ubi_uno" class="mb-0"><strong>Ubicación Actual:</strong> <span class="text-muted">Cargando...</span></p>
              </div>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label text-muted"><i class="fas fa-location-arrow me-2"></i>Nueva Ubicación</label>
        </div>

        <div class="select-ubicacion">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Local</label>
              <select name="ubicacion" id="ubicacion" class="form-control">
                @foreach($locales as $b)
                  <option value="{{$b->id}}">{{$b->local_nombre}}</option>
                @endforeach
              </select>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Piso</label>
              <select name="piso" id="piso" class="form-control">
                <option value="p1">Piso 1</option>
                <option value="p2">Piso 2</option>
                <option value="p3">Piso 3</option>
              </select>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Estantería</label>
              <select name="estanteria" id="estanteria" class="form-control">
                <option value="0">Seleccione Estantería</option>
                @for ($i = ord('A'); $i <= ord('Z'); $i++)
                  <option value="{{ chr($i) }}">{{ chr($i) }}</option>
                @endfor
                <option value="Ñ">Ñ</option>
              </select>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Bandeja</label>
              <select name="bandeja" id="bandeja" class="form-control">
                <option value="b0">Seleccione Bandeja</option>
                @for ($i = ord('A'); $i <= ord('Ñ'); $i++)
                  <option value="b{{ chr($i) }}">Bandeja {{ chr($i) }}</option>
                @endfor
              </select>
            </div>
            
            <div class="col-12">
              <label class="form-label">Pasillo</label>
              <select name="pasillo" id="pasillo" class="form-control">
                <option value="p0">Seleccione Pasillo</option>
                @for ($i = 1; $i <= 20; $i++)
                  <option value="p{{ $i }}">Pasillo {{ $i }}</option>
                @endfor
              </select>
            </div>
          </div>
        </div>
      </div>
      
      <div class="modal-footer" style="background: #f8f9fa; border-radius: 0 0 15px 15px; border: none;">
        <button type="button" class="btn" style="background: #6c757d; color: white; border-radius: 8px;" data-dismiss="modal">
          <i class="fas fa-times me-2"></i>Cerrar
        </button>
        <button type="button" class="btn" style="background: #28a745; color: white; border-radius: 8px;" onclick="guardar_ubicacion()">
          <i class="fas fa-save me-2"></i>Guardar Cambios
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Información del Repuesto -->
<div class="modal fade" id="modal_informacion_repuesto" tabindex="-1" role="dialog" aria-labelledby="modal_informacion_repuesto_label" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
      
      <div class="modal-header" style="background: #6c757d; color: white; border-radius: 15px 15px 0 0; border: none;">
        <h5 class="modal-title" id="modal_informacion_repuesto_label">
          <i class="fas fa-info-circle me-2"></i>Información del Repuesto
        </h5>
        <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close" style="background: none; border: none; color: white; font-size: 1.5rem;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div class="modal-body p-4" id="modal_body_repuesto" style="background: white;">
        <div class="row">
          
          <!-- Imagen del producto -->
          <div class="col-md-5 mb-4">
            <div class="card h-100" style="border: 2px solid #dee2e6; border-radius: 10px;">
              <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <small class="text-muted"><i class="fas fa-camera me-1"></i>Imagen del Producto</small>
              </div>
              <div class="card-body p-2 d-flex align-items-center justify-content-center">
                <img src="https://placehold.co/300x200/f8f9fa/6c757d?text=Imagen+del+Producto" alt="Repuesto" class="img-fluid" style="border-radius: 8px; max-height: 200px;">
              </div>
            </div>
          </div>
          
          <!-- Información del producto -->
          <div class="col-md-7">
            <div class="row g-3">
              
              <div class="col-12">
                <label class="form-label text-muted"><i class="fas fa-barcode me-1"></i>Código Interno</label>
                <input type="text" id="codigo_interno" class="form-control" readonly style="background: #f8f9fa; border: 2px solid #dee2e6;">
              </div>
              
              <div class="col-12">
                <label class="form-label text-muted"><i class="fas fa-tag me-1"></i>Descripción</label>
                <textarea id="descripcion" class="form-control" readonly rows="3" style="background: #f8f9fa; border: 2px solid #dee2e6; resize: none;"></textarea>
              </div>
              
              <div class="col-md-6">
                <label class="form-label text-muted"><i class="fas fa-industry me-1"></i>Marca</label>
                <input type="text" id="marca_" class="form-control" readonly style="background: #f8f9fa; border: 2px solid #dee2e6;">
              </div>
              
              <div class="col-md-6">
                <label class="form-label text-muted"><i class="fas fa-cubes me-1"></i>Stock Actual</label>
                <div class="input-group">
                  <input type="text" id="stock_actual" class="form-control" readonly style="background: #f8f9fa; border: 2px solid #dee2e6;">
                  <span class="input-group-text" style="background: #6c757d; color: white; border: 2px solid #6c757d;">
                    <i class="fas fa-boxes"></i>
                  </span>
                </div>
              </div>
              
            </div>
            
            <!-- Información adicional -->
            <div class="mt-4">
              <div class="alert alert-info" style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; border-radius: 8px;">
                <i class="fas fa-info-circle me-2"></i>
                <small><strong>Nota:</strong> Esta información es solo de consulta y se actualiza en tiempo real.</small>
              </div>
            </div>
          </div>
          
        </div>
      </div>
      
      <div class="modal-footer" style="background: #f8f9fa; border-radius: 0 0 15px 15px; border: none;">
        <button type="button" class="btn" style="background: #6c757d; color: white; border-radius: 8px;" data-dismiss="modal">
          <i class="fas fa-times me-2"></i>Cerrar
        </button>
      </div>
    </div>
  </div>
</div>


<!-- DATOS DE VITAL IMPORTANCIA -->

@if(isset($num_solicitud))

<input type="hidden" name="num_solicitud" id="num_solicitud" value="{{$num_solicitud}}">

@endif



<input type="hidden" name="num_solicitud_ingreso" id="num_solicitud_ingreso">

@endsection
