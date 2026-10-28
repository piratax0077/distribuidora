@extends('plantillas.app')
@section('titulo','HOME')
@section('javascript')
<script type="text/javascript">
    function mostrar_deliverys_pendientes(){
        let f=new Date();
        let fecha_hoy= f.getFullYear() + "-" + (f.getMonth() +1) + "-" + f.getDate();
        let url='{{url("reportes/deliverys_pendientes")}}/'+fecha_hoy;
        $.ajax({
            type:'GET',
            beforeSend: function () {
                //$('#mensajes').html("Cargando Formas de Pago...");
                },
            url:url,
            success:function(html){
                if(html=="0")
                {
                    console.log(html);
                    console.log("no hay deliverys pendientes o no es admin");
                }else{
                    $("#mostrar_pendientes").html(html);
                    $("#delivery-modal").modal("show");
                }

            },
            error: function(error){
                Vue.swal({
                    title: 'ERROR',
                    text: error.responseText,
                    icon: 'error',
                });
            }

        });
    }

    function dameultimosrepuestos(){
      let url='{{url("repuesto/dameultimosrepuestos")}}';
        $.ajax({
          type:'get',
          url:url,
          beforeSend: function(){

          },
          success: function(resp){
            console.log(resp);
            if(resp == "0"){
              console.log("No es ADMIN o no tiene permisos");
              return false;
            }
            if(resp[0].length > 0){
              $('#modal_body_ultimos_repuestos').empty();
              var contador = 0;
              resp[0].forEach(e => {
                let precio_format = Number(e.precio_venta).toFixed(0);
                precio_total = precio_format;
                $('#modal_body_ultimos_repuestos').append(`
                <div class="col-md-3">

                  <img src="`+resp[1][contador]+`" style="width: 200px; height: 200px;"/><br>
                  <span>`+e.descripcion+`</span><br>
                  <span>`+e.codigo_interno+`</span><br>
                  <span class="font-weight-bold badge badge-info text-white" style="font-size: 16px;">$ `+commaSeparateNumber(precio_total)+` </span>
                </div>

                `);
                contador = contador + 1;

              });
              $("#ultimos-repuestos-modal").modal("show");
            }


          },
          error: function(error){
            console.log(error.responseText);
          }
        });
    }

    function mostrar_bajo_stock(){

        let url='{{url("reportes/bajo_stock_home")}}';
        $.ajax({
          type:'get',
          url:url,
          beforeSend: function(){

          },
          success: function(resp){
            if(resp == "0"){
              console.log("No hay repuestos o no es admin");
              return false;
            }
            if(resp[0].length > 0){
              $('#modal_body_bajo_stock').empty();
              resp[1].forEach(e => {
                $('#modal_body_bajo_stock').append(`
                  <a href="/reportes/bajo_stock/`+e.id+`" class="btn btn-primary" onclick="bajoStock(`+e.id+`)" target="_blank">`+e.local_nombre+` </a>
              `);
              });
              $("#bajo-stock-modal").modal("show");
            }


          },
          error: function(error){
            console.log(error.responseText);
          }
        });
    }

    function bajoStock(local_id){
      $("#bajo-stock-modal").modal("hide");
    }
    window.onload = function(e){
            // mostrar_deliverys_pendientes();
            dameultimosrepuestos();
            indicadoresEconomicos();
            //mostrar_bajo_stock();
            // $('#saludo').modal('show');
    }

    function indicadoresEconomicos(){
      let url = "https://mindicador.cl/api";

      $.ajax({
        type:'get',
        url:url,
        success: function(resp){
          console.log(resp);
          let valorUf = resp.uf.valor;
          let valorUtm = resp.utm.valor;
          let valorDolar = resp.dolar.valor;
          // agregar separador de miles
          valorUf = Number(valorUf).toFixed(0);
          valorUf = commaSeparateNumber(valorUf);
          valorUtm = Number(valorUtm).toFixed(0);
          valorUtm = commaSeparateNumber(valorUtm);
          valorDolar = Number(valorDolar).toFixed(0);
          valorDolar = commaSeparateNumber(valorDolar);

          $('#valorUf').html('$'+valorUf);
          $('#valorUtm').html('$'+valorUtm);
          $('#valorDolar').html('$'+valorDolar);
        },
        error: function(error){
          console.log(error.responseText);
        }
      });
    }

    function cargar_pedido(pedido_id){

            let url = '/ventas/cargar_pedido/'+pedido_id;
            $.ajax({
                type:'get',
                url: url,
                success: function(resp){
                  $('#modalInfoPedidoTerrestre').modal('show');

                    $('#modalBodyInfoPedidoTerrestre').empty();
                    let cliente = resp[0];
                    console.log(cliente);
                    $('#nombre_cliente').html(cliente.nombre_cliente);
                    $('#numero_pedido').html(cliente.num_abono);
                    $('#fecha_emision_pedido').html(cliente.fecha_emision);
                    $('#hora_emision').html(cliente.hora_emision);
                    let repuestos_pedidos = resp[1];
                    repuestos_pedidos.forEach(repuesto => {
                            // al precio unitario de los repuestos agregarle el separador de miles
                            let precio_unitario = Number(repuesto.precio_unitario).toFixed(0);
                            precio_unitario = commaSeparateNumber(precio_unitario);
                            // al precio total de los repuestos agregarle el separador de miles
                            let total = Number(repuesto.total).toFixed(0);
                            total = commaSeparateNumber(total);

                            $('#modalBodyInfoPedidoTerrestre').append(`
                            <tr>
                                <td>`+repuesto.cantidad+`</td>
                                <td>`+repuesto.descripcion+` </td>
                                <td>$`+precio_unitario+` </td>
                                <td>$`+total+` </td>

                            </tr>
                            `);
                    });
                },
                error: function(err){
                    console.log(err);
                }
            })
    }

    function eliminar_abono_modal(id_abono){
    Vue.swal({
                    title: '¿Estás seguro?',
                    text: "Se eliminará el pedido permantemente",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, ¡Confirmar!'
            }).then((result) => {
                    if(result.isConfirmed){
                        let url = '/ventas/eliminar_abono_modal/'+id_abono;

                        //   var url='{{url("ventas/imprimir_pedido")}}';
                        //   let parametros = {id_abono: id_abono};

                        $.ajaxSetup({
                                headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });

                        $.ajax({
                            type:'get',
                            url: url,
                            success: function(resp){

                                if(resp.length > 0){
                                        let pedidos_terrestre = resp[0];
                                        let pedidos_aereos = resp[1];
                                        $('#tbody_revision_pedidos_terrestres').empty();
                                        $('#tbody_revision_pedidos_aereos').empty();
                                        pedidos_terrestre.forEach( pedido => {
                                            $('#tbody_revision_pedidos_terrestres').append(`
                                            <tr>
                                                <td><a href="javascript:void(0)" onclick="cargar_pedido(`+pedido.num_abono+`)">`+pedido.num_abono+` </a> </td>
                                                <td>`+pedido.nombre_cliente+` </td>
                                                <td>`+pedido.fecha_emision+` </td>
                                                <td><button class='btn btn-outline-danger btn-sm' onclick='eliminar_abono_modal(`+pedido.id+`)'>X</button> </td>
                                            </tr>
                                            `);
                                        });

                                        pedidos_aereos.forEach( pedido => {
                                            $('#tbody_revision_pedidos_aereos').append(`
                                            <tr>
                                                <td><a href="javascript:void(0)" onclick="cargar_pedido(`+pedido.num_abono+`)">`+pedido.num_abono+`</a> </td>
                                                <td>`+pedido.nombre_cliente+` </td>
                                                <td>`+pedido.fecha_emision+` </td>
                                                <td><button class='btn btn-outline-danger btn-sm' onclick='eliminar_abono_modal(`+pedido.id+`)'>X</button> </td>
                                            </tr>
                                            `);
                                        });
                                    }
                            },

                            error: function(err){
                                console.log(err.responseText);
                            }
                        });
                    }
                })

  }

  function mensaje() {
    Vue.swal({
      title: 'Acceso Restringido',
      html: '<i class="fas fa-lock fa-3x text-warning mb-3"></i><br>No tienes permisos de administrador para acceder a esta sección.',
      icon: 'warning',
      confirmButtonText: 'Entendido',
      confirmButtonColor: '#356b8d'
    });
  }

  function mensaje() {
    Vue.swal({
      title: 'Acceso Restringido',
      html: '<i class="fas fa-lock fa-3x text-warning mb-3"></i><br><br>No tienes permisos de administrador para acceder a esta sección.',
      icon: 'warning',
      confirmButtonText: 'Entendido',
      confirmButtonColor: '#356b8d'
    });
  }

  function cargar_consignacion(num_consignacion){
    let url = '/ventas/cargar_consignacion_home/'+num_consignacion;
    console.log(url);
    $.ajax({
      type:'get',
      url: url,
      success: function(resp){
        console.log(resp);
        $('#modalInfoConsignacion').modal('show');

          $('#modalBodyInfoConsignacion').empty();
          let consignacion = resp[0];
          $('#nombre_cliente_consignacion').html(consignacion.nombre_consignacion);
          $('#numero_consignacion').html(consignacion.num_consignacion);

          $('#fecha_emision_consignacion').html(consignacion.fecha_emision);
          let repuestos_pedidos = resp[1];
          repuestos_pedidos.forEach(repuesto => {
            // convertir el precio_venta del repuesto a entero y multiplicarlo por la cantidad
            let t = parseInt(repuesto.precio_venta) * repuesto.cantidad;
                  // al precio unitario de los repuestos agregarle el separador de miles
                  let precio_unitario = Number(parseInt(repuesto.precio_venta)).toFixed(0);
                  precio_unitario = commaSeparateNumber(precio_unitario);
                  let precio_normal = Number(parseInt(repuesto.precio_normal)).toFixed(0);
                  precio_normal = commaSeparateNumber(precio_normal);
                  // al precio total de los repuestos agregarle el separador de miles
                  let total = Number(t).toFixed(0);
                  total = commaSeparateNumber(total);

                  $('#modalBodyInfoConsignacion').append(`
                  <tr>
                      <td>`+repuesto.cantidad+`</td>
                      <td>`+repuesto.descripcion+` </td>
                      <td>$`+precio_unitario+` </td>
                      <td>$`+precio_normal+` </td>

                      <td>$`+total+` </td>

                  </tr>
                  `);
          });
      },
      error: function(err){
          console.log(err);
      }
  })
  }
