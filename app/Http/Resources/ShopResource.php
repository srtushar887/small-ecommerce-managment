<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'logo' => $this->logo,
            'logo_url' => $this->logo_url,
            'phone' => $this->phone,
            'address' => $this->address,
            'is_active' => (bool) $this->is_active,
            'owner_id' => $this->user_id,
            'owner_name' => $this->whenLoaded('owner', fn () => $this->owner?->name),
            'owner_email' => $this->whenLoaded('owner', fn () => $this->owner?->email),
            'products_count' => $this->when(isset($this->products_count), fn () => (int) $this->products_count),
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
