@if($carrito->count()>0)

	<script>

		$('#monto-1').val({{$total_neto}});

		$('#total_venta_vuelto').val({{$total_neto}});

        function abrir_modal_editar_item(descripcion, cant, pu, descuento, id,codigo_interno){
            console.log(descripcion);
            $('#modalEditarItemCarritoTransferido').modal('show');
            $('#modalEditarItemCarritoTransferido').find('.modal-title').text('Editar Item:'+codigo_interno);
            $('#modalEditarItemCarritoTransferido').find('#descripcion_editar').val(descripcion);
            $('#modalEditarItemCarritoTransferido').find('#cantidad_editar').val(cant);
            $('#modalEditarItemCarritoTransferido').find('#pu_editar').val(pu);
            $('#modalEditarItemCarritoTransferido').find('#descuento_editar').val(descuento);
            $('#modalEditarItemCarritoTransferido').find('#id').val(id);

            $('#pu_editar').focus();
        }


	</script>

	{{-- @if($carrito_transferido->count() > 0)

		<p>Tiene un carrito pendiente</p>

	@endif --}}

	<div class="row">

		@foreach($carrito_transferido as $ct)



			<div class="col-md-3 mb-2">

				<div class="card">

					<div class="card-header d-flex justify-content-between align-items-start">

						<h5 class="card-title">N°{{$ct->numero_carrito}}</h5>

						@if($ct->estado == 0)<span class="badge badge-success">Facturado <br> {{ $ct->tipo_doc ?? '-' }} - {{ $ct->num_doc ?? '-' }}</span> @else <span class="badge badge-warning">En espera</span> @endif

					</div>

					<div class="card-body" style="height: 200px;">

						<div class="row mb-2">

							<div class="col-md-3">

								<img src="{{ url('usuarios/avatar/'.$ct->image_path) }}" alt="logo" class="w-100">

							</div>

							<div class="col-md-8">

								<span class="card-text">{{ $ct->name }}</span> <br>

								<span class="card-text text-secondary font-italic" style="font-size: 13px;">{{$ct->cliente}}</span> <br>
								<span class="card-text text-secondary">
									{{ $ct->created_at->format('d/m/Y') }}
									{{ $ct->created_at->format('H:i:s') }}
								</span>
								

							</div>
							

						</div>

					</div>

					<div class="card-footer">
						@if($ct->estado == 1)
						<div class="d-flex justify-content-between">

							<button onclick="abrir_carrito_transferido({{$ct->carrito_id}})" class="btn btn-outline-success btn-sm"><i class="fas fa-check"></i> Abrir</button>

							<a href="javascript:borrar_carrito_transferido({{$ct->carrito_id}})" class="btn btn-outline-danger btn-sm disabled" ><i class="fas fa-trash"></i> Eliminar</a>

						</div>
						@else
						<button class="btn btn-outline-primary btn-sm float-right"><i class="fas fa-search"></i> Detalle</button>
						@endif
					</div>
				</div>

			</div>



		@endforeach

	</div>



	@if(isset($oferta) && $oferta == true)

	<input type="hidden" name="es_oferta" id="es_oferta" value="1">

	@else

	<input type="hidden" name="es_oferta" id="es_oferta" value="0">

	@endif



	<input type="hidden" name="confirmado" id="confirmado" value="">

	<div class="row">

		<div class="col-sm-12 tabla-scroll-y-300">

		<table id="tbl_carrito" class="table table-hover table-bordered table-sm letra-chica">

			<thead>

			<th width="5%" scope="col" class="alin-cen">Cod Int</th>

			<th width="32%" scope="col" class="alin-cen">Descripción</th>

			<th width="6%" scope="col" class="alin-cen">Marca</th>

			{{-- <th width="7%" scope="col" class="alin-cen">Cod Prov</th> --}}

			<th width="3%" scope="col" class="alin-cen">Cant</th>

			<th width="6%" scope="col" class="alin-cen">Precio</th>

            <th width="6%" scope="col" class="alin-cen">Ubicación</th>

			<th width="8%" scope="col" class="alin-cen">Total</th>

			<th width="7%" scope="col" class="alin-cen">Descuento</th>

			{{-- <th width="8%" scope="col" class="alin-cen">Local</th> --}}

			<th width="6%" scope="col" class="alin-cen">Total</th>

			<th width="3%" scope="col"></th> <!-- Ver Relacionados -->

			<th width="3%" scope="col"></th> <!-- Borrar Item -->

			</thead>

			<tbody>

			@foreach ($carrito as $item)

			<tr>



				<td class="alin-cen"> <a href="javascript:void(0)" onclick="dameInfoRepuestoModal({{$item->id_repuestos}})" data-target="#modalInfoRepuesto" data-toggle="modal">{{$item->codigo_interno}}</a>  </td>

			    <td>{{$item->descripcion}}</td>



			    <td>{{$item->marca}}</td>

                {{-- <td><span style="font-weight: bold; font-size: 13px;">{{$item->cod_repuesto_proveedor}}</span></td> --}}

                <td class="alin-cen">{{$item->cantidad}}</td>

                    @if(isset($oferta) && $item->oferta == 1 && ($oferta == true))

                        <td class="alin-der d-flex"><a href="javascript:void(0)" onclick="abrirModalPrecio({{$item->id_repuestos}})"><span class="badge badge-danger">Oferta</span> </a>{{number_format($item->pu,0,',','.')}}</td>

                    @elseif(isset($oferta) && $item->oferta == 2 && ($oferta == true))

                        <td class="alin-der d-flex"><a href="javascript:void(0)" onclick="abrirModalPrecio({{$item->id_repuestos}})"><span class="badge badge-primary">Oferta</span> </a>{{number_format($item->pu,0,',','.')}}</td>

                    @else

                        <td class="alin-der">{{number_format($item->pu,0,',','.')}}</td>

                    @endif

                    <td class="alin-der">{{$item->local_nombre}}</td>

                <td class="alin-der">{{number_format($item->subtotal_item,0,',','.')}}</td>

                <td class="alin-der">{{number_format($item->descuento_item,0,',','.')}}</td>



                <td class="alin-der">{{number_format($item->total_item,0,',','.')}}</td>



		<!--

			<td class="alin-cen"><a href="javascript:void(0);" onclick="ver_relacionados({{$item->id_repuestos}})"><abbr title="Repuestos Relacionados">R</abbr></a> </td>

			-->

			<td><button class="btn btn-outline-danger btn-sm" onclick="abrir_modal_editar_item({{ json_encode($item->descripcion) }},{{ $item->cantidad }},{{ $item->pu }},{{ $item->subtotal_item > 0 ? ($item->descuento_item * 100 / $item->subtotal_item) : 0 }},{{ $item->id }},{{ json_encode($item->codigo_interno) }})"><i class="fas fa-edit"></i></button></td>

			<td class="alin-cen"><a href="javascript:void(0);" onclick="borrar_item_carrito({{$item->id}})"><abbr title="Borrar Item"><b style="color:red">X</b></abbr></span></a></td>

			</tr>

			@endforeach

			</tbody>

		</table>

		</div>

	</div>



	<div class="row mt-3">

		<div class="col-sm-8">

			<input type="hidden" id="items_carrito" value="{{$carrito->count()}}">

			<input type="hidden" id="total_carrito" value="{{$total_neto}}">

			Total Items: {{$carrito->count()}}

		</div>

		<div class="col-sm-4" style="color:blue"><b>Total Neto: {{ number_format($total,0,',','.') }}</b><br> <b>Total Venta: {{number_format($total_neto,0,',','.')}}</b> </div>

	</div>

    <!-- modalEditarItemCarrito -->
