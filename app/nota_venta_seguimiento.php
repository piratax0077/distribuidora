<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class nota_venta_seguimiento extends Model
{
    protected $table = 'nota_venta_seguimientos';

    protected $fillable = [
        'numero_nota_venta', 'numero_solicitud_web', 'usuario_web_id', 'cliente_id',
        'estado_pedido', 'estado_pago', 'estado_entrega', 'tipo_entrega',
        'transportista', 'numero_seguimiento', 'observacion_cliente', 'observacion_interna',
        'confirmado_at', 'preparando_at', 'despachado_at', 'entregado_at',
    ];

    protected $casts = [
        'confirmado_at' => 'datetime',
        'preparando_at' => 'datetime',
        'despachado_at' => 'datetime',
        'entregado_at' => 'datetime',
    ];

    public static function estadosPedido()
    {
        return ['recibido', 'revision', 'confirmado', 'rechazado', 'cancelado'];
    }

    public static function estadosPago()
    {
        return ['pendiente', 'validando', 'pagado', 'rechazado', 'reembolsado'];
    }

    public static function estadosEntrega()
    {
        return ['pendiente', 'preparando', 'listo_retiro', 'despachado', 'entregado', 'incidencia'];
    }

    public function historial()
    {
        return $this->hasMany(nota_venta_estado_historial::class, 'nota_venta_seguimiento_id')->orderBy('created_at');
    }

    public function estadoGeneral()
    {
        if (in_array($this->estado_pedido, ['rechazado', 'cancelado'])) {
            return $this->estado_pedido;
        }
        if ($this->estado_entrega !== 'pendiente') {
            return $this->estado_entrega;
        }
        return $this->estado_pedido;
    }
}
