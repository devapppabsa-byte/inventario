<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

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
        $foto = Storage::disk('local')->path($this->reporte->img);
        $mail = $this->view('emails.reporte_soporte', ['foto_path' => $foto])
                    ->subject('Nueva Solicitud de Soporte Técnico - ' . $this->reporte->dispositivo)
                    ->attach($foto, ['as' => 'foto_' . basename($this->reporte->img)]);
        if ($this->mailFrom) {
            $mail->from($this->mailFrom, $this->mailFromName);
        }
        return $mail;
    }
}
