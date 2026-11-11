@extends('layouts.master')
@section('title', 'Admin Dashboard')

@section('post-css')
    @include('partials.page-styles')
    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #e9edf4;
            border-radius: 12px;
            padding: 1.1rem 1.2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #0b1a2e;
            line-height: 1.1;
        }

        .stat-label {
            font-size: 12px;
            color: #6b7a8f;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .panel-card {
            background: #fff;
            border: 1px solid #e9edf4;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .panel-card .panel-head {
            padding: .9rem 1.2rem;
            border-bottom: 1px solid #eef2f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .panel-card .panel-head h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: #0b1a2e;
        }

        .panel-card .panel-body {
            padding: .4rem 1.2rem 1rem;
        }

        .mini-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: .7rem 0;
            border-bottom: 1px solid #f4f6f9;
        }

        .mini-row:last-child {
            border-bottom: none;
        }

        .mini-thumb {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #eef2f7;
        }
    </style>
@endsection

@section('main-content')
    <div class="page-title"><span>Dashboard</span><span class="badge-box">🛡️ Admin Panel</span></div>
    <div class="page-subtitle">
        <ul class="breadcrumb">
            <li>Dashboard</li>
        </ul>
    </div>

    <div class="stat-grid">
        <div class="stat-card">
            <span class="stat-icon" style="background:#e8f0fe;color:#2d6a9f;"><i class="bx bx-store"></i></span>
            <div>
                <div class="stat-value">{{ $stats['shops'] }}</div>
                <div class="stat-label">Shops ({{ $stats['active_shops'] }} active)</div>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon" style="background:#e6f7e6;color:#1e6f3f;"><i class="bx bx-package"></i></span>
            <div>
                <div class="stat-value">{{ $stats['products'] }}</div>
                <div class="stat-label">Catalog ({{ $stats['active_products'] }} active)</div>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon" style="background:#f3e8ff;color:#6d28d9;"><i class="bx bx-group"></i></span>
            <div>
                <div class="stat-value">{{ $stats['owners'] }}</div>
                <div class="stat-label">Shop Owners</div>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon" style="background:#fff4e5;color:#b45309;"><i class="bx bx-link-alt"></i></span>
            <div>
                <div class="stat-value">{{ $stats['assignments'] }}</div>
                <div class="stat-label">Shop Assignments</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="panel-card h-100">
                <div class="panel-head">
                    <h6>Recent Shops</h6>
                    <a href="{{ route('admin.shop.index') }}" class="small text-decoration-none">View all</a>
                </div>
                <div class="panel-body">
                    @forelse ($recentShops as $shop)
                        <div class="mini-row">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $shop->logo_url }}" class="mini-thumb" alt="{{ $shop->name }}">
                                <div>
                                    <div class="fw-semibold" style="font-size:13px;color:#0b1a2e;">{{ $shop->name }}</div>
                                    <div class="small text-muted">{{ $shop->owner?->name ?? 'No owner' }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="status-badge {{ $shop->is_active ? 'active' : 'inactive' }}">{{ $shop->is_active ? 'Active' : 'Inactive' }}</span>
                                <div class="small text-muted mt-1">{{ $shop->products_count }} products</div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-msg">No shops yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="panel-card h-100">
                <div class="panel-head">
                    <h6>Recent Catalog Products</h6>
                    <a href="{{ route('admin.product.index') }}" class="small text-decoration-none">View all</a>
                </div>
                <div class="panel-body">
                    @forelse ($recentProducts as $product)
                        <div class="mini-row">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $product->thumbnail_url }}" class="mini-thumb" alt="{{ $product->name }}">
                                <div>
                                    <div class="fw-semibold" style="font-size:13px;color:#0b1a2e;">{{ $product->name }}</div>
                                    <div class="small text-muted">{{ $product->creator?->name ?? 'System' }} · {{ number_format((float) $product->base_price, 2) }} tk</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="status-badge {{ $product->is_active ? 'active' : 'inactive' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                                <div class="small text-muted mt-1">{{ $product->shops_count }} shops</div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-msg">No products yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
