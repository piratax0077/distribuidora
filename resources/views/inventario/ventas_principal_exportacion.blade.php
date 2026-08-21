@extends('plantillas.app')
@section('titulo', 'VENTAS')
@section('javascript')
<script>
    // Funciones JavaScript para el formulario
    function agregarBulto() {
        // Lógica para agregar bulto
    }

    function eliminarBulto() {
        // Lógica para eliminar bulto
    }

    function agregarProducto() {
        // Lógica para agregar producto
    }

    function eliminarProducto() {
        // Lógica para eliminar producto
    }
</script>
@endsection

@section('style')
<style>
    * {
        margin: 0px;
        padding: 0px;
    }

    .form-section {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        padding: 20px;
    }

    .section-title {
        background: #f8f9fa;
        color: #495057;
        font-weight: bold;
        margin: -20px -20px 15px -20px;
        padding: 10px 20px;
        border-radius: 8px 8px 0 0;
        border-bottom: 1px solid #dee2e6;
    }

    .table-container {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .btn-add {
        background: #007bff;
        border: none;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
    }

    .btn-remove {
        background: #dc3545;
        border: none;
        color: white;
        width: 25px;
        height: 25px;
        border-radius: 50%;
    }
</style>
@endsection

@section('contenido_titulo_pagina')
    @include('fragm.breadcrumb', ['titulo' => 'Ventas', 'titulo_modulo' => 'Exportación', 'ruta_modulo' => 'ventas/exportacion'])
@endsection

@section('contenido_ingresa_datos')
<div class="container-fluid">
    <form id="formExportacion" method="POST" action="">
        @csrf

        <!-- Datos Generales -->
        <div class="form-section">
            <div class="section-title">Datos generales</div>
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Factura de Exportación</label>
                    <select class="form-select form-select-sm" name="factura_exportacion">
                        <option value="">Seleccionar...</option>
                        <option value="1">Factura de Exportación</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Desde</label>
                    <input type="date" class="form-control form-control-sm" name="fecha_desde" value="2023-07-01">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hasta</label>
                    <input type="date" class="form-control form-control-sm" name="fecha_hasta" value="2023-07-01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Casa matriz Calle 123, Santaigel</label>
                    <select class="form-select form-select-sm" name="casa_matriz">
                        <option value="">Seleccionar...</option>
                        <option value="casa_matriz">Casa matriz Calle 123, Santaigel</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Moneda</label>
                    <select class="form-select form-select-sm" name="moneda">
                        <option value="USD">DÓLAR USA</option>
                        <option value="CLP">PESO CHILENO</option>
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <small class="text-muted">Junto que será nulo las condiciones del pago del D.T.E (opcional)</small>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-3">
                    <label class="form-label">Tipo de documento referenciado</label>
                    <select class="form-select form-select-sm" name="tipo_documento_ref">
                        <option value="">Seleccionar...</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha del documento referenciado</label>
                    <input type="date" class="form-control form-control-sm" name="fecha_doc_ref">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado documento</label>
                    <select class="form-select form-select-sm" name="estado_documento">
                        <option value="">Seleccionar...</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Razón de la referencia</label>
                    <input type="text" class="form-control form-control-sm" name="razon_referencia">
                </div>
            </div>
        </div>

        <!-- Receptor -->
        <div class="form-section">
            <div class="section-title">Receptor</div>
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Código interno (opcional)</label>
                    <input type="text" class="form-control form-control-sm" name="codigo_interno">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nombre del receptor</label>
                    <input type="text" class="form-control form-control-sm" name="nombre_receptor">
                </div>
                <div class="col-md-3">
                    <label class="form-label">ID extranjero (opcional)</label>
                    <input type="text" class="form-control form-control-sm" name="id_extranjero">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Receptor (opcional)</label>
                    <input type="text" class="form-control form-control-sm" name="receptor">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-3">
                    <label class="form-label">Giro o actividad (opcional)</label>
                    <input type="text" class="form-control form-control-sm" name="giro_actividad">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Dirección del receptor</label>
                    <input type="text" class="form-control form-control-sm" name="direccion_receptor">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Comuna o localidad (opcional)</label>
                    <input type="text" class="form-control form-control-sm" name="comuna_localidad">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ciudad del receptor</label>
                    <input type="text" class="form-control form-control-sm" name="ciudad_receptor">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-3">
                    <label class="form-label">Nacionalidad</label>
                    <select class="form-select form-select-sm" name="nacionalidad">
                        <option value="">Seleccionar...</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Email del receptor (opcional)</label>
                    <input type="email" class="form-control form-control-sm" name="email_receptor">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Teléfono del receptor (opcional)</label>
                    <input type="text" class="form-control form-control-sm" name="telefono_receptor">
                </div>
            </div>
        </div>

        <!-- Sección de Transporte y Envío -->
        <div class="row">
            <!-- Forma de pago y venta -->
            <div class="col-md-2">
                <div class="form-section h-100">
                    <div class="section-title">Forma de pago y venta</div>
                    <div class="mb-3">
                        <label class="form-label">¿Forma de pago?</label>
                        <select class="form-select form-select-sm" name="forma_pago">
                            <option value="">Seleccionar...</option>
                            <option value="contado">Contado</option>
                            <option value="credito">Crédito</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">A firme</label>
                        <select class="form-select form-select-sm" name="a_firme">
                            <option value="">Seleccionar...</option>
                            <option value="si">Sí</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">¿Clausula de venta?</label>
                        <select class="form-select form-select-sm" name="clausula_venta">
                            <option value="">Seleccionar...</option>
                            <option value="fob">FOB</option>
                            <option value="cif">CIF</option>
                            <option value="exw">EXW</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total clausula de venta</label>
                        <input type="text" class="form-control form-control-sm" name="total_clausula" readonly>
                    </div>
                </div>
            </div>

            <!-- Transporte -->
            <div class="col-md-2">
                <div class="form-section h-100">
                    <div class="section-title">Transporte</div>
                    <div class="mb-3">
                        <label class="form-label">¿Transporte?</label>
                        <select class="form-select form-select-sm" name="transporte">
                            <option value="">Seleccionar...</option>
                            <option value="aereo">Aéreo</option>
                            <option value="maritimo">Marítimo</option>
                            <option value="terrestre">Terrestre</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre del transportista</label>
                        <input type="text" class="form-control form-control-sm" name="nombre_transportista">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">RUT compañía de transporte</label>
                        <input type="text" class="form-control form-control-sm" name="rut_transporte">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre compañía de transporte</label>
                        <input type="text" class="form-control form-control-sm" name="nombre_compania_transporte">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Info adicional transporte</label>
                        <textarea class="form-control form-control-sm" name="info_adicional_transporte" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">N° reserva operador</label>
                        <input type="text" class="form-control form-control-sm" name="numero_reserva">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Operador</label>
                        <input type="text" class="form-control form-control-sm" name="operador">
                    </div>
                </div>
            </div>

            <!-- Puerto origen y destino -->
            <div class="col-md-2">
                <div class="form-section h-100">
                    <div class="section-title">Puerto origen y destino</div>
                    <div class="mb-3">
                        <label class="form-label">¿Puerto de embarque?</label>
                        <select class="form-select form-select-sm" name="puerto_embarque">
                            <option value="">Seleccionar...</option>
                            <option value="valparaiso">Valparaíso</option>
                            <option value="san_antonio">San Antonio</option>
                            <option value="arica">Arica</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Info extra del puerto de embarque</label>
                        <textarea class="form-control form-control-sm" name="info_puerto_embarque" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">¿Puerto desembarque?</label>
                        <select class="form-select form-select-sm" name="puerto_desembarque">
                            <option value="">Seleccionar...</option>
                            <option value="buenos_aires">Buenos Aires</option>
                            <option value="santos">Santos</option>
                            <option value="callao">Callao</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Info extra del puerto de desembarque</label>
                        <textarea class="form-control form-control-sm" name="info_puerto_desembarque" rows="2"></textarea>
                    </div>
                </div>
            </div>

            <!-- Precio de envío -->
            <div class="col-md-3">
                <div class="form-section h-100">
                    <div class="section-title">Precio de envío</div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Tara</label>
                            <input type="text" class="form-control form-control-sm" name="tara">
                        </div>
                        <div class="col-6">
                            <label class="form-label">¿Unidad Tara?</label>
                            <select class="form-select form-select-sm" name="unidad_tara">
                                <option value="kg">KG</option>
                                <option value="ton">TON</option>
                                <option value="lb">LB</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Peso Bruto</label>
                            <input type="text" class="form-control form-control-sm" name="peso_bruto">
                        </div>
                        <div class="col-6">
                            <label class="form-label">¿Unidad Bruto?</label>
                            <select class="form-select form-select-sm" name="unidad_bruto">
                                <option value="kg">KG</option>
                                <option value="ton">TON</option>
                                <option value="lb">LB</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Peso neto</label>
                            <input type="text" class="form-control form-control-sm" name="peso_neto">
                        </div>
                        <div class="col-6">
                            <label class="form-label">¿Unidad Neto?</label>
                            <select class="form-select form-select-sm" name="unidad_neto">
                                <option value="kg">KG</option>
                                <option value="ton">TON</option>
                                <option value="lb">LB</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Otros datos envío -->
            <div class="col-md-3">
                <div class="form-section h-100">
                    <div class="section-title">Otros datos envío</div>
                    <div class="mb-3">
                        <label class="form-label">¿País del receptor extranjero?</label>
                        <select class="form-select form-select-sm" name="pais_receptor">
                            <option value="">Seleccionar...</option>
                            <option value="argentina">Argentina</option>
                            <option value="brasil">Brasil</option>
                            <option value="peru">Perú</option>
                            <option value="colombia">Colombia</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">¿País de destino de la mercadería?</label>
                        <select class="form-select form-select-sm" name="pais_destino">
                            <option value="">Seleccionar...</option>
                            <option value="argentina">Argentina</option>
                            <option value="brasil">Brasil</option>
                            <option value="peru">Perú</option>
                            <option value="colombia">Colombia</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monto flete</label>
                        <input type="text" class="form-control form-control-sm" name="monto_flete">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monto seguro</label>
                        <input type="text" class="form-control form-control-sm" name="monto_seguro">
                    </div>
                </div>
            </div>
        </div>
        <!-- SECCIÓN BULTOS -->

        <div class="form-section">
            <div class="section-title">Bultos</div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Tipo de bulto</th>
                            <th>Cantidad</th>
                            <th>Marcas</th>
                            <th>ID contenedor</th>
                            <th>Sello</th>
                            <th>Emisor sello</th>
                            <th width="50px"></th>
                        </tr>
                    </thead>
                    <tbody id="bultos-container">
                        <!-- Aquí se agregarán dinámicamente los bultos -->
                    </tbody>
                </table>
                <div class="text-end">
                    <button type="button" class="btn-add" onclick="agregarBulto()">+</button>
                </div>
            </div>
        </div>

        <!-- SECCIÓN DETALLE DE PRODUCTOS -->
        <div class="form-section">
            <div class="section-title">Detalle de productos</div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Detalle</th>
                            <th>Elaboración</th>
                            <th>Vencimiento</th>
                            <th>Cant.</th>
                            <th>Unidad</th>
                            <th>P. Unitario</th>
                            <th>Desc.</th>
                            <th>% / $</th>
                            <th>Subtotal</th>
                            <th width="70px"></th>
                        </tr>
                    </thead>
                    <tbody id="productos-container">
                        <tr>
                            <td><input type="text" class="form-control form-control-sm"></td>
                            <td><input type="text" class="form-control form-control-sm"></td>
                            <td><input type="text" class="form-control form-control-sm"></td>
                            <td><input type="text" class="form-control form-control-sm"></td>
                            <td><input type="text" class="form-control form-control-sm"></td>
                            <td><input type="number" class="form-control form-control-sm" value="1"></td>
                            <td><input type="text" class="form-control form-control-sm"></td>
                            <td><input type="text" class="form-control form-control-sm"></td>
                            <td><input type="text" class="form-control form-control-sm" value="0"></td>
                            <td>
                                <select class="form-select form-select-sm">
                                    <option value="%">%</option>
                                    <option value="$">$</option>
                                </select>
                            </td>
                            <td><input type="text" class="form-control form-control-sm" value="0" readonly></td>
                            <td>
                                <button type="button" class="btn-add" onclick="agregarProducto()">+</button>
                                <button type="button" class="btn-remove" onclick="eliminarProducto(this)">-</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECCIÓN DOCUMENTOS DE REFERENCIA -->
        <div class="form-section">
            <div class="section-title">Documentos de referencia</div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <tbody>
                        <tr>
                            <td width="15%">Orden de compra</td>
                            <td width="18%"><input type="text" class="form-control form-control-sm"></td>
                            <td width="15%">Fecha</td>
                            <td width="18%"><input type="date" class="form-control form-control-sm"></td>
                            <td width="15%">Contrato</td>
                            <td width="18%"><input type="text" class="form-control form-control-sm"></td>
                            <td width="15%">Fecha</td>
                            <td width="18%"><input type="date" class="form-control form-control-sm"></td>
                        </tr>
                        <tr>
                            <td>DUS</td>
                            <td><input type="text" class="form-control form-control-sm"></td>
                            <td>Fecha</td>
                            <td><input type="date" class="form-control form-control-sm"></td>
                            <td>AWB (Air Will Bill)</td>
                            <td><input type="text" class="form-control form-control-sm"></td>
                            <td>Fecha</td>
                            <td><input type="date" class="form-control form-control-sm"></td>
                        </tr>
                        <tr>
                            <td>MIC/DTA</td>
                            <td><input type="text" class="form-control form-control-sm"></td>
                            <td>Fecha</td>
                            <td><input type="date" class="form-control form-control-sm"></td>
                            <td>Resolución del SNA</td>
                            <td><input type="text" class="form-control form-control-sm"></td>
                            <td>Fecha</td>
                            <td><input type="date" class="form-control form-control-sm"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECCIÓN TOTALES -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="form-section h-100">
                    <label>Subtotal</label>
                    <input type="text" class="form-control form-control-sm text-end" value="0" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-section h-100">
                    <label>Recargo global</label>
                    <input type="text" class="form-control form-control-sm text-end" value="0" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-section h-100">
                    <label>Descuento global</label>
                    <input type="text" class="form-control form-control-sm text-end" value="0" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-section h-100">
                    <label>Total</label>
                    <input type="text" class="form-control form-control-sm text-end" value="0" readonly>
                </div>
            </div>
        </div>

        <!-- BOTONES DE ACCIÓN -->
        <div class="row mb-4">
            <div class="col-md-4">
                <button type="button" class="btn btn-danger w-100">Cancelar</button>
            </div>
            <div class="col-md-4">
                <button type="button" class="btn btn-primary w-100">Cobrar</button>
            </div>
            <div class="col-md-4">
                <button type="button" class="btn btn-success w-100">Generar DTE</button>
            </div>
        </div>
    </form>
</div>
@endsection
