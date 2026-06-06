<?php

namespace App\Modules\ProductCatalog\Infrastructure\Repositories;

use App\Modules\ProductCatalog\Domain\Models\Product;
use App\Modules\ProductCatalog\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::active()->where('slug', $slug)->first();
    }

    public function paginateActive(int $perPage = 12, ?string $search = null): LengthAwarePaginator
    {
        $query = Product::active()->orderBy('created_at', 'desc');

        if ($search) {
            $query->search($search);
        }

        return $query->paginate($perPage);
    }

    public function paginateAll(int $perPage = 15): LengthAwarePaginator
    {
        return Product::withTrashed()->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        $product->delete(); // soft-delete
    }
}
