<style>
    .vendedor-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px 15px 0 0;
        padding: 2rem;
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .vendedor-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .vendedor-avatar:hover {
        transform: scale(1.05);
    }

    .vendedor-info {
        flex: 1;
        margin-left: 1.5rem;
    }

    .vendedor-name {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: white;
    }

    .vendedor-date {
        font-size: 0.8rem;
        opacity: 0.9;
        color: rgba(255, 255, 255, 0.8);
    }

    .modern-tabs {
        border-bottom: 2px solid #e0e0e0;
        margin-bottom: 2rem;
    }

    .modern-tabs .nav-item {
        margin-right: 0.5rem;
    }

    .modern-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        font-weight: 500;
        padding: 0.75rem 1.2rem;
        transition: all 0.3s ease;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .modern-tabs .nav-link:hover {
        color: #667eea;
        border-bottom-color: #667eea;
        background-color: rgba(102, 126, 234, 0.05);
    }

    .modern-tabs .nav-link.active {
        color: #667eea;
        border-bottom-color: #667eea;
        background-color: rgba(102, 126, 234, 0.1);
    }

    .search-box-modern {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .search-input-modern {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    .search-input-modern:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }

    .btn-search-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        color: white;
        padding: 0.5rem 1rem;
        margin-left: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
        font-size: 0.875rem;
    }

    .btn-search-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .table-modern {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .table-modern thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .table-modern thead th {
        border: none;
        padding: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .table-modern tbody tr {
        transition: all 0.2s ease;
    }

    .table-modern tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
        transform: scale(1.01);
    }

    .table-modern tbody td {
        padding: 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.875rem;
    }

    .btn-action-modern {
        border-radius: 6px;
        padding: 0.5rem 0.75rem;
        margin: 0 0.25rem;
        transition: all 0.3s ease;
        border: 2px solid;
    }

    .btn-action-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .solicitud-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .solicitud-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .solicitud-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 1rem 1.5rem;
    }

    .solicitud-card .card-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .price-badge {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 0.75rem;
        text-align: center;
        transition: all 0.3s ease;
    }

    .price-badge:hover {
        background: #e9ecef;
        transform: scale(1.05);
    }

    .price-label {
        font-size: 0.7rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .price-value {
        font-size: 1rem;
        font-weight: 700;
        color: #333;
        margin: 0;
    }

    .radio-modern {
        cursor: pointer;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .radio-modern:hover {
        background-color: rgba(102, 126, 234, 0.1);
    }

    .radio-modern input[type="radio"]:checked + label {
        color: #667eea;
        font-weight: 600;
    }

    .scroll-container {
        max-height: 500px;
        overflow-y: auto;
        border-radius: 10px;
        padding: 1rem;
    }

    .scroll-container::-webkit-scrollbar {
        width: 8px;
    }

    .scroll-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .scroll-container::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
    }

    .scroll-container::-webkit-scrollbar-thumb:hover {
        background: #764ba2;
    }

    .card-modern {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
</style>

<div class="card card-modern">
    <div class="vendedor-header">
        <div class="d-flex align-items-center">
            <img src="{{url('usuarios/avatar/'.$vendedor->image_path)}}" alt="Avatar" class="vendedor-avatar">
            <div class="vendedor-info">
                <h5 class="vendedor-name">{{ $vendedor->name }}</h5>
                <p class="vendedor-date mb-0"><i class="fas fa-calendar-alt mr-2"></i>Miembro desde {{ \Carbon\Carbon::parse($vendedor->created_at)->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>
    <div class="card-body" style="padding: 0px !important">
        <div class="container pt-4">
        </div>
        <section class="container py-4">
            <div class="row">
                <div class="col-md-12">
                    <ul id="tabs" class="nav nav-tabs modern-tabs">
                        <li class="nav-item"><a href="" data-target="#profile1" data-toggle="tab" class="nav-link"><i class="fas fa-user mr-2"></i>Perfil</a></li>
                        <li class="nav-item"><a href="" data-target="#messages1" data-toggle="tab" class="nav-link active"><i class="fas fa-users mr-2"></i>Clientes</a></li>
                        <li class="nav-item"><a href="" data-target="#reportes" data-toggle="tab" class="nav-link"><i class="fas fa-chart-line mr-2"></i>Reportes</a></li>
                        <li class="nav-item"><a href="" data-target="#solicitudes" data-toggle="tab" class="nav-link"><i class="fas fa-clipboard-list mr-2"></i>Solicitudes</a></li>
                    </ul>
                    <div id="tabsContent" class="tab-content pt-4">
                        <div id="profile1" class="tab-pane fade ">
                            <div class="row pb-2">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>Información del Perfil</strong>
                                        <p class="mb-0 mt-2">Esta sección mostrará información detallada del vendedor, estadísticas de rendimiento y configuraciones personales.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="messages1" class="tab-pane fade active show">
                            <div class="search-box-modern">
                                <h6 class="mb-3"><i class="fas fa-search mr-2"></i>Buscar Clientes Asignados</h6>
                                <div class="row align-items-center">
                                    <div class="col-sm-3">
                                        <div class="d-flex">
                                            <div class="form-check form-check-inline radio-modern">
                                                <input class="form-check-input" type="radio" name="buscapor_" id="buscaxrut_" checked="true">
                                                <label class="form-check-label" for="buscaxrut_">RUT</label>
                                            </div>
                                            <div class="form-check form-check-inline radio-modern">
                                                <input class="form-check-input" type="radio" name="buscapor_" id="buscaxnombres_">
                                                <label class="form-check-label" for="buscaxnombres_">Nombres</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-9 d-flex">
                                        <input type="search" name="buscado_" id="buscado_" class="form-control search-input-modern" placeholder="Ingrese su búsqueda..." onkeyup="enter_buscar_clientes_vendedor()">
                                        <button class="btn btn-search-modern" onclick="buscar_clientes_vendedor()"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </div>

                            <div class="scroll-container" id="contenedor_clientes_vendedor">
                                <table class="table table-modern" id="tabla_clientes_vendedor">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-user mr-2"></i>Nombre</th>
                                            <th><i class="fas fa-envelope mr-2"></i>Email</th>
                                            <th><i class="fas fa-map-marker-alt mr-2"></i>Región</th>
                                            <th><i class="fas fa-cog mr-2"></i>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($clientes as $cliente)
                                        <tr>
                                            <td>{{ $cliente->nombres }}</td>
                                            <td>{{ $cliente->email }}</td>
                                            <td>{{ $cliente->region }}</td>
                                            <td class="d-flex">
                                                <button class="btn btn-outline-primary btn-sm btn-action-modern" onclick="ver_cliente({{ $cliente->id }})" title="Ver cliente">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm btn-action-modern" onclick="eliminar_cliente({{ $cliente->id_vendedor_cliente }})" title="Eliminar cliente">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="search-box-modern mt-4">
                                <h6 class="mb-3"><i class="fas fa-user-plus mr-2"></i>Asignar Nuevos Clientes</h6>
                                <div class="row align-items-center mb-3">
                                    <div class="col-sm-3">
                                        <div class="d-flex">
                                            <div class="form-check form-check-inline radio-modern">
                                                <input class="form-check-input" type="radio" name="buscapor" id="buscaxrut" checked="true">
                                                <label class="form-check-label" for="buscaxrut">RUT</label>
                                            </div>
                                            <div class="form-check form-check-inline radio-modern">
                                                <input class="form-check-input" type="radio" name="buscapor" id="buscaxnombres">
                                                <label class="form-check-label" for="buscaxnombres">Nombres</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-9 d-flex">
                                        <input type="search" name="buscado" id="buscado" class="form-control search-input-modern" placeholder="Buscar clientes disponibles..." onkeyup="enter_buscar_clientes()">
                                        <button class="btn btn-search-modern" onclick="buscar_clientes()"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <select name="region" id="region" class="form-control search-input-modern">
                                                <option value="0">Seleccione Región</option>
                                                @foreach($regiones as $region)
                                                <option value="{{ $region->id }}">{{ $region->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button class="btn btn-search-modern w-100" onclick="buscar_clientes_por_region()">
                                            <i class="fas fa-search mr-2"></i>Buscar por Región
                                        </button>
                                    </div>
                                    <div class="col-md-9 scroll-container" id="contenedor_clientes">
                                        <table class="table table-modern letra_pequeña">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Email</th>
                                                    <th>Región</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="reportes" class="tab-pane fade">
                            <div class="search-box-modern">
                                <h6 class="mb-3"><i class="fas fa-chart-bar mr-2"></i>Reportes de Ventas</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="mb-2"><i class="fas fa-calendar-day mr-2"></i>Búsqueda Diaria</label>
                                            <div class="d-flex align-items-center">
                                                <input type="date" class="form-control search-input-modern" name="fecha_reporte" id="fecha_reporte" value="<?php echo date('Y-m-d') ?>">
                                                <button class="btn btn-search-modern ml-2" onclick="dame_ventas_diarias()">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="mb-2"><i class="fas fa-calendar-alt mr-2"></i>Búsqueda Mensual</label>
                                            <div class="d-flex align-items-center">
                                                <select name="mes_reporte" id="mes_reporte" class="form-control search-input-modern">
                                                    <option value="0">Seleccione el mes</option>
                                                    <option value="1">Enero</option>
                                                    <option value="2">Febrero</option>
                                                    <option value="3">Marzo</option>
                                                    <option value="4">Abril</option>
                                                    <option value="5">Mayo</option>
                                                    <option value="6">Junio</option>
                                                    <option value="7">Julio</option>
                                                    <option value="8">Agosto</option>
                                                    <option value="9">Septiembre</option>
                                                    <option value="10">Octubre</option>
                                                    <option value="11">Noviembre</option>
                                                    <option value="12">Diciembre</option>
                                                </select>
                                                <button class="btn btn-search-modern ml-2" onclick="dame_ventas_mensuales()">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9 scroll-container" id="contenedor_info">
                                        <div class="text-center text-muted py-5">
                                            <i class="fas fa-chart-line fa-3x mb-3" style="opacity: 0.3;"></i>
                                            <p>Seleccione un período para ver el reporte de ventas</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <canvas id="miGrafico" class="mt-4"></canvas>
                        </div>
                        <div id="solicitudes" class="tab-pane fade">
                            <div class="mb-3">
                                <h6><i class="fas fa-clipboard-list mr-2"></i>Solicitudes de Cambio de Precio</h6>
                                <p class="text-muted small">Gestiona las solicitudes de cambio de precio pendientes</p>
                            </div>
                            <div class="row scroll-container">
                                @forelse($solicitudes as $solicitud)
                                <div class="col-md-4">
                                    <div class="card solicitud-card">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-user-circle mr-2"></i>{{ $solicitud->vendedor }}
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <img src="https://placehold.co/600x400/667eea/ffffff?text=Producto" alt="" class="w-100 mb-3 rounded">
                                            <div class="mb-2">
                                                <span class="badge badge-primary">{{ $solicitud->codigo_interno }}</span>
                                            </div>
                                            <p class="text-muted small mb-3">{{ $solicitud->repuesto }}</p>

                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="price-badge">
                                                        <div class="price-label">Precio Normal</div>
                                                        <div class="price-value text-danger">${{ number_format($solicitud->precio_anterior, 0, ',', '.') }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="price-badge">
                                                        <div class="price-label">Precio Nuevo</div>
                                                        <div class="price-value text-success">${{ number_format($solicitud->precio_nuevo, 0, ',', '.') }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3" id="mensajes{{ $solicitud->id }}">
                                                @if($solicitud->duracion !== null)
                                                <div class="alert alert-success mb-0">
                                                    <i class="fas fa-check-circle mr-2"></i>
                                                    <small>Confirmada hasta {{ $solicitud->duracion }}</small>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-footer bg-white border-top">
                                            <button class="btn btn-success btn-sm btn-action-modern" onclick="confirmarSolicitudCambioPrecio({{ $solicitud->id }})">
                                                <i class="fas fa-check mr-1"></i>Aceptar
                                            </button>
                                            <button class="btn btn-danger btn-sm btn-action-modern" onclick="rechazarSolicitudCambioPrecio({{ $solicitud->id }})">
                                                <i class="fas fa-times mr-1"></i>Rechazar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12">
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fa-3x mb-3" style="opacity: 0.3;"></i>
                                        <p>No hay solicitudes pendientes en este momento</p>
                                    </div>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- MODAL CLIENTE -->
<div class="modal fade" id="modal_cliente" tabindex="-1" role="dialog" aria-labelledby="modal_cliente" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border: none; border-radius: 15px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                <h5 class="modal-title"><i class="fas fa-user-circle mr-2"></i>Información del Cliente</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 2rem;">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="text-muted small mb-1"><i class="fas fa-id-card mr-2"></i>RUT</label>
                        <input type="text" name="rut" id="rut" class="form-control search-input-modern" readonly>
                    </div>
                    <div class="col-md-8">
                        <label class="text-muted small mb-1"><i class="fas fa-user mr-2"></i>Nombre Completo</label>
                        <input type="text" name="nombres" id="nombres" class="form-control search-input-modern" readonly>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small mb-1"><i class="fas fa-envelope mr-2"></i>Email</label>
                        <input type="text" name="email" id="email" class="form-control search-input-modern" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small mb-1"><i class="fas fa-phone mr-2"></i>Teléfono</label>
                        <input type="text" name="telefono" id="telefono" class="form-control search-input-modern" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3"><i class="fas fa-credit-card mr-2"></i>Formas de Pago</h6>
                        <div class="scroll-container" style="max-height: 200px;">
                            <ul class="list-group" id="lista_formas_pago">
                                <li class="list-group-item text-center text-muted">Sin información</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3"><i class="fas fa-map-marker-alt mr-2"></i>Direcciones</h6>
                        <div class="scroll-container" style="max-height: 200px;">
                            <ul class="list-group" id="lista_direcciones">
                                <li class="list-group-item text-center text-muted">Sin información</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    function buscar_clientes_por_region(){
        var region = $('#region').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "{{ route('buscar_clientes_por_region') }}",
            method: 'POST',
            data: {
                region: region
            },
            beforeSend: function(){
                $('#contenedor_clientes').html('<div class="alert alert-info">Buscando clientes...</div>');
            },
            success: function(data){
                console.log(data);
                $('#contenedor_clientes').html(data);
            }
        });
    }

    function eliminar_cliente(id_cliente){
        Vue.swal({
            title: 'Eliminar cliente',
            text: '¿Estás seguro de eliminar este cliente para este vendedor?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if(result.isConfirmed){
                confirmar_eliminar_cliente(id_cliente);
            }
        })
    }

    function confirmar_eliminar_cliente(id_cliente){
        let data = {
            id_cliente: id_cliente,
            id_vendedor : {{ $vendedor->id }}
        };

        $.ajax({
            url: "{{ route('eliminar_cliente_vendedor') }}",
            method: 'POST',
            data: data,
            success: function(response){
                console.log(response);
                let mensaje = response.mensaje;
                if(mensaje == 'OK'){
                    let clientes = response.clientes;
                    $('#tabla_clientes_vendedor tbody').empty();
                    clientes.forEach(cliente => {
                        $('#tabla_clientes_vendedor tbody').append(`
                            <tr>
                                <td>${cliente.nombres}</td>
                                <td>${cliente.email}</td>
                                <td>${cliente.region}</td>
                                <td>
                                    <button class="btn btn-outline-primary btn-sm" onclick="ver_cliente(`+cliente.id_vendedor_cliente+`)"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="eliminar_cliente(`+cliente.id_vendedor_cliente+`)"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `);
                    });
                    Vue.swal({
                        title: 'Cliente eliminado',
                        text: 'El cliente ha sido eliminado correctamente',
                        icon: 'success',
                        timer: 2000
                    });
                }
            },
            error: function(error){
                console.log(error);
            }
        });
    }

    function ver_cliente(idcliente){
        var url = "/clientes/"+idcliente+"/cargar";
        $.ajax({
            url: url,
            method: 'GET',
            success: function(data){
                console.log(data);
                let cliente = JSON.parse(data[0]);
                let formas_pago = (data[3]);
                let direcciones = (data[4]);
                $('#rut').val(cliente.rut);
                $('#nombres').val(cliente.nombres);
                // $('#apellidos').val(cliente.apellidos);
                $('#email').val(cliente.email);
                $('#telefono').val(cliente.telf1);
                // abrir modal con los datos del cliente
                $('#modal_cliente').modal('show');

                $('#lista_formas_pago').empty();
                formas_pago.forEach(forma_pago => {
                    $('#lista_formas_pago').append(`
                        <li class="list-group-item">${forma_pago.formapago}</li>
                    `);
                });

                $('#lista_direcciones').empty();
                direcciones.forEach(direccion => {
                    $('#lista_direcciones').append(`
                        <li class="list-group-item">${direccion.direccion}</li>
                    `);
                });
            },
            error: function(error){
                console.log(error);
            }
        });

    }

    function dame_ventas_diarias(){
        Vue.swal({
            title:'info',
            text:'En construccion',
            icon:'info'
        });
    }

    function dame_ventas_mensuales() {
        let id_vendedor = $('#id_vendedor').val();
        let mes = $('#mes_reporte').val();
        let url = "{{ ROUTE('dame_ventas_vendedor') }}";

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: 'post',
            url: url,
            data: {
                id_vendedor: id_vendedor,
                mes: mes
            },
            success: function (resp) {
                console.log(resp);
                $('#contenedor_info').empty();

                if (resp.sus_ventas.length === 0) {
                    $('#contenedor_info').html('<div class="alert alert-warning">No hay ventas registradas para este mes.</div>');
                    return;
                }

                let tabla = `
                    <div class="table-responsive tabla-scroll-y-600">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>RUT</th>
                                    <th>Nº Factura</th>
                                    <th>Total Pagado</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                resp.sus_ventas.forEach((venta, index) => {
                    tabla += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${venta.nombres}</td>
                            <td>${venta.rut}</td>
                            <td>${venta.num_factura}</td>
                            <td>$${parseInt(venta.total_pagos).toLocaleString('es-CL')}</td>
                        </tr>
                    `;
                });
                let totalSusVentas = resp.sus_ventas.reduce((acc, venta) => acc + parseFloat(venta.total_pagos), 0);
                tabla += `
                    <tr class="table-success fw-bold">
                        <td colspan="4" class="text-end">TOTAL</td>
                        <td>$${totalSusVentas.toLocaleString('es-CL')}</td>
                    </tr>
                `;

                tabla += `
                            </tbody>
                        </table>
                    </div>
                `;

                if(id_vendedor == 238){
                    tabla += `
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha</th>
                                        <th>Vendedor</th>
                                        <th>Cliente</th>
                                        <th>Nº Nota Venta</th>
                                        <th>Total Pagado</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        resp.ventas_ajenas.forEach((venta, index) => {
                        tabla += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${venta.fecha_emision}</td>
                                    <td>${venta.vendedor.name}</td>
                                    <td>${venta.cliente.nombres}</td>
                                    <td>${venta.numero_carrito}</td>
                                    <td>$${parseInt(venta.total).toLocaleString('es-CL')}</td>
                                </tr>
                            `;
                        });

                        let totalVentasAjenas = resp.ventas_ajenas.reduce((acc, venta) => acc + parseFloat(venta.total), 0);
                        tabla += `
                            <tr class="table-success fw-bold">
                                <td colspan="5" class="text-end">TOTAL</td>
                                <td>$${totalVentasAjenas.toLocaleString('es-CL')}</td>
                            </tr>
                        `;

                        tabla += `
                                </tbody>
                            </table>
                        </div>
                    `;
                }

                $('#contenedor_info').html(tabla);
            },
            error: function (error) {
                console.log(error.responseText);
                $('#contenedor_info').html('<div class="alert alert-danger">Ocurrió un error al obtener los datos.</div>');
            }
        });

        // Elimina o comenta el Swal si ya no es necesario
        // Vue.swal({
        //     title:'info',
        //     text:'En construccion',
        //     icon:'info'
        // });
    }

</script>
