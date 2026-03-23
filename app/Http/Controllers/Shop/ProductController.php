<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::query()
                ->where('is_active', true)
                ->withAvg('approvedReviews', 'rating')
                ->withCount('approvedReviews')
                ->with([
                    'category:id,name,slug',
                    'brand:id,name,logo',
                    'images' => fn ($q) => $q->orderBy('order'),
                ]);

            if ($request->filled('category_id')) {
                $query->where('category_id', (int) $request->query('category_id'));
            }

            if ($request->filled('brand_id')) {
                $query->where('brand_id', (int) $request->query('brand_id'));
            }

            if ($request->filled('search')) {
                $s = '%'.str_replace(['%', '_'], ['\\%', '\\_'], (string) $request->query('search')).'%';
                $query->where(function ($q) use ($s) {
                    $q->where('name', 'like', $s)
                        ->orWhere('short_description', 'like', $s)
                        ->orWhere('description', 'like', $s);
                });
            }

            if ($request->boolean('featured')) {
                $query->where('is_featured', true);
            }

            $sort = $request->query('sort', 'newest');
            match ($sort) {
                'price_asc' => $query->orderBy('price'),
                'price_desc' => $query->orderByDesc('price'),
                'name' => $query->orderBy('name'),
                default => $query->orderByDesc('created_at'),
            };

            $perPage = min((int) $request->query('per_page', 15), 50);
            $paginator = $query->paginate($perPage);

            $paginator->getCollection()->transform(fn (Product $p) => $this->productSummary($p));

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $paginator->items(),
                    'meta' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Shop products index: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر تحميل المنتجات',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show(Product $product): JsonResponse
    {
        try {
            $product = Product::query()
                ->where('is_active', true)
                ->whereKey($product->getKey())
                ->withAvg('approvedReviews', 'rating')
                ->withCount('approvedReviews')
                ->with([
                    'category:id,name,slug,description',
                    'brand:id,name,logo,description',
                    'images' => fn ($q) => $q->orderBy('order'),
                    'relatedProducts' => fn ($q) => $q->where('is_active', true)
                        ->withAvg('approvedReviews', 'rating')
                        ->withCount('approvedReviews')
                        ->with(['images', 'category', 'brand']),
                ])
                ->firstOrFail();

            $payload = $this->productDetail($product);

            return response()->json([
                'success' => true,
                'data' => ['product' => $payload],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'المنتج غير متوفر',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Shop product show: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر تحميل المنتج',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function productSummary(Product $product): array
    {
        $primary = $product->images->firstWhere('is_primary', true) ?? $product->images->first();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'short_description' => $product->short_description,
            'price' => (string) $product->price,
            'compare_price' => $product->compare_price !== null ? (string) $product->compare_price : null,
            'is_in_stock' => $product->is_in_stock,
            'stock_quantity' => $product->stock_quantity,
            'is_featured' => $product->is_featured,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'brand' => $product->brand ? [
                'id' => $product->brand->id,
                'name' => $product->brand->name,
                'logo' => $product->brand->logo ? Storage::url($product->brand->logo) : null,
            ] : null,
            'primary_image_url' => $primary && $primary->image_path ? Storage::url($primary->image_path) : null,
            'average_rating' => round((float) ($product->approved_reviews_avg_rating ?? 0), 2),
            'reviews_count' => (int) ($product->approved_reviews_count ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function productDetail(Product $product): array
    {
        $summary = $this->productSummary($product);

        return array_merge($summary, [
            'description' => $product->description,
            'sku' => $product->sku,
            'weight' => $product->weight !== null ? (string) $product->weight : null,
            'images' => $product->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => $img->image_path ? Storage::url($img->image_path) : null,
                'is_primary' => $img->is_primary,
                'order' => $img->order,
            ])->values()->all(),
            'related_products' => $product->relatedProducts->map(fn (Product $p) => $this->productSummary(
                $p->loadMissing(['category', 'brand', 'images'])
            ))->values()->all(),
        ]);
    }
}
