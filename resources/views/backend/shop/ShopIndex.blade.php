@extends('layouts.master')
@section('title', 'Shops')

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

        .shop-logo {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #eef2f6;
        }

        .owner-cell small {
            display: block;
            color: #7c8aa0;
        }
    </style>
@endsection

@section('main-content')
    <div id="shop-vue" v-cloak>
        <div class="page-title"><span>Shops</span><span class="badge-box">🏬 Management</span></div>
        <div class="page-subtitle">
            <ul class="breadcrumb">
                <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="active">Shops</li>
            </ul>
        </div>

        <div class="list-wrapper">
            <div class="list-toolbar">
                <div class="left-group">
                    <div class="search-list">
                        <input type="text" v-model="searchQuery" @input="handleSearch" placeholder="Search shops..." />
                    </div>
                    <select class="filter-select" v-model="statusFilter" @change="applyFilters">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="right-group">
                    <button class="btn-add" @click="openCreateModal">
                        <i class="fas fa-plus-circle"></i> Add Shop
                    </button>
                </div>
            </div>

            <div class="list-table-wrap">
                <table class="list-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Shop</th>
                            <th>Owner</th>
                            <th>Contact</th>
                            <th>Products</th>
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
                                <div class="empty-msg">No shops found. Click "Add Shop" to create one.</div>
                            </td>
                        </tr>
                        <tr v-if="!loading" v-for="(shop, index) in items" :key="shop.id">
                            <td>@{{ index + 1 + (pagination.current_page - 1) * pagination.per_page }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img :src="shop.logo_url" class="shop-logo" alt="">
                                    <div>
                                        <strong>@{{ shop.name }}</strong>
                                        <small class="text-muted d-block">/@{{ shop.slug }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="owner-cell">
                                <span>@{{ shop.owner_name || '—' }}</span>
                                <small>@{{ shop.owner_email }}</small>
                            </td>
                            <td>
                                <div class="small">@{{ shop.phone || '—' }}</div>
                                <div class="text-muted small">@{{ shop.address || '' }}</div>
                            </td>
                            <td><span class="info-badge">@{{ shop.products_count }}</span></td>
                            <td>
                                <span class="status-badge" :class="shop.is_active ? 'active' : 'inactive'">
                                    @{{ shop.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td><small class="text-muted">@{{ formatDate(shop.created_at) }}</small></td>
                            <td>
                                <div class="action-icons">
                                    <a :href="'{{ url('/shop') }}/' + shop.slug" target="_blank" title="View shop">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a @click="openEditModal(shop)" title="Edit"><i class="fas fa-pen"></i></a>
                                    <a @click="toggleStatus(shop)" title="Toggle status">
                                        <i class="fas" :class="shop.is_active ? 'fa-toggle-on' : 'fa-toggle-off'"></i>
                                    </a>
                                    <a @click="confirmDelete(shop)" title="Delete" class="btn-del"><i class="fas fa-trash-alt"></i></a>
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

        {{-- Create / edit modal --}}
        <div class="modal fade" id="shopModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content service-modal">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-store"></i> @{{ form.id ? 'Edit Shop' : 'Add Shop' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitShop">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="service-modal-label">Shop Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" :class="{ 'is-invalid': errors.name }" v-model="form.name" required>
                                    <div v-if="errors.name" class="text-danger small mt-1">@{{ errors.name[0] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="service-modal-label">Shop Owner <span class="text-danger">*</span></label>
                                    <select class="form-select" :class="{ 'is-invalid': errors.user_id }" v-model="form.user_id" required>
                                        <option value="">Select shop owner</option>
                                        <option v-for="owner in owners" :key="owner.id" :value="owner.id">
                                            @{{ owner.name }} (@{{ owner.email }})
                                        </option>
                                    </select>
                                    <div v-if="errors.user_id" class="text-danger small mt-1">@{{ errors.user_id[0] }}</div>
                                    <div v-if="errors.message" class="text-danger small mt-1">@{{ errors.message }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="service-modal-label">Phone</label>
                                    <input type="text" class="form-control" v-model="form.phone" :class="{ 'is-invalid': errors.phone }">
                                    <div v-if="errors.phone" class="text-danger small mt-1">@{{ errors.phone[0] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="service-modal-label">Address</label>
                                    <input type="text" class="form-control" v-model="form.address" :class="{ 'is-invalid': errors.address }">
                                    <div v-if="errors.address" class="text-danger small mt-1">@{{ errors.address[0] }}</div>
                                </div>
                                <div class="col-12">
                                    <label class="service-modal-label">Description</label>
                                    <textarea class="form-control" rows="3" v-model="form.description" :class="{ 'is-invalid': errors.description }"></textarea>
                                    <div v-if="errors.description" class="text-danger small mt-1">@{{ errors.description[0] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="service-modal-label">Logo</label>
                                    <input type="file" id="shopLogo" class="form-control" accept="image/*" @change="onLogoChange" :class="{ 'is-invalid': errors.logo }">
                                    <div v-if="errors.logo" class="text-danger small mt-1">@{{ errors.logo[0] }}</div>
                                    <small class="text-muted">JPG, PNG, WEBP or SVG · max 4 MB</small>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="shopStatus" v-model="form.is_active" true-value="1" false-value="0">
                                        <label class="form-check-label" for="shopStatus">Active</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" :disabled="submitting" @click="submitShop">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="fas fa-save me-1"></i> @{{ form.id ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Delete modal --}}
        <div class="modal fade" id="shopDeleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content service-modal">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-trash-alt"></i> Delete Shop</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete <strong>@{{ deleteCandidate.name }}</strong>?</p>
                        <p class="text-danger mb-0"><small>All product assignments for this shop will be removed.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" :disabled="submitting" @click="deleteShop">
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
                    owners: @json($owners),
                    searchQuery: '',
                    statusFilter: '',
                    pagination: { current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0, prev_page_url: null, next_page_url: null },
                    loading: false,
                    submitting: false,
                    errors: {},
                    form: { id: null, name: '', user_id: '', phone: '', address: '', description: '', is_active: 1 },
                    logoFile: null,
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
                    axios.get("{{ route('admin.shop.fetch') }}", {
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
                        this.showToast('error', 'Could not load shops.', 'ALERT!');
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
                    return bootstrap.Modal.getOrCreateInstance(document.getElementById('shopModal'));
                },
                resetForm() {
                    this.form = { id: null, name: '', user_id: '', phone: '', address: '', description: '', is_active: 1 };
                    this.logoFile = null;
                    this.errors = {};
                    const input = document.getElementById('shopLogo');
                    if (input) input.value = '';
                },
                openCreateModal() {
                    this.resetForm();
                    this.modalEl().show();
                },
                openEditModal(shop) {
                    this.errors = {};
                    axios.get("{{ url('admin/shops') }}/" + shop.id)
                        .then(response => {
                            const s = response.data.shop;
                            this.form = {
                                id: s.id,
                                name: s.name,
                                user_id: s.owner_id || '',
                                phone: s.phone || '',
                                address: s.address || '',
                                description: s.description || '',
                                is_active: s.is_active ? 1 : 0,
                            };
                            this.logoFile = null;
                            const input = document.getElementById('shopLogo');
                            if (input) input.value = '';
                            this.modalEl().show();
                        })
                        .catch(() => this.showToast('error', 'Could not load shop.', 'ALERT!'));
                },
                onLogoChange(event) {
                    this.logoFile = event.target.files[0] || null;
                },
                submitShop() {
                    this.submitting = true;
                    this.errors = {};

                    const data = new FormData();
                    Object.entries(this.form).forEach(([key, value]) => {
                        if (value !== null && value !== undefined) data.append(key, value);
                    });
                    if (this.logoFile) data.append('logo', this.logoFile);

                    const url = this.form.id ? "{{ route('admin.shop.update') }}" : "{{ route('admin.shop.store') }}";
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
                toggleStatus(shop) {
                    axios.post("{{ route('admin.shop.toggle') }}", { id: shop.id })
                        .then(response => {
                            this.fetchItems();
                            this.showToast('success', response.data.message, 'SUCCESS!');
                        })
                        .catch(() => this.showToast('error', 'Could not update status.', 'ALERT!'));
                },
                confirmDelete(shop) {
                    this.deleteCandidate = shop;
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('shopDeleteModal')).show();
                },
                deleteShop() {
                    this.submitting = true;
                    axios.post("{{ route('admin.shop.delete') }}", { id: this.deleteCandidate.id })
                        .then(response => {
                            bootstrap.Modal.getInstance(document.getElementById('shopDeleteModal'))?.hide();
                            this.fetchItems();
                            this.showToast('success', response.data.message, 'SUCCESS!');
                        })
                        .catch(error => {
                            this.showToast('error', error.response?.data?.message || 'Failed to delete shop.', 'ALERT!');
                        })
                        .finally(() => { this.submitting = false; });
                },
            },
        }).mount('#shop-vue');
    </script>
@endsection
