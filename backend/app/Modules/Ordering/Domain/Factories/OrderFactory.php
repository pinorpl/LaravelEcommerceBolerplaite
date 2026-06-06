<?php

namespace App\Modules\Ordering\Domain\Factories;

use App\Modules\Ordering\Domain\Models\Cart;
use App\Modules\Ordering\Domain\Models\Order;
use App\Modules\Ordering\Domain\Repositories\CartRepositoryInterface;
use App\Modules\Ordering\Domain\Repositories\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * OrderFactory – Factory pattern for creating Orders from Carts.
 *
 * Encapsulates the business logic of:
 * 1. Computing the total from cart items.
 * 2. Creating the Order record.
 * 3. Copying CartItems as OrderItems (with price snapshots).
 * 4. Clearing the cart.
 *
 * Wrapped in a DB transaction to ensure atomicity.
 */
class OrderFactory
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CartRepositoryInterface $cartRepository,
    ) {}

    public function createFromCart(Cart $cart, string $shippingAddress): Order
    {
        return DB::transaction(function () use ($cart, $shippingAddress) {
            $cart->load('items.product');

            $total = $cart->getTotal();

            $order = $this->orderRepository->create([
                'user_id'          => $cart->user_id,
                'status'           => 'pending',
                'total_amount'     => $total,
                'shipping_address' => $shippingAddress,
            ]);

            foreach ($cart->items as $cartItem) {
                $this->orderRepository->addItem($order, [
                    'product_id'   => $cartItem->product_id,
                    'product_name' => $cartItem->product->name, // snapshot
                    'quantity'     => $cartItem->quantity,
                    'unit_price'   => $cartItem->price,         // snapshot
                ]);
            }

            $this->cartRepository->clearCart($cart);

            return $order->load('items');
        });
    }
}
