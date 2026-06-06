<?php

namespace App\Modules\ProductCatalog\Domain\Repositories;

use App\Modules\ProductCatalog\Domain\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;

    public function findBySlug(string $slug): ?Product;

    /**
     * Paginated product listing with optional search filter.
     * Only returns active products (for public-facing API).
     */
    public function paginateActive(int $perPage = 12, ?string $search = null): LengthAwarePaginator;

    /** Admin listing – includes inactive products */
    public function paginateAll(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): void;
}
