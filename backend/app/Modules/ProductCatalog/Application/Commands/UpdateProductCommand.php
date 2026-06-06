<?php

namespace App\Modules\ProductCatalog\Application\Commands;

use App\Modules\ProductCatalog\Domain\Models\Product;
use App\Modules\ProductCatalog\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Str;

class UpdateProductCommand
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function execute(Product $product, array $data): Product
    {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->productRepository->update($product, $data);
    }
}
