<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Modules\ProductCatalog\Application\Commands\CreateProductCommand;
use App\Modules\ProductCatalog\Application\Commands\UpdateProductCommand;
use App\Modules\ProductCatalog\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin-only CRUD for products.
 * Protected by 'auth:api' + 'role:admin' middleware (defined in routes/api.php).
 */
class AdminProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CreateProductCommand $createProductCommand,
        private readonly UpdateProductCommand $updateProductCommand,
    ) {}

    /** GET /api/admin/products */
    public function index(): JsonResponse
    {
        $products = $this->productRepository->paginateAll(15);
        return response()->json(ProductResource::collection($products)->response()->getData(true));
    }

    /** POST /api/admin/products */
    public function store(ProductRequest $request): JsonResponse
    {
        $product = $this->createProductCommand->execute(
            $request->validated(),
            $request->user()->id,
        );

        return response()->json(new ProductResource($product), 201);
    }

    /** PUT /api/admin/products/{id} */
    public function update(ProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productRepository->findById($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $updated = $this->updateProductCommand->execute($product, $request->validated());

        return response()->json(new ProductResource($updated));
    }

    /** DELETE /api/admin/products/{id} */
    public function destroy(int $id): JsonResponse
    {
        $product = $this->productRepository->findById($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $this->productRepository->delete($product);

        return response()->json(['message' => 'Product deleted.']);
    }
}
