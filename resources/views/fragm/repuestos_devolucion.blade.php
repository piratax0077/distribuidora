<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-dark text-white d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">
                <i class="fas fa-box-open mr-2"></i>
                Detalle de la devolución
            </h5>

            <small class="text-white-50">
                Revise cada producto antes de aceptar o rechazar.
            </small>
        </div>

        <span class="badge badge-light mt-2 mt-md-0">
            {{ $repuestos->count() }}
            {{ $repuestos->count() === 1 ? 'producto' : 'productos' }}
        </span>
    </div>

    <div class="card-body p-0">
        @if($repuestos->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="align-middle">Código interno</th>
                            <th class="align-middle">Descripción</th>
                            <th class="align-middle text-center">Cantidad</th>
                            <th class="align-middle">Destino</th>
                            <th class="align-middle">Enviado por</th>
                            <th class="align-middle text-center" style="min-width: 190px;">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($repuestos as $repuesto)
                            <tr id="fila-devolucion-{{ $repuesto->id_devolucion  }}">
                                <td class="align-middle font-weight-bold">
                                    {{ $repuesto->codigo_interno }}
                                </td>

                                <td class="align-middle">
                                    <div style="min-width: 220px; max-width: 420px;">
                                        {{ $repuesto->descripcion }}
                                    </div>
                                </td>

                                <td class="align-middle text-center">
                                    <span class="badge badge-info px-3 py-2">
                                        {{ $repuesto->cantidad }}
                                    </span>
                                </td>

                                <td class="align-middle">
                                    <span class="badge badge-secondary px-2 py-2">
                                        <i class="fas fa-warehouse mr-1"></i>
                                        {{ $repuesto->local_nombre }}
                                    </span>
                                </td>

                                <td class="align-middle">
                                    <i class="fas fa-user text-muted mr-1"></i>
                                    {{ $repuesto->name }}
                                </td>

                                <td class="align-middle text-center">
                                    <div class="d-flex flex-wrap justify-content-center">
                                        <button
                                            type="button"
                                            class="btn btn-success btn-sm mr-2 mb-1"
                                            onclick='opciones_devolucion(
                                                    @json((int) $repuesto->id_devolucion),
                                                    @json((int) $repuesto->cantidad),
                                                    @json($repuesto->codigo_interno),
                                                    "+"
                                                )'
                                            title="Aceptar producto devuelto"
                                        >
                                            <i class="fas fa-check mr-1"></i>
                                            Aceptar
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm mb-1"
                                            onclick='opciones_devolucion(
                                                @json((int) $repuesto->id_devolucion),
                                                @json((int) $repuesto->cantidad),
                                                @json($repuesto->codigo_interno),
                                                "-"
                                            )'
                                            title="Rechazar producto devuelto"
                                        >
                                            <i class="fas fa-times mr-1"></i>
                                            Rechazar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-light">
                <div class="alert alert-info mb-0 py-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Al aceptar un producto, su stock será incorporado en la ubicación indicada.
                    Al rechazarlo, no se modificará el inventario.
                </div>
            </div>
        @else
            <div class="text-center text-muted py-5">
                <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>

                <h5>No hay productos pendientes</h5>

                <p class="mb-0">
                    Todos los repuestos de esta devolución ya fueron procesados.
                </p>
            </div>
        @endif
    </div>
</div>

<input
    type="hidden"
    name="num_nc"
    id="num_nc"
    value="{{ $num_nc }}"
>
