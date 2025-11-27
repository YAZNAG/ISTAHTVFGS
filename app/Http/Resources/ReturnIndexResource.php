<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReturnIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'numero'        => $this->numero,
            'date'          => $this->date->toDateString(),
            'motif'         => $this->motif ?? '---',
            'returner_id'   => $this->returner_id,
            'returner_name' => $this->whenLoaded('returner', fn() => $this->returner->name),
            'receiver_id'   => $this->receiver_id,
            'receiver_name' => $this->whenLoaded('receiver', fn() => $this->receiver->name),
            'articles_count'=> (int) $this->when(isset($this->articles_count), $this->articles_count),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
