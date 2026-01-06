<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreateMenuCollectiviteResource extends JsonResource
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
            'plat' => $this->plat->nom,
            'repas' => $this->repas->nom,
            'repas_id' => $this->repas_id,
            'effectif' => $this->effectif,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
