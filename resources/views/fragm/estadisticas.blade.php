@php
    $chartColors = [
        '#4F46E5','#10B981','#F59E0B','#EF4444','#3B82F6',
        '#8B5CF6','#EC4899','#14B8A6','#F97316','#6366F1',
        '#22C55E','#EAB308','#06B6D4','#A855F7','#F43F5E',
        '#0EA5E9','#84CC16','#D946EF','#FB923C','#64748B',
        '#2DD4BF','#818CF8','#FB7185','#34D399','#FBBF24',
        '#60A5FA','#C084FC','#F472B6','#4ADE80','#A78BFA',
    ];

    $fmt = fn($v) => number_format((float)($v ?? 0), 0, ',', '.');

    $totalBoletas       = $total_boletas ?? $mejores_vendedores_boleta->sum('total_pagos');
    $totalFacturas      = $total_facturas ?? $mejores_vendedores_factura->sum('total_pagos_con_descuento');
    $totalNC            = $total_notas_credito ?? 0;
    $totalVendidoBruto  = $total_vendido_bruto ?? ($totalBoletas + $totalFacturas + $totalNC);
    $totalGeneral       = $total_general ?? ($totalBoletas + $totalFacturas - $totalNC);

    $cantBoletas        = $mejores_vendedores_boleta->sum('total');
    $cantFacturas       = $mejores_vendedores_factura->sum('total');
    $cantDte            = $cantBoletas + $cantFacturas;
    $diasConVentas      = $dias_mas_vendidos->count();

    $ticketPromedio     = $cantDte > 0 ? ($totalGeneral / $cantDte) : 0;
    $promedioDiario     = $diasConVentas > 0 ? ($totalGeneral / $diasConVentas) : 0;
    $porcentajeNC       = $totalVendidoBruto > 0 ? (($totalNC / $totalVendidoBruto) * 100) : 0;

    $mejorCliente       = $mejores_clientes->first();
    $mejorVendedor      = $ranking_vendedores->first();
    $mejorRepuesto      = $rep_mas_vendidos->first();
    $mejorDia           = $dias_mas_vendidos->first();
@endphp

