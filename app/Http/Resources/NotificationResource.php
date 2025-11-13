<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' =>  data_get($this->data, 'message'),
            'url' => data_get($this->data, 'url'),
            'time' =>  "Il y a {$this->created_at->diffForHumans()}",
            'type' => class_basename($this->type),
            'read' => $this->read_at ? true : false
        ];
    }
}
