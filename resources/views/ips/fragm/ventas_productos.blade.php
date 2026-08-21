<table class="table letra_pequeña">
    <thead>
        <tr>
            <th>Codigo interno</th>
            <th>Descripcion</th>
            <th>CU</th>
            <th>FT</th>
            <th>ST</th>
            <th>Marca</th>
            <th>Pais</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Cantidad</th>
            <th>Nuevo Precio</th>
            <th>Descuento %</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($resultados as $repuesto)
            <tr>
                <td>@if(Auth::user()->rol->nombrerol == "Administrador")<a href="{{url('repuesto/modificar')}}/{{$repuesto->id}}" target="_blank">{{ $repuesto->codigo_interno }}</a> @else {{$repuesto->codigo_interno}}  @endif </td>
                <td>{{ $repuesto->descripcion }}</td>
                <td>{{ $repuesto->cu }}</td>
                <td>{{ $repuesto->ct }}</td>
                <td>{{ $repuesto->ft }}</td>
                <td>{{ $repuesto->marca }}</td>
                <td>{{ $repuesto->pais->nombre_pais }}</td>
                <td>@if($repuesto->oferta == 1) <span class="badge badge-info">Oferta</span>  @endif{{number_format($repuesto->precio_venta,0,',','.') }}</td>
                @php
                    $stock_total = $repuesto->stock_actual + $repuesto->stock_actual_mermas + $repuesto->stock_actual_preventa;
                    $stocks_del_repuesto = isset($stocks_repuesto[$repuesto->id]) ? $stocks_repuesto[$repuesto->id] : [];
                @endphp
                <td>
                    @if(count($stocks_del_repuesto) > 0)
                        <select class="letra_pequeña select-locales-{{ $repuesto->id }}" id="select_locales_{{ $repuesto->id }}">
                            @foreach ($stocks_del_repuesto as $local)
                                <option value="{{ $local['id_local'] }}">{{ $local['local_nombre'] }} ({{ $local['stock_actual'] }})</option>
                            @endforeach
                        </select>
                    @else
                        <span class="badge badge-danger">Sin stock</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="number" id="cantidad_{{ $repuesto->id }}" class="form-control form-control-sm" value="1" min="1" style="width: 60px;" @if(count($stocks_del_repuesto) == 0) disabled @endif>
                        <button class="btn btn-outline-success btn-sm ml-1" onclick="agregarProductoCarro({{ $repuesto->id }})" @if(count($stocks_del_repuesto) == 0) disabled @endif>
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </td>
                <td>
                    <input type="number" id="nuevo_precio_{{ $repuesto->id }}"
                           class="form-control form-control-sm"
                           placeholder="Nuevo precio"
                           data-precio-original="{{ $repuesto->precio_venta }}"
                           data-repuesto-id="{{ $repuesto->id }}"
                           onkeyup="calcularDescuentoDesdePrecio({{ $repuesto->id }})"
                           style="width: 100px;"
                           @if(count($stocks_del_repuesto) == 0) disabled @endif>
                </td>
                <td>
                    <div class="input-group input-group-sm" style="width: 120px;">
                        <input type="number" id="descuento_{{ $repuesto->id }}"
                               class="form-control form-control-sm"
                               placeholder="%"
                               min="0"
                               max="100"
                               step="0.01"
                               data-precio-original="{{ $repuesto->precio_venta }}"
                               onkeyup="aplicarDescuento({{ $repuesto->id }})"
                               style="width: 60px;"
                               @if(count($stocks_del_repuesto) == 0) disabled @endif>
                        <div class="input-group-append">
                            <button class="btn btn-outline-primary btn-sm"
                                    onclick="aplicarDescuento({{ $repuesto->id }})"
                                    title="Aplicar descuento"
                                    @if(count($stocks_del_repuesto) == 0) disabled @endif>
                                <i class="fas fa-calculator"></i>
                            </button>
                        </div>
                    </div>
                </td>
                <td class="d-flex">
                    <button class="btn btn-outline-warning btn-sm" onclick="detalle({{ $repuesto->id }})">Detalle</button>
                    <button class="btn btn-outline-danger btn-sm" onclick="codigo_barras({{ $repuesto->id }})">CB</button>
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot class="bg-light">
        <tr>
            <td colspan="8" class="text-right font-weight-bold">
                <i class="fas fa-box-open"></i> Total de productos encontrados:
            </td>
            <td colspan="3" class="font-weight-bold">
                <span class="badge badge-primary badge-lg" style="font-size: 1rem; padding: 0.5rem 1rem;">
                    {{ $total }} {{ $total == 1 ? 'producto' : 'productos' }}
                </span>
            </td>
        </tr>
        @php
            $con_stock = 0;
            $sin_stock = 0;
            foreach($resultados as $r){
                $stocks_r = isset($stocks_repuesto[$r->id]) ? $stocks_repuesto[$r->id] : [];
                if(count($stocks_r) > 0){
                    $con_stock++;
                }else{
                    $sin_stock++;
                }
            }
        @endphp
        @if($total > 0)
        <tr class="bg-light">
            <td colspan="8" class="text-right">
                <small class="text-muted">Disponibilidad:</small>
            </td>
            <td colspan="3">
                @if($con_stock > 0)
                    <span class="badge badge-success">
                        <i class="fas fa-check-circle"></i> {{ $con_stock }} con stock
                    </span>
                @endif
                @if($sin_stock > 0)
                    <span class="badge badge-warning ml-2">
                        <i class="fas fa-exclamation-triangle"></i> {{ $sin_stock }} sin stock
                    </span>
                @endif
            </td>
        </tr>
        @endif
    </tfoot>
</table>

