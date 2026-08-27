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
        $mail = $this->view('emails.pedido_tinta', ['foto_path' => $foto])
                    ->subject('Nuevo Pedido de Tintas - ' . $this->pedido->numero)
                    ->attach($foto, ['as' => 'foto_' . basename($this->pedido->foto_tintas)]);
        if ($this->mailFrom) {
            $mail->from($this->mailFrom, $this->mailFromName);
        }
        return $mail;
    }
}
