<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    .metismenu .menu-title {
        padding: 12px 20px 8px !important;
        font-size: 11px !important;
        text-transform: uppercase !important;
        letter-spacing: 1px !important;
        color: #6a7a94 !important;
        font-weight: 600 !important;
        opacity: 0.7 !important;
    }

    .metismenu .menu-title:not(:first-child) {
        margin-top: 15px !important;
        padding-top: 15px !important;
        border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
    }

    .metismenu .has-arrow::after {
        content: "\f054" !important;
        font-family: "Font Awesome 6 Free" !important;
        font-weight: 900 !important;
        font-size: 12px !important;
        line-height: 1 !important;
        border: none !important;
        width: auto !important;
        height: auto !important;
        background: transparent !important;
        transition: transform 0.25s ease;
        color: #8899b0;
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%) rotate(0deg);
    }

    .metismenu .mm-active > .has-arrow::after,
    .metismenu .has-arrow[aria-expanded="true"]::after {
        transform: translateY(-50%) rotate(90deg) !important;
    }

    .metismenu .has-arrow {
        position: relative;
        padding-right: 40px;
    }

    .metismenu .has-arrow:hover::after {
        color: #ffffff;
    }

    #side-menu .role-chip {
        margin: 14px 16px 4px;
        padding: 6px 12px;
        border-radius: 8px;
        background: rgba(45, 106, 159, 0.18);
        border: 1px solid rgba(120, 160, 210, 0.25);
        color: #c3d4ea;
        font-size: 11px;
        letter-spacing: .6px;
        text-transform: uppercase;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

@php
    $__user = Auth::user();
    $__isAdmin = $__user?->hasRole('admin');
    $__isOwner = $__user?->hasRole('shop-owner');
    $__ownerShop = $__isOwner ? $__user->shop : null;
@endphp

<ul class="metismenu list-unstyled" id="side-menu">
    <li class="role-chip"><i class="bx bx-user-circle me-1"></i> {{ $__isAdmin ? 'Administrator' : 'Shop Owner' }}</li>

    <li class="menu-title">Menu</li>

    @if ($__isAdmin)
        <li class="{{ request()->routeIs('admin.dashboard') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="waves-effect">
                <i class="bx bx-home-circle"></i><span>Dashboard</span>
            </a>
        </li>
        <li class="{{ request()->routeIs('admin.shop.*') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.shop.index') }}" class="waves-effect">
                <i class="bx bx-store"></i><span>Shops</span>
            </a>
        </li>
        <li class="{{ request()->routeIs('admin.product.*') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.product.index') }}" class="waves-effect">
                <i class="bx bx-package"></i><span>Shared Products</span>
            </a>
        </li>
        <li class="{{ request()->routeIs('admin.shop-owner.*') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.shop-owner.index') }}" class="waves-effect">
                <i class="bx bx-group"></i><span>Shop Owners</span>
            </a>
        </li>
    @elseif ($__isOwner)
        <li class="{{ request()->routeIs('shop-owner.dashboard') ? 'mm-active' : '' }}">
            <a href="{{ route('shop-owner.dashboard') }}" class="waves-effect">
                <i class="bx bx-home-circle"></i><span>Dashboard</span>
            </a>
        </li>
        <li class="{{ request()->routeIs('shop-owner.catalog.*') ? 'mm-active' : '' }}">
            <a href="{{ route('shop-owner.catalog.index') }}" class="waves-effect">
                <i class="bx bx-collection"></i><span>Browse Catalog</span>
            </a>
        </li>
        <li class="{{ request()->routeIs('shop-owner.product.*') ? 'mm-active' : '' }}">
            <a href="{{ route('shop-owner.product.index') }}" class="waves-effect">
                <i class="bx bx-package"></i><span>My Shop Products</span>
            </a>
        </li>
        @if ($__ownerShop)
            <li class="{{ request()->routeIs('shop-owner.shop*') ? 'mm-active' : '' }}">
                <a href="{{ route('shop.show', $__ownerShop) }}" target="_blank" class="waves-effect">
                    <i class="bx bx-world"></i><span>View My Shop</span>
                </a>
            </li>
        @endif
    @endif

    <li class="menu-title">Storefront</li>
    <li>
        <a href="{{ route('home') }}" target="_blank" class="waves-effect">
            <i class="bx bx-shopping-bag"></i><span>All Shops</span>
        </a>
    </li>
</ul>