<script>
    // Función para aplicar descuento y calcular nuevo precio
    function aplicarDescuento(id) {
        let descuento = parseFloat($('#descuento_' + id).val()) || 0;
        let precioOriginal = parseFloat($('#descuento_' + id).data('precio-original'));

        if(descuento < 0 || descuento > 100){
            Vue.swal({
                icon: 'error',
                title: 'Error',
                text: 'El descuento debe estar entre 0 y 100%',
                position: 'top-right',
                timer: 2000,
                toast: true,
                showConfirmButton: false
            });
            $('#descuento_' + id).val('');
            return;
        }

        let descuentoMonto = precioOriginal * (descuento / 100);
        let nuevoPrecio = precioOriginal - descuentoMonto;

        $('#nuevo_precio_' + id).val(Math.round(nuevoPrecio));

        // Mostrar confirmación visual
        $('#nuevo_precio_' + id).addClass('bg-success text-white').delay(500).queue(function(){
            $(this).removeClass('bg-success text-white').dequeue();
        });
    }

    // Función para calcular descuento cuando se ingresa un nuevo precio
    function calcularDescuentoDesdePrecio(id) {
        let nuevoPrecio = parseFloat($('#nuevo_precio_' + id).val()) || 0;
        let precioOriginal = parseFloat($('#nuevo_precio_' + id).data('precio-original'));

        if(nuevoPrecio > precioOriginal){
            Vue.swal({
                icon: 'warning',
                title: 'Atención',
                text: 'El nuevo precio es mayor al precio original',
                position: 'top-right',
                timer: 2000,
                toast: true,
                showConfirmButton: false
            });
        }

        if(nuevoPrecio > 0 && nuevoPrecio < precioOriginal){
            let descuento = ((precioOriginal - nuevoPrecio) / precioOriginal) * 100;
            $('#descuento_' + id).val(descuento.toFixed(2));
        }
    }

    // Función para limpiar descuentos
    function limpiarDescuento(id){
        $('#descuento_' + id).val('');
        $('#nuevo_precio_' + id).val('');
    }

    function agregarProductoCarro(id) {
        let cantidad = document.getElementById('cantidad_' + id).value;
        let id_local = $('#select_locales_' + id).val();

        // Obtener el nuevo precio si se aplicó un descuento
        let nuevoPrecio = parseFloat($('#nuevo_precio_' + id).val()) || 0;
        let precioOriginal = parseFloat($('#nuevo_precio_' + id).data('precio-original'));
        let precioFinal = nuevoPrecio > 0 ? nuevoPrecio : precioOriginal;

        console.log('Cantidad:', cantidad);
        console.log('Precio original:', precioOriginal);
        console.log('Nuevo precio:', nuevoPrecio);
        console.log('Precio final:', precioFinal);

        if(cantidad == 0) {
            return Vue.swal({
                title: 'Error',
                text: 'La cantidad no puede ser 0',
                icon: 'error',
                position:'top-right',
                timer: 2000,
                timerProgressBar: true,
                toast: true,
                showConfirmButton: false
            })
        }

        // si el input de tipo hidden es_transferido es distinto a 0 es porque se esta transfiriendo
        var es_transferido = document.getElementById("es_transferido").value;
        console.log(es_transferido);
        if(es_transferido != 0){
          var url="/ventas/agregar-producto-carro-transferido";
          var parametros = {
              id: id,
              idlocal: id_local,
              cantidad: cantidad,
              cliente_id: es_transferido,
              carrito_id: 0,
              precio_custom: nuevoPrecio > 0 ? nuevoPrecio : null
          };

        }else{
          var url="/ventas/agregar-producto-carro";
          var parametros={
              id: id,
              idlocal: id_local,
              cantidad: cantidad,
              precio_custom: nuevoPrecio > 0 ? nuevoPrecio : null
          };
        }

        console.log('Parametros:', parametros);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: url,
            method: 'POST',
            data: parametros,
            success: function(response) {
                console.log(response);
                dame_carrito();
                if(response == 'error') {
                    Vue.swal({
                        title: 'Error',
                        text: response,
                        icon: 'error',
                        position:'top-right',
                        timer: 2000,
                        timerProgressBar: true,
                        toast: true,
                        showConfirmButton: false
                    });

                }else{

                    Vue.swal({
                        title: 'Producto agregado',
                        text: 'El producto se ha agregado al carro de compras',
                        icon: 'success',
                        position:'top-right',
                        timer: 2000,
                        timerProgressBar: true,
                        toast: true,
                        showConfirmButton: false
                    });
                    let t=new Intl.NumberFormat('es-CL').format(response);
                    $("#mensajes-modal").html("Total: "+t);
                }
            },
            error: function(error) {
                console.log(error.responseText);
            }
        });
    }

    function detalle(id){
        Vue.swal({
            title: 'Detalle del producto',
            text: 'Aqui va el detalle del producto',
            icon: 'info',
            position:'top-right',
            timer: 2000,
            timerProgressBar: true,
            toast: true,
            showConfirmButton: false
        });
    }

    function codigo_barras(id){
        Vue.swal({
            title: 'Codigo de barras',
            text: 'Aqui va el codigo de barras',
            icon: 'info',
            position:'top-right',
            timer: 2000,
            timerProgressBar: true,
            toast: true,
            showConfirmButton: false
        });
    }

    function codigo_barras(id){

        let url = "/generate-barcode/"+id;

        //  window.open(url,'_blank');
        // window.location.href = "/generate-barcode/"+id_repuesto;

        $.ajax({
            type:'get',
            url:url,
            beforeSend: function(){
            Vue.swal({
                            title: 'ESPERE...',
                            icon: 'info',
                        });
            },
            success: function(resp){
            console.log(resp);
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
            error: function(err){
            console.log(err);
            }
        })
        }
</script>
