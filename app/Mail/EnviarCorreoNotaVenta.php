<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\carrito_transferido;
use App\cliente_direcciones;
use App\regiones;
use App\ciudades;
use Illuminate\Support\Facades\Auth;

class EnviarCorreoNotaVenta extends Mailable
{
    use Queueable, SerializesModels;

    protected $correoDestino;
    protected $detalles;
    protected $cliente;
    protected $total_neto;
    protected $total;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    // Constructor modificado para aceptar un array en $det
    public function __construct($correoDestino, $det, $cliente, $total_neto, $total) {
        $this->correoDestino = $correoDestino;
        $this->detalles = $det; // Asumiendo que $det es un array
        $this->cliente = $cliente;
        $this->total_neto = $total_neto;
        $this->total = $total;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $correo_origen = 'contacto@ipschile.cl';

        $notaventa = $this->detalles; // Asumiendo que $det es un array
        $vendedor = Auth::user()->name;
        $notaventa->vendedor = $vendedor;
        $cliente = $this->cliente;
        // direccion del cliente
        $direccion = cliente_direcciones::where('id_cliente',$cliente->id)->first();
        $cliente->direccion = trim($direccion->direccion);

        $region = regiones::find($direccion->id_region);
        $ciudad = ciudades::find($direccion->id_comuna);

        $cliente->ciudad = $ciudad->nombre;
        $cliente->region = $region->nombre;

        $cliente->giro = '';

        $asunto = "Nota de Venta ".$notaventa[0]->numero_carrito." - ".$cliente->empresa;

        $total_neto = $this->total_neto;
        $total = $this->total;

        return $this->view('correos.nota_venta', compact('asunto','notaventa','cliente','total_neto','total'))
                    ->from($correo_origen)
                    ->subject('Nota de Venta '.$notaventa[0]->numero_carrito.' - '.$cliente->empresa)
                    ->to($this->correoDestino);

    }
}
