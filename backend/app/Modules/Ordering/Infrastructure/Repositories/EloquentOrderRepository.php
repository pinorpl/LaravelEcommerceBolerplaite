<?php

namespace App\Modules\Ordering\Infrastructure\Repositories;

use App\Modules\Ordering\Domain\Models\Order;
use App\Modules\Ordering\Domain\Models\OrderItem;
use App\Modules\Ordering\Domain\Repositories\OrderRepositoryInterface;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function addItem(Order $order, array $itemData): void
    {
        $order->items()->create($itemData);
    }
}
