<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Modules\ProductCatalog\Application\Queries\GetProductsQuery;
use App\Modules\ProductCatalog\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public product endpoints (no auth required).
 * Pages are SSR-rendered by Next.js which calls these endpoints server-side.
 */
class ProductController extends Controller
{
    public function __construct(
        private readonly GetProductsQuery $getProductsQuery,
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * GET /api/products?search=keyword&per_page=12
     * Returns paginated active products. Used for SSR product listing page.
     */
    public function index(Request $request): JsonResponse
    {
        $products = $this->getProductsQuery->execute(
            perPage: (int) $request->get('per_page', 12),
            search: $request->get('search'),
        );

        return response()->json(ProductResource::collection($products)->response()->getData(true));
    }

    /**
     * GET /api/products/{slug}
     * Returns a single active product by its URL slug.
     */
    public function show(string $slug): JsonResponse
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json(new ProductResource($product));
    }
}
