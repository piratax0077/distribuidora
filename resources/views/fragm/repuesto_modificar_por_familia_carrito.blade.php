<div class="card mt-2" style="width: 100%;">
    @if(isset($fotos))
    <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
              
              @foreach($fotos as $foto)
              <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                <img class="d-block w-100" src="{{asset('storage/'.$foto->urlfoto)}}" alt="Second slide">
              </div>
              @endforeach
              
        </div>
        <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="sr-only">Next</span>
        </a>
      </div>
      @else
      <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img class="d-block w-100" src="https://via.placeholder.com/150" alt="First slide">
          </div>
          
        </div>
        <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="sr-only">Next</span>
        </a>
      </div>
      @endif
    <div class="card-body">
      <h5 class="card-title">{{$repuesto->descripcion}}</h5>
      <table class="table">
        <thead>

        </thead>
        <tbody>
          <tr>
            <td>Codigo Interno</td>
            <td><p class="card-text">{{$repuesto->codigo_interno}}</p></td>
          </tr>
          <tr>
            <td>Codigo OEM</td>
            <td><p class="card-text">{{$repuesto->codigo_OEM_repuesto}}</p></td>
          </tr>
        
            {{-- <tr>
              <td>Codigo Proveedor</td>
              <td><p class="card-text">{{$repuesto->cod_repuesto_proveedor}}</p></td>
            </tr> --}}
            <tr>
              <td>Fabricantes</td>
              @if(count($fabs) > 0)
              <td>@foreach($fabs as $fab) <span>{{$fab->codigo_fab}} - {{$fab->marcarepuesto}}</span> <br> @endforeach</td>
              @else
              <td><p class="card-text">Sin fabricantes</p></td>
              @endif
            </tr>
            {{-- <tr>
              <td>Medidas</td>
              <td><p class="card-text">{{$repuesto->medidas}}</p></td>
            </tr> --}}
          <tr>
            <td>Precio venta</td>
            <td><p class="card-text">${{number_format($repuesto->precio_venta)}}</p></td>
          </tr>
          
          <tr>
            <td>Stock</td>
            <td><p class="card-text">{{$repuesto->stock_actual}}</p></td>
          </tr>
        </tbody>
        
      </table>
      
   
  </div>