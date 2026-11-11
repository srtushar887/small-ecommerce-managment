<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use App\Models\User;
use App\Support\ImageUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(): View
    {
        $owners = User::role('shop-owner')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('backend.shop.ShopIndex', compact('owners'));
    }

    public function fetch(Request $request): JsonResponse
    {
        $query = Shop::with('owner')->withCount('products');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('owner', fn ($q) => $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', (bool) $request->status);
        }

        $items = $query->latest()->paginate(10);

        return response()->json([
            'data' => ShopResource::collection($items->getCollection())->resolve($request),
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
            'user_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'is_active' => 'required|in:0,1',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $owner = User::findOrFail($request->user_id);
        if (! $owner->hasRole('shop-owner')) {
            return response()->json([
                'success' => false,
                'message' => 'The selected user is not a shop owner.',
            ], 422);
        }

        if (Shop::where('user_id', $owner->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This shop owner already has a shop assigned.',
            ], 422);
        }

        $shop = Shop::create([
            'user_id' => $owner->id,
            'name' => $request->name,
            'slug' => Shop::generateUniqueSlug($request->name),
            'description' => $request->description,
            'logo' => ImageUploader::store($request->file('logo'), 'shops'),
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => (int) $request->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shop created successfully.',
            'shop' => new ShopResource($shop->load('owner')),
        ]);
    }

    public function show(Shop $shop): JsonResponse
    {
        return response()->json([
            'success' => true,
            'shop' => new ShopResource($shop->load('owner')->loadCount('products')),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:shops,id',
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'is_active' => 'required|in:0,1',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $shop = Shop::findOrFail($request->id);
        $owner = User::findOrFail($request->user_id);

        if (! $owner->hasRole('shop-owner')) {
            return response()->json([
                'success' => false,
                'message' => 'The selected user is not a shop owner.',
            ], 422);
        }

        if (Shop::where('user_id', $owner->id)->where('id', '!=', $shop->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This shop owner already has another shop assigned.',
            ], 422);
        }

        $shop->update([
            'user_id' => $owner->id,
            'name' => $request->name,
            'slug' => Shop::generateUniqueSlug($request->name, $shop->id),
            'description' => $request->description,
            'logo' => ImageUploader::store($request->file('logo'), 'shops', $shop->logo),
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => (int) $request->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shop updated successfully.',
            'shop' => new ShopResource($shop->fresh()->load('owner')),
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['id' => 'required|exists:shops,id']);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid shop.'], 422);
        }

        $shop = Shop::findOrFail($request->id);

        DB::transaction(function () use ($shop) {
            $shop->products()->detach();
            ImageUploader::delete('shops', $shop->logo);
            $shop->delete();
        });

        return response()->json(['success' => true, 'message' => 'Shop deleted successfully.']);
    }

    public function toggleStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['id' => 'required|exists:shops,id']);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid shop.'], 422);
        }

        $shop = Shop::findOrFail($request->id);
        $shop->update(['is_active' => ! $shop->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Shop status updated successfully.',
            'is_active' => $shop->is_active,
        ]);
    }
}