</script>
@endsection
@section('style')
<style>
    :root {
        --ips-blue: #356b8d;
        --ips-blue-dark: #274c66;
        --ips-blue-soft: #5f8298;
        --ips-slate: #566671;
        --ips-surface: #f1f4f6;
        --ips-border: #d9e0e5;
        --ips-teal: #4f7773;
        --ips-amber: #9a7848;
        --ips-danger: #955b5b;
    }

    #mensajes{
        background-color: #fff;
        position: relative;
        bottom: 22px;
        left:0px;
        width: 100%;
        height: 60px;
        color: white;
    }

    /* Dashboard Profesional */
    .dashboard-container {
        background: var(--ips-surface);
        min-height: 100vh;
        padding: 20px 0;
    }

    .dashboard-header {
        background: linear-gradient(135deg, var(--ips-blue-dark) 0%, var(--ips-blue) 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 6px 18px rgba(39, 76, 102, 0.18);
    }

    .dashboard-header h1 {
        font-weight: 700;
        margin: 0;
        font-size: 2rem;
    }

    .dashboard-card {
        border: 1px solid var(--ips-border);
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 3px 10px rgba(39, 55, 65, 0.08);
        height: 100%;
        background: white;
    }

    .dashboard-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(39, 55, 65, 0.13);
    }

    .dashboard-card-icon {
        width: 100%;
        height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .dashboard-card-icon::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.10) 0%, transparent 70%);
    }

    @keyframes shimmer {
        0% { transform: translate(-50%, -50%); }
        100% { transform: translate(50%, 50%); }
    }

    .bg-gradient-blue {
        background: linear-gradient(135deg, #315a73 0%, #477a98 100%);
    }

    .bg-gradient-green {
        background: linear-gradient(135deg, #426963 0%, #5c857f 100%);
    }

    .bg-gradient-orange {
        background: linear-gradient(135deg, #6d6257 0%, #8b7967 100%);
    }

    .bg-gradient-cyan {
        background: linear-gradient(135deg, #476b7b 0%, #658b9b 100%);
    }

    .bg-gradient-purple {
        background: linear-gradient(135deg, #59646c 0%, #78838b 100%);
    }

    .bg-gradient-red {
        background: linear-gradient(135deg, #805253 0%, #a16a6b 100%);
    }

    .dashboard-card-body {
        padding: 25px;
    }

    .dashboard-card-title {
        font-weight: 700;
        font-size: 1.3rem;
        color: #2f414c;
        margin-bottom: 10px;
    }

    .dashboard-card-text {
        color: #697984;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .dashboard-btn {
        border-radius: 25px;
        padding: 10px 25px;
        font-weight: 600;
        border: 2px solid;
        transition: all 0.3s ease;
    }

    .dashboard-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(39, 55, 65, 0.14);
    }

    .dashboard-card .btn-outline-primary,
    .dashboard-card .btn-outline-info,
    .info-card .btn-outline-primary {
        color: var(--ips-blue);
        border-color: var(--ips-blue);
    }

    .dashboard-card .btn-outline-success {
        color: var(--ips-teal);
        border-color: var(--ips-teal);
    }

    .dashboard-card .btn-outline-secondary {
        color: var(--ips-slate);
        border-color: var(--ips-slate);
    }

    .dashboard-card .btn-outline-danger,
    .info-card .btn-outline-danger {
        color: var(--ips-danger);
        border-color: var(--ips-danger);
    }

    .dashboard-card .btn-outline-warning {
        color: var(--ips-amber);
        border-color: var(--ips-amber);
    }

    .dashboard-card .dashboard-btn:hover,
    .info-card .btn:hover {
        color: #fff;
        background-color: var(--ips-blue-dark);
        border-color: var(--ips-blue-dark);
    }

    .info-sidebar {
        position: sticky;
        top: 20px;
    }

    .info-card {
        border: 1px solid var(--ips-border);
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(39, 55, 65, 0.08);
        overflow: hidden;
    }

    .info-card-header {
        background: linear-gradient(135deg, var(--ips-blue-dark) 0%, var(--ips-blue) 100%);
        color: white;
        padding: 20px;
        border: none;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .user-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 3px solid white;
        object-fit: cover;
    }

    .indicator-table {
        font-size: 0.9rem;
    }

    .indicator-table td {
        padding: 12px 8px;
        border-bottom: 1px solid #e2e8f0;
    }

    .indicator-value {
        font-weight: 700;
        color: var(--ips-blue);
    }

    /* Mensaje de acceso restringido */
    .restricted-access {
        background: white;
        border-radius: 15px;
        padding: 60px 40px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        text-align: center;
        margin: 40px 0;
    }

    .restricted-icon {
        width: 120px;
        height: 120px;
        background: #eee5d8;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        box-shadow: 0 6px 16px rgba(86, 102, 113, 0.14);
    }

    .restricted-icon i {
        font-size: 3.5rem;
        color: #e53e3e;
    }

    .restricted-title {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 15px;
    }

    .restricted-text {
        font-size: 1.1rem;
        color: #718096;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .restricted-info {
        background: #edf1f3;
        border-radius: 10px;
        padding: 20px;
        margin-top: 30px;
        text-align: left;
    }

    .restricted-info p {
        margin: 0;
        color: #4a5568;
        font-size: 0.95rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-header h1 {
            font-size: 1.5rem;
        }

        .dashboard-card-icon {
            height: 140px;
            font-size: 3rem;
        }

        .info-sidebar {
            position: relative;
            margin-top: 30px;
        }

        .restricted-access {
            padding: 40px 20px;
        }

        .restricted-title {
            font-size: 1.5rem;
        }

        .restricted-icon {
            width: 100px;
            height: 100px;
        }

        .restricted-icon i {
            font-size: 2.5rem;
        }
    }

    /* Animaciones de entrada */
    .fade-in-up {
        animation: fadeInUp 0.6s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stagger-1 { animation-delay: 0.1s; }
    .stagger-2 { animation-delay: 0.2s; }
    .stagger-3 { animation-delay: 0.3s; }
    .stagger-4 { animation-delay: 0.4s; }
    .stagger-5 { animation-delay: 0.5s; }
    .stagger-6 { animation-delay: 0.6s; }

</style>

@endsection
@section('contenido')
<div class="container-fluid">

  @php
    $mensaje = Session::get('PARAM_MSG');
  @endphp
  @if(!empty($mensaje))
  <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
    <h1> <strong> {{$mensaje}}</strong></h1>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  @endif
    <div class="row">
      @if(Auth::user()->rol->nombrerol === "Administrador")
        <div class="col-lg-9 col-md-8">
          <!-- Header del Dashboard -->
          <div class="dashboard-header fade-in-up">
            <h1><i class="fas fa-tachometer-alt mr-3"></i>Panel de Control</h1>
            <p class="mb-0 mt-2" style="opacity: 0.9;">Bienvenido al sistema de gestión de repuestos IPS</p>
          </div>

          <!-- Grid de Cards -->
          <div class="row">
            <!-- Card 1: Gestión de Repuestos -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="dashboard-card fade-in-up stagger-1">
                <div class="dashboard-card-icon bg-gradient-blue">
                  <i class="fas fa-cogs"></i>
                </div>
                <div class="dashboard-card-body">
                  <h5 class="dashboard-card-title">Gestión de Repuestos</h5>
                  <p class="dashboard-card-text">Administra el inventario completo de repuestos, precios y stock disponible.</p>
                  <a href="{{url('repuesto/stockrepuesto')}}" class="btn btn-outline-primary dashboard-btn w-100">
                    <i class="fas fa-arrow-right mr-2"></i>Acceder
                  </a>
                </div>
              </div>
            </div>

            <!-- Card 2: Ventas -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="dashboard-card fade-in-up stagger-2">
                <div class="dashboard-card-icon bg-gradient-green">
                  <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="dashboard-card-body">
                  <h5 class="dashboard-card-title">Punto de Venta</h5>
                  <p class="dashboard-card-text">Procesa ventas, genera boletas, facturas y gestiona pedidos de clientes.</p>
                  <a href="{{url('ventas')}}" class="btn btn-outline-success dashboard-btn w-100">
                    <i class="fas fa-cash-register mr-2"></i>Vender
                  </a>
                </div>
              </div>
            </div>

            <!-- Card 3: Clientes -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="dashboard-card fade-in-up stagger-3">
                <div class="dashboard-card-icon bg-gradient-orange">
                  <i class="fas fa-users"></i>
                </div>
                <div class="dashboard-card-body">
                  <h5 class="dashboard-card-title">Clientes</h5>
                  <p class="dashboard-card-text">Administra la base de datos de clientes, historiales y cuentas corrientes.</p>
                  <a href="{{url('clientes')}}" class="btn btn-outline-danger dashboard-btn w-100">
                    <i class="fas fa-user-friends mr-2"></i>Ver Clientes
                  </a>
                </div>
              </div>
            </div>

            <!-- Card 4: Reportes -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="dashboard-card fade-in-up stagger-4">
                <div class="dashboard-card-icon bg-gradient-cyan">
                  <i class="fas fa-chart-bar"></i>
                </div>
                <div class="dashboard-card-body">
                  <h5 class="dashboard-card-title">Reportes</h5>
                  <p class="dashboard-card-text">Genera reportes de ventas, stock, movimientos y análisis detallados.</p>
                  <a href="{{url('reportes/ventasdiarias')}}" class="btn btn-outline-info dashboard-btn w-100">
                    <i class="fas fa-file-alt mr-2"></i>Ver Reportes
                  </a>
                </div>
              </div>
            </div>

            <!-- Card 5: Proveedores -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="dashboard-card fade-in-up stagger-5">
                <div class="dashboard-card-icon bg-gradient-purple">
                  <i class="fas fa-truck"></i>
                </div>
                <div class="dashboard-card-body">
                  <h5 class="dashboard-card-title">Proveedores</h5>
                  <p class="dashboard-card-text">Gestiona proveedores, órdenes de compra y registro de mercadería.</p>
                  <a href="{{url('proveedor')}}" class="btn btn-outline-secondary dashboard-btn w-100">
                    <i class="fas fa-boxes mr-2"></i>Gestionar
                  </a>
                </div>
              </div>
            </div>

            <!-- Card 6: Stock Crítico -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="dashboard-card fade-in-up stagger-6">
                <div class="dashboard-card-icon bg-gradient-red">
                  <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="dashboard-card-body">
                  <h5 class="dashboard-card-title">Stock Crítico</h5>
                  <p class="dashboard-card-text">Revisa productos con stock mínimo y genera alertas de reposición.</p>
                  <a href="{{url('reportes/bajo_stock')}}" class="btn btn-outline-danger dashboard-btn w-100">
                    <i class="fas fa-bell mr-2"></i>Ver Alertas
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-4">
          <div class="info-sidebar">
            <div class="info-card fade-in-up">
              <div class="info-card-header">
                <div class="user-info">
                  <img src="{{ asset('storage/imagenes/vendedor.png') }}" class="user-avatar" alt="Avatar">
                  <div>
                    <h6 class="mb-1" style="font-weight: 700;">{{ Auth::user()->name }}</h6>
                    <small style="opacity: 0.9;">{{ Auth::user()->rol->nombrerol }}</small>
                  </div>
                </div>
              </div>
              <div class="card-body p-0">
                <div class="p-3 bg-light">
                  <small class="text-muted d-block mb-1"><i class="fas fa-envelope mr-2"></i>Correo</small>
                  <p class="mb-0" style="font-size: 0.85rem;">{{ Auth::user()->email }}</p>
                </div>
                <div class="p-3">
                  <small class="text-muted d-block mb-1"><i class="fas fa-calendar-alt mr-2"></i>Fecha</small>
                  <p class="mb-0 font-weight-bold">{{ $hoy }}</p>
                </div>
                <hr class="my-0">
                <div class="p-3">
                  <h6 class="font-weight-bold mb-3"><i class="fas fa-chart-line mr-2"></i>Indicadores Económicos</h6>
                  <table class="table table-sm indicator-table mb-0">
                    <tbody>
                      <tr>
                        <td class="border-0"><i class="fas fa-dollar-sign mr-2 text-success"></i>UF</td>
                        <td class="border-0 text-right indicator-value" id="valorUf">Cargando...</td>
                      </tr>
                      <tr>
                        <td><i class="fas fa-money-bill-wave mr-2 text-info"></i>UTM</td>
                        <td class="text-right indicator-value" id="valorUtm">Cargando...</td>
                      </tr>
                      <tr>
                        <td><i class="fas fa-coins mr-2 text-warning"></i>Dólar</td>
                        <td class="text-right indicator-value" id="valorDolar">Cargando...</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="card-footer bg-white p-3">
                <button onclick="dameultimosrepuestos()" class="btn btn-outline-primary btn-block mb-2">
                  <i class="fas fa-box-open mr-2"></i>Últimos Repuestos
                </button>
                <a href="{{url('reportes/bajo_stock')}}" class="btn btn-outline-danger btn-block">
                  <i class="fas fa-exclamation-triangle mr-2"></i>Stock Mínimo
                </a>
              </div>
            </div>
          </div>
        </div>
      @else
        <div class="col-lg-9 col-md-8">
          <!-- Header del Dashboard -->
          <div class="dashboard-header fade-in-up">
            <h1><i class="fas fa-home mr-3"></i>Inicio</h1>
            <p class="mb-0 mt-2" style="opacity: 0.9;">Bienvenido {{ Auth::user()->name }}</p>
          </div>

          <!-- Mensaje de Acceso Restringido -->
          <div class="restricted-access fade-in-up">
            <div class="restricted-icon">
              <i class="fas fa-user-lock"></i>
            </div>
            <h2 class="restricted-title">Acceso Restringido</h2>
            <p class="restricted-text">
              Tu cuenta no tiene permisos de administrador para acceder al panel de control completo.
              <br>
              Si necesitas acceso adicional, por favor contacta con el administrador del sistema.
            </p>
            <div class="restricted-info">
              <p>
                <i class="fas fa-user mr-2"></i><strong>Usuario:</strong> {{ Auth::user()->name }}<br>
                <i class="fas fa-id-badge mr-2 mt-2"></i><strong>Rol:</strong> {{ Auth::user()->rol->nombrerol }}<br>
                <i class="fas fa-envelope mr-2 mt-2"></i><strong>Email:</strong> {{ Auth::user()->email }}
              </p>
            </div>
          </div>

          {{-- Grid de Cards deshabilitados para referencia --}}
          <div class="row" style="display: none;">
            <!-- Card 1: Consultar Repuestos -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="dashboard-card fade-in-up stagger-1">
                <div class="dashboard-card-icon bg-gradient-blue">
                  <i class="fas fa-search"></i>
                </div>
                <div class="dashboard-card-body">
                  <h5 class="dashboard-card-title">Consultar Repuestos</h5>
                  <p class="dashboard-card-text">Busca y consulta información de repuestos disponibles en inventario.</p>
                  <a href="javascript:void(0)" onclick="mensaje()" class="btn btn-outline-primary dashboard-btn w-100">
                    <i class="fas fa-search mr-2"></i>Buscar
                  </a>
                </div>
              </div>
            </div>

            <!-- Card 2: Mis Ventas -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="dashboard-card fade-in-up stagger-2">
                <div class="dashboard-card-icon bg-gradient-green">
                  <i class="fas fa-receipt"></i>
                </div>
                <div class="dashboard-card-body">
                  <h5 class="dashboard-card-title">Mis Ventas</h5>
                  <p class="dashboard-card-text">Accede al punto de venta y gestiona tus transacciones del día.</p>
                  <a href="javascript:void(0)" onclick="mensaje()" class="btn btn-outline-success dashboard-btn w-100">
                    <i class="fas fa-cash-register mr-2"></i>Vender
                  </a>
                </div>
              </div>
            </div>

            <!-- Card 3: Clientes -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="dashboard-card fade-in-up stagger-3">
                <div class="dashboard-card-icon bg-gradient-orange">
                  <i class="fas fa-address-book"></i>
                </div>
                <div class="dashboard-card-body">
                  <h5 class="dashboard-card-title">Clientes</h5>
                  <p class="dashboard-card-text">Consulta información de clientes y sus historiales de compra.</p>
                  <a href="javascript:void(0)" onclick="mensaje()" class="btn btn-outline-danger dashboard-btn w-100">
                    <i class="fas fa-users mr-2"></i>Ver Clientes
                  </a>
                </div>
              </div>
            </div>

            <!-- Card 4: Reportes -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="dashboard-card fade-in-up stagger-4">
                <div class="dashboard-card-icon bg-gradient-cyan">
                  <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="dashboard-card-body">
                  <h5 class="dashboard-card-title">Mis Reportes</h5>
                  <p class="dashboard-card-text">Consulta reportes de tus ventas y movimientos realizados.</p>
                  <a href="javascript:void(0)" onclick="mensaje()" class="btn btn-outline-info dashboard-btn w-100">
                    <i class="fas fa-chart-pie mr-2"></i>Ver Reportes
                  </a>
                </div>
              </div>
            </div>

            <!-- Card 5: Ayuda -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="dashboard-card fade-in-up stagger-5">
                <div class="dashboard-card-icon bg-gradient-purple">
                  <i class="fas fa-question-circle"></i>
                </div>
                <div class="dashboard-card-body">
                  <h5 class="dashboard-card-title">Centro de Ayuda</h5>
                  <p class="dashboard-card-text">Accede a tutoriales, guías y soporte técnico del sistema.</p>
                  <a href="javascript:void(0)" onclick="mensaje()" class="btn btn-outline-secondary dashboard-btn w-100">
                    <i class="fas fa-life-ring mr-2"></i>Ayuda
                  </a>
                </div>
              </div>
            </div>

            <!-- Card 6: Perfil -->
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="dashboard-card fade-in-up stagger-6">
                <div class="dashboard-card-icon bg-gradient-red">
                  <i class="fas fa-user-circle"></i>
                </div>
                <div class="dashboard-card-body">
                  <h5 class="dashboard-card-title">Mi Perfil</h5>
                  <p class="dashboard-card-text">Actualiza tu información personal y preferencias de usuario.</p>
                  <a href="javascript:void(0)" onclick="mensaje()" class="btn btn-outline-warning dashboard-btn w-100">
                    <i class="fas fa-user-edit mr-2"></i>Editar Perfil
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-4">
          <div class="info-sidebar">
            <div class="info-card fade-in-up">
              <div class="info-card-header">
                <div class="user-info">
                  <img src="{{ asset('storage/imagenes/vendedor.png') }}" class="user-avatar" alt="Avatar">
                  <div>
                    <h6 class="mb-1" style="font-weight: 700;">{{ Auth::user()->name }}</h6>
                    <small style="opacity: 0.9;">{{ Auth::user()->rol->nombrerol }}</small>
                  </div>
                </div>
              </div>
              <div class="card-body p-0">
                <div class="p-3 bg-light">
                  <small class="text-muted d-block mb-1"><i class="fas fa-envelope mr-2"></i>Correo</small>
                  <p class="mb-0" style="font-size: 0.85rem;">{{ Auth::user()->email }}</p>
                </div>
                <div class="p-3">
                  <small class="text-muted d-block mb-1"><i class="fas fa-calendar-alt mr-2"></i>Fecha</small>
                  <p class="mb-0 font-weight-bold">{{ $hoy }}</p>
                </div>
                <hr class="my-0">
                <div class="p-3">
                  <h6 class="font-weight-bold mb-3"><i class="fas fa-chart-line mr-2"></i>Indicadores Económicos</h6>
                  <table class="table table-sm indicator-table mb-0">
                    <tbody>
                      <tr>
                        <td class="border-0"><i class="fas fa-dollar-sign mr-2 text-success"></i>UF</td>
                        <td class="border-0 text-right indicator-value" id="valorUf">Cargando...</td>
                      </tr>
                      <tr>
                        <td><i class="fas fa-money-bill-wave mr-2 text-info"></i>UTM</td>
                        <td class="text-right indicator-value" id="valorUtm">Cargando...</td>
                      </tr>
                      <tr>
                        <td><i class="fas fa-coins mr-2 text-warning"></i>Dólar</td>
                        <td class="text-right indicator-value" id="valorDolar">Cargando...</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="card-footer bg-white p-3">
                <button onclick="dameultimosrepuestos()" class="btn btn-outline-primary btn-block">
                  <i class="fas fa-box-open mr-2"></i>Últimos Repuestos
                </button>
              </div>
            </div>
          </div>
        </div>
      @endif
    </div>
    <div id="mensajes my-3"></div>
    <footer class="h-auto text-center">
        <img src="{{asset('storage/imagenes/ips_logo.png')}}" width='100px'><br>
        @php
            $entorno = App::environment();
            echo "Entorno: ".$entorno."<br><br>";
        @endphp

    </footer>
</div>
<!-- MODAL AVISAR DELIVERYS -->
<div style="margin-top:100px" class="modal fade" id="delivery-modal" tabindex="-1" role="dialog" aria-labelledby="pagar-delivery-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="pagar-delivery-label">AVISO</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div id="mostrar_pendientes"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">CERRAR</button>
        </div>
      </div>
    </div>
</div>

  <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" id="bajo-stock-modal">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="pagar-delivery-label">BAJO STOCK</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center" id="modal_body_bajo_stock">

      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">CERRAR</button>
      </div>
      </div>
    </div>
  </div>

  <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" id="ultimos-repuestos-modal">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="pagar-delivery-label">ULTIMOS REPUESTOS</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center" >
          <div class="row" id="modal_body_ultimos_repuestos">

          </div>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-outline-danger" data-dismiss="modal">CERRAR</button>
      </div>
      </div>
    </div>
  </div>

  <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" id="saludo">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Saludos</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <img src="{{asset('storage/imagenes/saludo.jpeg')}}" alt="" srcset="" width="100%">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
<div class="modal fade" id="modalInfoPedidoTerrestre" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Información Pedido</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>El cliente <span id="nombre_cliente"></span> ha realizado un pedido con el numero <span id="numero_pedido"></span> con fecha <span id="fecha_emision_pedido"></span> con hora <span id="hora_emision"></span></p>
        <table class="table">
          <thead class="thead-dark">
            <tr>
              <th scope="col">Cant.</th>
              <th scope="col">Descripción</th>
              <th scope="col">P.U.</th>
              <th scope="col">Total</th>
            </tr>
          </thead>
          <tbody id="modalBodyInfoPedidoTerrestre" style="font-size: 13px;">


          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

 <!-- Modal -->
 <div class="modal fade" id="modalInfoConsignacion" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Información Consignacion</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>El cliente <span id="nombre_cliente_consignacion"></span> ha realizado una consignacion con el numero <span id="numero_consignacion"></span> con fecha <span id="fecha_emision_consignacion"></span></p>
        <table class="table">
          <thead class="thead-dark">
            <tr>
              <th scope="col">Cant.</th>
              <th scope="col">Descripción</th>
              <th scope="col">P.U.</th>
              <th scope="col">P.Normal</th>
              <th scope="col">Total</th>
            </tr>
          </thead>
          <tbody id="modalBodyInfoConsignacion" style="font-size: 13px;">


          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
@endsection
