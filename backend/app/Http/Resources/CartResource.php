<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'total' => (float) $this->getTotal(),
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(fn ($item) => [
                    'id'         => $item->id,
                    'quantity'   => $item->quantity,
                    'price'      => (float) $item->price,
                    'subtotal'   => (float) ($item->price * $item->quantity),
                    'product'    => $item->relationLoaded('product') ? [
                        'id'    => $item->product->id,
                        'name'  => $item->product->name,
                        'slug'  => $item->product->slug,
                        'image' => $item->product->image,
                    ] : null,
                ]);
            }),
        ];
    }
}
