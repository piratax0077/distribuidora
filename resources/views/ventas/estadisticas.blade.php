@extends('plantillas.app')

@section('titulo','Estadisticas')
@section('contenido_titulo_pagina')
  @include('fragm.breadcrumb',['titulo'=>'Estadisticas','titulo_modulo'=>'Ventas'])
@endsection

@section('javascript')
<script>
    function mostrar(){
        let fechainicial = document.getElementById("ifechainicial").value;
        let fechafinal = document.getElementById("ifechafinal").value;
        let horarioinicial = document.getElementById("ihorarioinicial").value;
        let horariofinal = document.getElementById("ihorariofinal").value;
        if(fechainicial.trim().length==0)
        {
            Vue.swal({
                text: 'Seleccione Fecha Inicial',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }
        if(fechafinal.trim().length==0)
        {
            Vue.swal({
                text: 'Seleccione Fecha Final',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }

        // if(horarioinicial.trim().length==0)
        // {
        //     Vue.swal({
        //         text: 'Seleccione Horario Inicial',
        //         position: 'top-end',
        //         icon: 'warning',
        //         toast: true,
        //         showConfirmButton: false,
        //         timer: 3000,
        //     });
        //     return false;
        // }

        // if(horariofinal.trim().length==0)
        // {
        //     Vue.swal({
        //         text: 'Seleccione Horario Final',
        //         position: 'top-end',
        //         icon: 'warning',
        //         toast: true,
        //         showConfirmButton: false,
        //         timer: 3000,
        //     });
        //     return false;
        // }

        // validar el horario

        if(fechainicial>fechafinal){
            Vue.swal({
                text: 'Fecha Inicial debe ser menor o igual a Fecha Final',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            return false;
        }
            let url='{{url("/ventas/estadisticas_resumen")}}'+'/'+fechainicial+"&"+fechafinal+"&"+horarioinicial+"&"+horariofinal;
            $.ajax({
                type:'GET',
                url:url,
                beforeSend: function(){
                    Vue.swal({
                        icon:'info',
                        text:'Cargando'
                    });
                },
                success:function(resp){
                    console.log(resp);
                    Vue.swal.close();
                    $('#resumen').html(resp);

                },
                error: function(error){
                    Vue.swal({
                        title: 'ERROR',
                        text: error.responseText,
                        icon: 'error',
                    });
                }
            });
        }
      </script>
@endsection
@section('style')
<style>
    /* Estilos Profesionales para Estadísticas */
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }

    .page-container {
        padding: 20px 0;
    }

    .filters-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        border: none;
    }

    .filters-card-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group.floating {
        position: relative;
        margin-bottom: 20px;
    }

    .form-group.floating label {
        font-weight: 600;
        color: #4a5568;
        font-size: 0.9rem;
        margin-bottom: 8px;
        display: block;
    }

    .form-group.floating .form-control {
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        padding: 12px 15px;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .form-group.floating .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }

    .btn-modern {
        border-radius: 10px;
        padding: 12px 30px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        width: 100%;
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-show {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .results-container {
        margin-top: 30px;
    }

    .results-panel {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        min-height: 400px;
        width: 100%;
    }

    .panel-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 3px solid #667eea;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #a0aec0;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    .empty-state h5 {
        font-weight: 600;
        margin-bottom: 10px;
    }

    .transbank {
        color: #718096;
        font-style: italic;
    }

    /* Stats Cards dentro del resumen */
    .stat-card {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        border-left: 4px solid #667eea;
    }

    .stat-card-value {
        font-size: 2rem;
        font-weight: 700;
        color: #667eea;
    }

    .stat-card-label {
        font-size: 0.9rem;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
    }

    /* Animaciones */
    .fade-in {
        animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .filters-card {
            padding: 20px;
        }

        .results-panel {
            padding: 20px;
        }

        .filters-card-title {
            font-size: 1.1rem;
        }
    }

</style>
@endsection
@section('contenido_ingresa_datos')

<div class="page-container">
    @php
        $año_actual=date("Y");
    @endphp

    <!-- Card de Filtros -->
    <div class="filters-card fade-in">
        <h5 class="filters-card-title">
            <i class="fas fa-filter"></i>
            Filtros de Búsqueda
        </h5>
        <div class="row align-items-end">
            <div class="col-lg-3 col-md-6">
                <div class="form-group floating">
                    <label for="ifechainicial">
                        <i class="fas fa-calendar-day mr-2"></i>Fecha Inicial
                    </label>
                    <input type="date" name="ifechainicial" value='<?php echo date("Y-m-d"); ?>' id="ifechainicial" class="form-control">
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="form-group floating">
                    <label for="ifechafinal">
                        <i class="fas fa-calendar-day mr-2"></i>Fecha Final
                    </label>
                    <input type="date" name="ifechafinal" value='<?php echo date("Y-m-d"); ?>' id="ifechafinal" class="form-control">
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="form-group floating">
                    <label for="ihorarioinicial">
                        <i class="fas fa-clock mr-2"></i>Hora Inicial
                    </label>
                    <input type="time" class="form-control" id="ihorarioinicial" value="08:00" />
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="form-group floating">
                    <label for="ihorariofinal">
                        <i class="fas fa-clock mr-2"></i>Hora Final
                    </label>
                    <input type="time" class="form-control" id="ihorariofinal" value="20:00" />
                </div>
            </div>
            <div class="col-lg-2 col-md-12">
                <button class="btn btn-modern btn-show" onclick="mostrar()">
                    <i class="fas fa-chart-bar mr-2"></i>Generar Estadísticas
                </button>
            </div>
        </div>
    </div>

    <!-- Contenedor de Resultados -->
    <div class="results-container fade-in">
        <div class="results-panel" id="resumen">
            <h5 class="panel-title">
                <i class="fas fa-chart-bar"></i>
                Estadísticas y Gráficos
            </h5>
            <div class="empty-state">
                <i class="fas fa-chart-line"></i>
                <h5>Sin datos para mostrar</h5>
                <p>Selecciona un período y haz clic en "Generar Estadísticas" para ver los gráficos y resultados</p>
            </div>
        </div>
    </div>
</div>


@endsection
