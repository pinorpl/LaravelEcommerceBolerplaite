<?php

namespace App\Modules\Ordering\Domain\Repositories;

use App\Modules\Ordering\Domain\Models\Order;

interface OrderRepositoryInterface
{
    public function create(array $data): Order;

    public function addItem(Order $order, array $itemData): void;
}
