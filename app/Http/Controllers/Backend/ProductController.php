<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Support\ImageUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('backend.product.ProductIndex');
    }

    public function fetch(Request $request): JsonResponse
    {
        $query = Product::with('creator')->withCount('shops');

        if ($search = trim((string) $request->get('search'))) {
            $query->search($search);
        }

        if ($request->filled('status')) {
            $query->where('is_active', (bool) $request->status);
        }

        if ($request->filled('owner')) {
            if ($request->owner === 'mine') {
                $query->where('created_by', auth()->id());
            } elseif ($request->owner === 'admin') {
                $query->whereHas('creator', fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', 'admin')));
            }
        }

        $items = $query->latest()->paginate(10);

        return response()->json([
            'data' => ProductResource::collection($items->getCollection())->resolve($request),
            'current_page' => $items->currentPage(),
            'last_page' => $items->lastPage(),
            'per_page' => $items->perPage(),
            'total' => $items->total(),
            'from' => $items->firstItem(),
            'to' => $items->lastItem(),
            'prev_page_url' => $items->previousPageUrl(),
            'next_page_url' => $items->nextPageUrl(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'required|in:0,1',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $product = Product::create([
            'created_by' => auth()->id(),
            'name' => $request->name,
            'sku' => $request->sku,
            'description' => $request->description,
            'thumbnail' => ImageUploader::store($request->file('thumbnail'), 'products'),
            'base_price' => $request->base_price,
            'is_active' => (int) $request->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully and added to the shared catalog.',
            'product' => new ProductResource($product->load('creator')->loadCount('shops')),
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'product' => new ProductResource($product->load('creator')->loadCount('shops')),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,'.$request->id,
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'required|in:0,1',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $product = Product::findOrFail($request->id);

        if (! auth()->user()->hasRole('admin') && $product->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit products that you created.',
            ], 403);
        }

        $product->update([
            'name' => $request->name,
            'sku' => $request->sku,
            'description' => $request->description,
            'thumbnail' => ImageUploader::store($request->file('thumbnail'), 'products', $product->thumbnail),
            'base_price' => $request->base_price,
            'is_active' => (int) $request->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'product' => new ProductResource($product->fresh()->load('creator')->loadCount('shops')),
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['id' => 'required|exists:products,id']);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid product.'], 422);
        }

        $product = Product::findOrFail($request->id);

        if (! auth()->user()->hasRole('admin') && $product->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete products that you created.',
            ], 403);
        }

        DB::transaction(function () use ($product) {
            $product->shops()->detach();
            ImageUploader::delete('products', $product->thumbnail);
            $product->delete();
        });

        return response()->json(['success' => true, 'message' => 'Product deleted successfully.']);
    }

    public function toggleStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['id' => 'required|exists:products,id']);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid product.'], 422);
        }

        $product = Product::findOrFail($request->id);
        $product->update(['is_active' => ! $product->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Product status updated successfully.',
            'is_active' => $product->is_active,
        ]);
    }
}
