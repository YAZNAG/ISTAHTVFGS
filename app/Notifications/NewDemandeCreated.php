<?php

namespace App\Notifications;

use App\Models\Demande;          // <-- switch to the Demande model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewDemandeCreated extends Notification
{
    use Queueable;

    public function __construct(public Demande $demande) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $num = $this->demande->numero;   // or ->reference, ->id, etc.

        return [
            'object_id' => $this->demande->id,
            'message'   => "Nouvelle demande <strong class='underline'>#{$num}</strong>",
            'url'       => route('demandes.index'), // adjust route name
        ];
    }
}