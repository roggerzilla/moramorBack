<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockInsuficienteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $itemName;

    public function __construct($itemName)
    {
        $this->itemName = $itemName;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Stock insuficiente')
            ->line('Lo sentimos, no hay suficiente stock para el ítem: ' . $this->itemName)
            ->line('Por favor, revisa nuestro catálogo para otros productos disponibles.')
            ->action('Ver catálogo', url('/catalogo'))
            ->line('Gracias por usar nuestra aplicación.');
    }

    public function toArray($notifiable)
    {
        return [];
    }
}