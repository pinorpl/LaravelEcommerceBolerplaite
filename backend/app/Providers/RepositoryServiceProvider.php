<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// UserManagement
use App\Modules\UserManagement\Domain\Repositories\UserRepositoryInterface;
use App\Modules\UserManagement\Infrastructure\Repositories\EloquentUserRepository;

// ProductCatalog
use App\Modules\ProductCatalog\Domain\Repositories\ProductRepositoryInterface;
use App\Modules\ProductCatalog\Infrastructure\Repositories\EloquentProductRepository;

// Ordering
use App\Modules\Ordering\Domain\Repositories\CartRepositoryInterface;
use App\Modules\Ordering\Infrastructure\Repositories\EloquentCartRepository;
use App\Modules\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Ordering\Infrastructure\Repositories\EloquentOrderRepository;

/**
 * Binds Domain Repository interfaces to their Eloquent implementations.
 * This is the Dependency Inversion Principle in action: the domain layer
 * depends on abstractions, not on Eloquent directly.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(CartRepositoryInterface::class, EloquentCartRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
    }
}