<div class="modal fade" id="modalEditarItemCarritoTransferido" tabindex="-1" role="dialog" aria-labelledby="modalEditarItemCarritoTransferidoLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">

				<div class="modal-header">
					<h5 class="modal-title" id="modalEditarItemCarritoTransferidoLabel">Editar Item</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<input type="hidden" name="id" id="id">
				<div class="modal-body">
					<div class="form-row">
						<div class="form-group floating col-md-4">

							<input type="number" class="form-control floating" id="cantidad_editar" name="cantidad_editar" required>
                            <label class="floating" for="cantidad">Cantidad</label>
                        </div>
						<div class="form-group floating col-md-4">
                            <input type="text" class="form-control floating" id="pu_editar" name="pu_editar" required>
							<label class="floating" for="pu">Precio Unitario</label>


						</div>
						<div class="form-group floating col-md-4">

							<input type="number" class="form-control floating" id="descuento_editar" name="descuento_editar" required>
                            <label class="floating" for="descuento">Descuento</label>
                        </div>
					</div>

                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-4">
                            <div style="display: flex">
                                <button class="btn btn-outline-success btn-sm" onclick="agregar_iva()">+IVA</button>
                                <button class="btn btn-outline-primary btn-sm" onclick="quitar_iva()">-IVA</button>
                            </div>
                        </div>
                        <div class="col-md-4"></div>
                    </div>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cerrar</button>
					<button type="button" id="btn_guardar_edicion_item" class="btn btn-outline-primary btn-sm" onclick="guardar_edicion_item()">Guardar</button>
				</div>
		</div>
	</div>
</div>
@elseif($carrito_transferido->count() > 0)

