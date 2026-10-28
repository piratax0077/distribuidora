<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>



    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="{{asset('storage/imagenes/favicon1.png')}}" type="image/png" />
    <!-- Styles  -->
    @php
        $entorno = App::environment();
        /*
            08dic2020
            Modifiqué el archivo Mix.php linea 46 para que pueda encontrar el manifiesto del cambio de versionado para app.js y app.css
            en /repuestos/vender/laravel/framework/src/illuminate/Foundation/Mix.php
        */
        if($entorno=='local'){
            echo "<link href=\"".asset('css/app.css')."\" rel='stylesheet'>";
            echo "<link href=\"".asset('css/dev.css')."\" rel='stylesheet'>";
        }elseif($entorno=='production'){
            echo "<link href=\"".mix('app.css','dist')."\" rel='stylesheet'>";
            echo "<link href=\"".mix('dev.css','dist')."\" rel='stylesheet'>";
        }
    @endphp
   <style>
       :root {
            --ips-blue: #356b8d;
            --ips-blue-dark: #274c66;
            --ips-blue-light: #dce7ed;
            --ips-slate: #566671;
            --ips-surface: #f3f6f7;
            --ips-border: #d7e0e5;
       }

       /* Estilos Base */
       body {
            background: #fff;
            background-repeat: no-repeat;
            background-position: top;
            font-family: 'Nunito', sans-serif;
       }

       /* Estilos Profesionales para Login */
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #203e52 0%, #356b8d 58%, #496f84 100%);
            padding: 40px 20px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 0.8s ease;
        }

        .logo-section img {
            max-width: 180px;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.22));
            transition: transform 0.3s ease;
        }

        .logo-section img:hover {
            transform: scale(1.02);
        }

        .user-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255,255,255,.28);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(15, 36, 49, 0.18);
            background: white;
            margin-bottom: 24px;
            animation: fadeInUp 0.6s ease;
            animation-fill-mode: both;
        }

        .user-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(15, 36, 49, 0.24);
        }

        .user-card-header {
            background: #315d78;
            color: white;
            padding: 16px;
            font-weight: 600;
            font-size: 1.1rem;
            text-align: center;
            border-bottom: 1px solid #284e65;
        }

        .user-card-body {
            background: #f4f7f8;
            padding: 24px;
            text-align: center;
        }

        .user-avatar-container {
            position: relative;
            display: inline-block;
            margin-bottom: 12px;
        }

        .user-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #dce7ed;
            object-fit: cover;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(39, 76, 102, 0.18);
        }

        .user-avatar:hover {
            transform: scale(1.04);
            border-color: var(--ips-blue);
        }

        .user-role-badge {
            background: var(--ips-blue-light);
            color: var(--ips-blue-dark);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            box-shadow: none;
        }

        .login-modal .modal-content {
            border: 1px solid var(--ips-border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 18px 45px rgba(15, 36, 49, 0.28);
        }

        .login-modal .modal-header {
            background: linear-gradient(135deg, var(--ips-blue-dark) 0%, var(--ips-blue) 100%) !important;
            color: white;
            border-bottom: none;
            padding: 24px;
        }

        .login-modal .modal-header .logoHeader {
            max-width: 120px;
            filter: brightness(0) invert(1);
        }

        .login-modal .modal-body {
            padding: 40px;
            background: var(--ips-surface);
        }

        .login-modal .modal-footer {
            background: var(--ips-surface);
            border-top: none;
            padding: 20px;
        }

        .login-modal .modal-title {
            color: white;
            font-weight: 700;
        }

        .login-modal .close {
            color: white;
            opacity: 0.9;
            text-shadow: none;
        }

        .login-modal .close:hover {
            opacity: 1;
        }

        .form-group label {
            font-weight: 600;
            color: #495057;
            font-size: 0.95rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--ips-blue);
            box-shadow: 0 0 0 0.2rem rgba(53, 107, 141, 0.15);
        }

        .form-control:read-only {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .btn-primary {
            background: var(--ips-blue);
            border: none;
            border-radius: 10px;
            padding: 12px 32px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(39, 76, 102, 0.22);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(39, 76, 102, 0.3);
            background: var(--ips-blue-dark);
        }

        .btn-link {
            color: var(--ips-blue);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-link:hover {
            color: var(--ips-blue-dark);
            text-decoration: none;
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            animation: slideInDown 0.5s ease;
        }

        .alert-success {
            background: #e4efe9;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: #f3e4e4;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Animación escalonada para las cards */
        .user-card:nth-child(1) { animation-delay: 0.1s; }
        .user-card:nth-child(2) { animation-delay: 0.2s; }
        .user-card:nth-child(3) { animation-delay: 0.3s; }
        .user-card:nth-child(4) { animation-delay: 0.4s; }
        .user-card:nth-child(5) { animation-delay: 0.5s; }
        .user-card:nth-child(6) { animation-delay: 0.6s; }

        /* Checkbox personalizado */
        .form-check-input:checked {
            background-color: var(--ips-blue);
            border-color: var(--ips-blue);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .user-card {
                margin-bottom: 16px;
            }

            .logo-section img {
                max-width: 140px;
            }

            .login-modal .modal-body {
                padding: 24px;
            }

            .user-avatar {
                width: 100px;
                height: 100px;
            }

            .login-container {
                padding: 20px 10px;
            }
        }

        @media (max-width: 576px) {
            .user-card-header {
                font-size: 1rem;
                padding: 12px;
            }

            .user-avatar {
                width: 90px;
                height: 90px;
            }

            .btn-primary {
                padding: 10px 24px;
            }
        }
   </style>

</head>
<body>

    <div id="app">

            @yield('contenido')

    </div>
     <!-- Scripts  -->
     @php
        if($entorno=='local'){
            echo "<script src=\"".asset('js/app.js')."\" defer></script>";
        }elseif($entorno=='production'){
            echo "<script src=\"".mix('app.js','dist')."\" defer></script>";
        }
    @endphp
</body>
</html>
