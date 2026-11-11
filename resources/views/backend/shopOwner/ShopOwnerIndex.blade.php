@extends('layouts.master')
@section('title', 'Shop Owners')

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
            width: 42px;
            height: 42px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #eef2f6;
        }

        .form-section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #2d6a9f;
            border-bottom: 1px solid #eef2f7;
            padding-bottom: .35rem;
            margin: .25rem 0 .1rem;
        }
    </style>
@endsection

@section('main-content')
    <div id="shop-owner-vue" v-cloak>
        <div class="page-title"><span>Shop Owners</span><span class="badge-box">👥 Accounts</span></div>
        <div class="page-subtitle">
            <ul class="breadcrumb">
                <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="active">Shop Owners</li>
            </ul>
        </div>

        <div class="list-wrapper">
            <div class="list-toolbar">
                <div class="left-group">
                    <div class="search-list">
                        <input type="text" v-model="searchQuery" @input="handleSearch" placeholder="Search owners or shops..." />
                    </div>
                    <select class="filter-select" v-model="statusFilter" @change="applyFilters">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="right-group">
                    <button class="btn-add" @click="openCreateModal">
                        <i class="fas fa-user-plus"></i> Add Shop Owner
                    </button>
                </div>
            </div>

            <div class="list-table-wrap">
                <table class="list-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Owner</th>
                            <th>Shop</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="7">
                                <div class="empty-msg">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <span>Loading...</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-else-if="items.length === 0">
                            <td colspan="7">
                                <div class="empty-msg">No shop owners found. Click "Add Shop Owner" to create one.</div>
                            </td>
                        </tr>
                        <tr v-if="!loading" v-for="(owner, index) in items" :key="owner.id">
                            <td>@{{ index + 1 + (pagination.current_page - 1) * pagination.per_page }}</td>
                            <td>
                                <strong>@{{ owner.name }}</strong>
                                <small class="text-muted d-block">@{{ owner.email }}</small>
                                <small class="text-muted d-block">@{{ owner.phone || 'No phone' }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2" v-if="owner.shop_id">
                                    <img :src="owner.shop_logo_url" class="shop-logo" alt="">
                                    <div>
                                        <span>@{{ owner.shop_name }}</span>
                                        <small class="d-block">
                                            <a :href="'{{ url('/shop') }}/' + owner.shop_slug" target="_blank" class="text-decoration-none">View shop</a>
                                        </small>
                                    </div>
                                </div>
                                <span v-else class="text-muted">No shop</span>
                            </td>
                            <td><span class="info-badge">@{{ owner.products_count }}</span></td>
                            <td>
                                <span class="status-badge" :class="owner.is_active ? 'active' : 'inactive'">
                                    @{{ owner.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td><small class="text-muted">@{{ formatDate(owner.created_at) }}</small></td>
                            <td>
                                <div class="action-icons">
                                    <a @click="openEditModal(owner)" title="Edit"><i class="fas fa-pen"></i></a>
                                    <a @click="toggleStatus(owner)" title="Toggle status">
                                        <i class="fas" :class="owner.is_active ? 'fa-toggle-on' : 'fa-toggle-off'"></i>
                                    </a>
                                    <a @click="confirmDelete(owner)" title="Delete" class="btn-del"><i class="fas fa-trash-alt"></i></a>
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

        <div class="modal fade" id="shopOwnerModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content service-modal">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-tie"></i> @{{ form.id ? 'Edit Shop Owner' : 'Add Shop Owner' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitOwner">
                            <div class="form-section-title">Owner Account</div>
                            <div class="row g-3 mb-2">
                                <div class="col-md-6">
                                    <label class="service-modal-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" :class="{ 'is-invalid': errors.name }" v-model="form.name" required>
                                    <div v-if="errors.name" class="text-danger small mt-1">@{{ errors.name[0] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="service-modal-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" :class="{ 'is-invalid': errors.email }" v-model="form.email" required>
                                    <div v-if="errors.email" class="text-danger small mt-1">@{{ errors.email[0] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="service-modal-label">Phone</label>
                                    <input type="text" class="form-control" v-model="form.phone" :class="{ 'is-invalid': errors.phone }">
                                    <div v-if="errors.phone" class="text-danger small mt-1">@{{ errors.phone[0] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="service-modal-label">
                                        Password <span class="text-danger" v-if="!form.id">*</span>
                                        <span class="text-muted" v-else>(leave blank to keep)</span>
                                    </label>
                                    <input type="password" class="form-control" v-model="form.password" :class="{ 'is-invalid': errors.password }" :required="!form.id">
                                    <div v-if="errors.password" class="text-danger small mt-1">@{{ errors.password[0] }}</div>
                                </div>
                            </div>

                            <div class="form-section-title">Shop Details</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="service-modal-label">Shop Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" :class="{ 'is-invalid': errors.shop_name }" v-model="form.shop_name" required>
                                    <div v-if="errors.shop_name" class="text-danger small mt-1">@{{ errors.shop_name[0] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="service-modal-label">Shop Phone</label>
                                    <input type="text" class="form-control" v-model="form.shop_phone" :class="{ 'is-invalid': errors.shop_phone }">
                                    <div v-if="errors.shop_phone" class="text-danger small mt-1">@{{ errors.shop_phone[0] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="service-modal-label">Shop Address</label>
                                    <input type="text" class="form-control" v-model="form.shop_address" :class="{ 'is-invalid': errors.shop_address }">
                                    <div v-if="errors.shop_address" class="text-danger small mt-1">@{{ errors.shop_address[0] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="service-modal-label">Shop Logo</label>
                                    <input type="file" id="shopOwnerLogo" class="form-control" accept="image/*" @change="onLogoChange" :class="{ 'is-invalid': errors.shop_logo }">
                                    <div v-if="errors.shop_logo" class="text-danger small mt-1">@{{ errors.shop_logo[0] }}</div>
                                </div>
                                <div class="col-12">
                                    <label class="service-modal-label">Shop Description</label>
                                    <textarea class="form-control" rows="2" v-model="form.shop_description" :class="{ 'is-invalid': errors.shop_description }"></textarea>
                                    <div v-if="errors.shop_description" class="text-danger small mt-1">@{{ errors.shop_description[0] }}</div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="shopOwnerStatus" v-model="form.is_active" true-value="1" false-value="0">
                                        <label class="form-check-label" for="shopOwnerStatus">Account and shop are active</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" :disabled="submitting" @click="submitOwner">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="fas fa-save me-1"></i> @{{ form.id ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="shopOwnerDeleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content service-modal">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-trash-alt"></i> Delete Shop Owner</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Delete <strong>@{{ deleteCandidate.name }}</strong> and the shop <strong>@{{ deleteCandidate.shop_name }}</strong>?</p>
                        <p class="text-danger mb-0"><small>This action cannot be undone.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" :disabled="submitting" @click="deleteOwner">
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
                    form: {
                        id: null,
                        name: '',
                        email: '',
                        phone: '',
                        password: '',
                        is_active: 1,
                        shop_name: '',
                        shop_description: '',
                        shop_address: '',
                        shop_phone: '',
                    },
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
                    axios.get("{{ route('admin.shop-owner.fetch') }}", {
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
                        this.showToast('error', 'Could not load shop owners.', 'ALERT!');
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
                    return bootstrap.Modal.getOrCreateInstance(document.getElementById('shopOwnerModal'));
                },
                resetForm() {
                    this.form = {
                        id: null,
                        name: '',
                        email: '',
                        phone: '',
                        password: '',
                        is_active: 1,
                        shop_name: '',
                        shop_description: '',
                        shop_address: '',
                        shop_phone: '',
                    };
                    this.logoFile = null;
                    this.errors = {};
                    const input = document.getElementById('shopOwnerLogo');
                    if (input) input.value = '';
                },
                openCreateModal() {
                    this.resetForm();
                    this.modalEl().show();
                },
                openEditModal(owner) {
                    this.errors = {};
                    axios.get("{{ url('admin/shop-owners') }}/" + owner.id)
                        .then(response => {
                            const o = response.data.owner;
                            this.form = {
                                id: o.id,
                                name: o.name,
                                email: o.email,
                                phone: o.phone || '',
                                password: '',
                                is_active: o.is_active ? 1 : 0,
                                shop_name: o.shop_name || '',
                                shop_description: o.shop_description || '',
                                shop_address: o.shop_address || '',
                                shop_phone: o.shop_phone || '',
                            };
                            this.logoFile = null;
                            const input = document.getElementById('shopOwnerLogo');
                            if (input) input.value = '';
                            this.modalEl().show();
                        })
                        .catch(() => this.showToast('error', 'Could not load shop owner.', 'ALERT!'));
                },
                onLogoChange(event) {
                    this.logoFile = event.target.files[0] || null;
                },
                submitOwner() {
                    this.submitting = true;
                    this.errors = {};

                    const data = new FormData();
                    Object.entries(this.form).forEach(([key, value]) => {
                        if (value === null || value === undefined) return;
                        if (key === 'password' && value === '') return;
                        data.append(key, value);
                    });
                    if (this.logoFile) data.append('shop_logo', this.logoFile);

                    const url = this.form.id ? "{{ route('admin.shop-owner.update') }}" : "{{ route('admin.shop-owner.store') }}";
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
                toggleStatus(owner) {
                    axios.post("{{ route('admin.shop-owner.toggle') }}", { id: owner.id })
                        .then(response => {
                            this.fetchItems();
                            this.showToast('success', response.data.message, 'SUCCESS!');
                        })
                        .catch(() => this.showToast('error', 'Could not update status.', 'ALERT!'));
                },
                confirmDelete(owner) {
                    this.deleteCandidate = owner;
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('shopOwnerDeleteModal')).show();
                },
                deleteOwner() {
                    this.submitting = true;
                    axios.post("{{ route('admin.shop-owner.delete') }}", { id: this.deleteCandidate.id })
                        .then(response => {
                            bootstrap.Modal.getInstance(document.getElementById('shopOwnerDeleteModal'))?.hide();
                            this.fetchItems();
                            this.showToast('success', response.data.message, 'SUCCESS!');
                        })
                        .catch(error => {
                            this.showToast('error', error.response?.data?.message || 'Failed to delete shop owner.', 'ALERT!');
                        })
                        .finally(() => { this.submitting = false; });
                },
            },
        }).mount('#shop-owner-vue');
    </script>
@endsection
