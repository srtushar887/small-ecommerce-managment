@extends('layouts.master')
@section('title', 'Shared Products')

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
    </style>
@endsection

@section('main-content')
    <div id="product-vue" v-cloak>
        <div class="page-title"><span>Shared Products</span><span class="badge-box">📦 Shared Catalog</span></div>
        <div class="page-subtitle">
            <ul class="breadcrumb">
                <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="active">Shared Products</li>
            </ul>
        </div>

        <div class="list-wrapper">
            <div class="list-toolbar">
                <div class="left-group">
                    <div class="search-list">
                        <input type="text" v-model="searchQuery" @input="handleSearch" placeholder="Search products by name, sku..." />
                    </div>
                    <select class="filter-select" v-model="statusFilter" @change="applyFilters">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="right-group">
                    <button class="btn-add" @click="openCreateModal">
                        <i class="fas fa-plus-circle"></i> Add Product
                    </button>
                </div>
            </div>

            <div class="list-table-wrap">
                <table class="list-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Created By</th>
                            <th>Base Price</th>
                            <th>Shops</th>
                            <th>Status</th>
                            <th>Created</th>
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
                                <div class="empty-msg">No products found. Click "Add Product" to create one.</div>
                            </td>
                        </tr>
                        <tr v-if="!loading" v-for="(product, index) in items" :key="product.id">
                            <td>@{{ index + 1 + (pagination.current_page - 1) * pagination.per_page }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img :src="product.thumbnail_url" class="product-thumb" alt="">
                                    <div>
                                        <strong>@{{ product.name }}</strong>
                                        <small class="text-muted d-block">@{{ product.sku }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span>@{{ product.creator_name || 'System' }}</span>
                                <small class="text-muted d-block" v-if="product.creator_name === '{{ auth()->user()->name }}'">Admin</small>
                            </td>
                            <td><strong>@{{ formatMoney(product.base_price) }} tk</strong></td>
                            <td><span class="info-badge">@{{ product.shops_count }}</span></td>
                            <td>
                                <span class="status-badge" :class="product.is_active ? 'active' : 'inactive'">
                                    @{{ product.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td><small class="text-muted">@{{ formatDate(product.created_at) }}</small></td>
                            <td>
                                <div class="action-icons">
                                    <a @click="openEditModal(product)" title="Edit"><i class="fas fa-pen"></i></a>
                                    <a @click="toggleStatus(product)" title="Toggle status">
                                        <i class="fas" :class="product.is_active ? 'fa-toggle-on' : 'fa-toggle-off'"></i>
                                    </a>
                                    <a @click="confirmDelete(product)" title="Delete" class="btn-del"><i class="fas fa-trash-alt"></i></a>
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

        <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content service-modal">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-box-open"></i> @{{ form.id ? 'Edit Product' : 'Add Product' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitProduct">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="service-modal-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" :class="{ 'is-invalid': errors.name }" v-model="form.name" required>
                                    <div v-if="errors.name" class="text-danger small mt-1">@{{ errors.name[0] }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="service-modal-label">SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" :class="{ 'is-invalid': errors.sku }" v-model="form.sku" required>
                                    <div v-if="errors.sku" class="text-danger small mt-1">@{{ errors.sku[0] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="service-modal-label">Base Price (tk) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control" :class="{ 'is-invalid': errors.base_price }" v-model="form.base_price" required>
                                    <div v-if="errors.base_price" class="text-danger small mt-1">@{{ errors.base_price[0] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="service-modal-label">Thumbnail</label>
                                    <input type="file" id="productThumbnail" class="form-control" accept="image/*" @change="onThumbnailChange" :class="{ 'is-invalid': errors.thumbnail }">
                                    <div v-if="errors.thumbnail" class="text-danger small mt-1">@{{ errors.thumbnail[0] }}</div>
                                </div>
                                <div class="col-12">
                                    <label class="service-modal-label">Description</label>
                                    <textarea class="form-control" rows="3" v-model="form.description" :class="{ 'is-invalid': errors.description }"></textarea>
                                    <div v-if="errors.description" class="text-danger small mt-1">@{{ errors.description[0] }}</div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="productStatus" v-model="form.is_active" true-value="1" false-value="0">
                                        <label class="form-check-label" for="productStatus">Active (visible in shared catalog)</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" :disabled="submitting" @click="submitProduct">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="fas fa-save me-1"></i> @{{ form.id ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="productDeleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content service-modal">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-trash-alt"></i> Delete Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Delete <strong>@{{ deleteCandidate.name }}</strong> from the shared catalog?</p>
                        <p class="text-danger mb-0"><small>The product will be removed from all shops.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" :disabled="submitting" @click="deleteProduct">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="fas fa-trash"></i> Delete
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
                    items: [],
                    searchQuery: '',
                    statusFilter: '',
                    pagination: { current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0, prev_page_url: null, next_page_url: null },
                    loading: false,
                    submitting: false,
                    errors: {},
                    form: { id: null, name: '', sku: '', description: '', base_price: 0, is_active: 1 },
                    thumbnailFile: null,
                    deleteCandidate: {},
                };
            },
            mounted() {
                this.fetchItems();
            },
            methods: {
                formatDate(date) {
                    if (!date) return '';
                    const d = new Date(date);
                    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                },
                formatMoney(value) {
                    return Number(value || 0).toFixed(2);
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
                    axios.get("{{ route('admin.product.fetch') }}", {
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
                        this.showToast('error', 'Could not load products.', 'ALERT!');
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
                    return bootstrap.Modal.getOrCreateInstance(document.getElementById('productModal'));
                },
                resetForm() {
                    this.form = { id: null, name: '', sku: '', description: '', base_price: 0, is_active: 1 };
                    this.thumbnailFile = null;
                    this.errors = {};
                    const input = document.getElementById('productThumbnail');
                    if (input) input.value = '';
                },
                openCreateModal() {
                    this.resetForm();
                    this.modalEl().show();
                },
                openEditModal(product) {
                    this.errors = {};
                    axios.get("{{ url('admin/products') }}/" + product.id)
                        .then(response => {
                            const p = response.data.product;
                            this.form = {
                                id: p.id,
                                name: p.name,
                                sku: p.sku,
                                description: p.description || '',
                                base_price: p.base_price,
                                is_active: p.is_active ? 1 : 0,
                            };
                            this.thumbnailFile = null;
                            const input = document.getElementById('productThumbnail');
                            if (input) input.value = '';
                            this.modalEl().show();
                        })
                        .catch(() => this.showToast('error', 'Could not load product.', 'ALERT!'));
                },
                onThumbnailChange(event) {
                    this.thumbnailFile = event.target.files[0] || null;
                },
                submitProduct() {
                    this.submitting = true;
                    this.errors = {};

                    const data = new FormData();
                    Object.entries(this.form).forEach(([key, value]) => {
                        if (value !== null && value !== undefined) data.append(key, value);
                    });
                    if (this.thumbnailFile) data.append('thumbnail', this.thumbnailFile);

                    const url = this.form.id ? "{{ route('admin.product.update') }}" : "{{ route('admin.product.store') }}";
                    axios.post(url, data, { headers: { 'Content-Type': 'multipart/form-data' } })
                        .then(response => {
                            if (response.data.success) {
                                this.modalEl().hide();
                                this.fetchItems();
                                this.showToast('success', response.data.message, 'SUCCESS!');
                            }
                        })
                        .catch(error => {
                            if (error.response && error.response.status === 422) {
                                this.errors = error.response.data.errors || { message: error.response.data.message };
                            } else {
                                this.showToast('error', 'An error occurred. Please try again.', 'ALERT!');
                            }
                        })
                        .finally(() => { this.submitting = false; });
                },
                toggleStatus(product) {
                    axios.post("{{ route('admin.product.toggle') }}", { id: product.id })
                        .then(response => {
                            this.fetchItems();
                            this.showToast('success', response.data.message, 'SUCCESS!');
                        })
                        .catch(() => this.showToast('error', 'Could not update status.', 'ALERT!'));
                },
                confirmDelete(product) {
                    this.deleteCandidate = product;
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('productDeleteModal')).show();
                },
                deleteProduct() {
                    this.submitting = true;
                    axios.post("{{ route('admin.product.delete') }}", { id: this.deleteCandidate.id })
                        .then(response => {
                            bootstrap.Modal.getInstance(document.getElementById('productDeleteModal'))?.hide();
                            this.fetchItems();
                            this.showToast('success', response.data.message, 'SUCCESS!');
                        })
                        .catch(error => {
                            this.showToast('error', error.response?.data?.message || 'Failed to delete product.', 'ALERT!');
                        })
                        .finally(() => { this.submitting = false; });
                },
            },
        }).mount('#product-vue');
    </script>
@endsection
