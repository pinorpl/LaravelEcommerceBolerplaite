<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'status'           => $this->status,
            'total_amount'     => (float) $this->total_amount,
            'shipping_address' => $this->shipping_address,
            'created_at'       => $this->created_at->toISOString(),
            'items'            => $this->whenLoaded('items', function () {
                return $this->items->map(fn ($item) => [
                    'id'           => $item->id,
                    'product_name' => $item->product_name,
                    'quantity'     => $item->quantity,
                    'unit_price'   => (float) $item->unit_price,
                    'subtotal'     => (float) ($item->unit_price * $item->quantity),
                ]);
            }),
        ];
    }
}
