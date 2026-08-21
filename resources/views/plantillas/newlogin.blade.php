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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 0.8s ease;
        }

        .logo-section img {
            max-width: 180px;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.15));
            transition: transform 0.3s ease;
        }

        .logo-section img:hover {
            transform: scale(1.05);
        }

        .user-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            background: white;
            margin-bottom: 24px;
            animation: fadeInUp 0.6s ease;
            animation-fill-mode: both;
        }

        .user-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        }

        .user-card-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 16px;
            font-weight: 600;
            font-size: 1.1rem;
            text-align: center;
            border-bottom: none;
        }

        .user-card-body {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
            border: 4px solid white;
            object-fit: cover;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .user-avatar:hover {
            transform: scale(1.1);
            border-color: #f5576c;
        }

        .user-role-badge {
            background: rgba(255, 255, 255, 0.95);
            color: #667eea;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .login-modal .modal-content {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .login-modal .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
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
            background: #f8f9fa;
        }

        .login-modal .modal-footer {
            background: #f8f9fa;
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
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .form-control:read-only {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 32px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        .btn-link {
            color: #667eea;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-link:hover {
            color: #764ba2;
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
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
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
            background-color: #667eea;
            border-color: #667eea;
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
