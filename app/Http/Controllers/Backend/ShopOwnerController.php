<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use App\Support\ImageUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ShopOwnerController extends Controller
{
    public function index(): View
    {
        return view('backend.shopOwner.ShopOwnerIndex');
    }

    public function fetch(Request $request): JsonResponse
    {
        $query = User::role('shop-owner')
            ->with(['shop' => fn ($query) => $query->withCount('products')]);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('shop', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', (bool) $request->status);
        }

        $items = $query->latest()->paginate(10);

        $data = $items->getCollection()->map(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'is_active' => (bool) $user->is_active,
                'shop_id' => $user->shop?->id,
                'shop_name' => $user->shop?->name,
                'shop_slug' => $user->shop?->slug,
                'shop_logo_url' => $user->shop?->logo_url,
                'shop_is_active' => (bool) ($user->shop?->is_active ?? false),
                'products_count' => (int) ($user->shop?->products_count ?? 0),
                'created_at' => optional($user->created_at)->toIso8601String(),
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

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:8',
            'is_active' => 'required|in:0,1',
            'shop_name' => 'required|string|max:255',
            'shop_description' => 'nullable|string',
            'shop_address' => 'nullable|string|max:255',
            'shop_phone' => 'nullable|string|max:50',
            'shop_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $result = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => strtolower(trim($request->email)),
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => (int) $request->is_active,
                'email_verified_at' => now(),
            ]);

            $user->assignRole('shop-owner');

            $shop = Shop::create([
                'user_id' => $user->id,
                'name' => $request->shop_name,
                'slug' => Shop::generateUniqueSlug($request->shop_name),
                'description' => $request->shop_description,
                'logo' => ImageUploader::store($request->file('shop_logo'), 'shops'),
                'phone' => $request->shop_phone,
                'address' => $request->shop_address,
                'is_active' => (int) $request->is_active,
            ]);

            return compact('user', 'shop');
        });

        return response()->json([
            'success' => true,
            'message' => 'Shop owner account created successfully.',
            'owner' => $result['user']->only(['id', 'name', 'email']),
            'shop' => $result['shop']->only(['id', 'name', 'slug']),
        ]);
    }

    public function show(User $shopOwner): JsonResponse
    {
        abort_unless($shopOwner->hasRole('shop-owner'), 404);

        $shop = $shopOwner->shop()->withCount('products')->first();

        return response()->json([
            'success' => true,
            'owner' => [
                'id' => $shopOwner->id,
                'name' => $shopOwner->name,
                'email' => $shopOwner->email,
                'phone' => $shopOwner->phone,
                'is_active' => (bool) $shopOwner->is_active,
                'shop_id' => $shop?->id,
                'shop_name' => $shop?->name,
                'shop_slug' => $shop?->slug,
                'shop_description' => $shop?->description,
                'shop_address' => $shop?->address,
                'shop_phone' => $shop?->phone,
                'shop_logo_url' => $shop?->logo_url,
                'shop_is_active' => (bool) ($shop?->is_active ?? false),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$request->id,
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:8',
            'is_active' => 'required|in:0,1',
            'shop_name' => 'required|string|max:255',
            'shop_description' => 'nullable|string',
            'shop_address' => 'nullable|string|max:255',
            'shop_phone' => 'nullable|string|max:50',
            'shop_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::findOrFail($request->id);

        if (! $user->hasRole('shop-owner')) {
            return response()->json(['success' => false, 'message' => 'This user is not a shop owner.'], 404);
        }

        DB::transaction(function () use ($request, $user) {
            $data = [
                'name' => $request->name,
                'email' => strtolower(trim($request->email)),
                'phone' => $request->phone,
                'is_active' => (int) $request->is_active,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            $shop = $user->shop;

            $shopData = [
                'name' => $request->shop_name,
                'description' => $request->shop_description,
                'phone' => $request->shop_phone,
                'address' => $request->shop_address,
                'is_active' => (int) $request->is_active,
            ];

            if ($shop) {
                $shopData['slug'] = Shop::generateUniqueSlug($request->shop_name, $shop->id);
                $shopData['logo'] = ImageUploader::store($request->file('shop_logo'), 'shops', $shop->logo);
                $shop->update($shopData);
            } else {
                $shopData['user_id'] = $user->id;
                $shopData['slug'] = Shop::generateUniqueSlug($request->shop_name);
                $shopData['logo'] = ImageUploader::store($request->file('shop_logo'), 'shops');
                Shop::create($shopData);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Shop owner account updated successfully.',
        ]);
    }

    public function toggleStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['id' => 'required|exists:users,id']);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid shop owner.'], 422);
        }

        $user = User::findOrFail($request->id);
        abort_unless($user->hasRole('shop-owner'), 404);

        $user->update(['is_active' => ! $user->is_active]);
        $user->shop?->update(['is_active' => $user->is_active]);

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'Shop owner activated.' : 'Shop owner deactivated.',
            'is_active' => $user->is_active,
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['id' => 'required|exists:users,id']);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid shop owner.'], 422);
        }

        $user = User::findOrFail($request->id);
        abort_unless($user->hasRole('shop-owner'), 404);

        DB::transaction(function () use ($user) {
            $shop = $user->shop;

            if ($shop) {
                $shop->products()->detach();
                ImageUploader::delete('shops', $shop->logo);
                $shop->delete();
            }

            $user->roles()->detach();
            $user->permissions()->detach();
            $user->tokens()->delete();
            $user->delete();
        });

        return response()->json(['success' => true, 'message' => 'Shop owner account deleted successfully.']);
    }
}
