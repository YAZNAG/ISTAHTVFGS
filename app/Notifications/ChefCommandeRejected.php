<?php

namespace App\Notifications;

use App\Models\ChefCommande;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ChefCommandeRejected extends Notification
{
    use Queueable;

    public function __construct(public ChefCommande $chefCommande) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $num = $this->chefCommande->numero;

        return [
            'object_id' => $this->chefCommande->id,
            'message'   => "Votre bon de commande <strong class='underline'>#{$num}</strong> a été refusé",
            'url'       => route('chef-commandes.index'),
        ];
    }
}