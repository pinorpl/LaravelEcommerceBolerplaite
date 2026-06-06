<?php

namespace App\Modules\Ordering\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\ProductCatalog\Domain\Models\Product;

/**
 * CartItem – a line in the shopping cart.
 * price is a snapshot of the product price at add-to-cart time,
 * so price changes don't retroactively affect existing carts.
 */
class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity', 'price'];

    protected function casts(): array
    {
        return [
            'price'    => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
