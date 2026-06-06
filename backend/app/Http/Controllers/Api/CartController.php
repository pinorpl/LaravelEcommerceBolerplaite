<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Http\Resources\OrderResource;
use App\Modules\Ordering\Domain\Factories\OrderFactory;
use App\Modules\Ordering\Domain\Repositories\CartRepositoryInterface;
use App\Modules\ProductCatalog\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Cart CRUD and checkout endpoint.
 * All routes require auth:api middleware.
 */
class CartController extends Controller
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly OrderFactory $orderFactory,
    ) {}

    /** GET /api/cart */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartRepository->findOrCreateForUser($request->user()->id);
        $cart->load('items.product');
        return response()->json(new CartResource($cart));
    }

    /** POST /api/cart/items  body: { product_id, quantity } */
    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = $this->productRepository->findById($validated['product_id']);

        if (! $product || ! $product->is_active) {
            return response()->json(['message' => 'Product not available.'], 422);
        }

        $cart = $this->cartRepository->findOrCreateForUser($request->user()->id);
        $item = $this->cartRepository->addItem(
            $cart,
            $product->id,
            $validated['quantity'],
            (float) $product->price,
        );

        $cart->load('items.product');
        return response()->json(new CartResource($cart), 201);
    }

    /** PUT /api/cart/items/{id}  body: { quantity } */
    public function updateItem(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['quantity' => 'required|integer|min:1']);

        $item = $this->cartRepository->findItemById($id);

        if (! $item || $item->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Item not found.'], 404);
        }

        $this->cartRepository->updateItemQuantity($item, $validated['quantity']);

        $cart = $item->cart->load('items.product');
        return response()->json(new CartResource($cart));
    }

    /** DELETE /api/cart/items/{id} */
    public function removeItem(Request $request, int $id): JsonResponse
    {
        $item = $this->cartRepository->findItemById($id);

        if (! $item || $item->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Item not found.'], 404);
        }

        $this->cartRepository->removeItem($item);

        return response()->json(['message' => 'Item removed.']);
    }

    /**
     * POST /api/cart/checkout
     * Converts the cart into an Order and empties the cart.
     * Uses the OrderFactory (Factory pattern) for the transformation.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_address' => 'required|string|max:500',
        ]);

        $cart = $this->cartRepository->findOrCreateForUser($request->user()->id);
        $cart->load('items');

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        $order = $this->orderFactory->createFromCart($cart, $validated['shipping_address']);

        return response()->json(new OrderResource($order), 201);
    }
}
