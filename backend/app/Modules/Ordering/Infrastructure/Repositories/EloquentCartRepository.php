<?php

namespace App\Modules\Ordering\Infrastructure\Repositories;

use App\Modules\Ordering\Domain\Models\Cart;
use App\Modules\Ordering\Domain\Models\CartItem;
use App\Modules\Ordering\Domain\Repositories\CartRepositoryInterface;

class EloquentCartRepository implements CartRepositoryInterface
{
    public function findOrCreateForUser(int $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function findItemById(int $itemId): ?CartItem
    {
        return CartItem::find($itemId);
    }

    public function addItem(Cart $cart, int $productId, int $quantity, float $price): CartItem
    {
        // If the product is already in the cart, increment quantity
        $existing = $cart->items()->where('product_id', $productId)->first();

        if ($existing) {
            $existing->increment('quantity', $quantity);
            return $existing->fresh();
        }

        return $cart->items()->create([
            'product_id' => $productId,
            'quantity'   => $quantity,
            'price'      => $price,
        ]);
    }

    public function updateItemQuantity(CartItem $item, int $quantity): CartItem
    {
        $item->update(['quantity' => $quantity]);
        return $item->fresh();
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }
}
