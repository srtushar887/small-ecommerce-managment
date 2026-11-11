<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('q'));

        return view('frontend.shops.ShopDirectory', compact('search'));
    }

    public function fetchShops(Request $request): JsonResponse
    {
        $search = trim((string) $request->get('search'));

        $items = Shop::where('is_active', true)
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->withCount(['products as products_count' => fn ($query) => $query->where('shop_product.is_active', true)])
            ->orderBy('name')
            ->paginate(9);

        $data = $items->getCollection()->map(function (Shop $shop) {
            return [
                'id' => $shop->id,
                'name' => $shop->name,
                'slug' => $shop->slug,
                'description' => $shop->description,
                'logo_url' => $shop->logo_url,
                'products_count' => (int) $shop->products_count,
                'url' => route('shop.show', $shop),
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

    public function show(Request $request, Shop $shop): View
    {
        abort_unless($shop->is_active, 404);

        $search = trim((string) $request->get('q'));

        $shopData = [
            'id' => $shop->id,
            'name' => $shop->name,
            'slug' => $shop->slug,
            'description' => $shop->description,
            'logo_url' => $shop->logo_url,
            'address' => $shop->address,
            'phone' => $shop->phone,
            'products_count' => ShopProduct::where('shop_id', $shop->id)
                ->where('is_active', true)
                ->whereHas('product', fn ($query) => $query->where('is_active', true))
                ->count(),
            'url' => route('shop.show', $shop),
        ];

        return view('frontend.shops.ShopShow', compact('shop', 'shopData', 'search'));
    }

    public function fetchProducts(Request $request, Shop $shop): JsonResponse
    {
        abort_unless($shop->is_active, 404);

        $search = trim((string) $request->get('search'));

        $items = ShopProduct::where('shop_id', $shop->id)
            ->where('is_active', true)
            ->whereHas('product', function ($query) use ($search) {
                $query->where('is_active', true)
                    ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    }));
            })
            ->with('product')
            ->orderByDesc('created_at')
            ->paginate(12);

        $data = $items->getCollection()->map(function (ShopProduct $item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product?->name,
                'sku' => $item->product?->sku,
                'details' => $item->effective_details,
                'price' => $item->effective_price,
                'price_formatted' => number_format($item->effective_price, 2),
                'thumbnail' => $item->effective_thumbnail,
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
}
