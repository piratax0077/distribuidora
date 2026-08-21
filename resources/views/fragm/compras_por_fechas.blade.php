<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-file-invoice-dollar"></i> Facturas de Compras por Fechas
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tabla-facturas">
                    <thead class="thead-dark">
                        <tr>
                            <th>N° Factura</th>
                            <th>Fecha Factura</th>
                            <th>Observaciones</th>
                            <th>Activo</th>
                            <th>Usuario</th>
                            <th>Empresa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($compras) && count($compras) > 0)
                            @foreach($compras as $factura)
                                <tr>
                                    <td>
                                        <a href="javascript:void(0)" onclick="verFactura('{{ $factura['id'] }}')">{{ $factura['factura_numero'] ?? '-' }}</a>
                                    </td>
                                    <td>
                                        @if($factura['factura_fecha'])
                                            {{ \Carbon\Carbon::parse($factura['factura_fecha'])->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if($factura['factura_observaciones'])
                                            <span title="{{ $factura['factura_observaciones'] }}">
                                                {{ Str::limit($factura['factura_observaciones'], 30) }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if($factura['activo'] == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>{{ $factura['usuario_nombre'] ?? '-' }}</td>
                                    <td>{{ $factura['empresa_nombre_corto'] ?? '-' }}</td>


                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="21" class="text-center">
                                    <div class="py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No hay facturas para mostrar</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if(isset($facturas) && count($facturas) > 0)
                <!-- Resumen de totales -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <h6>Total de Facturas</h6>
                                        <h4 class="text-primary">{{ count($facturas) }}</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <h6>Facturas Pagadas</h6>
                                        <h4 class="text-success">
                                            {{ collect($facturas)->where('pagada', 1)->count() }}
                                        </h4>
                                    </div>
                                    <div class="col-md-3">
                                        <h6>Facturas Pendientes</h6>
                                        <h4 class="text-danger">
                                            {{ collect($facturas)->where('pagada', 0)->count() }}
                                        </h4>
                                    </div>
                                    <div class="col-md-3">
                                        <h6>Total General</h6>
                                        <h4 class="text-info">
                                            ${{ number_format(collect($facturas)->sum('factura_total'), 2, ',', '.') }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .table th {
        font-size: 12px;
        white-space: nowrap;
    }
    .table td {
        font-size: 13px;
        vertical-align: middle;
    }
    .badge {
        font-size: 11px;
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Aquí puedes agregar funcionalidad JavaScript si es necesario
        console.log('Tabla de facturas cargada');

        // Ejemplo: hacer que la tabla sea más interactiva
        const filas = document.querySelectorAll('#tabla-facturas tbody tr');
        filas.forEach(fila => {
            fila.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f9fa';
            });
            fila.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });

    function verFactura(id) {
      // Aquí puedes implementar la lógica para ver los detalles de la factura
      // Por ejemplo, abrir un modal o redirigir a otra página
      console.log('Ver detalles de la factura con ID:', id);
      $.ajax({
        url: '/compras/listar_factura_id/' + id,
        method: 'GET',
        success: function(data) {
          // Mostrar los detalles en un modal o en una sección específica
          console.log('Detalles de la factura:', data);
          $('#detalle_compra').html(data);
        },
        error: function(error) {
          console.error('Error al obtener los detalles de la factura:', error);
          Vue.swal({
            icon: 'error',
            text: 'Error al cargar los detalles de la factura',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
          });
        }
      });
    }
</script>
