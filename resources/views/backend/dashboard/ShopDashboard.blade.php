@extends('layouts.master')
@section('title', 'Shop Owner Dashboard')

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

        .shop-hero {
            background: linear-gradient(135deg, #111c43, #2d6a9f);
            color: #fff;
            border-radius: 14px;
            padding: 1.4rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(17, 28, 67, 0.18);
        }

        .shop-hero img {
            width: 74px;
            height: 74px;
            border-radius: 14px;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, .2);
            background: #fff;
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

        .mini-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: .7rem 1.2rem;
            border-bottom: 1px solid #f4f6f9;
        }

        .mini-row:last-child {
            border-bottom: none;
        }

        .mini-thumb {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #eef2f7;
        }
    </style>
@endsection

@section('main-content')
    <div class="page-title"><span>Dashboard</span><span class="badge-box">🏬 Shop Owner</span></div>
    <div class="page-subtitle">
        <ul class="breadcrumb">
            <li>Dashboard</li>
        </ul>
    </div>

    @if (!$shop)
        <div class="alert alert-warning">
            No shop is assigned to your account yet. Please contact the administrator.
        </div>
    @else
        <div class="shop-hero">
            <img src="{{ $shop->logo_url }}" alt="{{ $shop->name }}">
            <div class="flex-grow-1">
                <h5 class="mb-1 fw-bold">{{ $shop->name }}</h5>
                <div style="font-size:13px; opacity:.85;">{{ $shop->address ?: 'No address provided' }}</div>
                <div class="mt-2 d-flex gap-2 flex-wrap">
                    <span class="badge {{ $shop->is_active ? 'bg-success' : 'bg-danger' }}">{{ $shop->is_active ? 'Shop Active' : 'Shop Inactive' }}</span>
                    <a href="{{ route('shop.show', $shop) }}" target="_blank" class="badge bg-light text-dark text-decoration-none">
                        <i class="bx bx-link-external"></i> View public shop
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="stat-grid">
        <div class="stat-card">
            <span class="stat-icon" style="background:#e8f0fe;color:#2d6a9f;"><i class="bx bx-collection"></i></span>
            <div>
                <div class="stat-value">{{ $stats['catalog_products'] }}</div>
                <div class="stat-label">Catalog Products</div>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon" style="background:#e6f7e6;color:#1e6f3f;"><i class="bx bx-package"></i></span>
            <div>
                <div class="stat-value">{{ $stats['assigned_products'] }}</div>
                <div class="stat-label">My Shop Products</div>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon" style="background:#fff4e5;color:#b45309;"><i class="bx bx-check-shield"></i></span>
            <div>
                <div class="stat-value">{{ $stats['active_assigned'] }}</div>
                <div class="stat-label">Visible To Customers</div>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon" style="background:#f3e8ff;color:#6d28d9;"><i class="bx bx-plus-circle"></i></span>
            <div>
                <div class="stat-value">{{ $stats['my_created'] }}</div>
                <div class="stat-label">Products I Created</div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap mb-3">
        <a href="{{ route('shop-owner.catalog.index') }}" class="btn-add"><i class="fas fa-plus-circle"></i> Browse Shared Catalog</a>
        <a href="{{ route('shop-owner.product.index') }}" class="btn btn-secondary" style="border-radius:6px;font-size:.8rem;">Manage My Shop Products</a>
    </div>

    <div class="panel-card">
        <div class="panel-head">
            <h6>Recently Added To My Shop</h6>
        </div>
        @forelse ($recentAssignments as $assignment)
            <div class="mini-row">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ $assignment->effective_thumbnail }}" class="mini-thumb" alt="">
                    <div>
                        <div class="fw-semibold" style="font-size:13px;color:#0b1a2e;">{{ $assignment->product?->name }}</div>
                        <div class="small text-muted">{{ $assignment->product?->sku }}</div>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-semibold" style="font-size:13px;">{{ number_format($assignment->effective_price, 2) }} tk</div>
                    <span class="status-badge {{ $assignment->is_active ? 'active' : 'inactive' }}">{{ $assignment->is_active ? 'Visible' : 'Hidden' }}</span>
                </div>
            </div>
        @empty
            <div class="empty-msg">You have not added any catalog product to your shop yet.</div>
        @endforelse
    </div>
@endsection
