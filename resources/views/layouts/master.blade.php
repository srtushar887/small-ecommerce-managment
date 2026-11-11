<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>ECOM DASHBOARD | @yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.css')

    @yield('post-css')
</head>

<body data-sidebar="dark" data-layout-mode="light">

    <!-- Begin page -->
    <div id="layout-wrapper">

        @include('partials.header')

        <!-- ========== Left Sidebar Start ========== -->
        <div class="vertical-menu" style="background-color: #111c43">
            <div data-simplebar class="h-100">
                <div id="sidebar-menu" style="background-color: #111c43">
                    @include('partials.sidebar')
                </div>
            </div>
        </div>
        <!-- Left Sidebar End -->

        <!-- Start right Content here -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid" style="margin-top: -60px;">
                    @yield('main-content')
                </div>
            </div>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                <span style="font-size: 13px; color: #6c757d;">
                                    <i class="bx bx-copyright me-1"></i>
                                    {{ \Carbon\Carbon::now()->format('Y') }}
                                    <span class="fw-semibold" style="color: #111c43;">ECOM DASHBOARD</span>
                                </span>
                                <span style="color: #ced4da;">|</span>
                                <span style="font-size: 12px; color: #6c757d;">v1.0.0</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-md-end gap-2">
                                <span style="font-size: 12px; color: #6c757d;">Shared catalog &amp; multi-shop commerce</span>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <div class="rightbar-overlay"></div>

    @include('partials.js')
    @yield('post-js')
    @include('partials.message')
</body>

</html>
