<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReporteSoporte extends Mailable
{
    use Queueable, SerializesModels;

    public $reporte;
    public $mailFrom;
    public $mailFromName;

    public function __construct($reporte, $mailFrom = null, $mailFromName = null)
    {
        $this->reporte = $reporte;
        $this->mailFrom = $mailFrom;
        $this->mailFromName = $mailFromName;
    }

    public function build()
    {
        $adjuntos = $this->reporte->adjuntos;

        // El numero va en el asunto: cuando llegan varias solicitudes seguidas es
        // lo primero que permite clasificarlas y saber a cual responder.
        $mail = $this->view('emails.reporte_soporte')
                    ->subject('Solicitud de soporte #' . $this->reporte->id
                        . ' - ' . ($this->reporte->dispositivo == 'Otro'
                            ? $this->reporte->otro
                            : $this->reporte->dispositivo));

        // Se adjunta todo lo que exista en disco. Antes solo se mandaba una foto y
        // sin comprobar nada: si el archivo faltaba, ->attach() reventaba la
        // peticion entera despues de haber guardado el reporte.
        foreach ($adjuntos as $adjunto) {
            if ($adjunto->existeEnDisco()) {
                $mail->attach($adjunto->rutaAbsoluta(), ['as' => $adjunto->nombre_original]);
            }
        }

        if ($this->mailFrom) {
            $mail->from($this->mailFrom, $this->mailFromName);
        }

        // El From es la casilla del sistema, pero si soporte le contesta el
        // correo tiene que llegarle a quien reporto y no perderse en sistemas@.
        // Sin esto, "Responder" se va a la direccion del sistema. Va al correo de
        // contacto (correo), no al login (email).
        if ($this->reporte->user) {
            $mail->replyTo($this->reporte->user->correo, $this->reporte->user->name);
        }

        return $mail;
    }
}