<div class="row">

	@foreach($carrito_transferido as $ct)
	<div class="col-md-3 mb-2">
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-start">
                <img src="{{ asset('storage/imagenes/ips_logo.png') }}" alt="logo" class="logoHeader">
				<h5 class="card-title"></h5>
				@if($ct->estado == 0)<span class="badge badge-success">N° {{$ct->numero_carrito === null ? $ct->id : $ct->numero_carrito}} Facturado <br> {{ $ct->tipo_doc ?? '-' }} - {{ $ct->num_doc ?? '-' }}</span> @else <span class="badge badge-warning">N° {{$ct->numero_carrito === null ? $ct->id : $ct->numero_carrito}} En espera</span> @endif
			</div>

			<div class="card-body" style="min-height: 200px;">
				<div class="row mb-2">
					<div class="col-md-3">
						<img src="{{ url('usuarios/avatar/'.$ct->image_path) }}" alt="logo" class="w-100">
					</div>
					<div class="col-md-9">
						<span class="card-text">{{ $ct->name }}</span> <br>
						<span class="card-text text-secondary" style="font-size: 11px;">{{ $ct->cliente }}</span><br>

						<span class="card-text text-secondary " style="font-size: 11px;">{{ $ct->created_at }}</span>
					</div>
				</div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <span class="card-text text-secondary font-italic " style="font-size: 11px;">{{$ct->titulo}}</span> <br>
                    </div>
                </div>
			</div>
            <div class="card-footer">
                @if($ct->estado == 1)
				<div class="d-flex justify-content-between">
					<button onclick="abrir_carrito_transferido({{$ct->carrito_id}})" class="btn btn-outline-success btn-sm">Abrir</button>
				    <a href="javascript:borrar_carrito_transferido({{$ct->carrito_id}})" class="btn btn-outline-danger btn-sm disabled" > <i class="fas fa-trash"></i>Eliminar</a>
				</div>
                @else
                <button class="btn btn-outline-primary btn-sm float-right" onclick="imprimir_ticket_para_bodega({{ $ct->cliente_id }})"><i class="fas fa-print"></i> Imprimir ticket cliente</button>
				@endif
            </div>
		</div>
	</div>
	@endforeach
</div>

@else

<div class="row">
	<div class="col-sm-3">
			<h4 class='alert alert-info text-center'>Nota vacia</h4><input type='hidden' id='items_carrito' value='0'>
			<input type="hidden" id="items_carrito" value="0">
	</div>
</div>



@endif

<script>
    function guardar_edicion_item(){
		var id = $('#id').val();
		var cantidad = $('#cantidad_editar').val();
		var pu = $('#pu_editar').val();
		var descuento = $('#descuento_editar').val();
        // saber que tipo de documento esta checkeado
        var tipo_documento = $('input[name="tipo_documento"]:checked').val();
		$.ajax({
			url: "{{route('editar_item_carrito')}}",
			type: 'POST',
			data: {
				_token: "{{ csrf_token() }}",
				id: id,
				cantidad: cantidad,
				pu: pu,
				descuento: descuento,
                tipo_documento: tipo_documento
            },
            beforeSend: function(){
                $('#btn_guardar_edicion_item')
					.prop('disabled', true)
					.html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');
            },
			success: function(response){
				// El controlador ya devuelve el carrito recalculado. Usar esa
				// respuesta evita una segunda petición y asegura que la vista
				// quede restablecida antes de cerrar el modal.
				$('#modalEditarItemCarritoTransferido')
					.one('hidden.bs.modal', function(){
						$('#carrito').html(response);
					})
					.modal('hide');
			},
			error: function(error){
				console.log(error);
			},
			complete: function(){
				$('#btn_guardar_edicion_item')
					.prop('disabled', false)
					.html('Guardar');
			}
		});
	}

    function agregar_iva(){
        var pu = $('#pu_editar').val();
        var nuevo_pu = parseFloat(pu) + (parseFloat(pu) * 0.19);
        // formatear el numero con 2 decimales
        nuevo_pu = nuevo_pu.toFixed(0);
        $('#pu_editar').val(nuevo_pu);
        // focus
        $('#pu_editar').focus();

        // Actualizar la etiqueta flotante (ejemplo para Bootstrap)
        if ($('#pu_editar').hasClass('is-invalid') || $('#pu_editar').hasClass('is-valid')) {
            $('#pu_editar').removeClass('is-invalid is-valid').addClass('is-valid');
        }else{
            $('#pu_editar').addClass('is-valid');
        }
    }

    function quitar_iva(){
        var pu = $('#pu_editar').val();
        var nuevo_pu = parseFloat(pu) / 1.19;
        // formatear el numero con 2 decimales
        nuevo_pu = nuevo_pu.toFixed(2);
        $('#pu_editar').val(nuevo_pu);
        // focus
        $('#pu_editar').focus();

        // Actualizar la etiqueta flotante (ejemplo para Bootstrap)
        if ($('#pu_editar').hasClass('is-invalid') || $('#pu_editar').hasClass('is-valid')) {
            $('#pu_editar').removeClass('is-invalid is-valid').addClass('is-valid');
        }else{
            $('#pu_editar').addClass('is-valid');
        }
    }
</script>





