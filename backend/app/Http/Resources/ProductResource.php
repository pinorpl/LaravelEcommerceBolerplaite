<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'price'       => (float) $this->price,
            'stock'       => $this->stock,
            'image'       => $this->image,
            'is_active'   => $this->is_active,
            'created_at'  => $this->created_at->toISOString(),
        ];
    }
}
