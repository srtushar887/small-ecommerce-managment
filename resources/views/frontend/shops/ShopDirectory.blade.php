@extends('frontend.layouts.storefront')
@section('title', 'All Shops')

@section('content')
    <div id="shop-directory-vue" v-cloak>
        <section class="page-hero">
            <h1>Discover shops near you</h1>
            <p>Browse every active shop and explore the products each shop has made available to customers.</p>

            <form class="hero-search" @submit.prevent="applySearch">
                <input type="text" v-model.trim="searchQuery" @input="handleSearch"
                       placeholder="Search shops by name or description...">
                <button type="submit"><i class="fas fa-search me-1"></i> Search</button>
            </form>
        </section>

        <div class="d-flex align-items-center justify-content-between mt-4 mb-3 flex-wrap gap-2">
            <h2 class="h6 mb-0 fw-bold text-uppercase" style="letter-spacing:.5px; color:#6b7a8f;">
                @{{ appliedSearch ? 'Search results for "' + appliedSearch + '"' : 'Active shops' }}
                <span class="badge bg-light text-dark ms-1">@{{ pagination.total }}</span>
            </h2>
            <button v-if="searchQuery || appliedSearch" type="button"
                    class="btn btn-sm btn-outline-secondary" style="border-radius:8px;" @click="clearSearch">
                <i class="fas fa-times"></i> Clear search
            </button>
        </div>

        <div v-if="loading" class="empty-state">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-3">Loading shops...</div>
        </div>

        <div v-else-if="shops.length === 0" class="empty-state">
            <i class="fas fa-store-slash" style="font-size:34px; color:#b8c4d4;"></i>
            <div class="mt-3">
                <template v-if="appliedSearch">
                    No shops matched <strong>"@{{ appliedSearch }}"</strong>. Try a different search.
                </template>
                <template v-else>
                    No active shops are available right now.
                </template>
            </div>
        </div>

        <template v-else>
            <div class="row g-3">
                <div class="col-sm-6 col-lg-4" v-for="shop in shops" :key="shop.id">
                    <a :href="shop.url" class="shop-card">
                        <div class="logo-wrap">
                            <img :src="shop.logo_url" :alt="shop.name">
                        </div>
                        <div class="body">
                            <h5>@{{ shop.name }}</h5>
                            <div class="desc">
                                @{{ truncate(shop.description || 'Explore this shop\'s product collection.', 96) }}
                            </div>
                            <div class="meta">
                                <span>
                                    <i class="fas fa-box me-1"></i>
                                    @{{ shop.products_count }} @{{ productLabel(shop.products_count) }}
                                </span>
                                <span class="visit">Visit shop <i class="fas fa-arrow-right ms-1"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="d-flex justify-content-center mt-4" v-if="pagination.last_page > 1">
                <div class="pager d-flex align-items-center flex-wrap">
                    <button type="button" class="pager-btn" :disabled="!pagination.prev_page_url"
                            @click="handlePageChange(pagination.current_page - 1)">
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <button v-for="page in getVisiblePages()" :key="page" type="button"
                            class="pager-btn" :class="{ current: page === pagination.current_page }"
                            @click="handlePageChange(page)">
                        @{{ page }}
                    </button>

                    <button type="button" class="pager-btn" :disabled="!pagination.next_page_url"
                            @click="handlePageChange(pagination.current_page + 1)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="text-center text-muted mt-3" style="font-size:12.5px;" v-if="pagination.total > 0">
                Showing @{{ pagination.from }}–@{{ pagination.to }} of @{{ pagination.total }} shops
            </div>
        </template>
    </div>
@endsection

@section('post-js')
    <script>
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['Accept'] = 'application/json';

        Vue.createApp({
            data() {
                return {
                    shops: [],
                    searchQuery: @json($search),
                    appliedSearch: @json($search),
                    pagination: { current_page: 1, last_page: 1, per_page: 9, total: 0, from: 0, to: 0, prev_page_url: null, next_page_url: null },
                    loading: true,
                };
            },
            mounted() {
                this.fetchShops();
            },
            methods: {
                productLabel(count) {
                    return Number(count) === 1 ? 'product' : 'products';
                },
                truncate(text, length) {
                    if (!text) return '';
                    return text.length > length ? text.substr(0, length) + '...' : text;
                },
                getVisiblePages() {
                    const pages = [];
                    const current = this.pagination.current_page;
                    const last = this.pagination.last_page;
                    const start = Math.max(1, current - 2);
                    const end = Math.min(last, current + 2);
                    for (let i = start; i <= end; i++) pages.push(i);
                    return pages;
                },
                fetchShops() {
                    this.loading = true;
                    this.appliedSearch = this.searchQuery;

                    axios.get("{{ route('shops.fetch') }}", {
                        params: { search: this.searchQuery, page: this.pagination.current_page }
                    }).then(response => {
                        this.shops = response.data.data || [];
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
                    }).catch(() => {
                        this.showToast('error', 'Could not load shops. Please try again.');
                    }).finally(() => {
                        this.loading = false;
                    });
                },
                handleSearch: _.debounce(function () {
                    this.pagination.current_page = 1;
                    this.fetchShops();
                }, 350),
                applySearch() {
                    this.pagination.current_page = 1;
                    this.fetchShops();
                },
                clearSearch() {
                    this.searchQuery = '';
                    this.pagination.current_page = 1;
                    this.fetchShops();
                },
                handlePageChange(page) {
                    if (page < 1 || page > this.pagination.last_page) return;
                    this.pagination.current_page = page;
                    this.fetchShops();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                showToast(type, message) {
                    if (typeof toastr === 'undefined') return;
                    toastr.options = { closeButton: false, progressBar: true, positionClass: 'toast-top-center', timeOut: 4000 };
                    toastr[type](message, 'ALERT!');
                },
            },
        }).mount('#shop-directory-vue');
    </script>
@endsection
