<div class="devoluciones-solicitudes">
    @if($solicitudes->count() > 0)
        <div class="devoluciones-solicitudes__encabezado">
            <h3 class="devoluciones-solicitudes__titulo">
                <i class="fas fa-undo-alt"></i>
                Solicitudes de devolución
            </h3>

            <span
                class="devoluciones-solicitudes__cantidad"
                title="Cantidad de solicitudes pendientes"
            >
                {{ $solicitudes->count() }}
            </span>
        </div>

        <div class="row">
            @foreach($solicitudes as $solicitud)
                <div class="col-sm-6 col-xl-4 mb-4">
                    <article class="solicitud-devolucion-card">
                        <header class="solicitud-devolucion-card__cabecera">
                            <div class="solicitud-devolucion-card__cabecera-superior">
                                <h5 class="solicitud-devolucion-card__titulo">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i>
                                    Solicitud<br>
                                    N° {{ $solicitud->num_nc }}
                                </h5>

                                <span class="solicitud-devolucion-card__estado">
                                    Pendiente
                                </span>
                            </div>
                        </header>

                        <div class="solicitud-devolucion-card__cuerpo">
                            <img
                                src="{{ asset('storage/imagenes/ips_logo.png') }}"
                                alt="Logo IPS"
                                class="solicitud-devolucion-card__logo"
                            >

                            <div class="solicitud-devolucion-card__dato">
                                <span class="solicitud-devolucion-card__etiqueta">
                                    N° documento
                                </span>

                                <strong class="solicitud-devolucion-card__valor">
                                    {{ $solicitud->num_nc }}
                                </strong>
                            </div>

                            <div class="solicitud-devolucion-card__dato">
                                <span class="solicitud-devolucion-card__etiqueta">
                                    Fecha de solicitud
                                </span>

                                <strong class="solicitud-devolucion-card__valor">
                                    {{ \Carbon\Carbon::parse($solicitud->created_at)->format('d-m-Y') }}
                                    <br>
                                    {{ \Carbon\Carbon::parse($solicitud->created_at)->format('H:i') }}
                                </strong>
                            </div>

                            <div class="solicitud-devolucion-card__dato">
                                <span class="solicitud-devolucion-card__etiqueta">
                                    Estado
                                </span>

                                <strong
                                    class="
                                        solicitud-devolucion-card__valor
                                        solicitud-devolucion-card__valor--pendiente
                                    "
                                >
                                    Pendiente de revisión
                                </strong>
                            </div>
                        </div>

                        <footer class="solicitud-devolucion-card__pie">
                            <button
                                type="button"
                                class="
                                    btn btn-primary
                                    solicitud-devolucion-card__boton
                                "
                                onclick='cargar_devolucion(@json($solicitud->num_nc))'
                            >
                                <i class="fas fa-search mr-1"></i>
                                Revisar
                            </button>
                        </footer>
                    </article>
                </div>
            @endforeach
        </div>
    @else
        <div class="devoluciones-solicitudes__vacio">
            <i class="fas fa-inbox"></i>

            <h5>No hay solicitudes pendientes</h5>

            <p class="mb-0">
                Todas las devoluciones de mercadería han sido procesadas.
            </p>
        </div>
    @endif
</div>
