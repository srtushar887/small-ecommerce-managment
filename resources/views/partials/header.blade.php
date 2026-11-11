<style>
    /* ── Dark top bar ── */
    #page-topbar {
        background-color: #111c43 !important;
        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.05);
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 1000 !important;
        height: 70px !important;
    }

    .navbar-header {
        padding: 0 20px !important;
        height: 70px !important;
    }

    .navbar-brand-box {
        background: transparent !important;
        padding: 0 !important;
        min-height: 0 !important;
        border-bottom: none !important;
        margin-right: 14px;
    }

    .navbar-brand-box .logo {
        display: flex;
        align-items: center;
        text-decoration: none;
    }

    .navbar-brand-box .logo-text {
        display: flex;
        flex-direction: column;
        line-height: 1.1;
    }

    .navbar-brand-box .logo-text .brand-name {
        font-size: 22px;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: 0.5px;
    }

    .navbar-brand-box .logo-text .brand-sub {
        font-size: 9px;
        color: #8a9bb5;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-weight: 400;
        margin-top: 2px;
    }

    #page-topbar .header-item,
    #page-topbar .header-item i {
        color: #c3cde0 !important;
    }

    #page-topbar .header-item:hover {
        color: #ffffff !important;
    }

    #page-topbar .header-profile-user {
        height: 32px;
        width: 32px;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, 0.15);
    }

    .header-user-name {
        font-size: 13px;
        font-weight: 600;
    }

    .header-user-role {
        font-size: 10px;
        color: #8a9bb5 !important;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .storefront-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #c3cde0 !important;
        text-decoration: none;
        font-size: 13px;
        padding: 6px 12px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 8px;
        transition: all .2s ease;
    }

    .storefront-link:hover {
        color: #fff !important;
        background: rgba(255, 255, 255, 0.08);
    }

    /* ── Layout alignment (no gap under header) ── */
    .vertical-menu {
        background-color: #111c43 !important;
        padding-top: 0 !important;
        margin-top: 0 !important;
        top: 70px !important;
    }
    #sidebar-menu {
        background-color: #111c43 !important;
        padding-top: 0 !important;
        margin-top: 0 !important;
    }
    #sidebar-menu ul { margin-top: 0 !important; padding-top: 0 !important; }
    #layout-wrapper .vertical-menu {
        top: 70px !important;
        position: fixed !important;
        left: 0 !important;
        bottom: 0 !important;
        width: 250px !important;
        z-index: 999 !important;
        overflow-y: auto !important;
        height: calc(100% - 70px) !important;
    }
    .main-content {
        margin-left: 250px !important;
        padding-top: 70px !important;
    }
    body.vertical-collpsed #layout-wrapper .vertical-menu { width: 70px !important; }
    body.vertical-collpsed .main-content { margin-left: 70px !important; }
    body.sidebar-enable .vertical-menu { display: block !important; }
    body.vertical-collpsed #layout-wrapper .vertical-menu:hover { width: 250px !important; }
    body.vertical-collpsed .vertical-menu:hover #sidebar-menu .menu-title,
    body.vertical-collpsed .vertical-menu:hover #sidebar-menu .badge { display: block !important; }
    body.vertical-collpsed .vertical-menu:hover #sidebar-menu > ul > li > a span {
        display: inline !important;
        padding-left: 0 !important;
    }
    body.vertical-collpsed .vertical-menu:hover #sidebar-menu .has-arrow:after { display: block !important; }

    @media (max-width: 991.98px) {
        .vertical-menu { width: 70px !important; }
        .main-content { margin-left: 70px !important; }
        .hide-mobile { display: none !important; }
    }
</style>

<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex align-items-center" style="width: 100%;">
            <div class="navbar-brand-box">
                <a href="{{ route('dashboard') }}" class="logo">
                    <span class="logo-text">
                        <span class="brand-name">ECOM</span>
                        <span class="brand-sub">Dashboard</span>
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect"
                    id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>

            <div class="flex-grow-1"></div>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('home') }}" target="_blank" class="storefront-link hide-mobile">
                    <i class="bx bx-world"></i> <span>Storefront</span>
                </a>

                <div class="dropdown d-inline-block">
                    <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-inline-flex align-items-center gap-2">
                            <span class="avatar-xs">
                                <span class="avatar-title rounded-circle"
                                      style="background:#2d6a9f; color:#fff; width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; font-size:13px; border-radius:50%;">
                                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                                </span>
                            </span>
                            <span class="d-none d-xl-inline-flex flex-column text-start">
                                <span class="header-user-name">{{ Auth::user()->name ?? 'User' }}</span>
                                <span class="header-user-role">{{ Auth::user()->getRoleNames()->first() ?? 'user' }}</span>
                            </span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('dashboard') }}">
                            <i class="bx bx-home-circle font-size-16 align-middle me-1"></i>
                            <span>Dashboard</span>
                        </a>
                        @if (Auth::user()->isShopOwner() && Auth::user()->shop)
                            <a class="dropdown-item" href="{{ route('shop.show', Auth::user()->shop) }}" target="_blank">
                                <i class="bx bx-store font-size-16 align-middle me-1"></i>
                                <span>View My Shop</span>
                            </a>
                        @endif
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i>
                            <span>Logout</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
