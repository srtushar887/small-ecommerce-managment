<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('shop-owner')) {
            return redirect()->route('shop-owner.dashboard');
        }

        abort(403, 'No dashboard is available for your account.');
    }

    public function admin(): View
    {
        $stats = [
            'shops' => Shop::count(),
            'active_shops' => Shop::where('is_active', true)->count(),
            'products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'owners' => User::role('shop-owner')->count(),
            'assignments' => \DB::table('shop_product')->count(),
        ];

        $recentShops = Shop::with('owner')
            ->withCount('products')
            ->latest()
            ->take(5)
            ->get();

        $recentProducts = Product::with('creator')
            ->withCount('shops')
            ->latest()
            ->take(5)
            ->get();

        return view('backend.dashboard.AdminDashboard', compact('stats', 'recentShops', 'recentProducts'));
    }

    public function shopOwner(): View
    {
        $shop = auth()->user()->shop()->withCount('products')->first();

        $stats = [
            'catalog_products' => Product::where('is_active', true)->count(),
            'assigned_products' => $shop ? $shop->products()->count() : 0,
            'active_assigned' => $shop ? $shop->products()->wherePivot('is_active', true)->count() : 0,
            'my_created' => Product::where('created_by', auth()->id())->count(),
        ];

        $recentAssignments = $shop
            ? \App\Models\ShopProduct::where('shop_id', $shop->id)->with('product')->latest()->take(5)->get()
            : collect();

        return view('backend.dashboard.ShopDashboard', compact('shop', 'stats', 'recentAssignments'));
    }
}
