<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Support\ImageUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(): View
    {
        $shop = auth()->user()->shop()->first();

        return view('backend.catalog.CatalogIndex', compact('shop'));
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

        $query = Product::query()
            ->where('is_active', true)
            ->with(['creator', 'shopProducts' => fn ($query) => $query->where('shop_id', $shop->id)]);

        if ($search = trim((string) $request->get('search'))) {
            $query->search($search);
        }

        if ($request->get('filter') === 'assigned') {
            $query->whereHas('shopProducts', fn ($q) => $q->where('shop_id', $shop->id));
        } elseif ($request->get('filter') === 'available') {
            $query->whereDoesntHave('shopProducts', fn ($q) => $q->where('shop_id', $shop->id));
        }

        $items = $query->latest()->paginate(12);

        $data = $items->getCollection()->map(function (Product $product) use ($shop) {
            /** @var ShopProduct|null $assignment */
            $assignment = $product->shopProducts->first();

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'description' => $product->description,
                'thumbnail_url' => $product->thumbnail_url,
                'base_price' => (float) $product->base_price,
                'is_active' => (bool) $product->is_active,
                'creator_name' => $product->creator?->name,
                'is_mine' => $product->created_by === auth()->id(),
                'assigned' => (bool) $assignment,
                'assignment_id' => $assignment?->id,
                'shop_price' => $assignment ? (float) ($assignment->price ?? $product->base_price) : null,
                'shop_details' => $assignment?->details,
                'shop_thumbnail_url' => $assignment?->effective_thumbnail,
                'shop_is_active' => (bool) ($assignment?->is_active ?? false),
                'created_at' => optional($product->created_at)->toIso8601String(),
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

    public function assign(Request $request): JsonResponse
    {
        $shop = auth()->user()->shop()->first();

        if (! $shop) {
            return response()->json([
                'success' => false,
                'message' => 'No shop is assigned to your account yet. Please contact the administrator.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'price' => 'nullable|numeric|min:0',
            'details' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $product = Product::where('is_active', true)->find($request->product_id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'This product is not available in the shared catalog.',
            ], 422);
        }

        $assignment = ShopProduct::firstOrNew([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
        ]);

        if ($request->hasFile('thumbnail')) {
            $assignment->thumbnail = ImageUploader::store(
                $request->file('thumbnail'),
                'products',
                $assignment->thumbnail
            );
        }

        if ($request->has('price')) {
            $assignment->price = $request->filled('price') ? $request->price : null;
        }

        if ($request->has('details')) {
            $assignment->details = $request->details;
        }

        $assignment->is_active = true;
        $assignment->save();

        return response()->json([
            'success' => true,
            'message' => 'Product added to your shop successfully.',
            'assignment_id' => $assignment->id,
        ]);
    }
}
