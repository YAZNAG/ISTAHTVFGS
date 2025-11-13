<?php

namespace App\Notifications;

use App\Models\ChefCommande;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewChefCommadeCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ChefCommande $chefCommande)
    {
        
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $data = [
            'object_id' => $this->chefCommande->id,
            'message' => "Nouvelle bon de commande <strong class='underline'>#{$this->chefCommande->id}</strong>",
            'url' => route('chef-commandes.show', $this->chefCommande->id),
        ];
        return $data;
    }
}
