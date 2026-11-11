@extends('layouts.master')
@section('title', 'My Shop Products')

@section('post-css')
    @include('partials.page-styles')
    <style>
        .list-toolbar .filter-select {
            border: 1px solid #dce1ea;
            border-radius: 6px;
            font-size: 0.8rem;
            padding: 0.35rem 0.6rem;
            background: #fff;
            color: #1e293b;
            width: 160px;
        }

        .product-thumb {
            width: 52px;
            height: 52px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #eef2f6;
        }

        .price-strike {
            color: #94a3b8;
            font-size: 11px;
            text-decoration: line-through;
        }

        .override-chip {
            display: inline-block;
            padding: .1rem .5rem;
            border-radius: 10px;
            font-size: .65rem;
            font-weight: 700;
            background: #fef3c7;
            color: #92400e;
        }

        .inherited-chip {
            display: inline-block;
            padding: .1rem .5rem;
            border-radius: 10px;
            font-size: .65rem;
            font-weight: 700;
            background: #eef2f7;
            color: #64748b;
        }
    </style>
@endsection

@section('main-content')
    <div id="my-shop-product-vue" v-cloak>
        <div class="page-title"><span>My Shop Products</span><span class="badge-box">🏬 Shop Catalog</span></div>
        <div class="page-subtitle">
            <ul class="breadcrumb">
                <li><a href="{{ route('shop-owner.dashboard') }}">Dashboard</a></li>
                <li class="active">My Shop Products</li>
            </ul>
        </div>

        @if (!$shop)
            <div class="alert alert-warning">
                No shop is assigned to your account yet. Please contact the administrator.
            </div>
        @else
            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                <span class="badge bg-dark px-3 py-2" style="font-size:.78rem;">
                    <i class="bx bx-store"></i> {{ $shop->name }}
                </span>
                <a href="{{ route('shop.show', $shop) }}" target="_blank" class="btn btn-sm btn-outline-secondary" style="border-radius:6px;">
                    <i class="bx bx-link-external"></i> View public shop
                </a>
                <a href="{{ route('shop-owner.catalog.index') }}" class="btn btn-sm btn-primary" style="border-radius:6px;">
                    <i class="fas fa-plus-circle"></i> Add more from catalog
                </a>
            </div>
        @endif

        <div class="list-wrapper">
            <div class="list-toolbar">
                <div class="left-group">
                    <div class="search-list">
                        <input type="text" v-model="searchQuery" @input="handleSearch" placeholder="Search my products by name, sku..." />
                    </div>
                    <select class="filter-select" v-model="statusFilter" @change="applyFilters">
                        <option value="">All Visibility</option>
                        <option value="1">Visible to customers</option>
                        <option value="0">Hidden</option>
                    </select>
                </div>
                <div class="right-group">
                    <a href="{{ route('shop-owner.catalog.index') }}" class="btn-add">
                        <i class="fas fa-plus-circle"></i> Add Product
                    </a>
                </div>
            </div>

            <div class="list-table-wrap">
                <table class="list-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Catalog Price</th>
                            <th>My Price</th>
                            <th>Details Source</th>
                            <th>Thumbnail</th>
                            <th>Visibility</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="8">
                                <div class="empty-msg">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <span>Loading...</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-else-if="items.length === 0">
                            <td colspan="8">
                                <div class="empty-msg">
                                    <span v-if="!hasShop">No shop is assigned to your account yet.</span>
                                    <span v-else>No products in your shop yet. Use "Add Product" to browse the shared catalog.</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!loading" v-for="(item, index) in items" :key="item.id">
                            <td>@{{ index + 1 + (pagination.current_page - 1) * pagination.per_page }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img :src="item.catalog_thumbnail_url" class="product-thumb" alt="">
                                    <div>
                                        <strong>@{{ item.name }}</strong>
                                        <small class="text-muted d-block">@{{ item.sku }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="text-muted">@{{ formatMoney(item.catalog_price) }} tk</span></td>
                            <td>
                                <strong>@{{ formatMoney(item.effective_price) }} tk</strong>
                                <div v-if="item.price !== null" class="price-strike">
                                    own price
                                </div>
                            </td>
                            <td>
                                <span class="override-chip" v-if="item.details">Custom</span>
                                <span class="inherited-chip" v-else>Catalog</span>
                                <div class="text-muted small mt-1" style="max-width:220px;">
                                    @{{ truncate(item.effective_details, 60) }}
                                </div>
                            </td>
                            <td>
                                <span class="override-chip" v-if="item.thumbnail">Custom</span>
                                <span class="inherited-chip" v-else>Catalog</span>
                            </td>
                            <td>
                                <span class="status-badge" :class="item.is_active ? 'active' : 'inactive'">
                                    @{{ item.is_active ? 'Visible' : 'Hidden' }}
                                </span>
                            </td>
                            <td>
                                <div class="action-icons">
                                    <a @click="openEditModal(item)" title="Edit price / details / thumbnail"><i class="fas fa-pen"></i></a>
                                    <a @click="toggleVisibility(item)" title="Toggle visibility">
                                        <i class="fas" :class="item.is_active ? 'fa-toggle-on' : 'fa-toggle-off'"></i>
                                    </a>
                                    <a @click="confirmDelete(item)" title="Remove from shop" class="btn-del"><i class="fas fa-trash-alt"></i></a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
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

        {{-- Edit modal --}}
        <div class="modal fade" id="shopProductModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content service-modal">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-pen"></i> Edit My Shop Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img :src="form.previewUrl || '{{ asset('assets/images/product-placeholder.svg') }}'" alt=""
                                 style="width:72px;height:72px;object-fit:cover;border-radius:10px;border:1px solid #eef2f7;">
                            <div>
                                <div class="fw-bold" style="color:#0b1a2e;">@{{ form.name }}</div>
                                <div class="small text-muted">Catalog price: @{{ formatMoney(form.catalog_price) }} tk</div>
                            </div>
                        </div>
                        <form @submit.prevent="submitUpdate">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="service-modal-label">My Price (tk)</label>
                                    <input type="number" step="0.01" min="0" class="form-control"
                                           v-model="form.price" :class="{ 'is-invalid': errors.price }"
                                           placeholder="Leave blank to use catalog price">
                                    <small class="text-muted">Blank = use catalog base price.</small>
                                    <div v-if="errors.price" class="text-danger small mt-1">@{{ errors.price[0] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="service-modal-label">Visibility</label>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="shopProductActive"
                                               v-model="form.is_active" true-value="1" false-value="0">
                                        <label class="form-check-label" for="shopProductActive">Visible to customers</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="service-modal-label">Shop Details</label>
                                    <textarea class="form-control" rows="3" v-model="form.details"
                                              :class="{ 'is-invalid': errors.details }"
                                              placeholder="Describe this product for your customers..."></textarea>
                                    <small class="text-muted">Leave blank to use the catalog description.</small>
                                    <div v-if="errors.details" class="text-danger small mt-1">@{{ errors.details[0] }}</div>
                                </div>
                                <div class="col-12">
                                    <label class="service-modal-label">Shop Thumbnail</label>
                                    <input type="file" id="shopProductThumbnail" class="form-control" accept="image/*"
                                           @change="onThumbnailChange" :class="{ 'is-invalid': errors.thumbnail }">
                                    <small class="text-muted">Optional. Overrides the catalog image for your shop.</small>
                                    <div v-if="errors.thumbnail" class="text-danger small mt-1">@{{ errors.thumbnail[0] }}</div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" :disabled="submitting" @click="submitUpdate">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="fas fa-save me-1"></i> Update
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Delete modal --}}
        <div class="modal fade" id="shopProductDeleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content service-modal">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-trash-alt"></i> Remove From My Shop</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Remove <strong>@{{ deleteCandidate.name }}</strong> from your shop?</p>
                        <p class="text-muted mb-0"><small>The product stays in the shared catalog and can be re-added later.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" :disabled="submitting" @click="deleteItem">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="fas fa-trash"></i> Remove
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
                    statusFilter: '',
                    pagination: { current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0, prev_page_url: null, next_page_url: null },
                    loading: false,
                    submitting: false,
                    errors: {},
                    form: { id: null, name: '', catalog_price: 0, price: '', details: '', is_active: 1, previewUrl: '' },
                    thumbnailFile: null,
                    deleteCandidate: {},
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
                    axios.get("{{ route('shop-owner.product.fetch') }}", {
                        params: { search: this.searchQuery, status: this.statusFilter, page: this.pagination.current_page }
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
                        this.showToast('error', 'Could not load your shop products.', 'ALERT!');
                    }).finally(() => { this.loading = false; });
                },
                handleSearch: _.debounce(function () {
                    this.pagination.current_page = 1;
                    this.fetchItems();
                }, 300),
                applyFilters() {
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
                modalEl() {
                    return bootstrap.Modal.getOrCreateInstance(document.getElementById('shopProductModal'));
                },
                openEditModal(item) {
                    this.errors = {};
                    this.form = {
                        id: item.id,
                        name: item.name,
                        catalog_price: item.catalog_price,
                        price: item.price !== null ? item.price : '',
                        details: item.details || '',
                        is_active: item.is_active ? 1 : 0,
                        previewUrl: item.effective_thumbnail,
                    };
                    this.thumbnailFile = null;
                    const input = document.getElementById('shopProductThumbnail');
                    if (input) input.value = '';
                    this.modalEl().show();
                },
                onThumbnailChange(event) {
                    this.thumbnailFile = event.target.files[0] || null;
                    if (this.thumbnailFile) {
                        this.form.previewUrl = URL.createObjectURL(this.thumbnailFile);
                    }
                },
                submitUpdate() {
                    this.submitting = true;
                    this.errors = {};

                    const data = new FormData();
                    data.append('id', this.form.id);
                    data.append('price', this.form.price === '' ? '' : this.form.price);
                    data.append('details', this.form.details || '');
                    data.append('is_active', this.form.is_active);
                    if (this.thumbnailFile) data.append('thumbnail', this.thumbnailFile);

                    axios.post("{{ route('shop-owner.product.update') }}", data, { headers: { 'Content-Type': 'multipart/form-data' } })
                        .then(response => {
                            if (response.data.success) {
                                this.modalEl().hide();
                                this.fetchItems();
                                this.showToast('success', response.data.message, 'SUCCESS!');
                            }
                        })
                        .catch(error => {
                            if (error.response && error.response.status === 422) {
                                this.errors = error.response.data.errors || {};
                                if (error.response.data.message) this.showToast('error', error.response.data.message, 'ALERT!');
                            } else {
                                this.showToast('error', 'Could not update the product.', 'ALERT!');
                            }
                        })
                        .finally(() => { this.submitting = false; });
                },
                toggleVisibility(item) {
                    const data = new FormData();
                    data.append('id', item.id);
                    data.append('price', item.price === null ? '' : item.price);
                    data.append('details', item.details || '');
                    data.append('is_active', item.is_active ? 0 : 1);

                    axios.post("{{ route('shop-owner.product.update') }}", data, { headers: { 'Content-Type': 'multipart/form-data' } })
                        .then(response => {
                            this.fetchItems();
                            this.showToast('success', response.data.message || 'Visibility updated.', 'SUCCESS!');
                        })
                        .catch(error => {
                            this.showToast('error', error.response?.data?.message || 'Could not update visibility.', 'ALERT!');
                        });
                },
                confirmDelete(item) {
                    this.deleteCandidate = item;
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('shopProductDeleteModal')).show();
                },
                deleteItem() {
                    this.submitting = true;
                    axios.post("{{ route('shop-owner.product.delete') }}", { id: this.deleteCandidate.id })
                        .then(response => {
                            bootstrap.Modal.getInstance(document.getElementById('shopProductDeleteModal'))?.hide();
                            this.fetchItems();
                            this.showToast('success', response.data.message, 'SUCCESS!');
                        })
                        .catch(error => {
                            this.showToast('error', error.response?.data?.message || 'Failed to remove product.', 'ALERT!');
                        })
                        .finally(() => { this.submitting = false; });
                },
            },
        }).mount('#my-shop-product-vue');
    </script>
@endsection
