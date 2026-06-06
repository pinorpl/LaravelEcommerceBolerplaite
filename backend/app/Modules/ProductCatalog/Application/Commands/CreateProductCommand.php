<?php

namespace App\Modules\ProductCatalog\Application\Commands;

use App\Modules\ProductCatalog\Domain\Models\Product;
use App\Modules\ProductCatalog\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Str;

class CreateProductCommand
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function execute(array $data, int $createdBy): Product
    {
        // Auto-generate slug from name if not provided
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['created_by'] = $createdBy;

        return $this->productRepository->create($data);
    }
}
