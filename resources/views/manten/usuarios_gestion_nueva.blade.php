@extends('plantillas.usuarios')
@section('titulo','Usuarios')
@section('style')
<style>
    :root { --users-primary:#2563eb; --users-navy:#17324d; --users-muted:#64748b; --users-border:#e2e8f0; --users-bg:#f5f7fb; }
    .users-page { background:var(--users-bg); min-height:calc(100vh - 118px); padding:24px 28px 48px; color:#1e293b; }
    .users-heading { display:flex; align-items:center; justify-content:space-between; gap:24px; max-width:1500px; margin:0 auto 20px; }
    .users-heading .eyebrow { color:var(--users-primary); font-size:11px; font-weight:800; letter-spacing:.13em; text-transform:uppercase; }
    .users-heading h1 { margin:3px 0 4px; color:var(--users-navy); font-size:27px; line-height:1.2; font-weight:700; }
    .users-heading p { margin:0; color:var(--users-muted); font-size:14px; }
    .heading-actions { display:flex; gap:10px; flex-shrink:0; }
    .heading-actions .btn { border-radius:8px; padding:9px 15px; font-weight:600; box-shadow:0 1px 2px rgba(15,23,42,.05); }
    .users-stats { max-width:1500px; margin:0 auto 18px; display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:14px; }
    .stat-item { display:flex; align-items:center; gap:12px; padding:14px 18px; border:1px solid var(--users-border); background:#fff; border-radius:10px; }
    .stat-item .stat-icon { display:grid; place-items:center; width:38px; height:38px; flex:0 0 38px; border-radius:9px; background:#e8f0ff; color:#2563eb; }
    .stat-item .stat-icon.success { background:#e9f8ef; color:#16854b; }
    .stat-item .stat-icon.roles { background:#f1eefe; color:#7057d9; }
    .stat-item strong,.stat-item small { display:block; }
    .stat-item strong { color:var(--users-navy); font-size:20px; line-height:1.1; }
    .stat-item small { margin-top:2px; color:var(--users-muted); font-size:12px; }
    .users-workspace { max-width:1500px; min-height:600px; margin:0 auto; overflow:hidden; background:#fff; border:1px solid var(--users-border); border-radius:12px; box-shadow:0 8px 26px rgba(15,23,42,.06); }
    .users-sidebar { border-right:1px solid var(--users-border); background:#fbfcfe; }
    .filter-header { display:flex; align-items:center; justify-content:space-between; padding:20px 20px 12px; }
    .filter-header h2 { display:inline; margin:0 8px 0 0; color:var(--users-navy); font-size:18px; font-weight:700; }
    .filter-header span { color:var(--users-muted); font-size:12px; }
    .btn-clear { width:31px; height:31px; border:1px solid var(--users-border); border-radius:7px; color:var(--users-muted); background:#fff; cursor:pointer; }
    .filters-row { display:grid; grid-template-columns:minmax(0,1.6fr) minmax(130px,1fr); gap:8px; padding:0 20px 16px; border-bottom:1px solid var(--users-border); }
    .search-box { position:relative; }
    .search-box i { position:absolute; left:12px; top:50%; color:#94a3b8; transform:translateY(-50%); }
    .search-box input,.filters-row .custom-select { width:100%; height:40px; border:1px solid #d8e0ea; border-radius:8px; color:#334155; background:#fff; font-size:13px; outline:none; }
    .search-box input { padding:0 12px 0 36px; }
    .search-box input:focus,.filters-row .custom-select:focus { border-color:#7ca7f8; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
    .users-list { max-height:620px; overflow-y:auto; padding:8px; }
    .user-card { position:relative; display:flex; align-items:center; gap:12px; margin:3px 0; padding:12px; border:1px solid transparent; border-radius:9px; cursor:pointer; transition:.16s ease; outline:none; }
    .user-card:hover { background:#f1f5f9; }
    .user-card:focus,.user-card.is-selected { border-color:#b8cffb; background:#edf4ff; box-shadow:inset 3px 0 0 var(--users-primary); }
    .avatar-wrap { position:relative; flex:0 0 50px; }
    .user-avatar { width:50px; height:50px; display:block; border:1px solid #dde5ee; border-radius:50%; object-fit:cover; background:#fff; }
    .status-dot { position:absolute; right:0; bottom:1px; width:12px; height:12px; border:2px solid #fff; border-radius:50%; }
    .status-dot.active { background:#22c55e; } .status-dot.inactive { background:#94a3b8; }
    .user-summary { min-width:0; flex:1; }
    .user-name { overflow:hidden; color:#243b53; font-size:14px; font-weight:700; text-overflow:ellipsis; white-space:nowrap; }
    .user-email { overflow:hidden; margin:1px 0 5px; color:#718096; font-size:11px; text-overflow:ellipsis; white-space:nowrap; }
    .role-badge { display:inline-block; padding:2px 7px; border-radius:10px; color:#315a8a; background:#e5eef8; font-size:10px; font-weight:700; }
    .edit-user { display:grid; place-items:center; width:30px; height:30px; border-radius:7px; color:#64748b; opacity:.55; transition:.16s; }
    .user-card:hover .edit-user,.user-card.is-selected .edit-user { background:#fff; color:var(--users-primary); opacity:1; }
    .empty-results,.detail-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; color:var(--users-muted); text-align:center; }
    .empty-results { min-height:250px; gap:7px; } .empty-results i { font-size:24px; color:#a8b4c3; }
    .users-detail { min-width:0; background:#fff; }
    .selection-actions { min-height:64px; display:flex; align-items:center; justify-content:space-between; gap:14px; padding:12px 22px; border-bottom:1px solid var(--users-border); }
    .selection-actions > span { color:var(--users-muted); font-size:12px; font-weight:600; }
    .selection-actions .btn { margin-left:5px; border-radius:7px; }
    .detail-panel { padding:0 22px 24px; }
    .detail-panel .nav-tabs { border-bottom:1px solid var(--users-border); }
    .detail-panel .nav-link { padding:17px 18px 13px; border:0; border-bottom:2px solid transparent; color:#64748b; font-size:13px; font-weight:700; }
    .detail-panel .nav-link.active { color:var(--users-primary); border-bottom-color:var(--users-primary); background:transparent; }
    .detail-panel .tab-content { padding-top:24px; }
    .detail-empty { min-height:420px; }
    .detail-empty span { display:grid; place-items:center; width:72px; height:72px; margin-bottom:16px; border-radius:50%; color:#7890aa; background:#edf2f7; font-size:29px; }
    .detail-empty h3 { margin:0 0 7px; color:#334e68; font-size:18px; }
    .detail-empty p { max-width:390px; margin:0; font-size:13px; line-height:1.6; }
    #rolesUser > .row { margin:0; padding:12px; border:1px solid var(--users-border); border-radius:10px; background:#fbfcfe; }
    #rolesUser .imgUserRol { width:180px; height:180px; max-width:100%; border-radius:12px; object-fit:cover; box-shadow:none; }
    #rolesUser .lbl-info-user { margin:5px 0 0; color:#64748b; font-size:10px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; }
    #rolesUser p { margin:0 0 5px; color:#243b53; font-size:14px; }
    #listado-permisos .table { font-size:13px; } #listado-permisos .table thead th { border-top:0; color:#64748b; }
    #listado-permisos-detalle { border:1px solid var(--users-border); border-radius:9px; box-shadow:0 8px 20px rgba(15,23,42,.07); }
    .permissions-toolbar { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:16px; }
    .permissions-toolbar h3 { margin:0 0 3px; color:var(--users-navy); font-size:17px; font-weight:700; }
    .permissions-toolbar p { margin:0; color:var(--users-muted); font-size:12px; }
    .permissions-count { min-width:42px; padding:7px 11px; border-radius:18px; color:#245aa6; background:#eaf2ff; font-size:12px; font-weight:800; text-align:center; }
    .permissions-list { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
    .permission-card { display:flex; align-items:center; gap:12px; min-width:0; padding:13px 14px; border:1px solid var(--users-border); border-radius:9px; background:#fff; transition:.16s ease; }
    .permission-card:hover { border-color:#bdd0ee; box-shadow:0 4px 12px rgba(15,23,42,.05); }
    .permission-icon { display:grid; place-items:center; width:34px; height:34px; flex:0 0 34px; border-radius:8px; color:#2563eb; background:#edf4ff; font-size:13px; }
    .permission-info { min-width:0; flex:1; }
    .permission-module { display:block; overflow:hidden; color:#243b53; font-size:13px; font-weight:700; text-overflow:ellipsis; white-space:nowrap; }
    .permission-description { display:block; overflow:hidden; margin-top:2px; color:#718096; font-size:11px; text-overflow:ellipsis; white-space:nowrap; }
    .permission-remove { display:grid; place-items:center; width:29px; height:29px; flex:0 0 29px; border:0; border-radius:7px; color:#dc3545; background:#fff1f2; cursor:pointer; transition:.15s; }
    .permission-remove:hover { color:#fff; background:#dc3545; }
    .permissions-footer { display:flex; justify-content:flex-end; margin-top:16px; padding-top:15px; border-top:1px solid var(--users-border); }
    .permissions-footer .btn { border-radius:8px; font-size:13px; font-weight:700; }
    .permissions-empty { grid-column:1/-1; padding:55px 20px; border:1px dashed #cbd5e1; border-radius:10px; color:#64748b; text-align:center; }
    .permissions-empty i { display:block; margin-bottom:10px; color:#9aabc0; font-size:26px; }
    .permission-editor { margin-top:18px !important; padding:0 !important; overflow:hidden; }
    .permission-editor-head { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; border-bottom:1px solid var(--users-border); background:#f8fafc; }
    .permission-editor-head h4 { margin:0; color:#334e68; font-size:14px; font-weight:700; }
    .permission-editor-body { padding:16px; }
    .permission-categories { max-height:270px; overflow-y:auto; padding-right:8px; }
    .permission-categories .form-check { margin:0 0 6px; padding:8px 8px 8px 30px; border-radius:7px; }
    .permission-categories .form-check:hover { background:#f1f5f9; }
    .permission-editor-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:14px; padding-top:14px; border-top:1px solid var(--users-border); }
    @media (max-width:991.98px) { .users-workspace { overflow:visible; } .users-sidebar { border-right:0; border-bottom:1px solid var(--users-border); } .users-list { max-height:440px; } }
    @media (max-width:767.98px) { .users-page { padding:16px 12px 36px; } .users-heading { align-items:flex-start; flex-direction:column; } .heading-actions { width:100%; } .heading-actions .btn { flex:1; } .users-stats { grid-template-columns:1fr; } .filters-row { grid-template-columns:1fr; } .selection-actions { align-items:flex-start; flex-direction:column; } .selection-actions > div { display:flex; width:100%; } .selection-actions .btn { flex:1; margin:0 4px 0 0; font-size:11px; } .permissions-list { grid-template-columns:1fr; } }
</style>
@endsection
@section('contenido')
    <script>
       
        function verUsuario(user_id){
            
                let id = user_id;
                let url = '/usuarios/user/'+id;
                $('#usuario_id').val(id);
                $('.user-card').removeClass('is-selected');
                $('.user-card[data-user-id="'+id+'"]').addClass('is-selected');
                $.ajax({
                    type:'get',
                    url: url,
                    beforeSend: function(){
                        $('#rolesUser').empty();
                        $('#rolesUser').append('Buscando ...');
                        
                    },
                    success: function(data){
                        console.log(data);
                        $('#listado-permisos-detalle').removeClass('d-block').addClass('d-none');
                        $('#panelPermisos').html('<div class="permissions-empty"><i class="fas fa-mouse-pointer"></i>Selecciona una categoría para ver sus permisos.</div>');
                        $('input[name="flexCheckPermisos"]').prop('checked', false);
                        $('#btnAgregarPermiso').removeClass('disabled').prop('disabled', false);
                        Vue.swal.close();
                        let urlImage = "usuarios/avatar/"+data[0].image_path;
                        let permisos = data[2];
                        let u_h_p = data[3];
                        var user = data[0];
                        
                        $('#rolesUser').empty();
                        $('#rolesUser').append(`
                        <div class='row'>
                            <div class='col-md-6'>
                                <img src='${urlImage}' alt='' class='imgUserRol'>
                            </div>
                            <div class='col-md-6'>
                                <label class='lbl-info-user' for='nameUser'>Nombre de usuario: </label>
                                <p>${user.name}</p>
                                <label class='lbl-info-user' for='cargoUser'>Cargo: </label>
                                <p id='roleName'>${data[1]}</p>
                                <label class='lbl-info-user' for='emailUser'>Email: </label>
                                <p id='roleEmail'>${user.email}</p>
                                <label class='lbl-info-user' for='rutUser'>Rut: </label>
                                <p id='roleRut'>${user.rut}</p>
                                <label class='lbl-info-user' for='telefonoUser'>Telefono: </label>
                                <p id='roleTelefono'>${user.telefono}</p>
                                <label class='lbl-info-user' for='estadoUser'>Estado: </label>
                                ${user.activo === 1 ? "<p id='roleEstado'>Activo</p>" : "<p id='roleEstado'>Inactivo</p>"}
                                <button class='btn btn-outline-dark' data-toggle='modal' data-target='#exampleModal${user.id}'>Cambiar rol </button>
                            </div>
                        </div>
                    `);
                    

                        $('#btnActivar').attr({'href':'usuarios/up/'+user.id});
                        $('#btnActivar').removeClass('disabled');
                        $('#btnDesactivar').attr('href','usuarios/down/'+user.id);
                        $('#btnDesactivar').removeClass('disabled');
                        $('#btnEliminar').attr('href','usuarios/delete/'+user.id);
                        $('#btnEliminar').removeClass('disabled');

                        renderizarPermisos(permisos, u_h_p, user);
                    },

                    error: function(err){
                        console.log(err);
                    },
                    complete: function(){
                        console.log('Completada');
                    }

                });

        }

        function filtrarUsuarios(){
            const texto = ($('#buscarUsuario').val() || '').toLowerCase().trim();
            const rol = $('#filtroRol').val();
            let visibles = 0;

            $('.user-card').each(function(){
                const coincideTexto = ($(this).data('search') || '').toString().toLowerCase().indexOf(texto) !== -1;
                const coincideRol = !rol || $(this).data('role').toString() === rol;
                const mostrar = coincideTexto && coincideRol;
                $(this).toggle(mostrar);
                if(mostrar) visibles++;
            });

            $('#usuariosVisibles').text(visibles);
            $('#sinResultados').toggleClass('d-none', visibles !== 0);
        }

        function limpiarFiltros(){
            $('#buscarUsuario').val('');
            $('#filtroRol').val('');
            filtrarUsuarios();
            $('#buscarUsuario').focus();
        }

        function escaparHtml(valor){
            return $('<div>').text(valor == null ? '' : valor).html();
        }

        function renderizarPermisos(permisos, detalles, usuario){
            const asignados = [];
            permisos.forEach(function(permiso){
                detalles.forEach(function(detalle){
                    if(permiso.id === detalle.permission_id){
                        asignados.push({
                            id: detalle.id,
                            modulo: permiso.name,
                            descripcion: detalle.descripcion
                        });
                    }
                });
            });

            $('#permisosCantidad').text(asignados.length);
            const listado = $('#tbody_permisos').empty();

            if(!asignados.length){
                listado.append(`<div class="permissions-empty"><i class="fas fa-shield-alt"></i><strong>Sin permisos adicionales</strong><div>Este usuario todavía no tiene permisos específicos asignados.</div></div>`);
            } else {
                asignados.forEach(function(item){
                    listado.append(`
                        <article class="permission-card">
                            <span class="permission-icon"><i class="fas fa-key"></i></span>
                            <div class="permission-info">
                                <span class="permission-module">${escaparHtml(item.modulo)}</span>
                                <span class="permission-description">${escaparHtml(item.descripcion)}</span>
                            </div>
                            <button type="button" class="permission-remove" onclick="eliminarPermiso(${item.id},${usuario.id})" title="Quitar permiso" aria-label="Quitar permiso"><i class="fas fa-trash-alt"></i></button>
                        </article>`);
                });
            }
        }



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



        function cambiarRolUser(user){

            let idNuevoRol = $('input[name="flexRadioDefault"]:checked').val();
            
            if(idNuevoRol === null || typeof idNuevoRol === "undefined"){
                return Vue.swal({
                    icon:'error',
                    text:'Debe seleccionar un rol'
                });
            }
            let data = {'user_id': user.id, 'role_id': idNuevoRol};
            let url = '/usuarios/cambiar-rol';

            $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
            });

            $.ajax({
                type:'POST',
                url:url,
                data: data,
                success: function(data){
                    
                    let datos = data[0];
                    let permisos = data[1][2];
                    let u_h_p = data[1][3];
                    console.log(u_h_p);
                    $('#roleName').empty();
                    $('#roleName').append(datos.nombrerol);
                    const tarjeta = $('.user-card[data-user-id="'+user.id+'"]');
                    tarjeta.attr('data-role', idNuevoRol).data('role', idNuevoRol);
                    tarjeta.find('.role-badge').text(datos.nombrerol);
                    renderizarPermisos(permisos, u_h_p, user);
                    Vue.swal({
                        title: 'Felicidades',
                        text: "Rol actualizado",
                        icon: 'success',
                    });
                },
                error: function(err){
                            Vue.swal({
                                title:'Error!',
                                text:err.responseText,
                                icon:'error'
                            });
                }

            })

            

        }



        function activarUsuario(event){

            event.preventDefault();

            let url = $('#btnActivar').attr('href');

            console.log(url);

            // Con substring extraigo el id del usuario que quiero activar

            let id = $('#usuario_id').val();

            $.ajax({

                type:'get',

                url: url,

                data: id,

                success: function(data){

                    $('#userDesactive').empty();

                    $('#userDesactive').removeClass('info-estado-usuario-inactivo');

                    $('#userDesactive').append('<span id="userActive">Activo</span>');

                    $('#userDesactive').addClass('info-estado-usuario-activo');

                    $('#roleEstado').empty();

                    $('#roleEstado').append("<h3 id='roleEstado'>Activo </h3>");
                    $('.user-card[data-user-id="'+id+'"]').find('.status-dot').removeClass('inactive').addClass('active').attr('title','Activo');

                    Vue.swal({

                        title: 'Felicidades',

                        text: "Usuario "+data+" activado con éxito",

                        icon: 'success',

                    });

                }

            });

        }

        function handleRoles(idrol){
            console.log(idrol);
        }

        function desactivarUsuario(event){

            event.preventDefault();

            let url = $('#btnDesactivar').attr('href');

            console.log(url);

            // Con substring extraigo el id del usuario que quiero activar

            let id = $('#usuario_id').val();

            $.ajax({

                type:'get',

                url: url,

                data: id,

                success: function(data){

                    $('#userActive').empty();

                    $('#userActive').removeClass('info-estado-usuario-activo');

                    $('#userActive').append('<span id="userDesactive">Inactivo</span>');

                    $('#userActive').addClass('info-estado-usuario-inactivo');

                    $('#roleEstado').empty();

                    $('#roleEstado').append("<h3 id='roleEstado'>Inactivo </h3>");
                    $('.user-card[data-user-id="'+id+'"]').find('.status-dot').removeClass('active').addClass('inactive').attr('title','Inactivo');

                    Vue.swal({

                        title: 'Felicidades',

                        text: "Usuario "+data+" desactivado con éxito",

                        icon: 'success',

                    });

                }

            });

        }



        function eliminarUsuario(event){

            event.preventDefault();

            Vue.swal({

                title: '¿Esta seguro de eliminar al usuario?',

                text: "¡No podrá revertir esta acción!",

                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#3085d6',

                cancelButtonColor: '#d33',

                confirmButtonText: 'Si, eliminalo!'

                }).then((result) => {

                if (result.isConfirmed) {

                    

                    let url = $('#btnEliminar').attr('href');

                    let id = url.substring(16,19);

                    $.ajax({

                        type:'get',

                        url: url,

                        data: id,

                        success: function(data){

                            console.log(data);

                            Vue.swal({

                                title:'Eliminado!',

                                text:'El usuario'+data.name+ 'ha sido eliminado',

                                icon:'success'

                            });

                        },

                        error: function(e){

                            Vue.swal({

                                title:'Error!',

                                text:e.responseText,

                                icon:'error'

                            })

                        }

                    });

                    setTimeout(function(){ window.location.href = '/usuarios'; },3000);

                }else{

                    console.log('No eliminado');

                }

                });

            

        }



        function editarUsuario(id){

            alert(id);

        }

        function abrirModalPermiso(){
            if(!$('#usuario_id').val()){
                return Vue.swal({ icon:'info', text:'Primero selecciona un usuario.' });
            }
            $('#listado-permisos-detalle').removeClass('d-none');
            $('#listado-permisos-detalle').addClass('d-block');
        }

        function esconderModal(){
            $('#listado-permisos-detalle').removeClass('d-block');
            $('#listado-permisos-detalle').addClass('d-none');
        }

        function cambiarDivPermisos(){
            let permisoId = $('input[name="flexCheckPermisos"]:checked').val();
            let url = '/usuarios/cambiar_div/'+permisoId;
            
            $.ajax({
                type:'get',
                url:url,
                beforeSend: function(){
                    $('#panelPermisos').empty();
                    $('#panelPermisos').append('Cargando ...');
                },
                success: function(resp){
                    
                    $('#panelPermisos').empty();
                    $('#panelPermisos').prepend(resp);
                },
                error: function(error){
                    console.log(error);
                }
            })
        }

        function guardarPermisos(){
            var choices = [];
            let permisoId = $('input[name="flexCheckPermisos"]:checked').val();
            let usuario_id = $('#usuario_id').val();
            let permisos = document.getElementsByName('permisos');
            for (var i=0;i<permisos.length;i++){
                if ( permisos[i].checked ) {
                    choices.push(permisos[i].value);
                }
            }

            if(choices.length == 0){
                Vue.swal({
                    icon:'error',
                    title:'Error',
                    text:'Debe escoger permisos'
                });
                return false;
            }

            let parametros = {
                permisoId: permisoId,
                detalles: choices,
                usuario_id: parseInt(usuario_id)
            }
            let url = '/usuarios/guardarPermisosDetalles';
            
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type:'post',
                data: parametros,
                url:url,
                success: function(data){
                    if(data == 'error'){
                        return Vue.swal({
                            icon:'error',
                            text:'Ya existe permiso'
                        });
                    }
                    let permisos = data[2];
                    let u_h_p = data[3];
                    var user = data[0];

                        renderizarPermisos(permisos, u_h_p, user);
              
                        Vue.swal({
                            icon:'success',
                            title:'felicidades',
                            text:'Permiso concedido'
                        });
                    
                   
                },
                error: function(error){
                    
                    Vue.swal({
                            icon:'success',
                            title:'felicidades',
                            text:error.responseText
                        });
                }
            })
           
        }

        function eliminarPermiso(id,user_id){
            Vue.swal({
                title: '¿Esta seguro de eliminar el permiso del usuario?',
                text: "¡Precaución!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si, eliminalo!'

                }).then((result) => {

                if (result.isConfirmed) {
                    let data = {'id':id,'user_id': user_id};
                    let url = "/usuarios/permisos/delete";
                    $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                    });

                    $.ajax({
                        type:'post',
                        data: data,
                        url: url,
                        success: function(data){
                            let permisos = data[2];
                            let u_h_p = data[3];
                            var user = data[0];

                        renderizarPermisos(permisos, u_h_p, user);
                            Vue.swal({

                                title:'Eliminado!',

                                text:'El permiso ha sido eliminado',

                                icon:'success'

                            });

                            //setTimeout(function(){ window.location.href = '/usuarios'; },3000);

                        },

                        error: function(err){

                            console.log(err);

                        }

                    })

                        }else{

                            console.log('No eliminado');

                        }

                        });

            

           

        }

        function seleccionarCajero(e){
            e.preventDefault();
            $.ajax({
                type:'get',
                url:'ventas/damecajeros',
                success: function(cajeros){
                console.log(cajeros);
                if(cajeros.length > 0){
                    $("#transferir-carrito-modal").modal("show");
                    $('#nombres-cajeros').empty();
                    $('#nombres-cajeros').append('<p>Bodegueros Disponibles </p>');
                    cajeros.forEach((cajero) => {
                    $('#nombres-cajeros').append(`
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="CheckCajero" id="CheckCajero`+cajero.id+`" value="`+cajero.id+`">
                    <label class="form-check-label" for="CheckCajero`+cajero.id+`">
                    `+cajero.name+`
                    </label>
                    </div>
                    
                    `);
                    });
                }else{
                    Vue.swal({
                        title: 'ERROR',
                        text: 'No hay cajeros dIPSonibles',
                        icon: 'error',
                        });
                }
                
                },
                error: function(e){
                Vue.swal({
                        title: 'ERROR',
                        text: formatear_error(e.responseText),
                        icon: 'error',
                        });
                }
            });
           
        }

        function guardarCajeros(){
            console.log('guardando');
            var choices = [];
            let usuario_id = $('#usuario_id').val();
            let cajeros = document.getElementsByName('CheckCajero');
            for (var i=0;i<cajeros.length;i++){
                if ( cajeros[i].checked ) {
                    choices.push(cajeros[i].value);
                }
            }

            if(choices.length == 0){
                Vue.swal({
                    icon:'error',
                    title:'Error',
                    text:'Debe escoger permisos'
                });
                return false;
            }

            let parametros = {
                detalles: choices
            }
            console.log(parametros);
            let url = '/usuarios/guardarCajerosDisponibles';

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type:'post',
                data: parametros,
                url:url,
                success: function(resp){
                    
                    if(resp[0] == 'OK'){
                        $('#listado_cajeros_dIPSonibles').empty();    
                        let cajeros = resp[1];
                        
                            cajeros.forEach(c => {
                                let nombre = c['name'];
                                let usuario_id = c['id_usuario'];
                                console.log(nombre);
                                $('#listado_cajeros_dIPSonibles').append(`
                                <tr> 
                                    <td>`+nombre+` </td>
                                    <td><button class="btn btn-danger btn-sm" onclick="eliminarCajeroDisponible(`+usuario_id+`)">X</button> </td>    
                                </tr>
                                `);
                        });
                    }else{
                        Vue.swal({
                            icon:'error',
                            text:resp
                        });
                        return false;
                    }
                    
                   
                },
                error: function(error){
                    
                    Vue.swal({
                            icon:'error',
                            title:'Error',
                            text:error.responseText
                        });
                }
            })
        }

         function eliminarCajeroDisponible(id_usuario){
            let url = '/usuarios/eliminar_cajero_disponible/'+id_usuario;
            $.ajax({
                type:'get',
                url: url,
                success: function(respuesta){
                    console.log(respuesta);

                    // Verificar si hay un error
                    if(respuesta.error){
                        Vue.swal({
                            icon:'error',
                            title:'Error',
                            text: respuesta.error
                        });
                        return false;
                    }

                    // Si es exitoso, procesar los cajeros
                    if(respuesta[0] == 'OK'){
                        let cajeros = respuesta[1];
                        $('#listado_cajeros_dIPSonibles').empty();

                        if(cajeros.length > 0){
                            cajeros.forEach(c => {
                                $('#listado_cajeros_dIPSonibles').append(`
                                <tr>
                                    <td>`+c.name+` </td>
                                    <td><button class="btn btn-danger btn-sm" onclick="eliminarCajeroDisponible(`+c.id_usuario+`)">X</button> </td>
                                </tr>
                                `);
                            });
                        }else{
                            $('#listado_cajeros_dIPSonibles').append(`
                            <tr>
                                <td class="alert-danger">No hay cajeros seleccionados.</td>
                            </tr>
                            `);
                        }

                        Vue.swal({
                            icon:'success',
                            title:'Éxito',
                            text: 'Cajero eliminado correctamente',
                            timer: 2000
                        });
                    }
                },
                error: function(error){
                    console.log(error.responseText);
                    Vue.swal({
                        icon:'error',
                        title:'Error',
                        text: 'Error al eliminar el cajero'
                    });
                }
            });
        }
    </script>
    <div class="users-page container-fluid">
        <div class="users-heading">
            <div>
                <span class="eyebrow">Administración</span>
                <h1>Usuarios, roles y permisos</h1>
                <p>Gestiona el acceso y las responsabilidades de tu equipo desde un solo lugar.</p>
            </div>
            <div class="heading-actions">
                <a class="btn btn-light border" href="" onclick="seleccionarCajero(event)" data-toggle="modal" data-target="#modalCajeros"><i class="fas fa-user-check mr-2"></i>Secretarias/os</a>
                <a href="/usuarios/crear" class="btn btn-primary"><i class="fas fa-user-plus mr-2"></i>Nuevo usuario</a>
            </div>
        </div>

        <div class="users-stats">
            <div class="stat-item"><span class="stat-icon"><i class="fas fa-users"></i></span><div><strong>{{ $users->where('id', '<>', Auth::user()->id)->count() }}</strong><small>Usuarios registrados</small></div></div>
            <div class="stat-item"><span class="stat-icon success"><i class="fas fa-user-check"></i></span><div><strong>{{ $users->where('activo', 1)->where('id', '<>', Auth::user()->id)->count() }}</strong><small>Usuarios activos</small></div></div>
            <div class="stat-item"><span class="stat-icon roles"><i class="fas fa-user-shield"></i></span><div><strong>{{ $roles->count() }}</strong><small>Roles disponibles</small></div></div>
        </div>

        <div class="users-workspace row no-gutters">
            <aside class="col-lg-5 col-xl-4 users-sidebar">
                <div class="filter-header">
                    <div><h2>Equipo</h2><span><b id="usuariosVisibles">{{ $users->where('id', '<>', Auth::user()->id)->count() }}</b> resultados</span></div>
                    <button type="button" class="btn-clear" onclick="limpiarFiltros()" title="Limpiar filtros"><i class="fas fa-redo-alt"></i></button>
                </div>
                <div class="filters-row">
                    <div class="search-box"><i class="fas fa-search"></i><input id="buscarUsuario" type="search" placeholder="Buscar por nombre o correo..." oninput="filtrarUsuarios()" aria-label="Buscar usuario"></div>
                    <select id="filtroRol" class="custom-select" onchange="filtrarUsuarios()" aria-label="Filtrar por rol">
                        <option value="">Todos los roles</option>
                        @foreach($roles as $rol)
                            <option value="{{$rol->id}}">{{$rol->nombrerol}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="users-list" id="listaUsuarios">
                    @foreach($users as $user)
                        @if ($user->id !== Auth::user()->id ) 
                        <article class="user-card" data-user-id="{{$user->id}}" data-role="{{$user->role_id}}" data-search="{{ $user->name.' '.$user->email.' '.$user->rol->nombrerol }}" onclick="verUsuario({{$user->id}})" tabindex="0" onkeydown="if(event.key === 'Enter') verUsuario({{$user->id}})">
                            <div class="avatar-wrap">
                                <img src="{{url('usuarios/avatar/'.$user->image_path)}}" alt="Avatar de {{$user->name}}" class="user-avatar">
                                <span class="status-dot {{ $user->activo == 1 ? 'active' : 'inactive' }}" title="{{ $user->activo == 1 ? 'Activo' : 'Inactivo' }}"></span>
                            </div>
                            <div class="user-summary">
                                <div class="user-name">{{$user->name}}</div>
                                <div class="user-email">{{$user->email}}</div>
                                <span class="role-badge">{{$user->rol->nombrerol}}</span>
                            </div>
                            <a class="edit-user" onclick="event.stopPropagation()" href="{{url('usuarios/edit/'.$user->id)}}" title="Editar datos de {{$user->name}}"><i class="fas fa-pen"></i></a>
                        </article>
                        @endif
                    @endforeach
                    <div id="sinResultados" class="empty-results d-none"><i class="fas fa-search"></i><strong>No encontramos usuarios</strong><span>Prueba con otro nombre o rol.</span><button class="btn btn-link" onclick="limpiarFiltros()">Limpiar filtros</button></div>
                </div>
            </aside>

            <main class="col-lg-7 col-xl-8 users-detail">
                <div class="selection-actions">
                    <span>Acciones del usuario seleccionado</span>
                    <div>
                        <a class="btn btn-outline-success btn-sm disabled" id="btnActivar" href="" onclick="activarUsuario(event)"><i class="fas fa-check-circle mr-1"></i>Activar</a>
                        <a class="btn btn-outline-warning btn-sm disabled" id="btnDesactivar" href="" onclick="desactivarUsuario(event)"><i class="fas fa-pause-circle mr-1"></i>Desactivar</a>
                        <a class="btn btn-outline-danger btn-sm disabled" id="btnEliminar" href="" onclick="eliminarUsuario(event)"><i class="fas fa-trash-alt mr-1"></i>Eliminar</a>
                    </div>
                </div>
                <section class="detail-panel">
                            <ul id="tabs" class="nav nav-tabs">
                                <li class="nav-item"><a href="" data-target="#home1" data-toggle="tab" class="nav-link active"><i class="far fa-user mr-2"></i>Perfil</a></li>
                                <li class="nav-item"><a href="" data-target="#profile1" data-toggle="tab" class="nav-link"><i class="fas fa-key mr-2"></i>Permisos</a></li>
                            </ul>
                            <div id="tabsContent" class="tab-content">
                                <div id="home1" class="tab-pane fade active show">        
                                    <div id="rolesUser">
                                        <div class="detail-empty"><span><i class="far fa-address-card"></i></span><h3>Selecciona un usuario</h3><p>Elige una persona de la lista para revisar su perfil, modificar su rol o administrar sus permisos.</p></div>
                                    </div>
                                </div>
                                <div id="profile1" class="tab-pane fade">  
                                    <div id="listado-permisos">
                                        <div class="permissions-toolbar">
                                            <div><h3>Permisos asignados</h3><p>Accesos adicionales habilitados para este usuario.</p></div>
                                            <span class="permissions-count"><span id="permisosCantidad">0</span> activos</span>
                                        </div>
                                        <div id="tbody_permisos" class="permissions-list">
                                            <div class="permissions-empty"><i class="fas fa-user-lock"></i><strong>Selecciona un usuario</strong><div>Sus permisos aparecerán en este espacio.</div></div>
                                        </div>
                                        <div class="permissions-footer">
                                            <button type="button" id="btnAgregarPermiso" class="btn btn-primary disabled" disabled onclick="abrirModalPermiso()"><i class="fas fa-plus mr-2"></i>Agregar permisos</button>
                                        </div>
                                    </div>

                                    <div id="listado-permisos-detalle" class="permission-editor d-none">
                                        <div class="permission-editor-head">
                                            <h4><i class="fas fa-shield-alt mr-2"></i>Asignar nuevos permisos</h4>
                                            <button type="button" class="close" onclick="esconderModal()" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                        </div>
                                        <div class="permission-editor-body">
                                            <div class="row">
                                                <div class="col-md-4 permission-categories">
                                                        @foreach($permisos as $permiso)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="flexCheckPermisos" value="{{$permiso->id}}" id="permisoCategoria{{$permiso->id}}" onclick="cambiarDivPermisos()">
                                                            <label class="form-check-label" for="permisoCategoria{{$permiso->id}}">
                                                            {{$permiso->name}}
                                                            </label>
                                                        </div>
                                                        @endforeach
                                                </div>
                                                <div class="col-md-8">
                                                    <div id="panelPermisos"><div class="permissions-empty"><i class="fas fa-mouse-pointer"></i>Selecciona una categoría para ver sus permisos.</div></div>
                                                    <div class="permission-editor-actions">
                                                        <button type="button" class="btn btn-light border btn-sm" onclick="esconderModal()">Cancelar</button>
                                                        <button type="button" class="btn btn-success btn-sm" onclick="guardarPermisos()"><i class="fas fa-save mr-1"></i>Guardar permisos</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="messages1" class="tab-pane fade">
                                    <div class="list-group"><a href="" class="list-group-item d-inline-block"><span class="float-right badge badge-pill badge-dark">44</span> Message 1</a> <a href="" class="list-group-item d-inline-block"><span class="float-right badge badge-pill badge-dark">8</span> Message 2</a> <a href="" class="list-group-item d-inline-block"><span class="float-right badge badge-pill badge-dark">23</span> Message 3</a> <a href="" class="list-group-item d-inline-block text-muted">Message n..</a></div>
                                </div>
                            </div>
                </section>
            </main>
        </div>
    </div>
    

 

    <!-- Modal -->

    @foreach ($users as $user )

    <div class="modal fade" id="exampleModal{{$user->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

        <div class="modal-dialog" role="document">

          <div class="modal-content">

            <div class="modal-header">

              <h5 class="modal-title" id="exampleModalLabel">{{$user->name}}</h5>

              <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                <span aria-hidden="true">&times;</span>

              </button>

            </div>

            <form action="" method="post">

            <div class="modal-body">

              @foreach ($roles as $rol )

              <div class="form-check">

                <input class="form-check-input" type="radio" onchange="handleRoles({{$rol->id}})" name="flexRadioDefault" id="flexRadioDefault{{$rol->id}}" value="{{$rol->id}}">

                <label class="form-check-label" for="flexRadioDefault">

                    {{$rol->nombrerol}}

                </label>

              </div>

                  

              @endforeach

             

            </div>

            <div class="modal-footer">

              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>

              <button type="button" class="btn btn-primary" onclick="cambiarRolUser({{$user}})" data-dismiss="modal">Guardar cambios</button>

            </div>

        </form>

          </div>

        </div>

      </div>


    @endforeach

    <!-- Modal -->
<div class="modal fade" id="modalCajeros" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">IPS</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" >
          <div class="row">
            <div class="col-md-6" id="nombres-cajeros">

            </div>
            <div class="col-md-6">
                <div >
                    @if($cajeros->count() > 0)
                    <table class="table">
                        <thead>
                            <tr>
                              <th scope="col">Nombre</th>
                              <th scope="col"></th>
                            </tr>
                          </thead>
                          <tbody id="listado_cajeros_dIPSonibles">
                            
                                @foreach($cajeros as $c)
                                <tr>
                                    <td>{{$c->name}}</td>
                                    <td><button class="btn btn-danger btn-sm" onclick="eliminarCajeroDisponible({{$c->id_usuario}})">X</button></td>
                                </tr>
                                @endforeach
                            
                            
                          </tbody>
                    </table>
                    @else
                    <table class="table">
                        <thead>
                            <tr>
                              <th scope="col">Nombre</th>
                              <th scope="col"></th>
                            </tr>
                          </thead>
                          <tbody id="listado_cajeros_dIPSonibles">
                            <tr>
                                <td class="alert-danger">No hay cajeros</td>
                            </tr>
                            
                            
                          </tbody>
                    </table>
                    @endif
                    
                </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary btn-sm" onclick="guardarCajeros()">Guardar</button>
        </div>
      </div>
    </div>
  </div>

    <!--Dato de vital importancia -->
    <input type="hidden" name="usuario_id" id="usuario_id">

@endsection
