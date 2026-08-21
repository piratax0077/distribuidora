<div class="table-responsive">
    <table class="table table-sm table-bordered mb-0">
        <thead class="thead-light">
            <tr>
                <th>Cód. interno</th>

                @if(isset($admin))
                    <th>Descripción</th>
                @endif

                <th class="text-center">Cantidad</th>
                <th>Destino</th>
                <th class="text-center">Acción</th>
            </tr>
        </thead>

        <tbody>
            @forelse($devoluciones as $devolucion)
                <tr>
                    <td>{{ $devolucion->codigo_interno }}</td>

                    @if(isset($admin))
                        <td>{{ $devolucion->descripcion }}</td>
                    @endif

                    <td class="text-center">
                        <span class="badge badge-info">
                            {{ $devolucion->cantidad }}
                        </span>
                    </td>

                    <td>{{ $devolucion->local_nombre }}</td>

                    <td class="text-center">
                        <button
                            type="button"
                            class="btn btn-danger btn-sm"
                            onclick="eliminar_devolucion(
                                {{ $devolucion->id_devolucion }},
                                {{ isset($doc_index) && $doc_index !== null
                                    ? $doc_index
                                    : 'null' }}
                            )"
                            title="Eliminar devolución"
                        >
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td
                        colspan="{{ isset($admin) ? 5 : 4 }}"
                        class="text-center text-muted py-3"
                    >
                        No existen repuestos agregados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
