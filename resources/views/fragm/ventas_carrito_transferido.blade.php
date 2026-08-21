
<script>
	$('#monto-1').val({{$total_neto}});
	$('#total_venta_vuelto').val({{$total_neto}});
</script>
@if(isset($oferta) && $oferta == true)
<input type="hidden" name="es_oferta" id="es_oferta" value="1">
@else
<input type="hidden" name="es_oferta" id="es_oferta" value="0">
@endif
<input type="hidden" name="es_transferido" id="es_transferido" value="1">
<input type="hidden" name="confirmado" id="confirmado" value="">
<div class="row">
	<div class="col-sm-12">
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
	    @foreach ($carrito_transferido as $item)
	    <tr>

			<td class="alin-cen"><span style="font-weight: bold;">{{$item->codigo_interno}}</span> </td>
	      <td>{{$item->descripcion}}</td>
		  <td>{{$item->marca}}</td>
	      <td class="alin-cen">{{$item->cantidad}}</td>
		  @if(isset($oferta) && $oferta === true && $item->oferta == 1)
		  <td class="d-flex justify-content-between"><a href="javascript:void(0)" onclick="abrirModalPrecio({{$item->id_repuestos}})"><span class="badge badge-danger">Oferta</span> </a>{{number_format($item->pu,0,',','.')}}</td>
		  {{-- @elseif($item->oferta == 2)
		  <td class="d-flex justify-content-between"><a href="javascript:void(0)" onclick="abrirModalPrecio({{$item->id_repuestos}})"><span class="badge badge-primary">Oferta</span> </a>{{number_format($item->pu,0,',','.')}}</td>	 --}}
		  @else
				<td class="alin-der justify-content-between">{{number_format($item->pu,0,',','.')}}</td>
			@endif
            <td class="alin-der">{{$item->local_nombre}}</td>
	      <td class="alin-der">{{number_format($item->subtotal_item,0,',','.')}}</td>
          <td class="alin-der">{{number_format($item->descuento_item,0,',','.')}}</td>

          <td class="alin-der">{{number_format($item->total_item,0,',','.')}}</td>
      <!--
		  <td class="alin-cen"><a href="javascript:void(0);" onclick="ver_relacionados({{$item->id_repuestos}})"><abbr title="Repuestos Relacionados">R</abbr></a> </td>
        -->
        <td><button class="btn btn-outline-danger btn-sm" onclick="abrir_modal_editar_item({{ $item->cantidad }},{{ $item->pu }},{{ $item->subtotal_item > 0 ? ($item->descuento_item * 100 / $item->subtotal_item) : 0 }},{{ $item->id }},{{ json_encode($item->codigo_interno) }})"><i class="fas fa-edit"></i></button></td>
          <td class="alin-cen"><a href="javascript:void(0);" onclick="borrar_item_carrito_transferido({{$item->id}},{{ $item->codigo_interno }})"><abbr title="Borrar Item"><b style="color:red">X</b></abbr></span></a></td>
	    	<!-- Input para guardar el vendedor -->
			<input type="hidden" name="vendedor_id" id="vendedor_id" value="{{$item->usuarios_id}}">
			<input type="hidden" name="cliente_id" id="cliente_id" value="{{$item->cliente_id}}">
			<input type="hidden" name="carrito_id" id="carrito_id" value="{{$item->id}}">
		</tr>
	    @endforeach
		</tbody>
	  </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-sm-8">
        <input type="hidden" id="items_carrito" value="{{$carrito_transferido->count()}}">
        <input type="hidden" id="total_carrito" value="{{$total_neto}}">
        Total Items: {{$carrito_transferido->count()}}
    </div>
    <div class="col-sm-4" style="color:blue"><b>Total Neto: {{ number_format($total,0,',','.') }}</b><br><b>Total Venta: {{number_format($total_neto,0,',','.')}}</b></div>
</div>

<!-- modalEditarItemCarritoTransferido -->
<div class="modal fade" id="modalEditarItemCarritoTransferido" tabindex="-1" role="dialog" aria-labelledby="modalEditarItemCarritoTransferidoLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-editar-item-dialog" role="document">
		<div class="modal-content modal-editar-item-content">

				<div class="modal-header modal-editar-item-header">
					<div>
						<div class="modal-editar-item-eyebrow">Carrito transferido</div>
						<h5 class="modal-title mb-0" id="modalEditarItemCarritoTransferidoLabel">Editar ítem</h5>
					</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<input type="hidden" name="id" id="id">
				<div class="modal-body">
					<p class="text-muted small mb-3">Actualice la cantidad o el precio unitario. Puede calcular el precio con o sin IVA.</p>
					<div class="form-row">
						<div class="form-group col-md-4">
							<label for="cantidad_editar" class="modal-editar-label"><i class="fas fa-boxes"></i> Cantidad</label>
							<input type="number" min="1" class="form-control" id="cantidad_editar" name="cantidad_editar" required>
                        </div>
						<div class="form-group col-md-4">
							<label for="pu_editar" class="modal-editar-label"><i class="fas fa-dollar-sign"></i> Precio unitario</label>
                            <input type="text" class="form-control" id="pu_editar" name="pu_editar" required>
						</div>
						<div class="form-group col-md-4">
							<label for="descuento_editar" class="modal-editar-label"><i class="fas fa-percent"></i> Descuento</label>
							<input type="number" min="0" class="form-control" id="descuento_editar" name="descuento_editar" placeholder="0">
                        </div>
					</div>

                    <div class="modal-editar-iva">
						<div>
							<strong>Ajuste rápido de IVA</strong>
							<small class="d-block text-muted">Se aplica sobre el precio unitario.</small>
						</div>
						<div class="btn-group btn-group-sm" role="group" aria-label="Ajustar IVA">
							<button type="button" class="btn btn-outline-success" onclick="agregar_iva()">+ IVA</button>
							<button type="button" class="btn btn-outline-primary" onclick="quitar_iva()">− IVA</button>
						</div>
                    </div>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
					<button type="button" id="btn_guardar_edicion_item" class="btn btn-primary" onclick="guardar_edicion_item()"><i class="fas fa-save mr-1"></i> Guardar cambios</button>
				</div>
		</div>
	</div>
</div>
<!-- /modalEditarItemCarritoTransferido -->

<script>
	function abrir_modal_editar_item(cant, pu, descuento, id,codigo_interno){
		$('#modalEditarItemCarritoTransferido').modal('show');
		$('#modalEditarItemCarritoTransferido').find('.modal-title').text('Editar ítem · '+codigo_interno);
		$('#modalEditarItemCarritoTransferido').find('#cantidad_editar').val(cant);
		$('#modalEditarItemCarritoTransferido').find('#pu_editar').val(pu);
		$('#modalEditarItemCarritoTransferido').find('#descuento_editar').val(descuento || 0);
		$('#modalEditarItemCarritoTransferido').find('#id').val(id);

        $('#pu_editar').focus();
	}

	function guardar_edicion_item(){
		var id = $('#id').val();
		var cantidad = $('#cantidad_editar').val();
		var pu = $('#pu_editar').val();
		var descuento = $('#descuento_editar').val();
        // saber que tipo de documento esta checkeado
        var tipo_documento = $('input[name="tipo_documento"]:checked').val();
		$.ajax({
			url: "{{route('editar_item_carrito_transferido')}}",
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
				// El modal vive dentro de #notas_ventas. Hay que esperar a que
				// Bootstrap quite su backdrop antes de reemplazar el contenido.
				$('#modalEditarItemCarritoTransferido')
					.one('hidden.bs.modal', function(){
						$('#notas_ventas').html(response.vista);
					})
					.modal('hide');
			},
			error: function(error){
				console.log(error);
			},
			complete: function(){
				$('#btn_guardar_edicion_item')
					.prop('disabled', false)
					.html('<i class="fas fa-save mr-1"></i> Guardar cambios');
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


