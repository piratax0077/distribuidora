@extends('plantillas.app')
@section('javascript')
    <script type="text/javascript">

        setInterval(() => {

            let url = '/revisar_solicitud';
            $.ajax({
              type:'get',
              url: url,
              success: function(resp){

                if(resp > 0){
                  $('#campana').empty();
                  $('#campana').append(`
                  <a href='javascript:dame_solicitudes()'>
                    <img src="{{asset('storage/imagenes/foco-notification.png')}}" width="30px"/>
                    <span class="badge badge-danger">`+resp+` </span>
                  </a>
                  `);
                }else{
                  $('#campana').empty();
                  $('#campana').append(`
                    <img src="{{asset('storage/imagenes/foco-notification.png')}}" width="30px"/>
                    <span class='badge badge-danger'>`+resp+` </span>
                  `);
                }
              },
              error: function(err){
                console.log(err.responseText);
              }
            })
        }, 15000);

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

        function dame_solicitudes(){

          let url = '/dame_solicitudes';
          $.ajax({
            type:'get',
            url: url,
            success: function(resp){
              $('#panel_informativo').html(resp);
            },
            error: function(err){
              console.log(err);
            }
          })
        }

        function procesar_traspaso_repuesto(repuesto,solicitud){

          Vue.swal({
            title: '¿Estás seguro?',
            text: repuesto.descripcion+' se descontarán '+solicitud.cantidad+' del stock de bodega',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, transferir!'
          }).then((result) => {
            if (result.isConfirmed) {
              let url = '/procesar_solicitud';
              let data = {'id_repuesto': repuesto.id,'cantidad': solicitud.cantidad,'id_solicitud': solicitud.id};
              $.ajaxSetup({
                  headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  }
              });
              $.ajax({
                type:'post',
                data: data,
                url: url,
                success: function(resp){
                  Vue.swal.fire(
                    'Transferido!',
                    resp.descripcion+' ha sido transferido a tienda.',
                    'success'
                  )
                  dame_solicitudes();
                },
                error: function(err){
                  Vue.swal({
                    icon:'error',
                    text: err.responseJSON ? err.responseJSON.error : 'Error al procesar la solicitud.'
                  });
                }
              })

            }
          });
        }


        function eliminar_traspaso_repuesto(id_solicitud){
          Vue.swal({
            title: '¿Estás seguro?',
            text: "No se podrá procesar la solicitud",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡eliminar!'
          }).then((result) => {
            if (result.isConfirmed) {
              let url = '/eliminar_solicitud/'+id_solicitud;
              $.ajax({
                type:'get',
                url: url,
                success: function(resp){
                  $('#panel_informativo').html(resp);
                  Vue.swal.fire(
                    'Eliminado!',
                    'La solicitud ha sido eliminada',
                    'success'
                  )
                },
                error: function(err){
                  console.log(err.responseText);
                }
              })

            }else{
              espere("La solicitud no se ha eliminado");
            }
          });
        }

        function cargar_devolucion(num_nc){
          let url ='/guiadespacho/dame_devoluciones/'+num_nc;
          $.ajax({
            type:'get',
            url: url,
            success: function(resp){
              $('#resultado_repuestos_devolucion').empty();
              $('#resultado_repuestos_devolucion').append(resp);
            },
            error: function(error){
              console.log(error.responseText);
            }
          })
        }

        function opciones_devolucion(
    devolucionId,
    cantidad,
    codigoInterno,
    opcion
) {
            const aceptar = opcion === '+';
            const accion = aceptar ? 'ACEPTAR' : 'RECHAZAR';
            const color = aceptar ? '#28a745' : '#dc3545';

            const numeroDocumento = $.trim($('#num_nc').val());

            if (!numeroDocumento) {
                Vue.swal({
                    icon: 'error',
                    text: 'No fue posible identificar la devolución.'
                });

                return;
            }

            Vue.swal({
                title: aceptar
                    ? '¿Aceptar producto devuelto?'
                    : '¿Rechazar producto devuelto?',
                icon: 'warning',
                html: `
                    <div class="text-left">
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <th>Cantidad</th>
                                    <td>${cantidad}</td>
                                </tr>
                                <tr>
                                    <th>Acción</th>
                                    <td>
                                        <strong>${accion}</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="alert ${
                            aceptar ? 'alert-success' : 'alert-danger'
                        } mb-0">
                            ${
                                aceptar
                                    ? 'El producto será incorporado al stock de la ubicación indicada.'
                                    : 'El producto será rechazado y no modificará el inventario.'
                            }
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: color,
                cancelButtonColor: '#6c757d',
                confirmButtonText: aceptar
                    ? 'Sí, aceptar'
                    : 'Sí, rechazar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: '/guiadespacho/opciones_devolucion',
                    dataType: 'json',
                    data: {
                        id_devolucion: devolucionId,
                        opcion: opcion
                    },
                    headers: {
                        'X-CSRF-TOKEN':
                            $('meta[name="csrf-token"]').attr('content')
                    },

                    beforeSend: function () {
                        Vue.swal({
                            title: 'Procesando devolución',
                            text: 'Espere un momento.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: function () {
                                Vue.swal.showLoading();
                            }
                        });
                    },

                    success: function (resp) {
                        Vue.swal.close();

                        if (!resp || Number(resp.estado) !== 1) {
                            Vue.swal({
                                icon: 'error',
                                text: resp && resp.error
                                    ? resp.error
                                    : 'No fue posible procesar la devolución.'
                            });

                            return;
                        }

                        $('#resultado_repuestos_devolucion')
                            .html(resp.html);

                        Vue.swal({
                            icon: 'success',
                            title: aceptar
                                ? 'Devolución aceptada'
                                : 'Devolución rechazada',
                            text: resp.mensaje,
                            timer: 2200,
                            showConfirmButton: false
                        });
                    },

                    error: function (xhr) {
                        Vue.swal.close();

                        let mensaje =
                            'Error al procesar la devolución.';

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.error) {
                                mensaje = xhr.responseJSON.error;
                            } else if (xhr.responseJSON.message) {
                                mensaje = xhr.responseJSON.message;
                            } else if (xhr.responseJSON.errors) {
                                const clave = Object.keys(
                                    xhr.responseJSON.errors
                                )[0];

                                if (
                                    clave &&
                                    xhr.responseJSON.errors[clave].length
                                ) {
                                    mensaje =
                                        xhr.responseJSON.errors[clave][0];
                                }
                            }
                        }

                        Vue.swal({
                            icon: 'error',
                            text: mensaje
                        });
                    }
                });
            });
        }

        function buscar_devolucion(){
         let num_nc = $('#num_devolucion').val();
         if(num_nc.trim() === ''){
          Vue.swal({
            icon:'error',
            text:'Debe ingresar un numero de nota de crédito'
          });
          return false;
         }
         let url = '/guiadespacho/historial_devolucion/'+num_nc;

         $.ajax({
          type:'get',
          url: url,
          beforeSend: function(){
            Vue.swal({
              icon:'info',
              text:'ESPERE ...'
            });
          },
          success: function(resp){
            Vue.swal.close();
            $('#panel_resultado_solicitudes').empty();
            $('#panel_resultado_solicitudes').append(resp);
          },
          error: function(error){
            console.log(error.responseText);
          }
         });
        }
    </script>
@endsection
@section('style')
<style>
      /* Estilos generales para Pedidos a Bodega */
      body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      }

      .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .page-header h4 {
        margin: 0;
        font-weight: 700;
        font-size: 1.5rem;
      }

      .notification-badge {
        position: relative;
        cursor: pointer;
        transition: transform 0.3s ease;
      }

      .notification-badge:hover {
        transform: scale(1.1);
      }

      .notification-badge img {
        border-radius: 50%;
        filter: drop-shadow(0 2px 8px rgba(0,0,0,0.2));
      }

      .notification-badge .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        padding: 4px 8px;
        border-radius: 10px;
        font-size: 0.75rem;
        animation: pulse 2s infinite;
      }

      @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
      }

      .user-info-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        border: none;
      }

      .user-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 4px solid #667eea;
        object-fit: cover;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      }

      .user-detail {
        text-align: left;
        padding: 0 15px;
      }

      .user-detail-label {
        font-size: 0.75rem;
        color: #718096;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 5px;
        display: block;
      }

      .user-detail-value {
        font-size: 1rem;
        color: #2d3748;
        font-weight: 600;
      }

      .documents-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        min-height: 400px;
      }

      .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
      }

      .document-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        height: 100%;
        cursor: pointer;
      }

      .document-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      }

      .document-card img {
        height: 120px;
        object-fit: contain;
        padding: 15px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      }

      .document-card-body {
        padding: 20px;
        background: white;
      }

      .document-card-title {
        font-weight: 700;
        font-size: 1.2rem;
        color: #667eea;
        margin: 0;
      }

      .document-card-title a {
        color: #667eea;
        text-decoration: none;
        transition: color 0.3s ease;
      }

      .document-card-title a:hover {
        color: #764ba2;
      }

      .no-documents-alert {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        border: none;
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        color: #721c24;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(252, 182, 159, 0.3);
      }

      .search-section {
        background: white;
        border-radius: 15px;
        padding: 0;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-top: 30px;
        overflow: hidden;
      }

      .search-panel {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 35px;
        height: 100%;
        min-height: 300px;
      }

      .search-panel label {
        color: white;
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 10px;
      }

      .search-panel .form-control {
        border-radius: 10px;
        border: none;
        padding: 12px 16px;
        font-size: 0.95rem;
      }

      .search-panel .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.3);
      }

      .btn-search {
        background: white;
        color: #667eea;
        border: none;
        border-radius: 10px;
        padding: 12px 30px;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-top: 15px;
      }

      .btn-search:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 255, 255, 0.4);
        background: #f8f9fa;
      }

      .results-panel {
        padding: 35px;
        min-height: 300px;
        background: #f8f9fa;
      }

      #panel_informativo {
        padding: 30px;
        overflow: visible;
      }

      #panel_informativo .row {
        margin-left: -10px;
        margin-right: -10px;
      }

      #panel_informativo [class*="col-"] {
        padding-left: 10px;
        padding-right: 10px;
      }

      #resultado_repuestos_devolucion {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        min-height: 400px;
      }

      /* Logo mejorado */
      .logo-header {
        max-width: 150px;
        filter: drop-shadow(0 4px 12px rgba(0,0,0,0.15));
        margin-bottom: 20px;
      }

      /* Animaciones */
      .fade-in {
        animation: fadeIn 0.6s ease;
      }

      @keyframes fadeIn {
        from {
          opacity: 0;
          transform: translateY(20px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      /* Responsive */
      @media (max-width: 768px) {
        .page-header h4 {
          font-size: 1.2rem;
        }

        .user-info-card {
          text-align: center;
        }

        .user-detail {
          text-align: center;
          padding: 10px 0;
        }

        .search-panel,
        .results-panel {
          min-height: auto;
        }
      }



      /* Tarjetas compactas de solicitudes de devolución */
      /*
           * Clases exclusivas de esta vista compartida.
           * No se usa .document-card para evitar conflictos con pedidos_a_bodega.
           */
          .devoluciones-solicitudes {
              width: 100%;
          }

          .devoluciones-solicitudes__encabezado {
              display: flex;
              align-items: center;
              justify-content: space-between;
              gap: 12px;
              padding-bottom: 14px;
              margin-bottom: 20px;
              border-bottom: 1px solid #dee2e6;
          }

          .devoluciones-solicitudes__titulo {
              display: flex;
              align-items: center;
              gap: 10px;
              margin: 0;
              color: #343a40;
              font-size: 1.45rem;
              font-weight: 700;
          }

          .devoluciones-solicitudes__cantidad {
              display: inline-flex;
              align-items: center;
              justify-content: center;
              min-width: 34px;
              height: 34px;
              padding: 0 10px;
              border-radius: 17px;
              background: #343a40;
              color: #fff;
              font-weight: 700;
          }

          .solicitud-devolucion-card {
              width: 100%;
              max-width: 245px;
              height: 100%;
              margin: 0 auto;
              overflow: hidden;
              border: 1px solid #dee2e6;
              border-radius: 14px;
              background: #fff;
              box-shadow: 0 4px 14px rgba(0, 0, 0, .10);
              transition: transform .2s ease, box-shadow .2s ease;
          }

          .solicitud-devolucion-card:hover {
              transform: translateY(-4px);
              box-shadow: 0 8px 22px rgba(0, 0, 0, .15);
          }

          .solicitud-devolucion-card__cabecera {
              min-height: 86px;
              padding: 14px 16px;
              background: #343a40;
              color: #fff;
          }

          .solicitud-devolucion-card__cabecera-superior {
              display: flex;
              align-items: flex-start;
              justify-content: space-between;
              gap: 8px;
          }

          .solicitud-devolucion-card__titulo {
              margin: 0;
              color: #fff;
              font-size: 1rem;
              line-height: 1.25;
              font-weight: 700;
          }

          .solicitud-devolucion-card__estado {
              flex-shrink: 0;
              padding: 4px 8px;
              border-radius: 6px;
              background: #ffeb3b;
              color: #212529;
              font-size: .72rem;
              font-weight: 700;
          }

          .solicitud-devolucion-card__cuerpo {
              padding: 18px 20px 12px;
          }

          .solicitud-devolucion-card__logo {
              display: block;
              width: 90px;
              height: 90px;
              margin: 0 auto 18px;
              object-fit: contain;
          }

          .solicitud-devolucion-card__dato {
              display: grid;
              grid-template-columns: minmax(0, 1fr) auto;
              align-items: start;
              gap: 12px;
              padding: 10px 0;
              border-bottom: 1px solid #eceff1;
          }

          .solicitud-devolucion-card__dato:last-child {
              border-bottom: 0;
          }

          .solicitud-devolucion-card__etiqueta {
              color: #6c757d;
              line-height: 1.35;
          }

          .solicitud-devolucion-card__valor {
              max-width: 115px;
              color: #343a40;
              text-align: right;
              line-height: 1.35;
              font-weight: 700;
              overflow-wrap: anywhere;
          }

          .solicitud-devolucion-card__valor--pendiente {
              color: #f1c40f;
          }

          .solicitud-devolucion-card__pie {
              padding: 8px 20px 20px;
          }

          .solicitud-devolucion-card__boton {
              width: 100%;
              border-radius: 7px;
              font-weight: 600;
          }

          .devoluciones-solicitudes__vacio {
              padding: 45px 20px;
              border: 1px dashed #ced4da;
              border-radius: 14px;
              background: #f8f9fa;
              color: #6c757d;
              text-align: center;
          }

          .devoluciones-solicitudes__vacio i {
              margin-bottom: 12px;
              font-size: 2.5rem;
              opacity: .65;
          }

          @media (max-width: 767.98px) {
              .solicitud-devolucion-card {
                  max-width: 100%;
              }

              .devoluciones-solicitudes__titulo {
                  font-size: 1.2rem;
              }
          }

</style>
@endsection
@section('contenido_titulo_pagina')
  <div class="page-header fade-in">
    <h4><i class="fas fa-truck-loading mr-3"></i>Solicitudes de Devolución de Mercadería</h4>
    <div class="notification-badge" id="campana">
      <img src="{{asset('storage/imagenes/foco-notification.png')}}" width="35px"/>
    </div>
  </div>
@endsection

@section('contenido_ingresa_datos')

    <div class="container-fluid">
      {{-- <img src="{{asset('storage/imagenes/ips_logo.png')}}" alt="IPS Logo" class="logo-header fade-in"> --}}

      <div class="user-info-card fade-in">
        <div class="row align-items-center">
          <div class="col-md-2 text-center">
            <img src="{{url('usuarios/avatar/'.Auth::user()->image_path)}}" alt="Avatar" class="user-avatar">
          </div>
          <div class="col-md-10">
            <div class="row">
              <div class="col-md-3 user-detail">
                <span class="user-detail-label"><i class="fas fa-user mr-2"></i>Nombre</span>
                <span class="user-detail-value" id="cajero_data">{{Auth::user()->name}}</span>
              </div>
              <div class="col-md-3 user-detail">
                <span class="user-detail-label"><i class="fas fa-id-badge mr-2"></i>Rol</span>
                <span class="user-detail-value" id="rol_data">{{Auth::user()->rol->nombrerol}}</span>
              </div>
              <div class="col-md-3 user-detail">
                <span class="user-detail-label"><i class="fas fa-phone mr-2"></i>Teléfono</span>
                <span class="user-detail-value" id="telefono_data">{{Auth::user()->telefono}}</span>
              </div>
              <div class="col-md-3 user-detail">
                <span class="user-detail-label"><i class="fas fa-envelope mr-2"></i>Correo</span>
                <span class="user-detail-value" id="email_data" style="font-size: 0.85rem;">{{Auth::user()->email}}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-6 mb-4">
          <div class="documents-section fade-in" id="panel_informativo">
            @include('fragm.tarjetas_pedidos', [
              'solicitudes' => $solicitudes
            ])
          </div>
        </div>

        <div class="col-lg-6 mb-4">
          <div id="resultado_repuestos_devolucion" class="fade-in">
            <div class="text-center text-muted py-5">
              <i class="fas fa-hand-pointer fa-3x mb-3" style="opacity: 0.3;"></i>
              <p>Selecciona un documento para ver los detalles</p>
            </div>
          </div>
        </div>
      </div>

        <div class="search-section fade-in">
          <div class="row g-0">
            <div class="col-lg-5">
              <div class="search-panel">
                <h4 class="text-white mb-4">
                  <i class="fas fa-search mr-2"></i>Búsqueda de Devoluciones
                </h4>
                <div class="form-group">
                  <label for="num_devolucion">
                    <i class="fas fa-file-invoice mr-2"></i>Número de Nota de Crédito
                  </label>
                  <input type="text" class="form-control" placeholder="Ej: 12345" id="num_devolucion">
                  <small class="form-text text-white-50 mt-2">
                    Ingrese el número de NC para buscar el historial
                  </small>
                </div>
                <button onclick="buscar_devolucion()" class="btn btn-search btn-block">
                  <i class="fas fa-search mr-2"></i>Buscar Devolución
                </button>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="results-panel" id="panel_resultado_solicitudes">
                <div class="text-center text-muted py-5">
                  <i class="fas fa-search fa-3x mb-3" style="opacity: 0.3;"></i>
                  <h5>Realiza una búsqueda</h5>
                  <p>Ingresa el número de nota de crédito para ver el historial</p>
                </div>
              </div>
            </div>
          </div>
    </div>
@endsection
