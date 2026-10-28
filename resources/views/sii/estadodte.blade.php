@extends('plantillas.app')
@section('titulo','Estado DTE')
@section('style')
<style>
    .contenedor{ padding: 16px; }
    .estado-dte-panel{
        background: #fff;
        border: 1px solid #dfe5eb;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        padding: 18px;
        margin-bottom: 14px;
    }
    .estado-dte-panel h5{ margin-bottom: 4px; color: #274c66; }
    .estado-dte-ayuda{ color: #657786; margin-bottom: 14px; }
    #tipo_dte{
        display: flex;
        flex-wrap: wrap;
        gap: 8px 18px;
        padding: 12px;
        border: 1px solid #e4e8ec;
        border-radius: 6px;
        background: #f8fafc;
    }
    #tipo_dte label{ margin: 0 0 0 4px; cursor: pointer; }
    #listado_dte{
        background-color:floralwhite;
        overflow-x: auto;
        border-radius: 6px;
    }
    #revision_sii{
        display: none;
        margin: 0 0 14px;
        padding: 12px 16px;
        border-left-width: 5px;
        font-size: 15px;
    }
    .estado-dte-leyenda{
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }
    .estado-dte-leyenda span{ border-radius: 12px; padding: 3px 10px; font-size: 12px; }
    @media (max-width: 767px){
        .contenedor{ padding: 8px; }
        .estado-dte-panel{ padding: 12px; }
    }
