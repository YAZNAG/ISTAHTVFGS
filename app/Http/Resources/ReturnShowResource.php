<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReturnShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'numero'          => $this->numero,
            'date'            => $this->date->toDateString(),
            'motif'           => $this->motif,

            'returner_id'     => $this->returner_id,
            'returner_name'   => $this->whenLoaded('returner', fn() => $this->returner->name),

            'receiver_id'     => $this->receiver_id,
            'receiver_name'   => $this->whenLoaded('receiver', fn() => $this->receiver->name),

            'articles_count'  => $this->items->count(),

            'items'           => $this->items->map(function ($item) {
                return [
                    'article_id'   => $item->article_id,
                    'quantite'     => $item->quantite,
                    'designation'    => $item->article->designation,
                    'unite_mesure'   => $item->article->unite_mesure,
                ];
            }),

            'created_at'      => $this->created_at->toDateString(),
        ];
    }
}
