<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexCollectiviteResource extends JsonResource
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
            'repas' => $this->repas->nom,
            'plat' => $this->plat->nom,
            'responsable' => $this->responsable,
            'effectif' => $this->effectif,
            'created_at' => $this->created_at,
        ];
    }
}