<style>
    .dashboard-wrap { width:100%; }
    .exec-hero {
        background: linear-gradient(135deg, #1e293b 0%, #334155 55%, #475569 100%);
        color: #fff;
        border-radius: 18px;
        padding: 24px;
        margin-bottom: 22px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .18);
    }
    .exec-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .08em; opacity: .8; font-weight: 800; }
    .exec-value { font-size: 2.15rem; font-weight: 900; line-height: 1.1; }
    .exec-sub { opacity: .85; font-size: .9rem; }
    .metric-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 18px;
        height: 100%;
        box-shadow: 0 6px 18px rgba(15, 23, 42, .07);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .metric-card:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(15, 23, 42, .12); }
    .metric-title { font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; color: #64748b; font-weight: 800; margin-bottom: 8px; }
    .metric-value { font-size: 1.45rem; font-weight: 900; color: #0f172a; line-height: 1.2; }
    .metric-desc { color: #64748b; font-size: .86rem; margin-top: 6px; }
    .metric-icon {
        width: 40px; height: 40px; border-radius: 12px; display: inline-flex;
        align-items: center; justify-content: center; margin-bottom: 10px; font-size: 1.05rem;
        background: #eef2ff; color: #4f46e5;
    }
    .metric-success .metric-icon { background:#ecfdf5; color:#10b981; }
    .metric-warning .metric-icon { background:#fffbeb; color:#f59e0b; }
    .metric-danger .metric-icon { background:#fef2f2; color:#ef4444; }
    .metric-info .metric-icon { background:#eff6ff; color:#3b82f6; }
    .section-title {
        display:flex; align-items:center; justify-content:space-between;
        font-weight: 900; color:#0f172a; font-size: 1rem; margin: 20px 0 12px;
    }
    .section-title small { font-weight: 600; color:#64748b; }
    .panel-card {
        background:#fff; border:1px solid #e2e8f0; border-radius:16px;
        padding:18px; box-shadow:0 6px 18px rgba(15,23,42,.06); height:100%;
    }
    .table-modern { margin-bottom:0; }
    .table-modern thead th { background:#0f172a; color:#fff; border:none; font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; }
    .table-modern td { vertical-align:middle; }
    .rank-badge { display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:50%; font-size:11px; font-weight:900; }
    .rank-1 { background:#FFD700; color:#333; }
    .rank-2 { background:#C0C0C0; color:#333; }
    .rank-3 { background:#CD7F32; color:#fff; }
    .rank-rest { background:#e2e8f0; color:#475569; }
    .tabla-scroll-y-400 { max-height:400px; overflow-y:auto; }
    .negative-money { color:#dc2626; font-weight:800; }
    .positive-money { color:#059669; font-weight:900; }
    .muted-note { font-size:.82rem; color:#64748b; }
    .summary-table td { padding:.55rem .75rem; }
    .summary-table tr:last-child td { border-top:2px solid #0f172a; font-size:1.05rem; }
    .chart-box { min-height: 260px; }

    .panel-card .table-modern thead th {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
    color: #ffffff !important;
    border: none !important;

    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.045em;

    padding: 12px 10px;
    vertical-align: middle;

    position: sticky;
    top: 0;
    z-index: 5;

    white-space: nowrap;
}

.panel-card .table-modern thead th:first-child {
    border-top-left-radius: 8px;
    border-bottom-left-radius: 8px;
}

.panel-card .table-modern thead th:last-child {
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
}
</style>


@php
    /*
     * Los charts llegan desde el backend como strings JSON.
     * Los convertimos a arrays PHP antes de imprimirlos en JS para evitar
     * variables undefined o JSON inválido dentro de la vista cargada por AJAX.
     */
    $emptyChartArray = ['labels' => [], 'data' => []];

    $decodeChart = function ($json) use ($emptyChartArray) {
        if (!isset($json) || trim((string)$json) === '') {
            return $emptyChartArray;
        }

        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            return $emptyChartArray;
        }

        if (!isset($decoded['labels']) || !is_array($decoded['labels'])) {
            $decoded['labels'] = [];
        }

        if (!isset($decoded['data']) || !is_array($decoded['data'])) {
            $decoded['data'] = [];
        }

        return [
            'labels' => array_values($decoded['labels']),
            'data'   => array_values($decoded['data']),
        ];
    };

    $chart_repuestos_arr            = $decodeChart($chart_repuestos ?? null);
    $chart_clientes_arr             = $decodeChart($chart_clientes ?? null);
    $chart_vendedores_boleta_arr    = $decodeChart($chart_vendedores_boleta ?? null);
    $chart_vendedores_factura_arr   = $decodeChart($chart_vendedores_factura ?? null);
    $chart_ranking_vendedores_arr   = $decodeChart($chart_ranking_vendedores ?? null);
    $chart_dias_arr                 = $decodeChart($chart_dias ?? null);
    $chart_composicion_arr          = $decodeChart($chart_composicion ?? null);
@endphp


<div class="dashboard-wrap fade-in">

    {{-- Resumen Ejecutivo --}}
    <div class="exec-hero">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-3 mb-lg-0">
                <div class="exec-label">Resumen ejecutivo del período</div>
                <div class="exec-value">${{ $fmt($totalGeneral) }}</div>
                <div class="exec-sub">Venta neta = boletas + facturas - notas de crédito emitidas en el período.</div>
            </div>
            <div class="col-lg-5">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="exec-label">Bruto</div>
                        <div class="h5 font-weight-bold mb-0">${{ $fmt($totalVendidoBruto) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="exec-label">NC</div>
                        <div class="h5 font-weight-bold mb-0 text-warning">-${{ $fmt($totalNC) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="exec-label">DTE</div>
                        <div class="h5 font-weight-bold mb-0">{{ $fmt($cantDte) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KPIs financieros --}}
    <div class="row mb-4">
        <div class="col-6 col-lg-3 mb-3">
            <div class="metric-card metric-info">
                <div class="metric-icon"><i class="fas fa-receipt"></i></div>
                <div class="metric-title">Total boletas</div>
                <div class="metric-value">${{ $fmt($totalBoletas) }}</div>
                <div class="metric-desc">{{ $fmt($cantBoletas) }} documentos</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="metric-card metric-success">
                <div class="metric-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div class="metric-title">Total facturas</div>
                <div class="metric-value">${{ $fmt($totalFacturas) }}</div>
                <div class="metric-desc">{{ $fmt($cantFacturas) }} documentos</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="metric-card metric-danger">
                <div class="metric-icon"><i class="fas fa-undo-alt"></i></div>
                <div class="metric-title">Notas de crédito</div>
                <div class="metric-value negative-money">-${{ $fmt($totalNC) }}</div>
                <div class="metric-desc">{{ number_format($porcentajeNC, 2, ',', '.') }}% de la venta bruta</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="metric-card metric-warning">
                <div class="metric-icon"><i class="fas fa-chart-line"></i></div>
                <div class="metric-title">Promedio diario</div>
                <div class="metric-value">${{ $fmt($promedioDiario) }}</div>
                <div class="metric-desc">{{ $fmt($diasConVentas) }} días con movimiento</div>
            </div>
        </div>
    </div>

    {{-- Indicadores destacados --}}
    <div class="section-title">
        <span><i class="fas fa-star text-warning mr-2"></i>Indicadores destacados</span>
        <small>Top 1 de cada ranking</small>
    </div>
    <div class="row mb-4">
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="metric-card">
                <div class="metric-title">Mejor cliente</div>
                <div class="metric-value">{{ $mejorCliente ? (trim($mejorCliente->nombres.' '.$mejorCliente->apellidos)) : 'Sin datos' }}</div>
                <div class="metric-desc">{{ $mejorCliente ? '$'.$fmt($mejorCliente->total_pesos).' · '.$fmt($mejorCliente->total).' DTE' : 'No hay clientes en el período' }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="metric-card">
                <div class="metric-title">Mejor vendedor</div>
                <div class="metric-value">{{ $mejorVendedor->nombres ?? 'Sin datos' }}</div>
                <div class="metric-desc">{{ $mejorVendedor ? '$'.$fmt($mejorVendedor->total_ventas) : 'No hay ventas en el período' }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="metric-card">
                <div class="metric-title">Repuesto líder</div>
                <div class="metric-value">{{ $mejorRepuesto->codigo_interno ?? 'Sin datos' }}</div>
                <div class="metric-desc">{{ $mejorRepuesto ? $mejorRepuesto->descripcion.' · '.$fmt($mejorRepuesto->cantidad).' uds.' : 'No hay productos vendidos' }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="metric-card">
                <div class="metric-title">Mejor día</div>
                <div class="metric-value">{{ $mejorDia->fecha ?? 'Sin datos' }}</div>
                <div class="metric-desc">{{ $mejorDia ? '$'.$fmt($mejorDia->total_pago).' · '.$fmt($mejorDia->total).' pagos' : 'No hay días con ventas' }}</div>
            </div>
        </div>
    </div>

    {{-- Resumen financiero y composición --}}
    <div class="section-title">
        <span><i class="fas fa-calculator mr-2"></i>Resumen financiero</span>
        <small>Detalle para cuadratura</small>
    </div>
    <div class="row mb-4">
        <div class="col-lg-5 mb-3">
            <div class="panel-card">
                <table class="table table-sm summary-table">
                    <tbody>
                        <tr><td>Total boletas</td><td class="text-right">${{ $fmt($totalBoletas) }}</td></tr>
                        <tr><td>Total facturas</td><td class="text-right">${{ $fmt($totalFacturas) }}</td></tr>
                        <tr class="font-weight-bold"><td>Venta bruta</td><td class="text-right">${{ $fmt($totalVendidoBruto) }}</td></tr>
                        <tr><td>Notas de crédito</td><td class="text-right negative-money">-${{ $fmt($totalNC) }}</td></tr>
                        <tr class="font-weight-bold"><td>Total general neto</td><td class="text-right positive-money">${{ $fmt($totalGeneral) }}</td></tr>
                        <tr><td>Ticket promedio</td><td class="text-right">${{ $fmt($ticketPromedio) }}</td></tr>
                    </tbody>
                </table>
                <div class="muted-note mt-2">Las notas de crédito se descuentan según su fecha de emisión dentro del período seleccionado.</div>
            </div>
        </div>
        <div class="col-lg-7 mb-3">
            <div class="panel-card chart-box">
                <div class="font-weight-bold mb-2">Composición del período</div>
                <canvas id="grafico_composicion" height="95"></canvas>
            </div>
        </div>
    </div>

    {{-- Gráficos principales --}}
    <div class="section-title">
        <span><i class="fas fa-chart-bar mr-2"></i>Gráficos principales</span>
        <small>Vista ejecutiva</small>
    </div>
    <div class="row mb-4">
        <div class="col-lg-12 mb-3">
            <div class="panel-card chart-box">
                <div class="font-weight-bold mb-2">Ventas por día</div>
                <canvas id="grafico7" height="70"></canvas>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="panel-card chart-box">
                <div class="font-weight-bold mb-2">Mejores clientes</div>
                <canvas id="grafico3" height="130"></canvas>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="panel-card chart-box">
                <div class="font-weight-bold mb-2">Ranking vendedores</div>
                <canvas id="grafico_ranking_vendedores" height="130"></canvas>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="panel-card chart-box">
                <div class="font-weight-bold mb-2">Repuestos más vendidos</div>
                <canvas id="grafico" height="130"></canvas>
            </div>
        </div>
        <div class="col-lg-3 mb-3">
            <div class="panel-card chart-box">
                <div class="font-weight-bold mb-2">Vendedores boleta</div>
                <canvas id="grafico9" height="190"></canvas>
            </div>
        </div>
        <div class="col-lg-3 mb-3">
            <div class="panel-card chart-box">
                <div class="font-weight-bold mb-2">Vendedores factura</div>
                <canvas id="grafico10" height="190"></canvas>
            </div>
        </div>
    </div>

    {{-- Tablas de detalle --}}
    <div class="section-title">
        <span><i class="fas fa-table mr-2"></i>Detalle de rankings</span>
        <small>Top 30 por categoría</small>
    </div>

    <div class="row w-100 mx-0">
        <div class="col-lg-6 mb-4">
            <div class="panel-card tabla-scroll-y-400 letra_pequeña">
                <div class="font-weight-bold mb-2">Mejores clientes</div>
                <table class="table table-striped table-sm table-modern">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Rut</th>
                            <th>Nombre / Razón social</th>
                            <th class="text-right">DTE</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mejores_clientes as $i => $c)
                            @php $pct = $totalGeneral > 0 ? (($c->total_pesos / $totalGeneral) * 100) : 0; @endphp
                            <tr>
                                <td><span class="rank-badge {{ $i===0?'rank-1':($i===1?'rank-2':($i===2?'rank-3':'rank-rest')) }}">{{ $i+1 }}</span></td>
                                <td>{{ $c->rut }}</td>
                                <td>{{ trim($c->nombres.' '.$c->apellidos) }}</td>
                                <td class="text-right">{{ $fmt($c->total) }}</td>
                                <td class="text-right">${{ $fmt($c->total_pesos) }}</td>
                                <td class="text-right">{{ number_format($pct, 1, ',', '.') }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary font-weight-bold">
                            <td colspan="3" class="text-right">Total mostrado:</td>
                            <td class="text-right">{{ $fmt($mejores_clientes->sum('total')) }}</td>
                            <td class="text-right">${{ $fmt($mejores_clientes->sum('total_pesos')) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="panel-card tabla-scroll-y-400 letra_pequeña">
                <div class="font-weight-bold mb-2">Repuestos más vendidos</div>
                <table class="table table-striped table-sm table-modern">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th class="text-right">Unidades</th>
                            <th class="text-right">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rep_mas_vendidos as $i => $r)
                            @php $pct = $rep_mas_vendidos->sum('cantidad') > 0 ? (($r->cantidad / $rep_mas_vendidos->sum('cantidad')) * 100) : 0; @endphp
                            <tr>
                                <td><span class="rank-badge {{ $i===0?'rank-1':($i===1?'rank-2':($i===2?'rank-3':'rank-rest')) }}">{{ $i+1 }}</span></td>
                                <td>{{ $r->codigo_interno }}</td>
                                <td>{{ $r->descripcion }}</td>
                                <td class="text-right">{{ $fmt($r->cantidad) }}</td>
                                <td class="text-right">{{ number_format($pct, 1, ',', '.') }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary font-weight-bold">
                            <td colspan="3" class="text-right">Total mostrado:</td>
                            <td class="text-right">{{ $fmt($rep_mas_vendidos->sum('cantidad')) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="col-12 mb-4">
            <div class="panel-card tabla-scroll-y-400 letra_pequeña">
                <div class="font-weight-bold mb-2">Ranking de vendedores <small class="text-muted">Boleta + Factura neto NC</small></div>
                <table class="table table-striped table-sm table-modern">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vendedor</th>
                            <th class="text-right">Boleta</th>
                            <th class="text-right">Factura</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ranking_vendedores as $i => $r)
                            @php $pct = $totalGeneral > 0 ? (($r->total_ventas / $totalGeneral) * 100) : 0; @endphp
                            <tr>
                                <td><span class="rank-badge {{ $i===0?'rank-1':($i===1?'rank-2':($i===2?'rank-3':'rank-rest')) }}">{{ $i+1 }}</span></td>
                                <td>{{ $r->nombres }}</td>
                                <td class="text-right">${{ $fmt($r->total_boleta) }}</td>
                                <td class="text-right">${{ $fmt($r->total_factura) }}</td>
                                <td class="text-right font-weight-bold">${{ $fmt($r->total_ventas) }}</td>
                                <td class="text-right">{{ number_format($pct, 1, ',', '.') }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary font-weight-bold">
                            <td colspan="2" class="text-right">Total mostrado:</td>
                            <td class="text-right">${{ $fmt($ranking_vendedores->sum('total_boleta')) }}</td>
                            <td class="text-right">${{ $fmt($ranking_vendedores->sum('total_factura')) }}</td>
                            <td class="text-right">${{ $fmt($ranking_vendedores->sum('total_ventas')) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="panel-card tabla-scroll-y-400 letra_pequeña">
                <div class="font-weight-bold mb-2">Vendedores — Boleta</div>
                <table class="table table-striped table-sm table-modern">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vendedor</th>
                            <th class="text-right">N°</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mejores_vendedores_boleta as $i => $v)
                            <tr>
                                <td><span class="rank-badge {{ $i===0?'rank-1':($i===1?'rank-2':($i===2?'rank-3':'rank-rest')) }}">{{ $i+1 }}</span></td>
                                <td>{{ $v->nombres }}</td>
                                <td class="text-right">{{ $fmt($v->total) }}</td>
                                <td class="text-right">${{ $fmt($v->total_pagos) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary font-weight-bold">
                            <td colspan="2" class="text-right">Total mostrado:</td>
                            <td class="text-right">{{ $fmt($mejores_vendedores_boleta->sum('total')) }}</td>
                            <td class="text-right">${{ $fmt($mejores_vendedores_boleta->sum('total_pagos')) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="panel-card tabla-scroll-y-400 letra_pequeña">
                <div class="font-weight-bold mb-2">Vendedores — Factura <small class="text-muted">neto NC</small></div>
                <table class="table table-striped table-sm table-modern">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vendedor</th>
                            <th class="text-right">N°</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mejores_vendedores_factura as $i => $vf)
                            <tr>
                                <td><span class="rank-badge {{ $i===0?'rank-1':($i===1?'rank-2':($i===2?'rank-3':'rank-rest')) }}">{{ $i+1 }}</span></td>
                                <td>{{ $vf->nombres }}</td>
                                <td class="text-right">{{ $fmt($vf->total) }}</td>
                                <td class="text-right">${{ $fmt($vf->total_pagos_con_descuento) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary font-weight-bold">
                            <td colspan="2" class="text-right">Total mostrado:</td>
                            <td class="text-right">{{ $fmt($mejores_vendedores_factura->sum('total')) }}</td>
                            <td class="text-right">${{ $fmt($mejores_vendedores_factura->sum('total_pagos_con_descuento')) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="panel-card tabla-scroll-y-400 letra_pequeña">
                <div class="font-weight-bold mb-2">Días con más ventas <small class="text-muted">neto NC por fecha de emisión</small></div>
                <table class="table table-striped table-sm table-modern">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th class="text-right">N° Pagos</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dias_mas_vendidos as $i => $d)
                            <tr>
                                <td><span class="rank-badge {{ $i===0?'rank-1':($i===1?'rank-2':($i===2?'rank-3':'rank-rest')) }}">{{ $i+1 }}</span></td>
                                <td>{{ $d->fecha }}</td>
                                <td class="text-right">{{ $fmt($d->total) }}</td>
                                <td class="text-right">${{ $fmt($d->total_pago) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary font-weight-bold">
                            <td colspan="3" class="text-right">Total acumulado:</td>
                            <td class="text-right">${{ $fmt($dias_mas_vendidos->sum('total_pago')) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>


<script>
(function () {
    var colors = {!! json_encode($chartColors) !!};

    window.__estadisticasCharts = window.__estadisticasCharts || {};

    function fmtPeso(v) {
        return '$' + Number(v || 0).toLocaleString('es-CL', { maximumFractionDigits: 0 });
    }

    function normalizeChart(chart) {
        if (!chart || typeof chart !== 'object') {
            return { labels: [], data: [] };
        }

        var labels = Array.isArray(chart.labels) ? chart.labels : [];
        var data = Array.isArray(chart.data) ? chart.data : [];

        labels = labels.map(function (label, index) {
            if (label === null || typeof label === 'undefined' || String(label).trim() === '') {
                return 'Sin dato ' + (index + 1);
            }
            return String(label);
        });

        data = data.map(function (value) {
            var n = Number(value);
            return isNaN(n) ? 0 : n;
        });

        return { labels: labels, data: data };
    }

    function getTooltipValue(context, type) {
        if (type === 'doughnut') {
            if (typeof context.parsed === 'number') return context.parsed;
            if (context.raw !== undefined) return context.raw;
        }

        if (context.parsed && typeof context.parsed.y !== 'undefined') {
            return context.parsed.y;
        }

        if (context.yLabel !== undefined) {
            return context.yLabel;
        }

        if (context.raw !== undefined) {
            return context.raw;
        }

        return 0;
    }

    function destroyChart(id) {
        if (window.__estadisticasCharts[id]) {
            try {
                window.__estadisticasCharts[id].destroy();
            } catch (e) {}
            delete window.__estadisticasCharts[id];
        }

        if (window.Chart && typeof Chart.getChart === 'function') {
            var oldChart = Chart.getChart(id);
            if (oldChart) oldChart.destroy();
        }
    }

    function makeChart(id, label, chartInfo, moneda, type) {
        var canvas = document.getElementById(id);
        if (!canvas || !window.Chart) return;

        chartInfo = normalizeChart(chartInfo);

        if (!chartInfo.labels.length || !chartInfo.data.length) {
            console.warn('Sin datos para gráfico:', id);
            return;
        }

        destroyChart(id);

        var chartType = type || 'bar';
        var config = {
            type: chartType,
            data: {
                labels: chartInfo.labels,
                datasets: [{
                    label: label,
                    data: chartInfo.data,
                    backgroundColor: colors.slice(0, Math.max(chartInfo.data.length, 1))
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                legend: {
                    display: chartType === 'doughnut',
                    position: 'bottom'
                },
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, data) {
                            var value = chartType === 'doughnut'
                                ? data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]
                                : tooltipItem.yLabel;

                            return moneda ? fmtPeso(value) : Number(value || 0).toLocaleString('es-CL') + ' uds.';
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: chartType === 'doughnut',
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                var value = getTooltipValue(context, chartType);
                                return moneda ? fmtPeso(value) : Number(value || 0).toLocaleString('es-CL') + ' uds.';
                            }
                        }
                    }
                }
            }
        };

        if (chartType !== 'doughnut') {
            config.options.scales = {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }],
                y: {
                    beginAtZero: true
                }
            };
        }

        window.__estadisticasCharts[id] = new Chart(canvas.getContext('2d'), config);
    }

    var cr   = {!! json_encode($chart_repuestos_arr) !!};
    var cc   = {!! json_encode($chart_clientes_arr) !!};
    var cvb  = {!! json_encode($chart_vendedores_boleta_arr) !!};
    var cvf  = {!! json_encode($chart_vendedores_factura_arr) !!};
    var crv  = {!! json_encode($chart_ranking_vendedores_arr) !!};
    var cd   = {!! json_encode($chart_dias_arr) !!};
    var comp = {!! json_encode($chart_composicion_arr) !!};

    makeChart('grafico_composicion',        'Composición ventas',       comp, true,  'doughnut');
    makeChart('grafico7',                   'Días con más ventas ($)',  cd,   true,  'bar');
    makeChart('grafico3',                   'Mejores clientes ($)',     cc,   true,  'bar');
    makeChart('grafico_ranking_vendedores', 'Ranking vendedores ($)',   crv,  true,  'bar');
    makeChart('grafico',                    'Repuestos más vendidos',   cr,   false, 'bar');
    makeChart('grafico9',                   'Vendedores boleta ($)',    cvb,  true,  'bar');
    makeChart('grafico10',                  'Vendedores factura ($)',   cvf,  true,  'bar');
})();
</script>

