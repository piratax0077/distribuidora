@extends('plantillas.app')
  @section('titulo','CLIENTES')
  @section('javascript')

<script type="text/javascript">
  var modifica=false;
  var elid=-1;
  var xDescuento=0;
  var xTipoDescuento=0;
  var seleccionados = [];
  var seleccionados_direcciones = [];
  function espere(mensaje)
    {
        Vue.swal({
                title: mensaje,
                icon: 'info',
                showConfirmButton: true,
                showCancelButton: false,
                allowOutsideClick:false,
            });
    }

  function soloNumeros(e)
  {
    var key = window.Event ? e.which : e.keyCode
    return ((key >= 48 && key <= 57) || (key==8) || (key==75) || (key==107))
  }

  function tipo_cliente(c){

    if(c==0){
      document.getElementById("empresa").disabled=true;
      document.getElementById("nombres").disabled=false;
      document.getElementById("apellidos").disabled=false;
      $('#div_razon_social').addClass('d-none');
    }

    if(c==1){
      document.getElementById("empresa").disabled=false;
      document.getElementById("nombres").disabled=true;
      document.getElementById("apellidos").disabled=true;
      $('#div_razon_social').removeClass('d-none');
    }

  }


  function limpiar_deuda_cero_clave(){
    Vue.swal({
        title: 'Ingrese Contraseña',
        input: 'password',
        confirmButtonText: 'Verificar',
        showConfirmButton: true,
        showCancelButton: true,
    }).then((result) => {
        if(result.isConfirmed){
            let clave=result.value;
            let url='{{url("/clave")}}';
            let parametros={
                clave:clave,
            };
            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
            type:'POST',
            url:url,
            data:parametros,
            success:function(resp){
                Vue.swal.close();
                if(resp=="OK"){
                    limpiar_deuda_cero();
                }
            },
            error: function(error){
                Vue.swal.close();
                    Vue.swal({
                        title: 'ERROR',
                        text: error.responseText,
                        icon: 'error',
                    });
            }
            });



        }


    });
  }

  function limpiar_deuda_cero(){
    var url='{{url("clientes")}}'+'/'+elid+'/borrardeuda';

    $.ajax({
    type:'GET',
    beforeSend: function () {
        //$('#mensajes').html("Borrando Cliente...");
        espere("Limpiando Deuda Cero Cliente...");
    },
    url:url,
    success:function(resp){
        $("#listar_clientes").html(resp);
        Vue.swal.close();
        Vue.swal({
            text: 'Listo...',
            position: 'top-end',
            icon: 'info',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
    },
    error: function(error){
        Vue.swal.close();
        Vue.swal({
            title: 'ERROR',
            text: error.responseText,
            icon: 'error',
        });
    }

    });
  }

  function borrar_cliente(idc){
    if (confirm('Esta seguro de eliminar el registro '+idc+' ?')==true)
    {

      var url='{{url("clientes")}}'+'/'+idc+'/borrar';

      $.ajax({
        type:'GET',
        beforeSend: function () {
          //$('#mensajes').html("Borrando Cliente...");
          espere("Borrando Cliente...");
        },
        url:url,
        success:function(resp){
            Vue.swal.close();
          location.href="{{url('clientes')}}";
          //$('#mensajes').html("Cliente Borrado - "+resp);
          Vue.swal({
                text: 'Cliente Borrado...',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        },
        error: function(error){
            Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
        }

      });
        return true;
    }
  }

  function enter_buscar(e){
    let keycode = e.keyCode;
    if(keycode=='13'){
        buscar_clientes();
    }
  }

  function buscar_clientes()
  {
    console.log('buscando');
    var url="{{url('clientes/buscar/')}}";
    var quien="clientes";
    var parametros={buscax:"nombres",buscado:document.getElementById("buscado").value,quien:quien};
    var bx=document.getElementById("buscaxrut").checked;

    if(bx) parametros=parametros={buscax:"rut",buscado:document.getElementById("buscado").value,quien:quien};

    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
     type:'POST',
     beforeSend: function () {
      //$("#mensajes").html("Buscando...");
      espere("Buscando...");
    },
    url:url,
    data:parametros,
    success:function(resp){
      console.log(resp);
        Vue.swal.close();
      //$("#mensajes").html("Listado de Clientes...");
      $("#listar_clientes").html(resp);
      limpiar_controles();
    },
    error: function(error){
      $('#problemas').html(error.responseText);
        Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
      }
      });
  }

  function cuenta_cliente(){
    if(!modifica){
        Vue.swal({
            title: 'ERROR',
            text: 'Debe Buscar y Elegir un Cliente',
            icon: 'error',
        });
    }else{
        var url='{{url("/clientes/dame_cuenta")}}'+'/'+elid;

      $.ajax({
        type:'GET',
        beforeSend: function () {
          //$('#mensajes').html("Borrando Cliente...");
          espere("Buscando cuenta del Cliente...");
        },
        url:url,
        success:function(resp){
            $("#listar_clientes").html(resp);
            Vue.swal.close();
          Vue.swal({
                text: 'Listo...',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        },
        error: function(error){
            Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
        }

      });


    }
  }

  function borrar_cuenta_clave(idop){
    console.log(idop);
    return false;
    Vue.swal({
                title: 'Ingrese Contraseña',
                input: 'password',
                confirmButtonText: 'Verificar',
                showConfirmButton: true,
                showCancelButton: true,
            }).then((result) => {
                if(result.isConfirmed){
                    let clave=result.value;
                    //Pedir contraseña
                    let url='{{url("/clave")}}';
                    let parametros={
                        clave:clave,
                    };
                    $.ajaxSetup({
                        headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                    type:'POST',
                    beforeSend: function () {
                    //espere("Guardando Cuenta...");
                    },
                    url:url,
                    data:parametros,
                    success:function(resp){
                        Vue.swal.close();
                        if(resp=="OK"){
                            borrar_cuenta(idop);
                        }
                    },
                    error: function(error){
                        Vue.swal.close();
                            Vue.swal({
                                title: 'ERROR',
                                text: error.responseText,
                                icon: 'error',
                            });
                    }
                    });



                }


            });
  }

  function borrar_cuenta(idop){
    let data=elid+"*"+idop;
    var url='{{url("/clientes/borrar_cuenta")}}'+'/'+data;

      $.ajax({
        type:'GET',
        beforeSend: function () {
          espere("Borrando cuenta del Cliente...");
        },
        url:url,
        success:function(resp){
            $("#listar_clientes").html(resp);
            Vue.swal.close();
          Vue.swal({
                text: 'Listo...',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        },
        error: function(error){
            Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
        }
      });
  }

  function cargar_cliente(idc)
  {
    modifica=true;
    var url='{{url("clientes")}}'+'/'+idc+'/cargar';
    $.ajax({
      type:'GET',
      beforeSend: function () {
        //$('#mensajes').html("Cargar Cliente...");
        espere("Cargando Cliente");
      },
      url:url,
      success:function(resp){
        //Llenar los datos del cliente en los controles.
        //viene en JSON desde el controlador
        console.log(resp);
        let giros = resp[2];
        $('#tbody_giros_cliente').empty();
        giros.forEach(function(giro){
            let fila = '<tr><td>'+giro.giro+'</td><td><button class="btn btn-outline-danger btn-sm" onclick="eliminarDatoCliente('+giro.id+', \'giro\')"><i class="fas fa-trash"></i></button> </td></tr>';
            $('#tbody_giros_cliente').append(fila);
        });
        Vue.swal.close();
        var ccc=JSON.parse(resp[0]);

        document.getElementById("id_cliente").value=ccc.id;
        elid=ccc.id;
        document.getElementById("rut").value=ccc.rut;
        tipo_cliente(ccc.tipo_cliente);
        if(ccc.tipo_cliente==0){
            document.getElementById("cliente_natural").checked=true;
        }else{
            document.getElementById("cliente_empresa").checked=true;
        }
        document.getElementById("nombres").value=ccc.nombres;
        document.getElementById("apellidos").value=ccc.apellidos;
        document.getElementById("empresa").value=ccc.empresa;
        document.getElementById("telf1").value=ccc.telf1;
        document.getElementById("telf2").value=ccc.telf2;
        document.getElementById("email").value=ccc.email;
        document.getElementById("contacto").value=ccc.contacto;
        document.getElementById("telfc").value=ccc.telfc;

        if(ccc.credito==1)
        {
          document.getElementById("credito_check").checked=true;
          document.getElementById("select_limites").disabled=false;
          document.getElementById("select_limites").value=ccc.limite;
          document.getElementById("dias").disabled=false;
          document.getElementById("dias").value=ccc.dias;
        }else{
          document.getElementById("select_limites").disabled=true;
          document.getElementById("credito_check").checked=false;
          document.getElementById("dias").disabled=true;
        }

        if(ccc.descuento==1)
        {
          switch(ccc.tipo_descuento)
          {
            case 1:
              document.getElementById("desc_general").checked=true;
              document.getElementById("desc_porcentaje").value=ccc.porcentaje;
              document.getElementById("desc_porcentaje").disabled=false;
            break;
            case 2:
              document.getElementById("desc_alcosto").checked=true;
              document.getElementById("desc_porcentaje").value=ccc.porcentaje;
              document.getElementById("desc_porcentaje").disabled=false;
            break;
            case 3:
              document.getElementById("desc_familia").checked=true;
              document.getElementById("desc_porcentaje").disabled=true;
              //activar los demas controles...
              document.getElementById('zona_familia').style.visibility="visible";
              document.getElementById('Familia_porcentaje').value=0;
              cargar_descuentos(idc);
            break;
          }
          document.getElementById("desc_porcentaje").value=ccc.porcentaje;

        }else{ // Sin descuento
          document.getElementById("desc_ninguno").checked=true;
          document.getElementById("desc_porcentaje").disabled=true;
          document.getElementById("desc_porcentaje").value=0;
          document.getElementById('zona_familia').style.visibility="hidden";
          document.getElementById('Familia_porcentaje').value=0;
        }

        if(ccc.tipo_descuento!=3)
        {
          document.getElementById('zona_familia').style.visibility="hidden";
          document.getElementById('Familia_porcentaje').value=0;
          borrar_descuentos();
        }
        let formas_pago = resp[3];
        $('#tbody_formas_pago_cliente').empty();
        formas_pago.forEach(function(forma){
            let fila = '<tr><td>'+forma.formapago+'</td><td><button class="btn btn-outline-danger btn-sm" onclick="eliminarDatoCliente('+forma.id+',\'formapago\')"><i class="fas fa-trash"></i></button> </td></tr>';
            $('#tbody_formas_pago_cliente').append(fila);
        });

        let direcciones = resp[4];
        $('#tbody_direcciones_cliente').empty();
        direcciones.forEach(function(direccion){
            let direccionJson = JSON.stringify(direccion);
            let fila = '<tr><td>'+direccion.direccion+'</td><td>'+direccion.numero+'</td><td>'+direccion.nombre_region+'</td><td>'+direccion.nombre_ciudad+'</td><td><button class="btn btn-outline-warning btn-sm" onclick="abrirModalEditarDireccion('+direccion.id+','+direccion.id_region+',`'+direccion.direccion+'`,'+direccion.numero+')"><i class="fas fa-edit"> </i> </button> <button class="btn btn-outline-danger btn-sm" onclick="eliminarDatoCliente('+direccion.id+',\'direccion\')"><i class="fas fa-trash"></i></button> </td></tr>';
            $('#tbody_direcciones_cliente').append(fila);
        });


        //$('#mensajes').html('Cliente cargado... : '+ccc.id);
        cargar_referencias_cliente(ccc.id);
      },
      error: function(error){
        Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
      }

    });
    //console.log("cargar cliente, modifica: "+modifica)
  }

  function abrirModalEditarDireccion(id_direccion,id_region,direccion, numero){
    $('#id_direccion_editar').val(id_direccion);
    $('#modalEditarDireccionCliente').modal('show');
    $('#nueva_region_editar').val(id_region);
    $('#nueva_direccion_editar').val(direccion);
    $('#nueva_direccion_numero_editar').val(numero);
    dameCiudadesEditar(id_region);
  }

  function cargar_referencias_cliente(idc){
    let url='{{url("/clientes/dame_tipo_documentos")}}'+"/"+idc;
    $.ajax({
      type:'GET',
      url:url,
      success:function(resp){
        if(resp.length==0){
            $("#documentos_listado").html("Cliente sin Documentos de Referencia");
        }else{
            let listadito="<table class='table table-sm table-hover'><thead><th scope='col' width='300px'>Documento</th><th scope='col'></th></thead><tbody>";
            resp.forEach(function(doc){
                listadito+="<tr><td>"+doc.nombre_documento+"</td><td><button class='btn btn-danger btn-sm' onclick='borrar_documento_cliente("+doc.id+")'>X</button></td></tr>";
            });
            listadito+="</tbody></table>";
            $("#documentos_listado").html(listadito);
        }

      },
      error: function(error){
        Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
      }

    });
  }

  function borrar_documento_cliente(iddoc){
    let idcliente=document.getElementById("id_cliente").value;
    if(idcliente==-1){
        Vue.swal({
            text: 'Elija un Cliente',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }
    let url='{{url("/clientes/borrar_documento_cliente")}}/'+idcliente+"&"+iddoc;
    $.ajax({
      type:'GET',
      url:url,
      success:function(resp){
        if(resp.length==0){
            $("#documentos_listado").html("Cliente sin Documentos de Referencia");
        }else{
            cargar_referencias_cliente(idcliente);
            Vue.swal({
                text: 'Documento Borrado',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        }
      },
      error: function(error){
        Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
      }
    });
  }

  function cargar_documentos(){
    let url='{{url("/dame_tipo_documentos")}}';
    $.ajax({
      type:'GET',
      url:url,
      success:function(resp){
        $("#documentos_select option").remove();
        $('#documentos_select').append('<option value="0">Elija un Documento</option>');
        resp.forEach(function(doc){
            $("#documentos_select").append('<option value="'+doc.id+'">'+doc.nombre_documento+'</option>');
        });
      },
      error: function(error){
        Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
      }
    });
  }

  function cargar_descuentos(idc)
  {
    var url='{{url("clientes")}}'+'/'+idc+'/cargardescuentos';
    $.ajax({
      type:'GET',
      beforeSend: function () {
        //$('#mensajes').html("Cargando Descuentos...");
      },
      url:url,
      success:function(resp){
        $("#familia_desc").html(resp);
        //$('#mensajes').html("Cliente con descuentos...");
      },
      error: function(error){
        Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
      }

    });
  }

function borrar_descuentos()
{
//Borrar la tabla temporal de descuentos.
    var url='{{url("clientes/borrarfamtodo")}}'; //petición
    //modifica=false;

    $.ajax({
      type:'GET',
      beforeSend: function () {
        //$('#mensajes').html("Borrar temporal descuentos...");
        //espere("Borrando descuento...");
      },
      url:url,
      success:function(resp){
        //Vue.swal.close();
        //$('#mensajes').html("Borrar temporal descuentos...");
      },
      error: function(error){
        Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
      }

    });
}

function eliminar_dato_cliente(param,tipo){
    let id=document.getElementById("id_cliente").value;
    if(id==-1){
        Vue.swal({
            text: 'Elija un Cliente',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }
    let url='{{url("clientes/eliminar_dato_cliente")}}';
    let parametros={
        id:id,
        param:param,
        tipo:tipo,
    };

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
    type:'POST',
    url:url,
    data:parametros,
    success:function(resp){
        if(tipo=="giro"){
          let giros = resp;
          $('#tbody_giros_cliente').empty();
          giros.forEach(function(giro){
              let fila = '<tr><td>'+giro.giro+'</td><td><button class="btn btn-outline-danger btn-sm" onclick="eliminarDatoCliente('+giro.id+', \'giro\')"><i class="fas fa-trash"></i></button> </td></tr>';
              $('#tbody_giros_cliente').append(fila);
          });
        }
        if(tipo=="formapago"){
            let formas_pago = resp;
            $('#tbody_formas_pago_cliente').empty();
            formas_pago.forEach(function(forma){
                let fila = '<tr><td>'+forma.formapago+'</td><td><button class="btn btn-outline-danger btn-sm" onclick="eliminarDatoCliente('+forma.id+', \'formapago\')"><i class="fas fa-trash"></i></button> </td></tr>';
                $('#tbody_formas_pago_cliente').append(fila);
            });
        }
        if(tipo=="direccion"){
            let direcciones = resp;
            $('#tbody_direcciones_cliente').empty();
            direcciones.forEach(function(direccion){
                let fila = '<tr><td>'+direccion.direccion+'</td><td>'+direccion.numero+'</td><td>'+direccion.nombre_region+'</td><td>'+direccion.nombre_ciudad+'</td><td><button class="btn btn-outline-danger btn-sm" onclick="eliminarDatoCliente('+direccion.id+', \'direccion\')"><i class="fas fa-trash"></i></button> </td></tr>';
                $('#tbody_direcciones_cliente').append(fila);
            });
        }
        Vue.swal({
            text: 'Dato Eliminado',
            position: 'top-end',
            icon: 'info',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
    },
    error: function(error){
        Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
    }
    });
}


  function nuevo_cliente()
  {
    elid=-1;
    borrar_descuentos();
    location.href="{{url('clientes')}}";
  }

  function agregar_cuenta(c)
  {
    let monto=0;
    let pago=document.getElementById('pago').value.trim();
    let deuda=document.getElementById('deuda').value.trim();
    let referencia=document.getElementById('referencia').value.trim();
    if(c==1){ //pago
        if(pago.length==0 || isNaN(parseInt(pago)) || parseInt(pago)==0){
            Vue.swal({
                text: 'Ingrese un Pago entero mayor a 0',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            document.getElementById("pago").value=0;
            return false;
        }
        monto=pago;
    }
    if(c==2){ //deuda
        if(deuda.length==0 || isNaN(parseInt(deuda)) || parseInt(deuda)==0){
            Vue.swal({
                text: 'Ingrese una Deuda entera mayor a 0',
                position: 'top-end',
                icon: 'warning',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            document.getElementById("deuda").value=0;
            return false;
        }
        monto=deuda;
    }
    if(referencia.length==0){
        Vue.swal({
            text: 'Ingrese una Referencia',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }

    var url='{{url("clientes/agrega_cuenta")}}';
    var parametros={
        id_cliente:elid,
        tipo_operacion:c,
        monto:monto,
        referencia:referencia
    };


    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
     type:'POST',
     beforeSend: function () {
      espere("Guardando Cuenta...");
    },
    url:url,
    data:parametros,
    success:function(resp){
        Vue.swal.close();
        $("#listar_clientes").html(resp);
        Vue.swal({
            text: 'Cuenta Agregada...',
            position: 'top-end',
            icon: 'info',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
    },
    error: function(error){
        Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
      }
      });


  }

  function agregar_documento(){
    let idcliente=document.getElementById("id_cliente").value;
    if(idcliente==-1){
        Vue.swal({
            text: 'Elija un Cliente',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }
    let docu=document.getElementById("documentos_select").value;
    if(docu==0){
        Vue.swal({
            text: 'Elija un Documento',
            position: 'top-end',
            icon: 'error',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        return false;
    }
    let url='{{url("clientes/agregar_documento")}}'+"/"+idcliente+"&"+docu;
    $.ajax({
        type:'GET',
        url:url,
        success:function(resp){
            if(resp==-1){
                Vue.swal({
                    text: 'Documento ya existe...',
                    position: 'top-end',
                    icon: 'error',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
            }else if(resp==-2){
                $("#documentos_listado").html("Cliente sin Documentos de Referencia");
            }else{

                let listadito="";
                cargar_referencias_cliente(idcliente);
                Vue.swal({
                    text: 'Documento Agregado...',
                    position: 'top-end',
                    icon: 'info',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                });
            }

        },
        error: function(error){
            Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
        }

    });

  }

  function revisarCredito()
  {
    var check=document.getElementById("credito_check");

    if(check.checked)
    {
      document.getElementById("select_limites").disabled=false;
      document.getElementById("dias").disabled=false;
    }else{
      document.getElementById("select_limites").disabled=true;
      document.getElementById("dias").disabled=true;
    }
  }

  function activa_descuento(elegido)
  {
    xTipoDescuento=elegido;
    if(elegido==0)
    {
      xDescuento=0;
    }else{
      xDescuento=1;
    }

    switch(elegido)
    {
      case 0:
        document.getElementById('zona_familia').style.visibility="hidden";
        document.getElementById('desc_porcentaje').disabled=true;
        document.getElementById("desc_porcentaje").value=0;
      break;
      case 1:
        document.getElementById('zona_familia').style.visibility="hidden";
        document.getElementById('desc_porcentaje').disabled=false;
      break;
      case 2:
        document.getElementById('zona_familia').style.visibility="hidden";
        document.getElementById('desc_porcentaje').disabled=false;
      break;
      case 3:
        document.getElementById('zona_familia').style.visibility="visible";
        document.getElementById('desc_porcentaje').disabled=true;
        document.getElementById("desc_porcentaje").value=0;
        document.getElementById('Familia_porcentaje').value=0;
        //cargar descuentos si hay
      break;
      default:
        document.getElementById('zona_familia').style.visibility="hidden";
        document.getElementById('desc_porcentaje').disabled=true;
      break;
    }
  }

  function agregar_familia()
  {
    var url='{{url("clientes/descfam")}}';
    var porc=document.getElementById("Familia_porcentaje").value;
    if(porc.trim().length==0 || isNaN(parseInt(porc)) || parseInt(porc)==0){
        Vue.swal({
            text: 'Ingrese porcentaje entero mayor a 0',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        document.getElementById("Familia_porcentaje").value=0;
        return false;
    }
    if(parseInt(porc)>10){
        Vue.swal({
            text: 'Descuento mayor a 10%',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
        document.getElementById("Familia_porcentaje").value=0;
        return false;
    }
    var usu_id="{{Session::get('usuario_id')}}";
    var parametros={
        id_familia:document.getElementById("Familia_id").value,
        porcentaje:porc,
        usuarios_id:usu_id
    };


    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
     type:'POST',
     beforeSend: function () {
      //$("#mensajes").html("Guardando Descuento, espere por favor...");
      espere("Guardando Descuento...");
    },
    url:url,
    data:parametros,
    success:function(resp){
        Vue.swal.close();
        $("#familia_desc").html(resp);
        //$("#mensajes").html("Descuento Guardando");
        Vue.swal({
            text: 'Descuento Guardado...',
            position: 'top-end',
            icon: 'info',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
    },
    error: function(error){
        Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
      }
      });


  }

  function quitar_familia(id)
  {

      var url='{{url("clientes")}}'+'/'+id+'/borrarfam'; //petición

      $.ajax({
        type:'GET',
        beforeSend: function () {
          //$("#mensajes").html("Borrando Descuento...");
          espere("Borrando Descuento");
        },
        url:url,
        success:function(resp){
            Vue.swal.close();
          $("#familia_desc").html(resp);
          Vue.swal({
                    text: 'Descuento por Familia Borrado',
                    position: 'top-end',
                    icon: 'warning',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    });
        },
        error: function(error){
            Vue.swal.close();
            Vue.swal({
                title: 'ERROR',
                text: error.responseText,
                icon: 'error',
            });
      }

      });


  }


  function ver_cliente()
  {
      alert("ID: "+document.getElementById("id_cliente").value);
  }

  function RUN_correcto(run)
  {
    var multiplicador=[3,2,7,6,5,4,3,2];
    var x=[0,0,0,0,0,0,0];
    if(run.length==8)
    {
      x[0]=0;
      x[1]=parseInt(run.substring(0,1));
      x[2]=parseInt(run.substring(1,2));
      x[3]=parseInt(run.substring(2,3));
      x[4]=parseInt(run.substring(3,4));
      x[5]=parseInt(run.substring(4,5));
      x[6]=parseInt(run.substring(5,6));
      x[7]=parseInt(run.substring(6,7));
      x[8]=run.substring(7,8); // porque puede ser la letra K
    }
    if(run.length==9)
    {
      x[0]=parseInt(run.substring(0,1));
      x[1]=parseInt(run.substring(1,2));
      x[2]=parseInt(run.substring(2,3));
      x[3]=parseInt(run.substring(3,4));
      x[4]=parseInt(run.substring(4,5));
      x[5]=parseInt(run.substring(5,6));
      x[6]=parseInt(run.substring(6,7));
      x[7]=parseInt(run.substring(7,8));
      x[8]=run.substring(8,9);
    }

    var suma=0;
    for(var i=0;i<8;i++)
    {
      suma=suma+x[i]*multiplicador[i];
    }
    var residuo=suma%11;
    var digito=11-residuo;
    console.log("X8: "+x[8]+" digito: "+digito);
    if(x[8]=="K")
    {
      if(digito==10){
        return true;
      }else{
        return false;
      }
    }else{
      if(x[8]==digito){
        return true;
      }else{
        if(x[8]==0 && digito==11){
            return true;
        }else{
            return false;
        }
      }
    }

  }

  function guardar_cliente() // En inventario.ventas_principal.blade.php también hay metodo javascript agregar_cliente_rapido()
  {
    //REVISAR RUT
    //le ponemos uppercase por si el último dígito es "k"
    var el_rut=document.getElementById("rut").value.toString().trim().toUpperCase();
    el_rut=el_rut.replace("-","");
    document.getElementById("rut").value=el_rut;

    if(el_rut.length<8)
    {
      Vue.swal({
        text: 'RUT debe tener mínimo 8 dígitos',
        position: 'top-end',
        icon: 'warning',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
        });
      return false;
    }

    if(!RUN_correcto(el_rut))
    {
      Vue.swal({
        text: 'RUT INVÁLIDO... DIGITO VERIFICADOR INVÁLIDO...',
        position: 'top-end',
        icon: 'warning',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
        });
      return false;
    }

    let tipo_cliente=document.querySelector("input[name=tipo_cliente]:checked").value

    if(tipo_cliente==0){
        var el_nombre=document.getElementById("nombres").value.toString();
        if(el_nombre.length<2)
        {
        Vue.swal({
            text: 'Falta Nombre o Muy corto...',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false;
        }

        var el_apellido=document.getElementById("apellidos").value.toString();
        if(el_nombre.length<2)
        {
        Vue.swal({
            text: 'Falta Apellido o Muy corto...',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false;
        }
    }

    if(tipo_cliente==1){
        var razon_social=document.getElementById("empresa").value.toString();
        el_nombre=razon_social;
        if(razon_social.length<5)
        {
        Vue.swal({
            text: 'Falta Razón Social o Muy corto...',
            position: 'top-end',
            icon: 'warning',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            });
        return false;
        }
    }

    // var el_giro=document.getElementById("giro").value.toString();
    // if(el_giro.length<5)
    // {
    //   Vue.swal({
    //     text: 'Falta Giro o Muy corto...',
    //     position: 'top-end',
    //     icon: 'warning',
    //     toast: true,
    //     showConfirmButton: false,
    //     timer: 3000,
    //     });
    //   return false;
    // }

    // var la_direccion=document.getElementById("direccion").value.toString();
    // if(la_direccion.length<10)
    // {
    //   Vue.swal({
    //     text: 'Falta Dirección o Muy corto...',
    //     position: 'top-end',
    //     icon: 'warning',
    //     toast: true,
    //     showConfirmButton: false,
    //     timer: 3000,
    //     });
    //   return false;
    // }

    // var la_comuna=document.getElementById("comuna").value.toString();
    // if(la_comuna.length<4)
    // {
    //   Vue.swal({
    //     text: 'Falta Comuna o Muy corto...',
    //     position: 'top-end',
    //     icon: 'warning',
    //     toast: true,
    //     showConfirmButton: false,
    //     timer: 3000,
    //     });
    //   return false;
    // }

    // var la_ciudad=document.getElementById("ciudad").value.toString();
    // if(la_ciudad.length<2)
    // {
    //   Vue.swal({
    //     text: 'Falta Ciudad o Muy corto...',
    //     position: 'top-end',
    //     icon: 'warning',
    //     toast: true,
    //     showConfirmButton: false,
    //     timer: 3000,
    //     });
    //   return false;
    // }

    var el_email=document.getElementById("email").value.toString();
    if(el_email.length<10)
    {
      Vue.swal({
        text: 'Falta Email o Muy corto...',
        position: 'top-end',
        icon: 'warning',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
        });
      return false;
    }


    var url="{{url('clientes/guardar')}}";
    var credito_check=document.getElementById("credito_check");

    if(credito_check.checked)
    {
      var xLimite=document.getElementById("select_limites").value;
      var xDia=document.getElementById("dias").value;
      var xCredito=1;
    }else{
      var xLimite=0;
      var xDia=0;
      var xCredito=0;
    }

    if(document.getElementById("desc_ninguno").checked) xTipoDescuento=0;
    if(document.getElementById("desc_general").checked) xTipoDescuento=1;
    if(document.getElementById("desc_alcosto").checked) xTipoDescuento=2;
    if(document.getElementById("desc_familia").checked) xTipoDescuento=3;

    if(xTipoDescuento>0)
    {
      xDescuento=1;
    }else{
      xDescuento=0;
    }

    if(modifica)
    {
      var mdfk=1;
      elid=document.getElementById("id_cliente").value;
    }else{ //Nuevo Cliente
      var mdfk=0;
      elid=-1;
    }
    var parametros={
        id_cliente:elid,
        modifika:mdfk,
        empresa: el_nombre,
        rut:el_rut,
        tipo_cliente:tipo_cliente,
        nombres:el_nombre,
        apellidos:document.getElementById("apellidos").value,
        telf1:document.getElementById("telf1").value,
        telf2:document.getElementById("telf2").value,
        email:document.getElementById("email").value,
        contacto:document.getElementById("contacto").value,
        telfc:document.getElementById("telfc").value,
        limite:xLimite,
        dia:xDia,
        credito:xCredito,
        descuento:xDescuento,
        tipodescuento:xTipoDescuento,
        porcentaje:document.getElementById("desc_porcentaje").value
      };

      console.log(parametros);

    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
     type:'POST',
     beforeSend: function () {
      //$("#mensajes").html("Guardando, espere por favor...");
      espere("Guardando Cliente...");
    },
    url:url,
    data:parametros,
    success:function(resp){
        Vue.swal.close();
        console.log(resp);
      //Si el primer carácter no se puede convertir en número, parseInt devuelve NaN.
      var x=parseInt(resp.id_cliente);
      if(isNaN(x))
      {
        $("#mensajes").html(resp);
      }else{
        console.log(resp.id_cliente);
        //devuelve el ID de la tabla cliente
        if(resp.id_cliente>0)
          document.getElementById("id_cliente").value=resp.id_cliente;
          //$("#mensajes").html("Cliente Guardado... ID: "+resp);
          Vue.swal({
                text: 'Cliente Guardado...',
                position: 'top-end',
                icon: 'info',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
            $("#listar_clientes").html("");
            limpiar_controles();
            let formas_pago = resp.formas_pago;
            $('#tbody_formas_pago_cliente').empty();
            formas_pago.forEach(forma_pago => {
              $('#tbody_formas_pago_cliente').append(`
              <tr><td>`+forma_pago.formapago+`</td><td><button class="btn btn-outline-danger btn-sm" onclick="eliminarDatoCliente(`+forma_pago.id+`, 'formapago')"><i class="fas fa-trash"></i></button> </td></tr>
              `);
            });
      }

    },
    error: function(error){
        Vue.swal.close();
        $('#problemas').html(error.responseText);
            // Vue.swal({
            //     title: 'ERROR',
            //     text: error.responseText,
            //     icon: 'error',
            // });
      }
      });

    }

    function guardar_cliente_xpress(){

    }



  function nuevo_giro(){
    $('#div_nuevo_giro').fadeIn("slow");
  }

  function agregar_giro(){
    let nuevo_giro = $('#nuevo_giro').val();
    if(nuevo_giro.trim() == 0 || nuevo_giro == ''){
      Vue.swal({
        icon:'error',
        text:'Giro vacio'
      });
      return false;
    }

    if(elid !== -1){
      let data = {
        giro: nuevo_giro,
        id_cliente: elid
      }

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type:'post',
        data: data,
        url:'/clientes/agregar_giro',
        success: function(resp){
          if(resp == 'OK'){
              Vue.swal({
                text: 'Listo...',
                position: 'top-end',
                icon: 'success',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
          }
        },
        error: function(error){
          Vue.swal({
                text: error.responseText,
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        }
      })
    }
  }

  function ocultar_nuevo_giro(){
    $('#div_nuevo_giro').fadeOut("slow");
  }

  function limpiar_controles()
  {
    $("#documentos_listado").html("SIN REFERENCIAS");
    document.getElementById("id_cliente").value=-1;
    document.getElementById("rut").value="";
    document.getElementById("nombres").value="";
    document.getElementById("apellidos").value="";
    document.getElementById("empresa").value="";
    // document.getElementById("giro").value="";
    // document.getElementById("direccion").value="";
    // document.getElementById("comuna").value="ARICA";
    // document.getElementById("ciudad").value="ARICA";
    document.getElementById("telf1").value="---";
    document.getElementById("telf2").value="---";
    document.getElementById("email").value="sinemail@gmail.com";
    document.getElementById("contacto").value="---";
    document.getElementById("telfc").value="---";

    document.getElementById("credito_check").checked=false;
    document.getElementById("select_limites").disabled=true;
    document.getElementById("select_limites").value=0;
    document.getElementById("dias").disabled=true;
    document.getElementById("dias").value=0;

    document.getElementById("desc_general").checked=false;
    document.getElementById("desc_alcosto").checked=false;
    document.getElementById("desc_familia").checked=false;
    document.getElementById("desc_ninguno").checked=true;
    document.getElementById("desc_porcentaje").value=0;
    document.getElementById("desc_porcentaje").disabled=true;

    document.getElementById('zona_familia').style.visibility="hidden";
    document.getElementById('Familia_porcentaje').value=0;
    document.getElementById("rut").focus();
    modifica=false;
    elid=-1;
    borrar_descuentos();

  }

  function dameFormasPago(){
    let url='{{url("/ventas/dame_solo_forma_pago")}}';
    $.ajax({
      type:'GET',
      url:url,
      success:function(resp){
        $("#formas_pago").html(resp);
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

  function agregarFormasPagoCliente(){

    var id_cliente = $('#id_cliente').val();

    if(id_cliente == -1){
      Vue.swal({
        text: 'Seleccione un Cliente...',
        position: 'top-end',
        icon: 'warning',
        toast: true,
        showConfirmButton: false,
        timer: 3000,
      });
      return false;
    }

    $("input[name='forma_pago']:checked").each(function() {
        var id = $(this).attr('value');
        // verificar si el valor ya existe en el array seleccionados
        if(seleccionados.includes(id)){
            return;
        }
        seleccionados.push(id);
        // $('#tbody_formas_pago_cliente').append(`
        // <tr><td>`+id+` </td><td><button class="btn btn-outline-danger btn-sm" onclick="eliminarFormaPagoCliente('`+id+`')"><i class="fa fa-trash"></i></button></td></tr>
        // `);
    });

    var url='{{url("/clientes/agregar_formas_pago")}}';
    var parametros={
        id_cliente:id_cliente,
        formas_pago:seleccionados
    };


    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
    });

    $.ajax({
     type:'POST',
     beforeSend: function () {
      espere("Guardando Formas de Pago...");
    },
    url:url,
    data:parametros,
    success:function(resp){
        Vue.swal.close();
        console.log(resp);
        if(resp.mensaje == 'OK'){
          Vue.swal({
            text: 'Formas de Pago Guardadas...',
            position: 'top-end',
            icon: 'info',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
          });
        }
        let formas_pago = resp.formas_pago;
        $('#tbody_formas_pago_cliente').empty();
        formas_pago.forEach(function(forma_pago){
          $('#tbody_formas_pago_cliente').append(`
          <tr><td>`+forma_pago.formapago+` </td><td><button class="btn btn-outline-danger btn-sm" onclick="eliminarDatoCliente(`+forma_pago.id+`,\'formapago\')"><i class="fa fa-trash"></i></button></td></tr>
          `);
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

function eliminarFormaPagoCliente(id){
  seleccionados = seleccionados.filter(function(item) {
    return item !== id;
  });
  $('#tbody_formas_pago_cliente').empty();
  seleccionados.forEach(function(id){
    $('#tbody_formas_pago_cliente').append(`
    <tr><td>`+id+` </td><td><button class="btn btn-outline-danger btn-sm" onclick="eliminarFormaPagoCliente(`+id+`)"><i class="fa fa-trash"></i></button></td></tr>
    `);
  });
}

function dameCiudades(){
  let id_region = $('#nueva_region').val();
  let url='{{url("/clientes/dame_ciudades")}}/'+id_region;
  $.ajax({
    type:'GET',
    url:url,
    success:function(resp){
      console.log(resp);
      let ciudades = resp;
      $('#nueva_ciudad').empty();
      ciudades.forEach(function(ciudad){
        $('#nueva_ciudad').append(`
          <option value="`+ciudad.id+`">`+ciudad.nombre+`</option>
        `);
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

function dameCiudadesEditar(id_region){
  let url='{{url("/clientes/dame_ciudades")}}/'+id_region;
  $.ajax({
    type:'GET',
    url:url,
    beforeSend: function(){
      $('#nueva_ciudad_editar').empty();
      $('#nueva_ciudad_editar').append(`
        <option value="-1">Cargando...</option>
      `);
    },
    success:function(resp){
      console.log(resp);
      let ciudades = resp;
      $('#nueva_ciudad_editar').empty();
      ciudades.forEach(function(ciudad){
        $('#nueva_ciudad_editar').append(`
          <option value="`+ciudad.id+`">`+ciudad.nombre+`</option>
        `);
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

function guardarDireccionCliente(){
  let id_cliente = $('#id_cliente').val();
  // validar el cliente
  if(id_cliente == -1){
    Vue.swal({
      text: 'Seleccione un Cliente...',
      position: 'top-end',
      icon: 'warning',
      toast: true,
      showConfirmButton: false,
      timer: 3000,
    });
    return false;
  }
  let direccion = $('#nueva_direccion').val();
  let numero = $('#nueva_direccion_numero').val();
  let ciudad = $('#nueva_ciudad').val();
  let region = $('#nueva_region').val();
  let url='{{url("/clientes/agregar_direccion")}}';
  let parametros = {
    id_cliente: id_cliente,
    direccion: direccion,
    numero:numero,
    ciudad: ciudad,
    region: region
  }
  $.ajax({
    type:'POST',
    url:url,
    data:parametros,
    success:function(resp){
      console.log(resp);
      if(resp.mensaje == 'OK'){
        Vue.swal({
          text: 'Dirección Guardada...',
          position: 'top-end',
          icon: 'info',
          toast: true,
          showConfirmButton: false,
          timer: 3000,
        });
      }

      let direcciones = resp.direcciones;
      $('#tbody_direcciones_cliente').empty();
      direcciones.forEach(function(direccion){
        $('#tbody_direcciones_cliente').append(`
        <tr>
          <td>`+direccion.direccion+`</td>
          <td>`+direccion.numero+`</td>
          <td>`+direccion.nombre_ciudad+`</td>
          <td>`+direccion.nombre_region+`</td>
          <td> <button class="btn btn-outline-warning btn-sm" onclick="abrirModalEditarDireccion(`+direccion.id+`,`+direccion.id_region+`,'`+direccion.direccion+`','`+direccion.numero+`')"><i class="fas fa-edit"></i> </button> <button class="btn btn-outline-danger btn-sm" onclick="eliminarDatoCliente(`+direccion.id+`,\'direccion\')"><i class="fa fa-trash"></i></button></td>
        </tr>
        `);
      });

      // cerrar modal
      $('#modalAgregarDireccionCliente').modal('hide');
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

function agregar_giro(){
  let nuevo_giro = $('#nuevo_giro').val();
  if(nuevo_giro.trim() == 0 || nuevo_giro == ''){
    Vue.swal({
      icon:'error',
      text:'Giro vacio'
    });
    return false;
  }

  if(elid !== -1){
    let data = {
      giro: nuevo_giro,
      id_cliente: elid
    }

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });
    $.ajax({
      type:'post',
      data: data,
      url:'/clientes/agregar_giro',
      success: function(resp){
        if(resp.mensaje == 'OK'){
            Vue.swal({
              text: 'Listo...',
              position: 'top-end',
              icon: 'success',
              toast: true,
              showConfirmButton: false,
              timer: 3000,
          });
        }

        let giros = resp.giros;
        $('#tbody_giros_cliente').empty();
        giros.forEach(function(giro){
          $('#tbody_giros_cliente').append(`
          <tr>
            <td>`+giro.giro+`</td>
            <td><button class="btn btn-outline-danger btn-sm" onclick="eliminarDatoCliente(`+giro.id+`,\'giro\')"><i class="fa fa-trash"></i></button></td>
          </tr>
          `);
        });
      },
      error: function(error){
        Vue.swal({
              text: error.responseText,
              position: 'top-end',
              icon: 'error',
              toast: true,
              showConfirmButton: false,
              timer: 3000,
          });
      }
    })
  }

}

function eliminarDatoCliente(id, tipo){

  let data = {
    id: id,
    tipo: tipo,
    id_cliente: elid
  }

  Vue.swal({
    title: 'Está seguro de eliminar este dato?',
    text: "No podrá revertir esto!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si, eliminar!',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      let url = '/clientes/eliminar_dato_cliente';
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type:'post',
        data: data,
        url:url,
        success: function(resp){
          console.log(resp);
          if(resp.mensaje == 'OK'){
              Vue.swal({
                text: 'Listo...',
                position: 'top-end',
                icon: 'success',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
          }

          let giros = resp.giros;
          let direcciones = resp.direcciones;
          let formas_pago = resp.formas_pago;
          if(tipo == 'giro'){
            $('#tbody_giros_cliente').empty();
            giros.forEach(function(giro){
              $('#tbody_giros_cliente').append(`
              <tr>
                <td>`+giro.giro+`</td>
                <td><button class="btn btn-outline-danger btn-sm" onclick="eliminarDatoCliente(`+giro.id+`,\'giro\')"><i class="fa fa-trash"></i></button></td>
              </tr>
              `);
            });
          }
          if(tipo == 'direccion'){
            $('#tbody_direcciones_cliente').empty();
            direcciones.forEach(function(direccion){
              $('#tbody_direcciones_cliente').append(`
              <tr>
                <td>`+direccion.direccion+`</td>
                <td>`+direccion.numero+`</td>
                <td>`+direccion.nombre_ciudad+`</td>
                <td>`+direccion.nombre_region+`</td>
                <td><button class="btn btn-outline-warning btn-sm" onclick="abrirModalEditarDireccion(`+direccion.id+`,`+direccion.id_region+`,'`+direccion.direccion+`','`+direccion.numero+`')"> <i class="fas fa-edit"></i> </button>  <button class="btn btn-outline-danger btn-sm" onclick="eliminarDatoCliente(`+direccion.id+`,\'direccion\')"><i class="fa fa-trash"></i></button></td>
              </tr>
              `);
            });
          }
          if(tipo == 'formapago'){
            $('#tbody_formas_pago_cliente').empty();
            formas_pago.forEach(function(forma_pago){
              $('#tbody_formas_pago_cliente').append(`
              <tr><td>`+forma_pago.formapago+` </td><td><button class="btn btn-outline-danger btn-sm" onclick="eliminarDatoCliente(`+forma_pago.id+`,\'formapago\')"><i class="fa fa-trash"></i></button></td></tr>
              `);
            });
          }
        },
        error: function(error){
          Vue.swal({
                text: error.responseText,
                position: 'top-end',
                icon: 'error',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
            });
        }
      });
    }else{
      console.log('cancelando');
    }
  })


}

function eliminar_cliente(id){
  // realizar confirmacion con swal
  Vue.swal({
    title: 'Está seguro de eliminar este cliente?',
    text: "No podrá revertir esto!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si, eliminar!',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      let url = '/clientes/'+id+'/eliminar';
      $.ajax({
        type:'GET',
        url:url,
        success:function(resp){
          if(resp == 'OK'){
            Vue.swal({
              text: 'Cliente Eliminado...',
              position: 'top-end',
              icon: 'success',
              toast: true,
              showConfirmButton: false,
              timer: 3000,
            });
            $('#cliente_'+id).remove();
          }
        },
        error: function(error){
          Vue.swal({
              title: 'ERROR',
              text: error.responseText,
              icon: 'error',
          });
        }
      });
    }else{
      console.log('cancelando');
    }
  })
}

function cambiar_estado_cliente(id, nombre_cliente, activo){
    console.log(activo);
    let swalOptions = {
        title: 'Está seguro de cambiar estado de ' + nombre_cliente + '?',
        text: "Esta accion será reflejada para el vendedor!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'Cancelar'
    };

    if (activo == 1) {
        swalOptions.html = '<input id="descripcion" class="swal2-input" placeholder="Ingrese una descripción">';
        swalOptions.preConfirm = () => {
            const descripcion = document.getElementById('descripcion').value;
            if (!descripcion) {
                Vue.swal.showValidationMessage('La descripción es requerida');
            }
            return { descripcion: descripcion };
        };
    }

    Vue.swal(swalOptions).then((result) => {
        if (result.isConfirmed) {
            let url = '/clientes/cambiar_estado';
            let data = {
                id: id,
                activo: activo,
                _token: '{{ csrf_token() }}'
            };

            if (activo == 1) {
                data.descripcion = result.value.descripcion;
            }



            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                success: function(resp){
                    console.log(resp);
                    if(resp.mensaje == 'OK'){
                        if(resp.activo == 2){
                            // Selecciona el botón y cambia su atributo onclick
                            $('#btn_estado_cliente' + id).attr('onclick', 'cambiar_estado_cliente(' + id + ', "' + nombre_cliente + '", ' + resp.activo + ')');
                            $('#estado_cliente' + id).html('MOROSO'+'<br>'+resp.descripcion);
                            $('#estado_cliente' + id).removeClass('badge-success');
                            $('#estado_cliente' + id).addClass('badge-danger');
                            Vue.swal({
                                text: 'Cliente ' + nombre_cliente + ' Moroso...',
                                position: 'top-end',
                                icon: 'warning',
                                toast: true,
                                showConfirmButton: false,
                                timer: 3000,
                            });
                        } else {
                            $('#btn_estado_cliente' + id).attr('onclick', 'cambiar_estado_cliente(' + id + ', "' + nombre_cliente + '", ' + resp.activo + ')');
                            $('#estado_cliente' + id).html('ACTIVO');
                            $('#estado_cliente' + id).removeClass('badge-danger');
                            $('#estado_cliente' + id).addClass('badge-success');
                            Vue.swal({
                                text: 'Cliente ' + nombre_cliente + ' Activado...',
                                position: 'top-end',
                                icon: 'success',
                                toast: true,
                                showConfirmButton: false,
                                timer: 3000,
                            });
                        }
                    }
                },
                error: function(error){
                    Vue.swal({
                        title: 'ERROR',
                        text: error.responseText,
                        icon: 'error',
                    });
                }
            });
        } else {
            console.log('cancelando');
        }
    });
}

function editarDireccionCliente(){
  let id_direccion_editar = $('#id_direccion_editar').val();
  let direccion_editar = $('#nueva_direccion_editar').val();
  let numero_editar = $('#nueva_direccion_numero_editar').val();
  let ciudad_editar = $('#nueva_ciudad_editar').val();
  let region_editar = $('#nueva_region_editar').val();
  let id_cliente = $('#id_cliente').val();

  let data = {
    id_direccion: id_direccion_editar,
    direccion: direccion_editar,
    numero: numero_editar,
    ciudad: ciudad_editar,
    region: region_editar,
    id_cliente: id_cliente
  }

  let url = '/clientes/editar_direccion';
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  $.ajax({
    type:'post',
    data: data,
    url:url,
    success: function(resp){
      console.log(resp);
      if(resp.mensaje == 'OK'){
          Vue.swal({
            text: 'Dirección Editada...',
            position: 'top-end',
            icon: 'success',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
        });
      }

      let direcciones = resp.direcciones;
      $('#tbody_direcciones_cliente').empty();
      direcciones.forEach(function(direccion){
        $('#tbody_direcciones_cliente').append(`
        <tr>
          <td>`+direccion.direccion+`</td>
          <td>`+direccion.numero+`</td>
          <td>`+direccion.nombre_region+`</td>
          <td>`+direccion.nombre_ciudad+`</td>

          <td> <button class="btn btn-outline-warning btn-sm" onclick="abrirModalEditarDireccion(`+direccion.id+`,`+direccion.id_region+`,'`+direccion.direccion+`','`+direccion.numero+`')"> <i class="fas fa-edit"></i></button> <button class="btn btn-outline-danger btn-sm" onclick="eliminarDatoCliente(`+direccion.id+`,\'direccion\')"><i class="fa fa-trash"></i></button></td>
        </tr>
        `);
      });
      // cerrar modal
      $('#modalEditarDireccionCliente').modal('hide');
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
            label{
                margin-bottom: 0px;
            }

            .table>tbody>tr>td, .table thead tr th{
                padding:0.5px;
            }


            .logo_{
              width: 120px;
            }


        </style>
    @endsection
  @section('contenido_titulo_pagina')
 @endsection

@section('contenido_ingresa_datos')
@include('fragm.breadcrumb',['titulo'=>'Clientes','titulo_modulo'=>'Clientes','ruta_modulo'=>'Clientes'])
<div class="container-fluid">


<div class="row w-100 border mb-3 p-2" >
  <div class="col-sm-12">
    @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
  </div>

  <div class="col-sm-3">
    <input type="radio" name="buscapor" id="buscaxrut" checked="true">
    <label for="buscaxrut">RUT</label>
    &nbsp;&nbsp;&nbsp;
    <input type="radio" name="buscapor" id="buscaxnombres" >
    <label for="buscaxnombres">Nombres</label>
  </div>
  <div class="col-sm-9 d-flex justify-content-between align-items-start px-2" >
    <div class="form-group floating w-100">
      <input type="text" id="buscado" class="form-control floating" size="20" onkeyup="enter_buscar(event)">
      <label for="buscado" class="floating">Buscar</label>
    </div>
    <button onclick="buscar_clientes()" class="btn btn-outline-success btn-sm"><i class="fas fa-search"></i></button>
  </div>
  {{-- <button class="btn btn-outline-success btn-sm float-right" data-target="#modalImportarClientes" data-toggle="modal">Importar Clientes</button> --}}
</div>
<div id="problemas"></div>
<div class="row contenedor w-100"> <!-- Contiene a las 2 partes -->
<!-- INGRESAR/MODIFICAR CLIENTES -->
    <div class="col-sm-4 border" id="datos_generales">

        <p class="titulazo">Datos Generales:</p>
        <input type="hidden" id="id_cliente">
        <div class="row m-2">
          <div class="col-sm-12">
            <div class="form-group floating">
              <input type="text" id="rut" class="form-control floating" size="20" onclick="return soloNumeros(event)">
              <label for="rut" class="floating">RUT</label>
            </div>

          </div>
        </div>

        <div class="row border p-2" style="background: #eee">
          <div class="col-sm-4"><button onclick="guardar_cliente()" id="btnGuardar" class="btn btn-outline-dark btn-sm w-100"><i class="fas fa-save"></i>Guardar</button></div>
          <div class="col-sm-4"><button onclick="nuevo_cliente()" class="btn btn-outline-dark btn-sm w-100"><i class="fas fa-user"></i>Nuevo</button></div>
          <div class="col-sm-4"><button onclick="cuenta_cliente()" class="btn btn-outline-dark btn-sm w-100"><i class="fas fa-search"></i>Cuenta</button></div>
        </div>


        <div class="row border p-2 my-3">
          <div class="col-sm-12 d-flex justify-content-between">
            <div>
              <input type="radio" name="tipo_cliente" value="0" id="cliente_natural" onclick="tipo_cliente(0)" checked="true">
              <label for="cliente_natural">Persona Natural</label>
            </div>
            <div>
              <input type="radio" name="tipo_cliente" value="1" id="cliente_empresa" onclick="tipo_cliente(1)">
              <label for="cliente_empresa">Empresa</label>
            </div>

          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <div class="form-group floating">
              <input type="text" id="nombres" class="form-control floating" size="20">
              <label for="nombre" class="floating">Nombre Completo</label>
              <input type="hidden" id="apellidos" class="form-control floating" size="20" value="">
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-10">
            <div class="form-group floating">
              <input type="text" id="empresa" class="form-control floating" size="40" disabled>
              <label for="empresa" class="floating">Razon social</label>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-10">
            <div class="form-group floating">
              <input type="text" id="email" class="form-control floating" size="40">
              <label for="email" class="floating">Email</label>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group floating">
              <input type="text" id="telf1" class="form-control floating" size="20">
              <label for="telf1" class="floating">Telf1</label>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group floating">
              <input type="text" id="telf2" class="form-control floating" size="20">
              <label for="telf2" class="floating">Telf2</label>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group floating">
              <input type="text" id="contacto" class="form-control floating" size="20">
              <label for="contacto" class="floating">Contacto</label>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group floating">
              <input type="text" id="telfc" class="form-control floating" size="20">
              <label for="telfc" class="floating">Telefono</label>
            </div>
          </div>
        </div>
        <div>
          <button class="btn btn-outline-dark btn-sm w-100 my-2" onclick="guardar_cliente()"><i class="fa fa-save"> </i>Guardar</button>
        </div>
        <hr>
        <h4 class="titulazo">Informacion</h4>
        <div class="row my-3 border p-2">
          <div class="col-sm-6">


            <table id="tabla_giros"  class="table mt-3 letra_pequeña" >
              <thead class="thead-light">
                <tr>
                  <th>Giro</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="tbody_giros_cliente">

              </tbody>
            </table>
            <button class="btn btn-outline-dark btn-sm w-100" data-target="#modalAgregarGiro" data-toggle="modal">+ Giro</button>
          </div>
          <div class="col-sm-6">

              <table class="table mt-3 letra_pequeña">
                <thead class="thead-light">
                  <tr>
                    <th>Formas Pago</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody id="tbody_formas_pago_cliente">

                </tbody>
              </table>
              <button class="btn btn-outline-dark btn-sm w-100" onclick="dameFormasPago()" data-target="#modalFormasPago" data-toggle="modal">+ Forma Pago</button>
          </div>
          <div class="col-sm-12">

            <table class="table mt-3 letra_pequeña">
              <thead class="thead-light">
                <tr>
                  <th>Dirección</th>
                  <th>N°</th>
                  <th>Region</th>
                  <th>Ciudad</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="tbody_direcciones_cliente">

              </tbody>
            </table>
            <button class="btn btn-outline-dark btn-sm w-100" data-target="#modalAgregarDireccionCliente" data-toggle="modal">+ Direccion</button>
          </div>

        </div>




        <br>

        <div class="row" >
          <div class="col-sm-3"><input type="checkbox" id="credito_check" onclick="revisarCredito()">Crédito</div>
          <div class="col-sm-5">
            Límite:
            @if($limites->count()>0)
            <select id = "select_limites">
              @foreach($limites as $limite)
                <option value = "{{$limite->valor}}">{{$limite->valor}}</option>
              @endforeach
            </select>
            @else
              <select id = "select_limites">
                <option value = "">Vacio</option>
            </select>
            @endif

          </div>
          <div class="col-sm-4">
            Días:
            @if($dias->count()>0)
            <select id = "dias">
              @foreach($dias as $dia)
                <option value = "{{$dia->valor}}">{{$dia->valor}}</option>
              @endforeach
            </select>
            @else
              <select id = "dias">
                <option value = "">Vacio</option>
            </select>
            @endif
          </div>
        </div>
        <br>
      <div class="row" style="display:block">
        <div class="row">
            <div class="col-sm-12">
                <h4>REFERENCIAS:</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8" style="margin-left:15px;">
                <select name="" id="documentos_select" class="form-control form-control-sm" style="width:90%; padding-left:5px">
                    <option value="0"></option>
                </select>
            </div>
            <div class="col-sm-2" style="padding:0px">
                <button class="btn btn-outline-success form-control-sm" style="padding-top:2px" onclick="agregar_documento()">+</button>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12" id="documentos_listado">
                SIN REFERENCIAS
            </div>
        </div>
      </div>
      <br>
      <div class="row">
        <fieldset id="zona_descuento">
            <div class="row">
                <div class="col-sm-12">
                    <h4>DESCUENTOS:</h4>
                    <input type="radio" name="descuento" value="0" id="desc_ninguno" onclick="activa_descuento(0)" checked="true">
                    <label for="desc_ninguno">Ninguno</label>
                    <br>
                    <input type="radio" name="descuento" value="1" id="desc_general" onclick="activa_descuento(1)">
                    <label for="desc_general">General</label>
                    &nbsp;&nbsp;
                    <input type="radio" name="descuento" value="2" id="desc_alcosto" onclick="activa_descuento(2)">
                    <label for="desc_alcosto">Al Costo</label>
                    &nbsp;&nbsp;
                    <input type="text" placeholder="Porcentaje" id="desc_porcentaje" size="10px" value="0">
                </div>
            </div>
            <div class="row">
              <div class="col-sm-10">
                <input type="radio" name="descuento" value="3" id="desc_familia" onclick="activa_descuento(3)">
                <label for="desc_familia">Por Familia</label>
              </div>
            </div>
        </fieldset>
      </div>


            <fieldset id="zona_familia" style="visibility: hidden;width:100%">
                <div class="row" style="height:250px">
                <div class="col-sm-8" id="familia_desc" style="padding:1px">
                <p>Sin descuentos agregados</p>
                </div>
                <div class="col-sm-4">
                <div class="row mb-1">
                    <label for="Familia_id">Familia:&nbsp;&nbsp;</label><br>
                    @if($familias->count()>0)
                        <select id = "Familia_id" style="width: 100%;">
                            @foreach($familias as $familia)
                                <option value = "{{$familia->id}}">{{strtoupper($familia->nombrefamilia)}}</option>
                            @endforeach
                        </select>
                    @else
                        <p>No hay familias definidas</p>
                    @endif
                </div>
                <div class="row mb-1">Porcentaje:&nbsp;
                    <input type="text" placeholder="porcentaje" id="Familia_porcentaje" style="width:30%" maxlength="2">
                </div>
                <div class="row"><button onclick="agregar_familia()" class="btn btn-primary btn-sm">Agregar</button></div>
                </div>
            </div>
            </fieldset>

    </div> <!-- fin col-sm-4 -->


<!-- BUSCAR CLIENTES -->
    <div class="col-sm-8">

      <div id="listar_clientes" class="letra_pequeña">
        <table class="table" id="miTabla">
          <thead class="thead-light">
            <tr>
              <th>RUT</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Telefono</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($clientes as $cliente)
            <tr>
              <td>{{$cliente->rut}}</td>
              <td>{{$cliente->nombres}} {{$cliente->apellidos}}</td>

              <td>{{$cliente->email}}</td>
              <td>{{$cliente->telf1}} <br> {{$cliente->telf2}}</td>
              <td>@if($cliente->activo == 1) <span id="estado_cliente{{ $cliente->id }}" class="badge badge-success">ACTIVO</span> @else <span id="estado_cliente{{ $cliente->id }}" class="badge badge-danger">MOROSO <br> {{ $cliente->descripcion_activo }}</span> @endif</td>
              <td class="d-flex">
                <button class="btn btn-outline-danger btn-sm" onclick="eliminar_cliente({{$cliente->id}})"><i class="fas fa-trash"></i></button>
                <button id="btn_estado_cliente{{ $cliente->id }}" class="btn btn-outline-dark btn-sm" onclick="cambiar_estado_cliente({{ $cliente->id }},'{{ $cliente->nombres }}','{{ $cliente->activo }}')"><i class="fa-solid fa-circle-exclamation"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        <div class="row">
          <div class="col-md-12">
            {{ $clientes->links('pagination::bootstrap-4') }}
          </div>
        </div>

    </div> <!--Fin buscar clientes -->

</div> <!-- fin row de ambas col-sm -->
<div id="error"></div>
</div> <!-- container fluid -->
<!-- MODAL modalFormasPago -->
<div class="modal fade" id="modalFormasPago" tabindex="-1" role="dialog" aria-labelledby="modalFormasPago" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="modalFormasPago">Formas de Pago</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" >
        <div id="formas_pago">
          <div class="row">
            <div class="col-sm-12">
              <button class="btn btn-outline-success" onclick="guardarFormaPago()">Guardar</button>
            </div>
          </div>

        </div>
        <button class="btn btn-outline-success btn-sm" onclick="agregarFormasPagoCliente()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- modal importar clientes con id modalImportarClientes -->
<div class="modal fade" id="modalImportarClientes" tabindex="-1" role="dialog" aria-labelledby="modalImportarClientes" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="modalImportarClientes">Importar Clientes</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body letra_pequeña" >
        <div class="row">

          <div class="col-sm-12">
            <form id="form-importar-repuestos" method="POST" action="{{ route('importar-clientes') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="form-group floating">
                      <input type="file" id="file" name="file" class="form-control floating" size="20">
                      <label for="file" class="floating">Archivo Clientes</label>
                    </div>
                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary">Importar</button>
                    </div>
            </form>
                <div id="loadingMessage" style="display: none;">Cargando...</div>
                <script>
                    document.getElementById('form-importar-repuestos').addEventListener('submit', function() {
                        document.getElementById('loadingMessage').style.display = 'block';
                    });
                </script>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- MODAL modalAgregarDireccionCliente -->
<div class="modal fade" id="modalAgregarDireccionCliente" tabindex="-1" role="dialog" aria-labelledby="modalAgregarDireccionCliente" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="modalAgregarDireccionCliente">Agregar Dirección Cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body letra_pequeña" >
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group floating">
              <select name="nueva_region" id="nueva_region" class="form-control floating" onchange="dameCiudades()">
                <option value="0">Seleccione</option>
                @foreach($regiones as $region)
                  <option value="{{$region->id}}">{{$region->nombre}}</option>
                @endforeach
              </select>
              <label for="nueva_region" class="floating">Region</label>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group floating">
              <select name="nueva_ciudad" id="nueva_ciudad" class="form-control floating">
                <option value="0">Seleccione</option>
              </select>
              <label for="nueva_ciudad" class="floating">Ciudad o Comuna</label>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-9">
            <div class="form-group floating">
              <input type="text" id="nueva_direccion" class="form-control floating" size="20">
              <label for="nueva_direccion" class="floating">Dirección</label>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="form-group floating">
              <input type="text" id="nueva_direccion_numero" class="form-control floating" size="20">
              <label for="nueva_direccion_numero" class="floating">N°</label>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <button class="btn btn-outline-success" onclick="guardarDireccionCliente()">Guardar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEditarDireccionCliente" tabindex="-1" role="dialog" aria-labelledby="modalAgregarDireccionCliente" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="modalAgregarDireccionCliente">Editar Dirección Cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body letra_pequeña" >
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group floating">
              <input type="hidden" name="id_direccion_editar" id="id_direccion_editar" value="">
              <select name="nueva_region_editar" id="nueva_region_editar" class="form-control floating" onchange="dameCiudades()" readonly>

                <option value="0">Seleccione</option>
                @foreach($regiones as $region)
                  <option value="{{$region->id}}">{{$region->nombre}}</option>
                @endforeach
              </select>
              <label for="nueva_region" class="floating">Region</label>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group floating">
              <select name="nueva_ciudad_editar" id="nueva_ciudad_editar" class="form-control floating">
                <option value="0">Seleccione</option>
              </select>
              <label for="nueva_ciudad" class="floating">Ciudad o Comuna</label>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-9">
            <div class="form-group floating">
              <input type="text" id="nueva_direccion_editar" class="form-control floating" size="20">
              <label for="nueva_direccion_editar" class="floating">Dirección</label>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="form-group floating">
              <input type="text" id="nueva_direccion_numero_editar" class="form-control floating" size="20">
              <label for="nueva_direccion_numero_editar" class="floating">N°</label>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <button class="btn btn-outline-warning" onclick="editarDireccionCliente()">Editar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- modalAgregarGiro -->
<div class="modal fade" id="modalAgregarGiro" tabindex="-1" role="dialog" aria-labelledby="modalAgregarGiro" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="modalAgregarGiro">Agregar Giro Cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body letra_pequeña" >
        <div class="row">
          <div class="col-sm-12">
            <div class="form-group floating">
              <input type="text" id="nuevo_giro" class="form-control floating" size="20">
              <label for="nuevo_giro" class="floating">Giro</label>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12">
            <button class="btn btn-outline-success" onclick="agregar_giro()">Guardar</button>
          </div>
        </div>
      </div>
    </div>
</div>


<script>
  window.onload=function() {
    document.getElementById("select_limites").disabled=true;
    document.getElementById("dias").disabled=true;
    document.getElementById('desc_porcentaje').disabled=true;
    document.getElementById('desc_porcentaje').value=0;
    document.getElementById("id_cliente").value=-1;
    //document.getElementById("zona_descuento").style.visibility="hidden";
    document.getElementById("zona_familia").style.visibility="hidden";
    document.getElementById('Familia_porcentaje').value=0;
    // $('#tabla_giros').DataTable();
    //IMPEDIR CTRL+V
    /*
    var rut = document.getElementById("rut");
    rut.onpaste = function(e)
    {
      e.preventDefault();
      alert("Digite el RUT...");
    }
    */
    cargar_documentos();

  }
</script>

@endsection

