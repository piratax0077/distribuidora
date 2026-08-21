@extends('plantillas.newlogin')

@section('contenido')
    <script type="text/javascript">
        var permiso;

        if (window.localStorage.getItem('token') !== undefined && window.localStorage.getItem('token')) {
            console.log('Ingresando al sistema ...');

        } else {
            window.location.href = "/";
            localStorage.removeItem('token');
            localStorage.removeItem('user');
        }

        function habilitar_usuarios() {
            let opt = confirm('¿Quiere habilitar a los usuarios para que accedan al servidor?');
            if (opt) {
                let url = '/habilitar_usuarios';
                $.ajax({
                    type: 'get',
                    url: url,
                    success: function(resp) {
                        console.log(resp);

                        if (resp === 'OK') {

                            alert('usuarios habilitados');
                            // Simulate a mouse click:
                            window.location.href = "/";
                        } else {
                            alert('algo ha ocurrido');
                        }
                    },
                    error: function(err) {
                        console.log(err);
                    }
                });
            }

        }

        function deshabilitar_usuarios() {
            let opt = confirm('¿Quiere deshabilitar a los usuarios para que no puedan acceder al servidor?');
            if (opt) {
                let url = '/deshabilitar_usuarios';
                $.ajax({
                    type: 'get',
                    url: url,
                    success: function(resp) {
                        console.log(resp);

                        if (resp === 'OK') {
                            alert('usuarios deshabilitados');
                            window.location.href = "/";
                        } else {
                            alert('algo ha ocurrido');
                        }
                    },
                    error: function(err) {
                        console.log(err);
                    }
                })
            }

        }

        function habilitar_jose() {
            let opt = confirm('¿Quiere habilitar a Jose Troncoso a acceder al servidor?');
            if (opt) {
                let url = '/habilitar_jose';
                $.ajax({
                    type: 'get',
                    url: url,
                    success: function(resp) {

                        if (resp === 'OK') {
                            alert('jose habilitado');
                            window.location.href = "/";
                        } else {
                            alert('algo ha ocurrido');
                        }
                    },
                    error: function(err) {
                        console.log(err);
                    }
                })
            }

        }

        function deshabilitar_jose() {
            let opt = confirm('¿Quiere quitar el permiso a José Troncoso a acceder al servidor?');
            if (opt) {
                let url = '/deshabilitar_jose';
                $.ajax({
                    type: 'get',
                    url: url,
                    success: function(resp) {

                        if (resp === 'OK') {
                            alert('jose deshabilitado');
                            window.location.href = "/";
                        } else {
                            alert('algo ha ocurrido');
                        }
                    },
                    error: function(err) {
                        console.log(err);
                    }
                })
            }

        }
    </script>
    <div class="container-fluid">
        @php
            $valor_almacenado = session('usuario');
        @endphp
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show">
                <p> {{ session('status') }}</p>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <p> {{ session('error') }}</p>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        {{-- <div class="row">
        @foreach ($users as $user)
            @if ($user->email === 'admin@ips.cl')
                <div class="list-user">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header"></div>
                            <div class="card-body">
                                
                                    <a href="#" data-toggle="modal" data-target="#myModal{{ $user->id }}" class="enlacePerfil"
                                        title="{{ $user->name }}">
                                        <img class="card-img-top imagePerfil" src="{{ asset('storage/imagenes/logoips.png') }}"
                                            alt="Card image cap">
                                    </a>
                                
                            </div>
                            <div class="card-footer"></div>
                        </div>
                    </div>
                </div>
                
                <div class="modal fade" id="myModal{{ $user->id }}" role="dialog"
                    aria-labelledby="myModal{{ $user->id }}">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel"><img
                                        src="{{ asset('storage/imagenes/logoips.png') }}" class="logoHeader"
                                        alt=""></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="{{ route('login') }}" autocomplete="off">
                                    @csrf

                                    <div class="form-group row">
                                        <label for="email"
                                            class="col-md-4 col-form-label text-md-right">{{ __('E-Mail') }}</label>

                                        <div class="col-md-6">
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                name="email" value="{{ $user->email }}" required autocomplete="email"
                                                style="pointer-events: none" autofocus>

                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="password"
                                            class="col-md-4 col-form-label text-md-right">{{ __('Clave') }}</label>

                                        <div class="col-md-6">
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror" name="password"
                                                required autocomplete="current-password">

                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-6 offset-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remember"
                                                    id="remember" {!! old('remember') ? 'checked' : '' !!}>

                                                <label class="form-check-label" for="remember">
                                                    {{ __('Recordar') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row mb-0">
                                        <div class="col-md-8 offset-md-4">
                                            <button type="submit" class="btn btn-primary">
                                                {{ __('Ingresar') }}
                                            </button>

                                            @if (Route::has('password.request'))
                                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                                    {{ __('Olvidó su clave?') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </form>

                            </div>
                            <div class="modal-footer">

                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
        </div> --}}


        <div class="list-user all-user">
            <div class="row">
                <div class="col-12">
                    <img src="{{ asset('storage/imagenes/logoips.png') }}" alt="logo" style="width: 150px;" class="mb-3">
                </div>
            </div>
            <div class="row">
                @foreach ($users as $user)
                    @if ($user->activo == 1)
                    <div class="col-md-3 mb-2">
                        <div class="card">
                            <div class="card-header" style="background:  #f0ae3e;">
                                {{ $user->name }}
                            </div>
                            <div class="card-body" style="background: rgb(52 46 46)">
                                <a href="#" data-toggle="modal" data-target="#myModal{{ $user->id }}" class="enlacePerfil"
                                    title="{{ $user->name }}">
                                    <img class="card-img-top imagePerfil" src="{{ url('avatar-user/' . $user->image_path) }}"
                                        alt="Card image cap">
                                        
                                </a>
                                <p class="text-light">{{ $user->rol->nombrerol }}</p>
                            </div>
                            <div class="card-footer"></div>
                        </div>
                    </div>
                        

                        <div class="modal fade" id="myModal{{ $user->id }}" role="dialog"
                            aria-labelledby="myModal{{ $user->id }}">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header" style="background: #eee">
                                        <h5 class="modal-title" id="exampleModalLabel"><img
                                                src="{{ asset('storage/imagenes/logoips.png') }}" class="logoHeader"
                                                alt=""></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="{{ route('login') }}" autocomplete="off">
                                            @csrf

                                            <div class="form-group row">
                                                <label for="email"
                                                    class="col-md-4 col-form-label text-md-right">{{ __('E-Mail') }}</label>

                                                <div class="col-md-6">
                                                    <input type="email"
                                                        class="form-control @error('email') is-invalid @enderror"
                                                        name="email" value="{{ $user->email }}" required
                                                        autocomplete="email" style="pointer-events: none" autofocus>

                                                    @error('email')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="password"
                                                    class="col-md-4 col-form-label text-md-right">{{ __('Clave') }}</label>

                                                <div class="col-md-6">
                                                    <input type="password"
                                                        class="form-control @error('password') is-invalid @enderror"
                                                        name="password" required autocomplete="current-password">

                                                    @error('password')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-6 offset-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="remember"
                                                            {!! old('remember') ? 'checked' : '' !!}>

                                                        <label class="form-check-label" for="remember">
                                                            {{ __('Recordar') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row mb-0">
                                                <div class="col-md-8 offset-md-4">
                                                    <button type="submit" class="btn btn-primary">
                                                        {{ __('Ingresar') }}
                                                    </button>

                                                    @if (Route::has('password.request'))
                                                        <a class="btn btn-link" href="{{ route('password.request') }}">
                                                            {{ __('Olvidó su clave?') }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                    <div class="modal-footer" style="background: #eee">

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            
        </div>

        {{-- @if ($valor_almacenado === 'admin')
            <div class="text-center">
                <button class="btn btn-warning btn-sm" onclick="habilitar_usuarios()">Habilitar</button>
                <button class="btn btn-danger btn-sm" onclick="deshabilitar_usuarios()">Deshablitar</button>
            </div>
        @endif --}}

        {{-- <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                    <div class="card-header">{{ __('Acceso') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}" autocomplete="off">
                        @csrf

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Clave') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {!! old('remember') ? 'checked' : '' !!}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Recordar') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Ingresar') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Olvidó su clave?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> --}}
    </div>
@endsection
