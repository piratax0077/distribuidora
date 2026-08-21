<table class="table">
    <thead>
      <tr>
        <th scope="col">Cod Int</th>
        <th scope="col">Fecha</th>
        <th scope="col">Stock</th>
        <th scope="col">Estado</th>
        <th scope="col"></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($repuestos as $r)
        @php
            if($r->estado == 'Pedido'){
                $clase = 'bg-success text-white';
            }elseif($r->estado == 'Eliminado del catálogo'){
                $clase = 'bg-light'; // temporal
            }else{
                $clase = 'bg-light';
            }
        @endphp
        <tr class="{{$clase}}">
            <td>{{$r->codigo_interno}}</td>
            <td>{{$r->fecha_emision}}</td>
            <td>{{$r->stock_actual}}</td>
            <td>{{$r->estado}}</td>
            <td><a class="btn btn-secondary btn-sm" href="javascript:void(0)" onclick="damerepuesto({{$r->id_repuesto}})" data-target="#modalNuevoStockMinimo" data-toggle="modal"><i class="fa-solid fa-arrows-rotate"></i></a></td>
            @if($r->estado == 'Pedido')<td><a href="javascript:void(0)" onclick="detalle_pedido({{$r->id_repuesto}})" class="btn btn-warning btn-sm">D</a></td>@endif
            @if($r->estado == 'Eliminado del catálogo')<td><a href="javascript:void(0)" onclick="detalle_pedido({{$r->id_repuesto}})" class="btn btn-warning btn-sm">D</a></td>@endif
        </tr>
        @endforeach
    </tbody>
  </table>
