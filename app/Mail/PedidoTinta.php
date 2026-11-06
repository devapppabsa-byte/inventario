<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PedidoTinta extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;
    public $mailFrom;
    public $mailFromName;

    public function __construct($pedido, $mailFrom = null, $mailFromName = null)
    {
        $this->pedido = $pedido;
        $this->mailFrom = $mailFrom;
        $this->mailFromName = $mailFromName;
    }

    public function build()
    {
        $foto = Storage::disk('local')->path($this->pedido->foto_tintas);
        $asunto = 'Solicitud de tinta #' . $this->pedido->id . ' - ' . $this->pedido->numero;
        if ($this->pedido->parent_id) {
            $asunto = '[Residual del pedido #' . $this->pedido->parent_id . '] ' . $asunto;
        }
        $mail = $this->view('emails.pedido_tinta', ['foto_path' => $foto])
                    ->subject($asunto)
                    ->attach($foto, ['as' => 'foto_' . basename($this->pedido->foto_tintas)]);
        if ($this->mailFrom) {
            $mail->from($this->mailFrom, $this->mailFromName);
        }
        // El From es la casilla del sistema: sin Reply-To, la respuesta de
        // recepcion se leeria sola a sistemas@ y el cliente se queda sin saber
        // que paso con su pedido. Va al correo de contacto (correo), no al login.
        if ($this->pedido->user) {
            $mail->replyTo($this->pedido->user->correo, $this->pedido->user->name);
        }
        return $mail;
    }
}
