<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostInteractionNotification extends Notification
{
    use Queueable;

    // /**
    //  * Create a new notification instance.
    //  *
    //  * @return void
    //  */


    protected $usuario;
    protected $publicacion;
    protected $tipo;


    public function __construct($usuario, $publicacion, $tipo)
    {
        $this->usuario = $usuario;
        $this->publicacion = $publicacion;
        $this->tipo = $tipo; 
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    // /**
    //  * Get the mail representation of the notification.
    //  *
    //  * @param  mixed  $notifiable
    //  * @return \Illuminate\Notifications\Messages\MailMessage
    //  */

    public function toDatabase($notifiable)
    {
        return [
            'message' => "{$this->user->name} ha {$this->type} en una publicación donde interactuaste.",
            'publicacion_id' => $this->publicacion->id,
            'user_id' => $this->user->id,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
