<table class="table letra_pequeña">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Region</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($clientes as $cliente)
        <tr>
            <td>{{ $cliente->nombres }}</td>
            <td>{{ $cliente->email }}</td>
            <td>{{ $cliente->region }}</td>
            @if(!isset($vendedor))
            <td>
                <button class="btn btn-outline-info btn-sm" onclick="asignar_cliente({{ $cliente->id }})">Asignar</button>
            </td>
            @else
            <td>
                <button class="btn btn-outline-primary btn-sm" onclick="ver_cliente({{ $cliente->id }})"><i class="fas fa-eye"></i></button>
                <button class="btn btn-outline-danger btn-sm" onclick="eliminar_cliente({{ $cliente->id_vendedor_cliente }})"><i class="fas fa-trash"></i></button>
            </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Modal asignar cliente -->
<div class="modal fade" id="modal_asignar_cliente" tabindex="-1" role="dialog" aria-labelledby="modal_asignar_cliente" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('asignar_cliente') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Asignar cliente a vendedor</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="{{ isset($cliente) ? $cliente->id : '' }}">
                    <div class="form-group">
                        <label for="id_vendedor">Vendedor</label>
                        {{-- <select name="id_vendedor" id="id_vendedor" class="form-control">
                            @foreach($vendedores as $vendedor)
                            <option value="{{ $vendedor->id }}">{{ $vendedor->nombres }}</option>
                            @endforeach
                        </select> --}}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-outline-primary">Asignar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function asignar_cliente(id_cliente){
        Vue.swal({
            title: 'Asignar cliente',
            text: '¿Estás seguro de asignar este cliente a un vendedor?',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Sí, asignar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if(result.isConfirmed){
                confirmar_asignar_cliente(id_cliente);
            }
        })
    }

    function confirmar_asignar_cliente(id_cliente){
        let id_vendedor = $('#id_vendedor').val();
        let data = {
            id_cliente: id_cliente,
            id_vendedor: id_vendedor
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "{{ route('asignar_cliente') }}",
            method: 'POST',
            data: data,
            success: function(response){
                console.log(response);
                let mensaje = response.mensaje;
                if(mensaje == 'OK'){
                    let clientes = response.clientes;
                    console.log(clientes);
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
                        title: 'Cliente asignado',
                        text: 'El cliente ha sido asignado correctamente',
                        icon: 'success',
                        timer: 2000
                    });
                }else{
                    Vue.swal({
                        title: 'Error',
                        text: mensaje,
                        icon: 'error',
                        timer: 2000
                    });
                }
            },
            error: function(error){
                console.log(error);
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
        });
    }

    function confirmar_eliminar_cliente(id_cliente){
        let data = {
            id_cliente: id_cliente,
            id_vendedor: $('#id_vendedor').val()
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
                    console.log(clientes);
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
</script>
