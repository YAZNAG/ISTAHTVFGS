<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DemandeRejected extends Notification
{
    use Queueable;

    public function __construct(public Demande $demande) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $num = $this->demande->numero;   // or ->reference

        return [
            'object_id' => $this->demande->id,
            'message'   => "Votre demande <strong class='underline'>#{$num}</strong> a été refusée",
            'url'       => route('demandes.index'),
        ];
    }
}