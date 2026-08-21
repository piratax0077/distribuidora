<style>
    .devolucion-wrapper {
        margin-top: 1rem;
    }

    .documento-selector .list-group-item {
        border-radius: .5rem;
        margin-bottom: .5rem;
        border: 1px solid #dee2e6;
    }

    .documento-selector .list-group-item:hover {
        background-color: #f8f9fa;
    }

    .documento-card {
        border: 1px solid #dee2e6;
        border-radius: .75rem;
        background: #fff;
        box-shadow: 0 3px 12px rgba(0, 0, 0, .08);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .documento-card-header {
        background: #343a40;
        color: #fff;
        padding: 1rem 1.25rem;
    }

    .documento-card-header h5 {
        margin: 0;
        font-weight: 600;
    }

    .documento-resumen {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 1rem;
    }

    .documento-resumen-item {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: .5rem;
        padding: .75rem;
        height: 100%;
    }

    .documento-resumen-item small {
        display: block;
        color: #6c757d;
        margin-bottom: .2rem;
    }

    .tabla-devolucion {
        margin-bottom: 0;
        font-size: .9rem;
    }

    .tabla-devolucion thead th {
        white-space: nowrap;
        vertical-align: middle;
        background: #343a40;
        color: #fff;
        border-color: #454d55;
    }

    .tabla-devolucion tbody td {
        vertical-align: middle;
    }

    .descripcion-repuesto {
        min-width: 220px;
        max-width: 320px;
        white-space: normal;
    }

    .cantidad-devolver {
        min-width: 90px;
    }

    .select-destino {
        min-width: 170px;
    }

    .resumen-devolucion {
        border-top: 1px solid #dee2e6;
        background: #f8f9fa;
        padding: 1rem;
    }

    .resumen-total {
        font-size: 1.05rem;
        font-weight: 600;
    }

    .sin-detalle {
        text-align: center;
        padding: 2rem;
        color: #6c757d;
    }

    @media (max-width: 767.98px) {
        .documento-card-header {
            padding: .8rem 1rem;
        }

        .tabla-devolucion {
            font-size: .82rem;
        }

        .descripcion-repuesto {
            min-width: 180px;
        }
    }
</style>

<div class="devolucion-wrapper">
    @if(count($documentos) > 1)
        <div class="documento-selector mb-3">
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle"></i>
                <strong>Se encontraron {{ count($documentos) }} documentos.</strong>
                Seleccione el documento que desea procesar.
            </div>

            <div class="list-group">
                @foreach($documentos as $index => $doc_data)
                    <button
                        type="button"
                        class="list-group-item list-group-item-action"
                        onclick="seleccionar_documento({{ $index }})"
                    >
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <h5 class="mb-1">
                                {{ $tipo_documento }} N° {{ $doc_data['documento']->num_doc }}
                            </h5>

                            <span class="badge badge-{{ $doc_data['documento']->estado_sii === 'ACEPTADO' ? 'success' : 'danger' }}">
                                {{ $doc_data['documento']->estado_sii }}
                            </span>
                        </div>

                        <div class="text-muted">
                            <strong>Fecha:</strong>
                            {{ \Carbon\Carbon::parse($doc_data['documento']->fecha_emision)->format('d-m-Y') }}
                            <span class="mx-2">|</span>
                            <strong>Ítems:</strong> {{ $doc_data['detalle']->count() }}

                            @if(!empty($doc_data['documento']->motivo_correccion))
                                <span class="mx-2">|</span>
                                <strong>Motivo:</strong> {{ $doc_data['documento']->motivo_correccion }}
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    @foreach($documentos as $index => $doc_data)
        @php
            $totalDocumento = 0;

            foreach ($doc_data['detalle'] as $detalleItem) {
                $precioDocumento = $detalleItem->precio_documento ?? $detalleItem->precio_venta ?? 0;
                $totalDocumento += $precioDocumento * $detalleItem->cantidad;
            }

            $numeroNotaCredito = $doc_data['documento']->num_nota_credito ?? null;
            $numeroDocumento = $doc_data['documento']->num_doc;
            $tipoDocumentoCodigo = strtolower(substr($tipo_documento, 0, 2));
        @endphp

        <div
            id="documento-{{ $index }}"
            class="documento-detalle"
            style="display: {{ count($documentos) === 1 ? 'block' : 'none' }};"
        >
            <div class="documento-card">
                <div class="documento-card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5>
                        <i class="fas fa-file-invoice"></i>
                        {{ $tipo_documento }} N° {{ $numeroDocumento }}
                    </h5>

                    <span class="badge badge-{{ $doc_data['documento']->estado_sii === 'ACEPTADO' ? 'success' : 'danger' }}">
                        {{ $doc_data['documento']->estado_sii }}
                    </span>
                </div>

                <div class="documento-resumen">
                    <div class="row">
                        <div class="col-sm-6 col-lg-3 mb-2">
                            <div class="documento-resumen-item">
                                <small>Tipo de documento</small>
                                <strong>{{ $tipo_documento }}</strong>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-3 mb-2">
                            <div class="documento-resumen-item">
                                <small>Fecha de emisión</small>
                                <strong>
                                    {{ \Carbon\Carbon::parse($doc_data['documento']->fecha_emision)->format('d-m-Y') }}
                                </strong>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-3 mb-2">
                            <div class="documento-resumen-item">
                                <small>Cantidad de ítems</small>
                                <strong>{{ $doc_data['detalle']->count() }}</strong>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-3 mb-2">
                            <div class="documento-resumen-item">
                                <small>Total documento</small>
                                <strong>$ {{ number_format($totalDocumento, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        @if(!empty($doc_data['documento']->motivo_correccion))
                            <div class="col-12 mt-2">
                                <div class="alert alert-warning mb-0">
                                    <strong>Motivo de corrección:</strong>
                                    {{ $doc_data['documento']->motivo_correccion }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover tabla-devolucion">
                        <thead>
                            <tr>
                                <th>Cód. interno</th>
                                <th>Descripción</th>
                                <th class="text-right">Precio</th>
                                <th class="text-center">Vendida</th>
                                <th class="text-right">Total</th>
                                <th class="text-center">A devolver</th>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($doc_data['detalle'] as $d)
                                @php
                                    $precio = $d->precio_documento ?? $d->precio_venta ?? 0;
                                    $cantidadVendida = (int) $d->cantidad;
                                    $cantidadDisponible = isset($d->cantidad_disponible)
                                        ? (int) $d->cantidad_disponible
                                        : $cantidadVendida;
                                    $completamenteDevuelto = $cantidadDisponible <= 0;
                                @endphp

                                <tr id="fila-{{ $index }}-{{ $d->id_repuestos }}">
                                    <td class="font-weight-bold">
                                        {{ $d->codigo_interno }}
                                    </td>

                                    <td class="descripcion-repuesto">
                                        {{ $d->descripcion }}
                                    </td>

                                    <td class="text-right">
                                        $ {{ number_format($precio, 0, ',', '.') }}
                                    </td>

                                    <td class="text-center">
                                        <span class="badge badge-secondary">
                                            {{ $cantidadVendida }}
                                        </span>

                                        <input
                                            type="hidden"
                                            id="merma-{{ $index }}-{{ $d->id_repuestos }}"
                                            value="{{ $cantidadDisponible }}"
                                        >
                                    </td>

                                    <td class="text-right">
                                        $ {{ number_format($cantidadVendida * $precio, 0, ',', '.') }}
                                    </td>

                                    <td class="text-center cantidad-devolver">
                                        @if($completamenteDevuelto)
                                            <span class="badge badge-secondary">
                                                Devuelto
                                            </span>
                                        @else
                                            <input
                                                type="number"
                                                min="1"
                                                max="{{ $cantidadDisponible }}"
                                                step="1"
                                                class="form-control form-control-sm text-center"
                                                id="cantidad-{{ $index }}-{{ $d->id_repuestos }}"
                                                value="1"
                                                inputmode="numeric"
                                                aria-label="Cantidad a devolver de {{ $d->descripcion }}"
                                            >

                                            @if(isset($d->cantidad_disponible))
                                                <small class="text-muted d-block mt-1">
                                                    Disponible: {{ $cantidadDisponible }}
                                                </small>
                                            @endif
                                        @endif
                                    </td>

                                    <td>
                                        {{ $d->local_nombre }}
                                    </td>

                                    <td>
                                        @if($completamenteDevuelto)
                                            <span class="text-muted">No aplica</span>
                                        @else
                                            <select
                                                name="local_id"
                                                id="local_id-{{ $index }}-{{ $d->id_repuestos }}"
                                                class="form-control form-control-sm select-destino"
                                                aria-label="Destino para {{ $d->descripcion }}"
                                            >
                                                <option value="">Seleccione destino</option>

                                                @foreach($locales as $l)
                                                    <option value="{{ $l->id }}">
                                                        {{ $l->local_nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if($completamenteDevuelto)
                                            <button
                                                type="button"
                                                class="btn btn-secondary btn-sm"
                                                disabled
                                            >
                                                <i class="fas fa-check"></i>
                                                Completado
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                class="btn btn-success btn-sm"
                                                onclick="confirmar_devolucion({{ $d->id_repuestos }}, {{ $index }})"
                                            >
                                                <i class="fas fa-undo-alt"></i>
                                                Agregar
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="sin-detalle">
                                        <i class="fas fa-box-open fa-2x mb-2"></i>
                                        <div>No existen repuestos disponibles para devolución.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="resumen-devolucion">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div id="table_devolucion_{{ $index }}">
                                @if(
                                    isset($doc_data['devoluciones']) &&
                                    $doc_data['devoluciones']->count() > 0
                                )
                                    @include('fragm.devoluciones_table', [
                                        'devoluciones' => $doc_data['devoluciones'],
                                        'doc_index' => $index
                                    ])
                                @else
                                    <div class="alert alert-light border text-muted mb-0">
                                        <i class="fas fa-info-circle"></i>
                                        Todavía no se han agregado repuestos a esta devolución.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-4 text-md-right mt-3 mt-md-0">
                            <div class="resumen-total mb-2">
                                Total documento:
                                $ {{ number_format($totalDocumento, 0, ',', '.') }}
                            </div>

                            @if($numeroNotaCredito)
                                <input
                                    type="hidden"
                                    name="num_nc"
                                    id="num_nc_{{ $index }}"
                                    value="{{ $numeroNotaCredito }}"
                                >

                                @if($index === 0)
                                    <input
                                        type="hidden"
                                        id="num_nc"
                                        value="{{ $numeroNotaCredito }}"
                                    >
                                @endif

                                <button
                                    type="button"
                                    class="btn btn-danger"
                                    onclick='cerrar_devolucion(@json($numeroNotaCredito))'
                                >
                                    <i class="fas fa-lock"></i>
                                    Cerrar devolución
                                </button>
                            @else
                                <input
                                    type="hidden"
                                    name="num_doc"
                                    id="num_doc_{{ $index }}"
                                    value="{{ $numeroDocumento }}"
                                >

                                @if($index === 0)
                                    <input
                                        type="hidden"
                                        id="num_doc"
                                        value="{{ $numeroDocumento }}"
                                    >
                                @endif

                                <button
                                    type="button"
                                    class="btn btn-danger"
                                    onclick='cerrar_devolucion(@json($numeroDocumento))'
                                >
                                    <i class="fas fa-lock"></i>
                                    Cerrar devolución
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
    function seleccionar_documento(index) {
        document.querySelectorAll('.documento-detalle').forEach(function (documento) {
            documento.style.display = 'none';
        });

        const documentoSeleccionado = document.getElementById('documento-' + index);

        if (documentoSeleccionado) {
            documentoSeleccionado.style.display = 'block';
            documentoSeleccionado.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
</script>

<div
    class="modal fade"
    id="exampleModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="exampleModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="exampleModalLabel">
                    <i class="fas fa-check-circle"></i>
                    Devolución procesada
                </h5>

                <button
                    type="button"
                    class="close text-white"
                    data-dismiss="modal"
                    aria-label="Cerrar"
                >
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="alert alert-success text-center">
                    <strong>Listo.</strong>
                    La solicitud de devolución fue cerrada correctamente.
                </div>

                <div id="listado_repuestos_devolucion"></div>
            </div>

            <div class="modal-footer">
                <button
                    id="btn-imprimir"
                    type="button"
                    class="btn btn-warning"
                    onclick="imprimir_devolucion_actual()"
                >
                    <i class="fas fa-print"></i>
                    Imprimir
                </button>

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-dismiss="modal"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
