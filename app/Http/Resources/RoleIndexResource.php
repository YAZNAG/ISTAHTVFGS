<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleIndexResource extends JsonResource
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
            'name' => $this->name,
            'permissions_count' => $this->permissions->count(),

            'userStats' => [
                'count' => max($this->users->count() - 5, 0),
                'users' => $this->users->take(5)->map(function ($user) {
                    return [
                        'name' => $user->name,
                    ];
                }),
            ],
        ];
    }
}
