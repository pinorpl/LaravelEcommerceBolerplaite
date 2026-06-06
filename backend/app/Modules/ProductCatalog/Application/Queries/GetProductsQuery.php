<?php

namespace App\Modules\ProductCatalog\Application\Queries;

use App\Modules\ProductCatalog\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Query handler for public product listing.
 * Read-only – no side effects.
 */
class GetProductsQuery
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function execute(int $perPage = 12, ?string $search = null): LengthAwarePaginator
    {
        return $this->productRepository->paginateActive($perPage, $search);
    }
}
