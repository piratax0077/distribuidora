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
    <div class="container-fluid login-container">
        @php
            $valor_almacenado = session('usuario');
        @endphp

        <div class="container">
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-check-circle mr-2"></i></strong>{{ session('status') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-circle mr-2"></i></strong>{{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        </div>
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


        <div class="container">
            <div class="logo-section">
                <img src="{{ asset('storage/imagenes/logoips.png') }}" alt="Logo IPS">
                <h2 class="text-white mt-3 mb-0" style="font-weight: 700; text-shadow: 0 2px 8px rgba(0,0,0,0.2);">Sistema de Gestión</h2>
                <p class="text-white-50" style="font-size: 1.1rem;">Selecciona tu perfil para acceder</p>
            </div>

            <div class="row justify-content-center">
                @foreach ($users as $user)
                    @if ($user->activo == 1)
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="card user-card">
                            <div class="card-header user-card-header">
                                {{ $user->name }}
                            </div>
                            <div class="card-body user-card-body">
                                <div class="user-avatar-container">
                                    <a href="#" data-toggle="modal" data-target="#myModal{{ $user->id }}" title="Iniciar sesión como {{ $user->name }}">
                                        <img class="user-avatar" src="{{ url('avatar-user/' . $user->image_path) }}" alt="{{ $user->name }}">
                                    </a>
                                </div>
                                <div>
                                    <span class="user-role-badge">
                                        <i class="fas fa-user-tag mr-1"></i>{{ $user->rol->nombrerol }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>


                        <div class="modal fade login-modal" id="myModal{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel{{ $user->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <div class="d-flex align-items-center w-100">
                                            <img src="{{ asset('storage/imagenes/logoips.png') }}" class="logoHeader mr-3" alt="Logo">
                                            <div>
                                                <h5 class="modal-title mb-0" id="loginModalLabel{{ $user->id }}">Bienvenido</h5>
                                                <small class="text-white-50">{{ $user->name }}</small>
                                            </div>
                                        </div>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="{{ route('login') }}" autocomplete="off">
                                            @csrf

                                            <div class="form-group">
                                                <label for="email{{ $user->id }}">
                                                    <i class="fas fa-envelope mr-2"></i>Correo Electrónico
                                                </label>
                                                <input type="email" id="email{{ $user->id }}" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $user->email }}" required autocomplete="email" readonly>
                                                @error('email')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="password{{ $user->id }}">
                                                    <i class="fas fa-lock mr-2"></i>Contraseña
                                                </label>
                                                <input type="password" id="password{{ $user->id }}" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Ingrese su contraseña">
                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="remember" id="remember{{ $user->id }}" {!! old('remember') ? 'checked' : '' !!}>
                                                    <label class="form-check-label" for="remember{{ $user->id }}">
                                                        Recordar mi sesión
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="form-group mb-0">
                                                <button type="submit" class="btn btn-primary btn-block">
                                                    <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                                                </button>
                                            </div>

                                            @if (Route::has('password.request'))
                                                <div class="text-center mt-3">
                                                    <a class="btn-link" href="{{ route('password.request') }}">
                                                        <i class="fas fa-key mr-1"></i>¿Olvidó su contraseña?
                                                    </a>
                                                </div>
                                            @endif
                                        </form>

                                    </div>
                                    <div class="modal-footer">

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
