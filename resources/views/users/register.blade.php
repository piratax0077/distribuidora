@extends('plantillas.app')
@section('titulo','Registrar usuario')
@section('contenido')

<div class="container">
    
    <div class="row justify-content-center mt-5">
        
        <div class="col-md-8">
            @if(session('error'))
                <div class="alert alert-danger">{{session('error')}}</div>
            @endif
            <div class="card">
                <div class="card-header">{{ __('Registro') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('user.save') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group floating">
                                    <input id="name" type="text" class="form-control floating @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                    <label for="name" class="floating">Nombre</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group floating">
                                    <input id="rut" type="text" class="form-control floating @error('rut') is-invalid @enderror" name="rut" value="{{ old('rut') }}" required autocomplete="rut" autofocus>
                                    <label for="rut" class="floating">Rut</label>
                                </div>
                            </div>
                        </div>
                             
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group floating">
                                    <input id="telefono" type="text" class="form-control floating @error('telefono') is-invalid @enderror" name="telefono" value="{{ old('telefono') }}" required autocomplete="telefono" autofocus>
                                    <label for="telefono" class="floating">Telefono</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group floating">
                                    <input id="email" type="email" class="form-control floating @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                                    <label for="email" class="floating">Email</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group floating">
                                    <input id="password" type="password" class="form-control floating @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                    <label for="password" class="floating">Password</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group floating">
                                    <input id="password-confirm" type="password" class="form-control floating" name="password_confirmation" required autocomplete="new-password">
                                    <label for="password-confirm" class="floating">Confirmar Password</label>
                                </div>
                            </div>
                        </div>
                        

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group floating">
                                    <input id="image_path" type="file" class="form-control floating @error('image_path') is-invalid @enderror" name="image_path" value="{{ old('image_path') }}" required autocomplete="image_path" autofocus>
                                    <label for="image_path" class="floating">Imagen</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group floating">
                                    <select name="rol" id="rol" class="form-control floating">
                                        <option value="0">Seleccione</option>
                                        @foreach($roles as $rol)
                                            <option value="{{$rol->id}}">{{$rol->nombrerol}}</option>
                                        @endforeach
                                    </select>
                                    <label for="rol" class="floating">Rol</label>
                                </div>
                            </div>
                        </div>

                        
                        
                        

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-outline-success btn-sm" style="border-radius: 100px;">
                                <i class="fa fa-save"></i>
                            </button>
                            <a href="/usuarios" class="btn btn-outline-danger btn-sm" style="border-radius: 100px;">X</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection