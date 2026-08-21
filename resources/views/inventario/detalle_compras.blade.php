@extends('plantillas.app')
@section('titulo','Detalle de compras')
@section('style')
<style>
    /* Estilos Profesionales para Detalle de Compras */
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }

    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }

    .page-header h4 {
        margin: 0;
        font-weight: 700;
        font-size: 1.8rem;
    }

    .logo-header {
        max-width: 150px;
        filter: drop-shadow(0 4px 12px rgba(0,0,0,0.15));
        margin-bottom: 25px;
    }

    .search-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        border: none;
    }

    .search-card-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group label {
        font-weight: 600;
        color: #4a5568;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }

    .form-control {
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }

    .btn-modern {
        border-radius: 10px;
        padding: 10px 25px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-search {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-excel {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }

    .quick-search-card {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);
        color: white;
    }

    .quick-search-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 20px;
        color: white;
    }

    .quick-search-card .form-control {
        background: rgba(255, 255, 255, 0.95);
        border: none;
    }

    .quick-search-card .btn-modern {
        background: white;
        color: #f5576c;
    }

    .quick-search-card .btn-modern:hover {
        background: #f8f9fa;
    }

    .results-container {
        display: flex;
        gap: 20px;
        margin-top: 30px;
    }

    .results-list {
        flex: 1;
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        max-height: 600px;
        overflow-y: auto;
    }

    .results-list::-webkit-scrollbar {
        width: 8px;
    }

    .results-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .results-list::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 10px;
    }

    .results-list::-webkit-scrollbar-thumb:hover {
        background: #764ba2;
    }

    .results-detail {
        flex: 1;
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        min-height: 400px;
    }

    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: #a0aec0;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    .modal-content {
        border-radius: 15px;
        border: none;
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        border: none;
    }

    .modal-title {
        font-weight: 700;
    }

    .modal-header .close {
        color: white;
        opacity: 0.9;
        text-shadow: none;
    }

    .modal-header .close:hover {
        opacity: 1;
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
    @media (max-width: 992px) {
        .results-container {
            flex-direction: column;
        }

        .results-list,
        .results-detail {
            max-height: none;
        }
    }

    @media (max-width: 768px) {
        .page-header h4 {
            font-size: 1.4rem;
        }

        .search-card,
        .quick-search-card {
            padding: 20px;
        }
    }

</style>
@endsection
@section('javascript')
    <script>
        function listar_detalle_compras(){
            let fechainicial = $('#ifechainicial').val();
            let fechafinal = $('#ifechafinal').val();

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
        let url='{{url("/compras/damecomprasporfechas")}}'+'/'+fechainicial+'/'+fechafinal;

        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                console.log(resp);
                $('#listado_detalle_compras').html(resp);
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

        function enter_buscar_documento(){
            if(event.key === 'Enter'){
                cargar_documento();
            }
        }

        function cargar_detalle_num_doc(num_doc,tipodte){
            let url = '{{url("/ventas/damedetalleboleta_num_doc")}}'+"/"+tipodte+"/"+num_doc;
            console.log(url);
            $('#numero_dte').val(num_doc);
            $('#tipo_dte').val(tipodte);
            $.ajax({
                type:'GET',
                url: url,
                beforeSend: function(){
                    $('#detalle_boleta').html('<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>');
                },
                success: function(resp){

                    $('#detalle_boleta').html(resp);
                    // valor del input con id es_credito
                    let acredito = $('#es_credito').val();
                    // si acredito es 1 entonces el checkbox con id acredito se chequea
                    if(acredito == 1){
                        $('#acredito').prop('checked', true);
                    }else{
                        $('#acredito').prop('checked', false);
                    }
                },
                error: function(error){
                    console.log(error.responseText);
                }
            });
        }

        function cargar_detalle(num_boleta,tipodte){
            let url = '{{url("/ventas/damedetalleboleta")}}'+"/"+tipodte+"/"+num_boleta;
            $('#numero_dte').val(num_boleta);
            $('#tipo_dte').val(tipodte);
            $.ajax({
                type:'GET',
                url: url,
                success: function(resp){

                    $('#detalle_boleta').html(resp);
                    // valor del input con id es_credito
                    let acredito = $('#es_credito').val();
                    // si acredito es 1 entonces el checkbox con id acredito se chequea
                    if(acredito == 1){
                        $('#acredito').prop('checked', true);
                    }else{
                        $('#acredito').prop('checked', false);
                    }
                },
                error: function(error){
                    console.log(error.responseText);
                }
            });
        }

        function modificarDte(){
            let num_dte = $('#numero_dte').val();
            let tipo_dte = $('#tipo_dte').val();
            // verificar si el checkbox con id acredito esta chequeado
            let acredito = $('#acredito').is(':checked');
            // si acredito esta seleccionado entonces el valor de acredito es 1, sino es 0
            let valor_acredito = acredito ? 1 : 0;

            let url = '{{url("/ventas/modificarDte")}}'+"/"+valor_acredito+"/"+num_dte+"/"+tipo_dte;

            $.ajax({
                type:'GET',
                url: url,
                success: function(resp){
                    if(resp == 'OK'){
                        Vue.swal({
                            title: 'OK',
                            text: 'DTE modificado correctamente',
                            icon: 'success',
                            });
                            // esconder el modal
                            $('#modalModificarBoleta').modal('hide');
                            // esconder el div con id detalle_boleta
                            $('#detalle_boleta').html('');
                            listar_detalle_ventas();
                    }else{
                        Vue.swal({
                            title: 'ERROR',
                            text: resp,
                            icon: 'error',
                            });
                    }
                },
                error: function(error){
                    console.log(error.responseText);
                }
            });
        }

        function soloNumeros(e)
        {
            var key = window.Event ? e.which : e.keyCode
            return ((key >= 48 && key <= 57) || (key==8) || (key==75) || (key==107))
        }

        function cargar_documento()
        {
            // obtener el valor del input de tipo radio con nombre tipo_dte
            let tipo_dte = $('input[name="tipo_dte"]:checked').val();
            // obtener el valor del input con id num_documento
            let num_documento = $('#num_documento').val();
            // si el valor de num_documento es igual a 0 entonces mostrar mensaje de error
            if(num_documento == 0 || num_documento.trim().length == 0){
                Vue.swal({
                    title: 'ERROR',
                    text: 'Ingrese número de documento',
                    icon: 'error',
                    });
                return false;
            }
            // si el valor de num_documento es igual al valor del input con id num_documento_h entonces mostrar mensaje de error
            if(num_documento == $('#num_documento_h').val()){
                Vue.swal({
                    title: 'ERROR',
                    text: 'Ingrese número de documento diferente',
                    icon: 'error',
                    });
                return false;
            }

            console.log('tipo_dte: '+tipo_dte);
            console.log('num_documento: '+num_documento);

            cargar_detalle_num_doc(num_documento,tipo_dte);

        }

        function exportar_ventas_excel() {
            Vue.swal({
                title: 'Exportar Ventas a Excel',
                text: '¿Desea exportar ventas a Excel?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Sí',
                cancelButtonText: 'No',
            }).then((result) => {
                if (result.isConfirmed) {
                    confirmar_exportar_ventas_excel();
                }
            });
        }

        function confirmar_exportar_ventas_excel() {
            let fechainicial = $('#ifechainicial').val();
            let fechafinal = $('#ifechafinal').val();
            let tipodte = $('input[name="tipo_dte"]:checked').val().trim();

            if (fechainicial.trim().length == 0) {
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
            if (fechafinal.trim().length == 0) {
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
            if (fechainicial > fechafinal) {
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

            // Redirigir a la URL para descargar el archivo Excel
            let url = '{{ route("ventas.exportar_ventas_excel") }}' +
                      '?fechainicial=' + encodeURIComponent(fechainicial) +
                      '&fechafinal=' + encodeURIComponent(fechafinal) +
                      '&tipodte=' + encodeURIComponent(tipodte);

            window.location.href = url;
        }
    </script>
@endsection
@section('contenido')
    <div class="page-header fade-in">
        <h4><i class="fas fa-shopping-bag mr-3"></i>Detalle de Compras</h4>
    </div>

    <div class="container-fluid">
        {{-- <img src="{{asset('storage/imagenes/ips_logo.png')}}" alt="Logo IPS" class="logo-header fade-in"> --}}

        <div class="row">
            <!-- Sección de Búsqueda por Fechas -->
            <div class="col-lg-8 mb-4">
                <div class="search-card fade-in">
                    <h5 class="search-card-title">
                        <i class="fas fa-calendar-alt"></i>
                        Búsqueda por Período
                    </h5>
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ifechainicial">
                                    <i class="fas fa-calendar-day mr-2"></i>Fecha Inicial
                                </label>
                                <input type="date" name="ifechainicial" value='<?php echo date("Y-m-d"); ?>' id="ifechainicial" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ifechafinal">
                                    <i class="fas fa-calendar-day mr-2"></i>Fecha Final
                                </label>
                                <input type="date" name="ifechafinal" value='@php echo date("Y-m-d"); @endphp' id="ifechafinal" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <button class="btn btn-modern btn-search flex-fill" onclick="listar_detalle_compras()">
                                    <i class="fas fa-search mr-2"></i>Buscar
                                </button>
                                <button class="btn btn-modern btn-excel" onclick="exportar_compras_excel()">
                                    <i class="fas fa-file-excel mr-2"></i>Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de Búsqueda Rápida -->
            <div class="col-lg-4 mb-4">
                <div class="quick-search-card fade-in">
                    <h5 class="quick-search-title">
                        <i class="fas fa-bolt mr-2"></i>Búsqueda Rápida
                    </h5>
                    <div class="form-group">
                        <label for="num_documento">
                            <i class="fas fa-hashtag mr-2"></i>Número de Documento
                        </label>
                        <input type="text" id="num_documento" class="form-control" placeholder="Ej: 12345" maxlength="15" onkeyup="enter_buscar_documento()" onKeyPress="return soloNumeros(event)">
                        <input type="hidden" id="num_documento_h" value="0">
                        <input type="hidden" id="total_nc" value="0">
                        <small class="form-text text-white-50 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>Ingrese solo números
                        </small>
                    </div>
                    <button class="btn btn-modern btn-block" onclick="cargar_documento()">
                        <i class="fas fa-search mr-2"></i>Buscar Documento
                    </button>
                </div>
            </div>
        </div>

        <!-- Contenedor de Resultados -->
        <div class="results-container fade-in">
            <div class="results-list" id="listado_detalle_compras">
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h5>Realiza una búsqueda</h5>
                    <p>Selecciona un rango de fechas o ingresa un número de documento para ver los resultados</p>
                </div>
            </div>
            <div class="results-detail" id="detalle_compra">
                <div class="empty-state">
                    <i class="fas fa-file-invoice"></i>
                    <h5>Detalles de compra</h5>
                    <p>Selecciona una compra para ver sus detalles</p>
                </div>
            </div>
        </div>
    </div>
    <!-- DATOS DE VITAL IMPORTANCIA -->
    <input type="hidden" name="numero_dte" id="numero_dte">
    <input type="hidden" name="tipo_dte" id="tipo_dte">
    <!-- Modal Modificar DTE -->
    <div class="modal fade" id="modalModificarBoleta" tabindex="-1" role="dialog" aria-labelledby="modalModificarBoletaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalModificarBoletaLabel">
                        <i class="fas fa-edit mr-2"></i>Modificar DTE
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="acredito">
                            <i class="fas fa-credit-card mr-2"></i>¿Es crédito?
                        </label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="acredito" name="acredito">
                            <label class="custom-control-label" for="acredito">Activar crédito</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modern" style="background: #e2e8f0; color: #2d3748;" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cerrar
                    </button>
                    <button type="button" class="btn btn-modern btn-search" onclick="modificarDte()">
                        <i class="fas fa-save mr-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
