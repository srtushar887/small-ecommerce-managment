<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ShopProduct;
use App\Support\ImageUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class MyShopProductController extends Controller
{
    public function index(): View
    {
        $shop = auth()->user()->shop()->first();

        return view('backend.catalog.MyShopProductIndex', compact('shop'));
    }

    public function fetch(Request $request): JsonResponse
    {
        $shop = auth()->user()->shop()->first();

        if (! $shop) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 10,
                'total' => 0,
                'from' => null,
                'to' => null,
                'prev_page_url' => null,
                'next_page_url' => null,
            ]);
        }

        $query = ShopProduct::where('shop_id', $shop->id)->with('product');

        if ($search = trim((string) $request->get('search'))) {
            $query->whereHas('product', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', (bool) $request->status);
        }

        $items = $query->latest()->paginate(10);

        $data = $items->getCollection()->map(function (ShopProduct $item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product?->name,
                'sku' => $item->product?->sku,
                'catalog_description' => $item->product?->description,
                'catalog_price' => (float) ($item->product?->base_price ?? 0),
                'catalog_thumbnail_url' => $item->product?->thumbnail_url,
                'price' => $item->price !== null ? (float) $item->price : null,
                'effective_price' => $item->effective_price,
                'details' => $item->details,
                'effective_details' => $item->effective_details,
                'thumbnail' => $item->thumbnail,
                'effective_thumbnail' => $item->effective_thumbnail,
                'is_active' => (bool) $item->is_active,
                'created_at' => optional($item->created_at)->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $data,
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

    public function show(ShopProduct $shopProduct): JsonResponse
    {
        $this->authorizeAssignment($shopProduct);

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $shopProduct->id,
                'product_id' => $shopProduct->product_id,
                'name' => $shopProduct->product?->name,
                'price' => $shopProduct->price !== null ? (float) $shopProduct->price : null,
                'details' => $shopProduct->details,
                'thumbnail' => $shopProduct->thumbnail,
                'effective_thumbnail' => $shopProduct->effective_thumbnail,
                'effective_price' => $shopProduct->effective_price,
                'is_active' => (bool) $shopProduct->is_active,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:shop_product,id',
            'price' => 'nullable|numeric|min:0',
            'details' => 'nullable|string',
            'is_active' => 'required|in:0,1',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $shopProduct = ShopProduct::with('product')->findOrFail($request->id);
        $this->authorizeAssignment($shopProduct);

        $shopProduct->update([
            'price' => $request->filled('price') ? $request->price : null,
            'details' => $request->details,
            'thumbnail' => ImageUploader::store(
                $request->file('thumbnail'),
                'products',
                $shopProduct->thumbnail
            ),
            'is_active' => (int) $request->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shop product updated successfully.',
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['id' => 'required|exists:shop_product,id']);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid shop product.'], 422);
        }

        $shopProduct = ShopProduct::findOrFail($request->id);
        $this->authorizeAssignment($shopProduct);

        ImageUploader::delete('products', $shopProduct->thumbnail);
        $shopProduct->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from your shop. It is still available in the shared catalog.',
        ]);
    }

    private function authorizeAssignment(ShopProduct $shopProduct): void
    {
        $shop = auth()->user()->shop()->first();

        if (! $shop || $shopProduct->shop_id !== $shop->id) {
            abort(404);
        }
    }
}
