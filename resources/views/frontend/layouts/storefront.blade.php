<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>ECOM MARKET | @yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets') }}/toastr/build/toastr.min.css">
    <style>
        [v-cloak] {
            display: none !important;
        }

        :root {
            --brand: #111c43;
            --brand-soft: #2d6a9f;
            --ink: #0b1a2e;
            --muted: #6b7a8f;
            --line: #e9edf4;
        }

        body {
            background: #f6f8fb;
            color: var(--ink);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .store-nav {
            background: var(--brand);
            color: #fff;
            padding: 0.9rem 0;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 6px 24px rgba(17, 28, 67, .18);
        }

        .store-nav .brand {
            color: #fff;
            text-decoration: none;
            font-weight: 800;
            letter-spacing: 1px;
            font-size: 20px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .store-nav .brand i {
            color: #7db4e8;
        }

        .store-nav .brand small {
            display: block;
            font-size: 9px;
            letter-spacing: 2px;
            font-weight: 400;
            color: #8a9bb5;
            text-transform: uppercase;
        }

        .store-nav .nav-links a {
            color: #c3cde0;
            text-decoration: none;
            font-size: 13px;
            margin-left: 18px;
            font-weight: 500;
        }

        .store-nav .nav-links a:hover {
            color: #fff;
        }

        .store-footer {
            background: var(--brand);
            color: #8a9bb5;
            padding: 1.4rem 0;
            margin-top: 3rem;
            font-size: 13px;
        }

        .store-footer strong {
            color: #fff;
        }

        .page-hero {
            background: linear-gradient(135deg, #111c43 0%, #2d6a9f 100%);
            color: #fff;
            border-radius: 18px;
            padding: 2.4rem 2rem;
            margin-top: 1.8rem;
            box-shadow: 0 16px 40px rgba(17, 28, 67, .22);
        }

        .page-hero h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 .4rem;
        }

        .page-hero p {
            color: #cdd9ea;
            margin: 0;
            font-size: 14px;
        }

        .hero-search {
            margin-top: 1.4rem;
            display: flex;
            gap: .6rem;
            max-width: 540px;
        }

        .hero-search input {
            border: none;
            border-radius: 10px;
            padding: .65rem 1rem;
            font-size: 14px;
            flex: 1;
        }

        .hero-search button {
            border: none;
            background: #fff;
            color: var(--brand);
            font-weight: 700;
            font-size: 14px;
            border-radius: 10px;
            padding: .65rem 1.3rem;
        }

        .hero-search button:hover {
            background: #eef4fb;
        }

        .shop-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 14px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: .2s;
            text-decoration: none;
            color: inherit;
        }

        .shop-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 36px rgba(15, 23, 42, .10);
            color: inherit;
        }

        .shop-card .logo-wrap {
            height: 130px;
            background: #eef2f7;
            overflow: hidden;
        }

        .shop-card .logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .shop-card .body {
            padding: 1rem 1.1rem 1.1rem;
            display: flex;
            flex-direction: column;
            flex: 1;
            gap: .35rem;
        }

        .shop-card h5 {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
        }

        .shop-card .desc {
            font-size: 12.5px;
            color: var(--muted);
            flex: 1;
        }

        .shop-card .meta {
            font-size: 11.5px;
            color: var(--muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #f1f4f9;
            padding-top: .6rem;
            margin-top: .2rem;
        }

        .shop-card .visit {
            color: var(--brand-soft);
            font-weight: 700;
            font-size: 12px;
        }

        .product-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 14px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: .2s;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 36px rgba(15, 23, 42, .10);
        }

        .product-card .thumb {
            height: 180px;
            width: 100%;
            object-fit: cover;
            background: #eef2f7;
        }

        .product-card .body {
            padding: .9rem 1rem 1rem;
            display: flex;
            flex-direction: column;
            flex: 1;
            gap: .35rem;
        }

        .product-card h6 {
            font-size: 14.5px;
            font-weight: 700;
            margin: 0;
        }

        .product-card .sku {
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .product-card .details {
            font-size: 12.5px;
            color: var(--muted);
            flex: 1;
        }

        .product-card .price {
            font-size: 17px;
            font-weight: 800;
            color: var(--brand-soft);
            margin-top: .2rem;
        }

        .empty-state {
            background: #fff;
            border: 1px dashed #cfdae8;
            border-radius: 14px;
            text-align: center;
            color: var(--muted);
            padding: 3rem 1rem;
        }

        .shop-header-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 1.4rem;
            margin-top: 1.8rem;
            display: flex;
            gap: 1.2rem;
            align-items: center;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        }

        .shop-header-card img {
            width: 96px;
            height: 96px;
            border-radius: 16px;
            object-fit: cover;
            border: 1px solid var(--line);
            background: #eef2f7;
        }

        .shop-header-card h1 {
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 .3rem;
        }

        .shop-header-card .contact {
            font-size: 12.5px;
            color: var(--muted);
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .breadcrumb-store {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 1.2rem;
        }

        .breadcrumb-store a {
            color: var(--brand-soft);
            text-decoration: none;
        }

        .pager a,
        .pager span,
        .pager .pager-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            padding: 0 .7rem;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
            text-decoration: none;
            font-size: 13px;
            margin: 0 3px;
            font-family: inherit;
            cursor: pointer;
        }

        .pager a:hover,
        .pager .pager-btn:hover:not(:disabled) {
            border-color: var(--brand-soft);
            color: var(--brand-soft);
        }

        .pager .current,
        .pager .pager-btn.current {
            background: var(--brand-soft);
            border-color: var(--brand-soft);
            color: #fff;
        }

        .pager .disabled,
        .pager .pager-btn:disabled {
            color: #c3c9d4;
            background: #f9fafc;
            cursor: not-allowed;
        }

        @media (max-width: 575px) {
            .shop-header-card {
                flex-direction: column;
                text-align: center;
            }

            .page-hero {
                padding: 1.8rem 1.3rem;
            }
        }
    </style>
    @yield('post-css')
</head>

<body>
    <nav class="store-nav">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="{{ route('home') }}" class="brand">
                <i class="fas fa-store"></i>
                <span>
                    ECOM MARKET
                    <small>Multi-shop storefront</small>
                </span>
            </a>
            <div class="nav-links">
                <a href="{{ route('home') }}">All Shops</a>
                @auth
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                @else
                    <a href="{{ route('login') }}">Login</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container pb-4">
        @yield('content')
    </main>

    <footer class="store-footer">
        <div class="container d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>&copy; {{ \Carbon\Carbon::now()->format('Y') }} <strong>ECOM MARKET</strong> · Shared catalog &amp; multi-shop commerce</div>
            <div>Shop-specific prices, details and images.</div>
        </div>
    </footer>

    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets') }}/toastr/build/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    @yield('post-js')
</body>

</html>
