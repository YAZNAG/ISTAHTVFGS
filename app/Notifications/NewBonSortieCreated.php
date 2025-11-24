<?php

namespace App\Notifications;

use App\Models\SortieStock;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBonSortieCreated extends Notification
{
    use Queueable;

    public function __construct(public SortieStock $bonSortie) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $num = $this->bonSortie->numero;   // or ->reference

        return [
            'object_id' => $this->bonSortie->id,
            'message'   => "Nouveau bon de sortie <strong class='underline'>#{$num}</strong> créé",
            'url'       => route('bon-sorties.index'),
        ];
    }
}