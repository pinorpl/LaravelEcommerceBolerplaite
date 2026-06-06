<?php

namespace App\Modules\Ordering\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\ProductCatalog\Domain\Models\Product;

/**
 * OrderItem – immutable snapshot of a product line at checkout time.
 * product_name and unit_price are denormalized snapshots so the order
 * remains accurate even if the product is later renamed or repriced.
 */
class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity'   => 'integer',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