</style>
@endsection
@section('javascript')
<script type="text/javascript">
    function enviar_correo(datos){
        let tipo_doc = $('input[name="tipo_dte"]:checked').val().trim();
        let dato=datos.split("&");
        let num_doc=dato[0];
        let correo=dato[1];
        let docu="";
        if(tipo_doc==33) docu="Enviar Factura N° ";
        if(tipo_doc==39) docu="Enviar Boleta N° ";
        if(tipo_doc==61) docu="Enviar Nota Crédito N° ";
        if(tipo_doc==56) docu="Enviar Nota Débito N° ";
        if(tipo_doc==52) docu="Enviar Guía Despacho N° ";

        console.log(tipo_doc);
        Vue.swal({
            html: docu+num_doc+" a <input type='text' id='que_correo' value='"+correo+"'>",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'CONTINUAR',
            cancelButtonText: 'CANCELAR'
        }).then((result) => {
            if (result.isConfirmed) {
                correo=document.getElementById("que_correo").value.trim();
                var url="{{url('/enviarcorreo')}}";
                var parametros={correo_destino:correo,tipo_doc:tipo_doc,num_doc:num_doc};

                $.ajaxSetup({
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    type:'POST',
                    beforeSend: function () {
                        Vue.swal({
                            text: 'Enviando correo...',
                            position: 'center',
                            icon: 'info'
                        });
                    },
                    url:url,
                    data:parametros,
                    success:function(resp){
                        console.log(resp);

                        Vue.swal({
                            text: resp,
                            position: 'center',
                            icon: 'info'
                        });
                    },
                    error: function(error){
                        console.log(error.responseText);
                    }
                });
            }
        })
        /*
        console.log("correo: "+correo);
        console.log("tipo_doc: "+tipo_doc);
        console.log("num_doc: "+num_doc);

        return false;

        */

    }


    function listar_dtes(){
        //documento seleccionado
        let tipo_dte=$('input[name="tipo_dte"]:checked').val().trim();
        let fechainicial = document.getElementById("ifechainicial").value;
        let fechafinal = document.getElementById("ifechafinal").value;
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
        let url='{{url("/ventas/damedteporfechas")}}'+'/'+tipo_dte+"/"+fechainicial+'/'+fechafinal;
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                console.log(resp);
                $('#listado_dte').html(resp);
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

    function enviar_correos(){
        let tipo_dte=$('input[name="tipo_dte"]:checked').val().trim();
        let fechainicial = document.getElementById("ifechainicial").value;
        let fechafinal = document.getElementById("ifechafinal").value;
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
        return Vue.swal({
            // mensaje en construccion
            title:'EN CONSTRUCCIÓN',
            text: 'Esta funcionalidad está en construcción.',
            icon: 'info',
        })
        let url='{{url("/ventas/enviar_correos_dte")}}'+'/'+tipo_dte+"/"+fechainicial+'/'+fechafinal;
        $.ajax({
            type:'GET',
            beforeSend: function () {
                Vue.swal({
                    title: 'Enviando Correos...',
                    icon: 'info',
                });
            },
            url:url,
            success:function(resp){
                console.log(resp);
                Vue.swal({
                    title: 'CORREOS ENVIADOS',
                    text: resp,
                    icon: 'success',
                });
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

    function imprimir(xml){
        let url='{{url("imprimir")}}'+'/'+xml;
        $.ajax({
            type:'GET',
            beforeSend: function () {
                Vue.swal({
                    title: 'ESPERE...',
                    icon: 'info',
                });
            },
            url:url,
            success:function(resp){
                Vue.swal.close();
                let r=JSON.parse(resp);
                if(r.estado=='OK'){
                    var config="location=yes,height=570,width=720,scrollbars=yes,status=yes";
                    var w=window.open(r.mensaje,'_blank',config);
                    w.focus();
                }else{
                    Vue.swal({
                        title: r.estado,
                        text: r.mensaje,
                        icon: 'error',
                    });
                }
            },
            error: function(error){
                Vue.swal({
                    title: 'ERROR',
                    text: formatear_error(error.responseText),
                    icon: 'error',
                    });

            }

        });

    }

    function ver_estado(TrackID){

        if(parseInt(TrackID)==0 || isNaN(parseInt(TrackID))){
            Vue.swal({
                title: 'ERROR',
                text: "No se puede revisar ESTADO, No hay TrackID Válido.",
                icon: 'error',
            });
            return false;
        }

        let tipoDTE = $('input[name="tipo_dte"]:checked').val().trim();
        var url='{{url("sii/verestado")}}'+"/"+tipoDTE+"&"+TrackID; //Controlador servicios_sii\sii_controlador ver_estadoUP
        $.ajax({
            type:'GET',
            url:url,
            beforeSend: function () {
                Vue.swal({
                    title: 'REVISANDO',
                    icon: 'info',
                });
            },
            success:function(rs){
                console.log(rs);
                Vue.swal.close();
                mostrar_revision_sii(rs, TrackID);
                listar_dtes();
            },
            error: function(error){
                Vue.swal.close();
                mostrar_revision_sii({
                    estado: 'ERROR',
                    mensaje: formatear_error(error.responseText || 'No fue posible consultar al SII.')
                }, TrackID);
            }
        });
    }

    function mostrar_revision_sii(respuesta, TrackID){
        let rs = respuesta;

        if(typeof rs === 'string'){
            try {
                rs = JSON.parse(rs);
            } catch (e) {
                rs = {estado: 'ERROR', mensaje: formatear_error(rs)};
            }
        }

        rs = rs || {};
        let estado = rs.estado || 'ERROR';
        let mensaje = rs.mensaje || 'El SII no entregó detalles de la revisión.';
        let esError = estado === 'ERROR' || estado.indexOf('ERROR') !== -1;
        let texto = 'Revisión SII' + (TrackID ? ' · TrackID ' + TrackID : '') +
            ': ' + estado + '. ' + mensaje;

        $('#revision_sii')
            .removeClass('alert-info alert-danger')
            .addClass(esError ? 'alert-danger' : 'alert-info')
            .text(texto)
            .show();

        return rs;
    }

    function ver_estadotrack(){

        let TrackID=document.getElementById('trackid').value;
        var url='{{url("sii/verestadotrack")}}'+"/"+TrackID;
        $.ajax({
            type:'GET',
            url:url,
            beforeSend: function () {
                Vue.swal({
                    title: 'REVISANDO',
                    icon: 'info',
                });
            },
            success:function(rs){
                console.log(rs);
                Vue.swal.close();
                rs = mostrar_revision_sii(rs, TrackID);
                Vue.swal({
                    title: 'RESULTADO. '+rs.estado,
                    icon: 'info',
                    text: rs.mensaje
                });
            },
            error: function(error){
                Vue.swal.close();
                mostrar_revision_sii({
                    estado: 'ERROR',
                    mensaje: formatear_error(error.responseText || 'No fue posible consultar al SII.')
                }, TrackID);
            }
        });
    }


    function formatear_error(error){
        let max=300;
        let rpta=error.substring(0,max);
        return rpta;
    }

    function limpiar_sesion(){
        let url='{{url("ventas/limpiarsesion")}}';
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                console.log("OK");
            },
            error: function(error){
                console.log("ERROR");
            }

        });
    }

    function set_xml_imprimir(xml){
        let url='{{url("ventas/setxmlimprimir")}}'+'/'+xml;
        $.ajax({
            type:'GET',
            url:url,
            success:function(resp){
                console.log("OK");
            },
            error: function(error){
                console.log("ERROR");
            }

        });
    }
</script>
@endsection
@section('contenido_titulo_pagina')
    @include('fragm.breadcrumb', ['titulo' => 'SII', 'titulo_modulo' => 'Estado DTE'])

@endsection
@section('contenido_ingresa_datos')

<div class="contenedor">
    <div class="estado-dte-panel">
        <h5>Consultar documentos tributarios</h5>
        <p class="estado-dte-ayuda">Seleccione el tipo de documento y un período para revisar los envíos realizados al SII.</p>

        <div id="tipo_dte">
            <strong>Tipo de documento:</strong>
            <div><input type="radio" name="tipo_dte" id="radio_boleta" value="39" checked><label for="radio_boleta">Boleta</label></div>
            <div><input type="radio" name="tipo_dte" id="radio_factura" value="33"><label for="radio_factura">Factura</label></div>
            <div><input type="radio" name="tipo_dte" id="radio_notacredito" value="61"><label for="radio_notacredito">Nota de crédito</label></div>
            <div><input type="radio" name="tipo_dte" id="radio_notadebito" value="56"><label for="radio_notadebito">Nota de débito</label></div>
            <div><input type="radio" name="tipo_dte" id="radio_guiadespacho" value="52"><label for="radio_guiadespacho">Guía de despacho</label></div>
        </div>

        <div class="form-row align-items-end mt-3">
            <div id="fechainicial" class="form-group col-md-3">
                <label for="ifechainicial"><strong>Desde</strong></label>
                <input type="date" name="ifechainicial" value='<?php echo date("Y-m-d"); ?>' id="ifechainicial" class="form-control">
            </div>
            <div id="fechafinal" class="form-group col-md-3">
                <label for="ifechafinal"><strong>Hasta</strong></label>
                <input type="date" name="ifechafinal" value='@php echo date("Y-m-d"); @endphp' id="ifechafinal" class="form-control">
            </div>
            <div id="boton_buscar" class="form-group col-md-3">
                <button type="button" class="btn btn-success btn-block" onclick="listar_dtes()">Buscar documentos</button>
            </div>
            <div id="btn_enviar_correo" class="form-group col-md-3">
                <button type="button" class="btn btn-outline-success btn-block" onclick="enviar_correos()">Enviar correos</button>
            </div>
        </div>
    </div>

    <div class="estado-dte-panel">
        <h5>Consultar directamente por TrackID</h5>
        <p class="estado-dte-ayuda">Use esta opción si conoce el identificador de seguimiento entregado por el SII.</p>
        <div id="ver_trackid" class="input-group">
            <input type="text" name="trackid" id="trackid" class="form-control" inputmode="numeric" placeholder="Ejemplo: 12618665418" aria-label="TrackID del envío">
            <div class="input-group-append">
                <button type="button" class="btn btn-info" onclick="ver_estadotrack()">Revisar estado</button>
            </div>
        </div>
        <div class="estado-dte-leyenda" aria-label="Guía de estados del SII">
            <span class="bg-success text-white">ACEPTADO</span>
            <span class="bg-info text-white">EN REVISIÓN</span>
            <span class="bg-warning text-dark">REPARO</span>
            <span class="bg-danger text-white">RECHAZADO / ERROR</span>
        </div>
    </div>

    <div id="revision_sii" class="alert" role="alert" aria-live="polite"></div>

    <div class="estado-dte-panel">
        <h5>Documentos encontrados</h5>
        <p class="estado-dte-ayuda">Use “Ver Estado” en un documento para actualizar su situación directamente desde el SII.</p>
        <div id="listado_dte">
            <div class="text-muted text-center py-4">Seleccione los filtros y presione “Buscar documentos”.</div>
        </div>
    </div>

</div>



@endsection
