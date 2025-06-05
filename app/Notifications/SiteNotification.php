<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SiteNotification extends Notification
{
    use Queueable;

    private $notification;

    /**
     * Create a new notification instance.
     */
    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (config('app.env') == 'production')
            return ['database', 'mail'];

        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Notificación de Bretail. ' . $this->notification['title'])
            ->greeting('Hola ' . $notifiable->nombre_completo)
            ->line(utf8_decode($this->notification['message']));

        if ($this->notification['link'])
            $mail->action('Ver', $this->notification['link']);

        $mail->line('Notificación enviada de forma automática. Por favor no responder!');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
//        $notificacion = [
//            'title' => 'Notificación.',
//            'message' => 'El cuerpo de la notificación',
//            'model' => Model::class,
//            'target' => target->id,
//            'img' => '',
//            'link' => '',
//        ];
        return $this->notification;
    }
}
