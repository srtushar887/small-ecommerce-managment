<?php

use App\Http\Controllers\Backend\CatalogController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\MyShopProductController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\ShopController;
use App\Http\Controllers\Backend\ShopOwnerController;
use App\Http\Controllers\Frontend\ShopCatalogController;
use Illuminate\Support\Facades\Route;


Route::get('/', [ShopCatalogController::class, 'index'])->name('home');
Route::get('/shops/fetch', [ShopCatalogController::class, 'fetchShops'])->name('shops.fetch');
Route::get('/shop/{shop}/products/fetch', [ShopCatalogController::class, 'fetchProducts'])->name('shop.products.fetch');
Route::get('/shop/{shop}', [ShopCatalogController::class, 'show'])->name('shop.show');


Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});



Route::prefix('admin')->name('admin.')->middleware(['auth', 'active', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

    // Shops
    Route::middleware('permission:view-shop')->group(function () {
        Route::get('/shops', [ShopController::class, 'index'])->name('shop.index');
        Route::get('/shops/fetch', [ShopController::class, 'fetch'])->name('shop.fetch');
        Route::get('/shops/{shop:id}', [ShopController::class, 'show'])->name('shop.show');
        Route::post('/shops/save', [ShopController::class, 'store'])->middleware('permission:create-shop')->name('shop.store');
        Route::post('/shops/update', [ShopController::class, 'update'])->middleware('permission:edit-shop')->name('shop.update');
        Route::post('/shops/delete', [ShopController::class, 'delete'])->middleware('permission:delete-shop')->name('shop.delete');
        Route::post('/shops/toggle-status', [ShopController::class, 'toggleStatus'])->middleware('permission:edit-shop')->name('shop.toggle');
    });

    // Shared product catalog
    Route::middleware('permission:view-product')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('product.index');
        Route::get('/products/fetch', [ProductController::class, 'fetch'])->name('product.fetch');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('product.show');
        Route::post('/products/save', [ProductController::class, 'store'])->middleware('permission:create-product')->name('product.store');
        Route::post('/products/update', [ProductController::class, 'update'])->middleware('permission:edit-product')->name('product.update');
        Route::post('/products/delete', [ProductController::class, 'delete'])->middleware('permission:delete-product')->name('product.delete');
        Route::post('/products/toggle-status', [ProductController::class, 'toggleStatus'])->middleware('permission:edit-product')->name('product.toggle');
    });

    // Shop owner accounts
    Route::middleware('permission:view-shop-owner')->group(function () {
        Route::get('/shop-owners', [ShopOwnerController::class, 'index'])->name('shop-owner.index');
        Route::get('/shop-owners/fetch', [ShopOwnerController::class, 'fetch'])->name('shop-owner.fetch');
        Route::get('/shop-owners/{shopOwner}', [ShopOwnerController::class, 'show'])->name('shop-owner.show');
        Route::post('/shop-owners/save', [ShopOwnerController::class, 'store'])->middleware('permission:create-shop-owner')->name('shop-owner.store');
        Route::post('/shop-owners/update', [ShopOwnerController::class, 'update'])->middleware('permission:edit-shop-owner')->name('shop-owner.update');
        Route::post('/shop-owners/delete', [ShopOwnerController::class, 'delete'])->middleware('permission:delete-shop-owner')->name('shop-owner.delete');
        Route::post('/shop-owners/toggle-status', [ShopOwnerController::class, 'toggleStatus'])->middleware('permission:edit-shop-owner')->name('shop-owner.toggle');
    });
});


Route::prefix('shop-owner')->name('shop-owner.')->middleware(['auth', 'active', 'role:shop-owner'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'shopOwner'])->name('dashboard');


    Route::middleware('permission:view-catalog')->group(function () {
        Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
        Route::get('/catalog/fetch', [CatalogController::class, 'fetch'])->name('catalog.fetch');
        Route::post('/catalog/assign', [CatalogController::class, 'assign'])
            ->middleware('permission:assign-catalog-product')->name('catalog.assign');

        // Shop owners can create products that instantly join the shared catalog
        Route::post('/catalog/products', [ProductController::class, 'store'])
            ->middleware('permission:create-product')->name('catalog.product.store');
    });

    Route::middleware('permission:view-my-shop-product')->group(function () {
        Route::get('/products', [MyShopProductController::class, 'index'])->name('product.index');
        Route::get('/products/fetch', [MyShopProductController::class, 'fetch'])->name('product.fetch');
        Route::get('/products/{shopProduct}', [MyShopProductController::class, 'show'])->name('product.show');
        Route::post('/products/update', [MyShopProductController::class, 'update'])
            ->middleware('permission:edit-my-shop-product')->name('product.update');
        Route::post('/products/delete', [MyShopProductController::class, 'delete'])
            ->middleware('permission:delete-my-shop-product')->name('product.delete');
    });
});
