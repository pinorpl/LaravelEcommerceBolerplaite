<?php

namespace App\Modules\Ordering\Domain\Repositories;

use App\Modules\Ordering\Domain\Models\Cart;
use App\Modules\Ordering\Domain\Models\CartItem;

interface CartRepositoryInterface
{
    /** Find or create a cart for the given user */
    public function findOrCreateForUser(int $userId): Cart;

    public function findItemById(int $itemId): ?CartItem;

    public function addItem(Cart $cart, int $productId, int $quantity, float $price): CartItem;

    public function updateItemQuantity(CartItem $item, int $quantity): CartItem;

    public function removeItem(CartItem $item): void;

    /** Delete all items from the cart (after checkout) */
    public function clearCart(Cart $cart): void;
}
