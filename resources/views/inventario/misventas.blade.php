@extends('plantillas.app')

@section('javascript')
<script>
    function imprimir(pdf){
        console.log(pdf);
        var url = "{{ url('ventas/imprimir_nota_facturada') }}"+"/"+pdf;
        $.ajax({
            type: 'get',
            url: url,
            beforeSend: function(){
                Vue.swal({
                    title: 'Imprimiendo Nota...!',
                    text: 'Espere un momento por favor',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    showCancelButton: false,
                });
            },
            success: function(resp) {
                Vue.swal.close();
                var config = "location=yes,height=570,width=720,scrollbars=yes,status=yes";
                var w = window.open(resp, '_blank', config);
                w.focus();

            },
            error: function(error) {
                console.log(error.responseText);
            }

        })
    }

    // Función auxiliar para formatear números con separadores de miles
    function formatearNumero(numero) {
        // Redondea el número a un entero
        var numeroRedondeado = Math.round(numero);
        // Convierte el número a una cadena y aplica el formato de separación de miles
        return numeroRedondeado.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function abrir_carrito_transferido_facturado(cliente_id) {
            //Se tienen que limpiar los descuentos al inicio
            console.log(cliente_id);
            let url = '/ventas/dame_carrito_transferido_cliente_facturado/' + cliente_id;
            $('#cliente_carrito_transferido').val(cliente_id);
            $.ajax({
                type: 'get',
                url: url,
                beforeSend: function(){
                    Vue.swal({
                        title: 'Cargando Nota...',
                        text: 'Espere un momento por favor',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        showCancelButton: false,
                    });
                },
                success: function(resp) {
                    Vue.swal.close();
                    // abrir modal con la info
                    let productos = resp;
                    console.log(productos);
                    let numero_carrito = productos[0].numero_carrito;
                    let n = numero_carrito === null ? productos[0].id : numero_carrito;
                    $('#modal_title_ventas').text('Detalle de la Nota de Venta N° ' + n);
                    $('#modalCarritoTransferidoFacturado').modal('show');
                    $('#modal_body_detalle_venta').empty();
                    $('#modal_body_detalle_venta').append(`
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>P.U.</th>
                                        <th>Descuento</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${productos.map(p => `
                                        <tr>
                                            <td>${p.codigo_interno}</td>
                                            <td>${p.descripcion}</td>
                                            <td>${p.cantidad}</td>
                                            <td>${formatearNumero(p.pu)}</td>
                                            <td>${p.descuento_item}</td>
                                            <td>${formatearNumero(p.total_item) }</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `);
                },
                error: function(error) {
                    console.log(error.responseText);
                }
            })
        }
</script>
@endsection

@section('contenido_titulo_pagina')
    @include('fragm.breadcrumb', ['titulo_modulo' => "Inventario",'titulo' => "Mis Ventas"])
@endsection

@section('contenido_ingresa_datos')
<div class="container-fluid py-3">
    <!-- Tabs mejorados -->
    <ul id="tabs" class="nav nav-tabs nav-fill border-0 mb-4">
        <li class="nav-item mr-2">
            <a href="" data-target="#facturados" data-toggle="tab" class="nav-link active shadow-sm">
                <i class="fas fa-check-circle me-2"></i>
                <span class="font-weight-bold">FACTURADOS</span>
            </a>
        </li>
        <li class="nav-item mr-2">
            <a href="" data-target="#pendientes" data-toggle="tab" class="nav-link shadow-sm">
                <i class="fas fa-clock me-2"></i>
                <span class="font-weight-bold">PENDIENTES</span>
            </a>
        </li>
        <li class="nav-item mr-2">
            <a href="" data-target="#misclientes" data-toggle="tab" class="nav-link shadow-sm">
                <i class="fas fa-users me-2"></i>
                <span class="font-weight-bold">MIS CLIENTES</span>
            </a>
        </li>
    </ul>

    <div id="tabsContent" class="tab-content">
        <div id="facturados" class="tab-pane fade active show">
            <!-- Barra de búsqueda mejorada -->
            <div class="row mb-4">
                <div class="col-md-4 ml-auto">
                    <div class="input-group shadow-sm">
                        <input type="search" class="form-control border-0" placeholder="Buscar por N° nota..." aria-label="Buscar">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Cards mejorados -->
            <div class="row">
                @if(count($misventas) == 0)
                    <div class="col-md-12">
                        <div class="alert alert-info shadow-sm border-0" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            No tienes ventas facturadas
                        </div>
                    </div>
                @endif
                @foreach($misventas as $mv)
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm venta-card">
                            <!-- Header del card -->
                            <div class="card-header bg-gradient-primary text-white border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 font-weight-bold">
                                        <i class="fas fa-file-invoice me-1"></i>
                                        Nota #{{ $mv->numero_carrito === null ? $mv->id : $mv->numero_carrito}}
                                    </h6>
                                    @if($mv->estado == 0)
                                        <span class="badge badge-light">
                                            <i class="fas fa-check-circle text-success"></i> Procesado
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> En espera
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Body del card -->
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-container">
                                        <img src="{{ url('usuarios/avatar/'.Auth::user()->image_path) }}"
                                             alt="avatar"
                                             class="rounded-circle shadow-sm"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    </div>
                                    <div class="ml-3 flex-grow-1">
                                        <h6 class="mb-0 text-dark font-weight-bold">{{ $mv->cliente }}</h6>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-tag me-1"></i>{{$mv->titulo}}
                                        </small>
                                    </div>
                                </div>

                                <div class="venta-info">
                                    <div class="d-flex align-items-center text-muted mb-2">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        <small>{{ $mv->created_at }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer del card -->
                            <div class="card-footer bg-white border-0 pt-0">
                                <div class="d-flex justify-content-between gap-2">
                                    <button onclick="abrir_carrito_transferido_facturado({{$mv->carrito_id}})"
                                            class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                    <a href="javascript:imprimir(`{{$mv->url_pdf}}`)"
                                       class="btn btn-outline-danger btn-sm flex-fill">
                                        <i class="fas fa-print"></i> Imprimir
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        <div id="pendientes" class="tab-pane fade">
            <!-- Barra de búsqueda -->
            <div class="row mb-4">
                <div class="col-md-4 ml-auto">
                    <div class="input-group shadow-sm">
                        <input type="search" class="form-control border-0" placeholder="Buscar por N° nota..." aria-label="Buscar">
                        <div class="input-group-append">
                            <button class="btn btn-warning" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards pendientes -->
            <div class="row">
                @if(count($misnoventas) == 0)
                    <div class="col-md-12">
                        <div class="alert alert-warning shadow-sm border-0" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No tienes ventas pendientes
                        </div>
                    </div>
                @endif
                @foreach($misnoventas as $mv)
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm venta-card">
                            <!-- Header del card -->
                            <div class="card-header bg-gradient-warning text-dark border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 font-weight-bold">
                                        <i class="fas fa-file-invoice me-1"></i>
                                        Nota #{{ $mv->numero_carrito === null ? $mv->id : $mv->numero_carrito}}
                                    </h6>
                                    @if($mv->estado == 0)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Procesado
                                        </span>
                                    @else
                                        <span class="badge badge-light">
                                            <i class="fas fa-clock text-warning"></i> En espera
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Body del card -->
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-container">
                                        <img src="{{ url('usuarios/avatar/'.Auth::user()->image_path) }}"
                                             alt="avatar"
                                             class="rounded-circle shadow-sm"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    </div>
                                    <div class="ml-3 flex-grow-1">
                                        <h6 class="mb-0 text-dark font-weight-bold">{{ $mv->cliente }}</h6>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-tag me-1"></i>{{$mv->titulo}}
                                        </small>
                                    </div>
                                </div>

                                <div class="venta-info">
                                    <div class="d-flex align-items-center text-muted mb-2">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        <small>{{ $mv->created_at }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer del card -->
                            <div class="card-footer bg-white border-0 pt-0">
                                <div class="d-flex justify-content-between gap-2">
                                    <button onclick="abrir_carrito_transferido_facturado({{$mv->carrito_id}})"
                                            class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                    <a href="javascript:imprimir(`{{$mv->url_pdf}}`)"
                                       class="btn btn-outline-secondary btn-sm flex-fill disabled">
                                        <i class="fas fa-print"></i> Imprimir
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div id="misclientes" class="tab-pane fade">
            <div class="container">

                <div class="row">
                    @if(count($misclientes) == 0)
                        <div class="col-md-12">
                            <div class="alert alert-warning" role="alert">
                                No tienes clientes
                            </div>
                        </div>
                    @endif
                    <table class="table letra_pequeña">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($misclientes as $mc)
                                <tr>
                                    <td>{{ $mc->nombres }}</td>
                                    <td>{{ $mc->email }}</td>
                                    <td>{{ $mc->created_at }}</td>
                                    <td>
                                        <a href="#" class="btn btn-outline-primary btn-sm"><i class="fas fa-search"></i> Ver</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>
    <span class="badge badge-danger">En construccion</span>

</div>

<!-- Modal mejorado -->
<div class="modal fade" id="modalCarritoTransferidoFacturado" tabindex="-1" role="dialog" aria-labelledby="modalCarritoTransferidoFacturado" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white border-0">
                <h5 class="modal-title font-weight-bold" id="modal_title_ventas">
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    Detalle de la Nota de Venta
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body letra_pequeña" id="modal_body_detalle_venta">
                <!-- Aqui va el detalle de la nota de venta -->
            </div>
        </div>
    </div>
</div>

@endsection

@section('style')
<style>
    /* Estilos para tabs mejorados */
    #tabs .nav-link {
        border-radius: 10px 10px 0 0;
        transition: all 0.3s ease;
        color: #6c757d;
        background-color: #f8f9fa;
        margin-right: 5px;
        border: none;
    }

    #tabs .nav-link:hover {
        background-color: #e9ecef;
        transform: translateY(-2px);
    }

    #tabs .nav-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: bold;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    /* Estilos para cards de ventas */
    .venta-card {
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .venta-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .bg-gradient-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    /* Input de búsqueda mejorado */
    .input-group .form-control {
        border-radius: 20px 0 0 20px;
        padding: 0.75rem 1.25rem;
    }

    .input-group .btn {
        border-radius: 0 20px 20px 0;
        padding: 0.75rem 1.5rem;
    }

    /* Avatar mejorado */
    .avatar-container img {
        border: 3px solid #f8f9fa;
        transition: transform 0.3s ease;
    }

    .venta-card:hover .avatar-container img {
        transform: scale(1.1);
    }

    /* Botones mejorados */
    .card-footer .btn {
        transition: all 0.3s ease;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .card-footer .btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    /* Alertas mejoradas */
    .alert {
        border-radius: 15px;
        border-left: 4px solid;
    }

    .alert-info {
        border-left-color: #17a2b8;
        background: linear-gradient(135deg, #d4f1f9 0%, #e3f2fd 100%);
    }

    .alert-warning {
        border-left-color: #ffc107;
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    }

    /* Badges mejorados */
    .badge {
        padding: 0.5em 0.85em;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    /* Animación de entrada */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .venta-card {
        animation: fadeInUp 0.5s ease;
    }

    /* Gap para botones */
    .gap-2 {
        gap: 0.5rem;
    }

    /* Modal mejorado */
    .modal-content {
        border-radius: 15px;
    }

    .modal-header {
        border-radius: 15px 15px 0 0;
    }
</style>
@endsection
