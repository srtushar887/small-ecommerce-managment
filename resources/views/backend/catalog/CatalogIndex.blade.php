@extends('layouts.master')
@section('title', 'Browse Catalog')

@section('post-css')
    @include('partials.page-styles')
    <style>
        .catalog-filter {
            display: inline-flex;
            background: #eef2f7;
            border-radius: 8px;
            padding: 3px;
            gap: 3px;
        }

        .catalog-filter button {
            border: none;
            background: transparent;
            color: #475569;
            font-size: .78rem;
            font-weight: 600;
            padding: .35rem .9rem;
            border-radius: 6px;
            transition: .18s;
        }

        .catalog-filter button.active {
            background: #fff;
            color: #1a3a6b;
            box-shadow: 0 2px 6px rgba(15, 23, 42, .12);
        }

        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
            padding: 1.1rem 1.25rem;
        }

        .catalog-card {
            border: 1px solid #e9edf4;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            display: flex;
            flex-direction: column;
            transition: .2s;
        }

        .catalog-card:hover {
            box-shadow: 0 8px 24px rgba(15, 23, 42, .09);
            transform: translateY(-2px);
        }

        .catalog-card .thumb {
            width: 100%;
            height: 160px;
            object-fit: cover;
            background: #eef2f7;
        }

        .catalog-card .card-body {
            padding: .85rem 1rem;
            display: flex;
            flex-direction: column;
            gap: .35rem;
            flex: 1;
        }

        .catalog-card .card-title {
            font-size: 14px;
            font-weight: 700;
            color: #0b1a2e;
            margin: 0;
        }

        .catalog-card .card-meta {
            font-size: 11.5px;
            color: #7c8aa0;
        }

        .catalog-card .card-price {
            font-size: 15px;
            font-weight: 700;
            color: #1a3a6b;
        }

        .catalog-card .card-footer {
            padding: .75rem 1rem;
            border-top: 1px solid #f1f4f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
        }

        .catalog-card .btn-add-card {
            border: none;
            background: #2d6a9f;
            color: #fff;
            font-size: .75rem;
            font-weight: 600;
            padding: .35rem .9rem;
            border-radius: 6px;
        }

        .catalog-card .btn-add-card.assigned {
            background: #eef2f7;
            color: #1a3a6b;
        }

        .catalog-card .btn-add-card:hover {
            opacity: .9;
        }

        .catalog-empty {
            grid-column: 1 / -1;
            text-align: center;
            color: #94a3b8;
            padding: 3rem 1rem;
            font-size: .9rem;
        }

        .badge-soft {
            display: inline-block;
            padding: .12rem .55rem;
            border-radius: 12px;
            font-size: .68rem;
            font-weight: 600;
        }

        .badge-soft.green { background: #e6f7e6; color: #1e6f3f; }
        .badge-soft.blue { background: #e8f0fe; color: #2d6a9f; }
        .badge-soft.grey { background: #eef2f7; color: #64748b; }
    </style>
@endsection

@section('main-content')
    <div id="catalog-vue" v-cloak>
        <div class="page-title"><span>Browse Catalog</span><span class="badge-box">🛒 Shared Catalog</span></div>
        <div class="page-subtitle">
            <ul class="breadcrumb">
                <li><a href="{{ route('shop-owner.dashboard') }}">Dashboard</a></li>
                <li class="active">Browse Catalog</li>
            </ul>
        </div>

        @if (!$shop)
            <div class="alert alert-warning">
                No shop is assigned to your account yet. Please contact the administrator before adding products.
            </div>
        @endif

        <div class="list-wrapper">
            <div class="list-toolbar">
                <div class="left-group">
                    <div class="search-list">
                        <input type="text" v-model="searchQuery" @input="handleSearch" placeholder="Search the shared catalog..." />
                    </div>
                    <div class="catalog-filter">
                        <button type="button" :class="{ active: filter === '' }" @click="setFilter('')">All</button>
                        <button type="button" :class="{ active: filter === 'available' }" @click="setFilter('available')">Not in my shop</button>
                        <button type="button" :class="{ active: filter === 'assigned' }" @click="setFilter('assigned')">In my shop</button>
                    </div>
                </div>
                <div class="right-group">
                    <button class="btn-add" @click="openProductModal" :disabled="!hasShop">
                        <i class="fas fa-plus-circle"></i> Create New Product
                    </button>
                </div>
            </div>

            <div class="catalog-grid">
                <div v-if="loading" class="catalog-empty">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <div class="mt-2">Loading catalog...</div>
                </div>
                <div v-else-if="items.length === 0" class="catalog-empty">
                    No products matched your filter.
                </div>
                <div v-else class="catalog-card" v-for="product in items" :key="product.id">
                    <img :src="product.thumbnail_url" class="thumb" alt="">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <h6 class="card-title">@{{ product.name }}</h6>
                            <span class="badge-soft" :class="product.assigned ? 'green' : 'grey'">
                                @{{ product.assigned ? 'In my shop' : 'Catalog' }}
                            </span>
                        </div>
                        <div class="card-meta">SKU: @{{ product.sku }} · By @{{ product.creator_name || 'System' }}</div>
                        <div class="card-meta" style="min-height: 34px;">@{{ truncate(product.description, 90) }}</div>
                        <div class="d-flex align-items-center justify-content-between mt-1">
                            <span class="card-price">@{{ formatMoney(product.assigned ? product.shop_price : product.base_price) }} tk</span>
                            <span v-if="product.assigned" class="badge-soft blue">My price</span>
                            <span v-else class="card-meta">Base price</span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn-add-card" :class="{ assigned: product.assigned }"
                                @click="openAssignModal(product)" :disabled="!hasShop">
                            <i class="fas" :class="product.assigned ? 'fa-pen' : 'fa-cart-plus'"></i>
                            @{{ product.assigned ? 'Update My Shop' : 'Add to My Shop' }}
                        </button>
                        <a v-if="product.assigned" href="{{ route('shop-owner.product.index') }}" class="small text-decoration-none">Manage</a>
                    </div>
                </div>
            </div>

            <div class="list-pagination" v-if="pagination.total > 0">
                <span class="pagination-info">Showing @{{ pagination.from }}–@{{ pagination.to }} of @{{ pagination.total }}</span>
                <div class="pagination-buttons">
                    <button class="page-btn" :disabled="!pagination.prev_page_url" @click="handlePageChange(pagination.current_page - 1)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button v-for="page in getVisiblePages()" :key="page" class="page-btn"
                            :class="{ active: page === pagination.current_page }" @click="handlePageChange(page)">
                        @{{ page }}
                    </button>
                    <button class="page-btn" :disabled="!pagination.next_page_url" @click="handlePageChange(pagination.current_page + 1)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Assign modal --}}
        <div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content service-modal">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-cart-plus"></i> Add To My Shop</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img :src="assignCandidate.thumbnail_url" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:10px;border:1px solid #eef2f7;">
                            <div>
                                <div class="fw-bold" style="color:#0b1a2e;">@{{ assignCandidate.name }}</div>
                                <div class="small text-muted">Catalog price: @{{ formatMoney(assignCandidate.base_price) }} tk</div>
                            </div>
                        </div>
                        <form @submit.prevent="submitAssign">
                            <div class="mb-3">
                                <label class="service-modal-label">My Price (tk)</label>
                                <input type="number" step="0.01" min="0" class="form-control" v-model="assignForm.price" :class="{ 'is-invalid': assignErrors.price }">
                                <small class="text-muted">Leave blank to use the catalog base price.</small>
                                <div v-if="assignErrors.price" class="text-danger small mt-1">@{{ assignErrors.price[0] }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="service-modal-label">Shop Details</label>
                                <textarea class="form-control" rows="3" v-model="assignForm.details" placeholder="Describe this product for your customers..." :class="{ 'is-invalid': assignErrors.details }"></textarea>
                                <div v-if="assignErrors.details" class="text-danger small mt-1">@{{ assignErrors.details[0] }}</div>
                            </div>
                            <div class="mb-0">
                                <label class="service-modal-label">Shop Thumbnail</label>
                                <input type="file" id="assignThumbnail" class="form-control" accept="image/*" @change="onAssignThumbnail" :class="{ 'is-invalid': assignErrors.thumbnail }">
                                <small class="text-muted">Optional. Overrides the catalog image for your shop.</small>
                                <div v-if="assignErrors.thumbnail" class="text-danger small mt-1">@{{ assignErrors.thumbnail[0] }}</div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" :disabled="submitting" @click="submitAssign">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="fas fa-save me-1"></i> Save To My Shop
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Create product modal --}}
        <div class="modal fade" id="newProductModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content service-modal">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-box-open"></i> Create New Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">Products you create are added to the shared catalog and can be used by every shop.</p>
                        <form @submit.prevent="submitProduct">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="service-modal-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" v-model="productForm.name" :class="{ 'is-invalid': productErrors.name }" required>
                                    <div v-if="productErrors.name" class="text-danger small mt-1">@{{ productErrors.name[0] }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="service-modal-label">SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" v-model="productForm.sku" :class="{ 'is-invalid': productErrors.sku }" required>
                                    <div v-if="productErrors.sku" class="text-danger small mt-1">@{{ productErrors.sku[0] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="service-modal-label">Base Price (tk) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control" v-model="productForm.base_price" :class="{ 'is-invalid': productErrors.base_price }" required>
                                    <div v-if="productErrors.base_price" class="text-danger small mt-1">@{{ productErrors.base_price[0] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="service-modal-label">Thumbnail</label>
                                    <input type="file" id="newProductThumbnail" class="form-control" accept="image/*" @change="onProductThumbnail" :class="{ 'is-invalid': productErrors.thumbnail }">
                                    <div v-if="productErrors.thumbnail" class="text-danger small mt-1">@{{ productErrors.thumbnail[0] }}</div>
                                </div>
                                <div class="col-12">
                                    <label class="service-modal-label">Description</label>
                                    <textarea class="form-control" rows="3" v-model="productForm.description" :class="{ 'is-invalid': productErrors.description }"></textarea>
                                    <div v-if="productErrors.description" class="text-danger small mt-1">@{{ productErrors.description[0] }}</div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" :disabled="submitting" @click="submitProduct">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="fas fa-save me-1"></i> Create Product
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('post-js')
    <script>
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['Accept'] = 'application/json';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

        Vue.createApp({
            data() {
                return {
                    hasShop: @json((bool) $shop),
                    items: [],
                    searchQuery: '',
                    filter: '',
                    pagination: { current_page: 1, last_page: 1, per_page: 12, total: 0, from: 0, to: 0, prev_page_url: null, next_page_url: null },
                    loading: false,
                    submitting: false,
                    assignCandidate: {},
                    assignForm: { price: '', details: '' },
                    assignThumbnailFile: null,
                    assignErrors: {},
                    productForm: { name: '', sku: '', description: '', base_price: 0 },
                    productThumbnailFile: null,
                    productErrors: {},
                };
            },
            mounted() {
                this.fetchItems();
            },
            methods: {
                formatMoney(value) {
                    return Number(value || 0).toFixed(2);
                },
                truncate(text, length) {
                    if (!text) return '';
                    return text.length > length ? text.substr(0, length) + '...' : text;
                },
                getVisiblePages() {
                    const pages = [];
                    const current = this.pagination.current_page;
                    const last = this.pagination.last_page;
                    if (last <= 5) {
                        for (let i = 2; i < last; i++) pages.push(i);
                    } else {
                        const start = Math.max(2, current - 2);
                        const end = Math.min(last - 1, current + 2);
                        for (let i = start; i <= end; i++) pages.push(i);
                    }
                    return pages;
                },
                fetchItems() {
                    this.loading = true;
                    axios.get("{{ route('shop-owner.catalog.fetch') }}", {
                        params: { search: this.searchQuery, filter: this.filter, page: this.pagination.current_page }
                    }).then(response => {
                        this.items = response.data.data || [];
                        this.pagination = {
                            current_page: response.data.current_page,
                            last_page: response.data.last_page,
                            per_page: response.data.per_page,
                            total: response.data.total,
                            from: response.data.from,
                            to: response.data.to,
                            prev_page_url: response.data.prev_page_url,
                            next_page_url: response.data.next_page_url,
                        };
                    }).catch(error => {
                        console.error(error);
                        this.showToast('error', 'Could not load catalog.', 'ALERT!');
                    }).finally(() => { this.loading = false; });
                },
                handleSearch: _.debounce(function () {
                    this.pagination.current_page = 1;
                    this.fetchItems();
                }, 300),
                setFilter(filter) {
                    this.filter = filter;
                    this.pagination.current_page = 1;
                    this.fetchItems();
                },
                handlePageChange(page) {
                    if (page < 1 || page > this.pagination.last_page) return;
                    this.pagination.current_page = page;
                    this.fetchItems();
                },
                showToast(type, message, title) {
                    if (typeof toastr === 'undefined') {
                        alert(message);
                        return;
                    }
                    toastr.options = { closeButton: false, progressBar: true, positionClass: 'toast-top-center', timeOut: 5000 };
                    toastr[type](message, title);
                },
                openAssignModal(product) {
                    this.assignCandidate = product;
                    this.assignForm.price = product.shop_price !== null ? product.shop_price : product.base_price;
                    this.assignForm.details = product.shop_details || '';
                    this.assignThumbnailFile = null;
                    this.assignErrors = {};
                    const input = document.getElementById('assignThumbnail');
                    if (input) input.value = '';
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('assignModal')).show();
                },
                onAssignThumbnail(event) {
                    this.assignThumbnailFile = event.target.files[0] || null;
                },
                submitAssign() {
                    this.submitting = true;
                    this.assignErrors = {};

                    const data = new FormData();
                    data.append('product_id', this.assignCandidate.id);
                    if (this.assignForm.price !== '' && this.assignForm.price !== null) data.append('price', this.assignForm.price);
                    if (this.assignForm.details !== null) data.append('details', this.assignForm.details || '');
                    if (this.assignThumbnailFile) data.append('thumbnail', this.assignThumbnailFile);

                    axios.post("{{ route('shop-owner.catalog.assign') }}", data, { headers: { 'Content-Type': 'multipart/form-data' } })
                        .then(response => {
                            if (response.data.success) {
                                bootstrap.Modal.getInstance(document.getElementById('assignModal'))?.hide();
                                this.fetchItems();
                                this.showToast('success', response.data.message, 'SUCCESS!');
                            }
                        })
                        .catch(error => {
                            if (error.response && error.response.status === 422) {
                                this.assignErrors = error.response.data.errors || {};
                                if (error.response.data.message) this.showToast('error', error.response.data.message, 'ALERT!');
                            } else {
                                this.showToast('error', 'Could not add the product to your shop.', 'ALERT!');
                            }
                        })
                        .finally(() => { this.submitting = false; });
                },
                openProductModal() {
                    this.productForm = { name: '', sku: '', description: '', base_price: 0 };
                    this.productThumbnailFile = null;
                    this.productErrors = {};
                    const input = document.getElementById('newProductThumbnail');
                    if (input) input.value = '';
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('newProductModal')).show();
                },
                onProductThumbnail(event) {
                    this.productThumbnailFile = event.target.files[0] || null;
                },
                submitProduct() {
                    this.submitting = true;
                    this.productErrors = {};

                    const data = new FormData();
                    Object.entries(this.productForm).forEach(([key, value]) => data.append(key, value));
                    data.append('is_active', 1);
                    if (this.productThumbnailFile) data.append('thumbnail', this.productThumbnailFile);

                    axios.post("{{ route('shop-owner.catalog.product.store') }}", data, { headers: { 'Content-Type': 'multipart/form-data' } })
                        .then(response => {
                            if (response.data.success) {
                                bootstrap.Modal.getInstance(document.getElementById('newProductModal'))?.hide();
                                this.fetchItems();
                                this.showToast('success', response.data.message, 'SUCCESS!');
                            }
                        })
                        .catch(error => {
                            if (error.response && error.response.status === 422) {
                                this.productErrors = error.response.data.errors || {};
                            } else {
                                this.showToast('error', 'Could not create the product.', 'ALERT!');
                            }
                        })
                        .finally(() => { this.submitting = false; });
                },
            },
        }).mount('#catalog-vue');
    </script>
@endsection
