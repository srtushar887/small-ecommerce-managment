@extends('frontend.layouts.storefront')
@section('title', $shop->name)

@section('content')
    <div id="shop-show-vue" v-cloak>
        <div class="breadcrumb-store">
            <a :href="shopsUrl"><i class="fas fa-arrow-left me-1"></i> All shops</a>
            <span class="mx-1">/</span>
            <span>@{{ shop.name }}</span>
        </div>

        <section class="shop-header-card">
            <img :src="shop.logo_url" :alt="shop.name">
            <div class="flex-grow-1">
                <h1>@{{ shop.name }}</h1>
                <p class="mb-2" style="font-size:13.5px;color:#6b7a8f;max-width:720px;">
                    @{{ shop.description || 'Browse the products this shop has selected from the shared catalog.' }}
                </p>
                <div class="contact">
                    <span v-if="shop.address"><i class="fas fa-map-marker-alt me-1"></i> @{{ shop.address }}</span>
                    <span v-if="shop.phone"><i class="fas fa-phone me-1"></i> @{{ shop.phone }}</span>
                    <span>
                        <i class="fas fa-box me-1"></i>
                        @{{ shop.products_count }} @{{ productLabel(shop.products_count) }} available
                    </span>
                </div>
            </div>
        </section>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-4 mb-3">
            <h2 class="h6 mb-0 fw-bold text-uppercase" style="letter-spacing:.5px; color:#6b7a8f;">
                @{{ appliedSearch ? 'Search results for "' + appliedSearch + '"' : 'Products in this shop' }}
            </h2>

            <form :action="shop.url" method="GET" class="d-flex gap-2" style="max-width:360px;width:100%;"
                  @submit.prevent="applySearch">
                <input type="text" name="q" v-model.trim="searchQuery" @input="handleSearch"
                       class="form-control form-control-sm" placeholder="Search products..." style="border-radius:8px;">
                <button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;">
                    <i class="fas fa-search"></i>
                </button>
                <button v-if="searchQuery || appliedSearch" type="button"
                        class="btn btn-sm btn-outline-secondary" style="border-radius:8px;" @click="clearSearch">
                    <i class="fas fa-times"></i>
                </button>
            </form>
        </div>

        <div v-if="loading" class="empty-state">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-3">Loading products...</div>
        </div>

        <div v-else-if="products.length === 0" class="empty-state">
            <i class="fas fa-box-open" style="font-size:34px; color:#b8c4d4;"></i>
            <div class="mt-3">
                <template v-if="appliedSearch">
                    No products matched <strong>"@{{ appliedSearch }}"</strong> in this shop.
                </template>
                <template v-else>
                    This shop has not made any products available yet.
                </template>
            </div>
        </div>

        <template v-else>
            <div class="row g-3">
                <div class="col-sm-6 col-lg-4 col-xl-3" v-for="item in products" :key="item.id">
                    <div class="product-card">
                        <img :src="item.thumbnail" class="thumb" :alt="item.name">
                        <div class="body">
                            <div class="sku">@{{ item.sku }}</div>
                            <h6>@{{ item.name }}</h6>
                            <div class="details">
                                @{{ truncate(item.details || 'Product available at ' + shop.name + '.', 105) }}
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="price">@{{ item.price_formatted }} tk</span>
                                <span class="badge bg-light text-success" style="font-size:10px;">
                                    <i class="fas fa-check-circle me-1"></i>In stock
                                </span>
                            </div>
                        </div>
                    </div>
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
                Showing @{{ pagination.from }}–@{{ pagination.to }} of @{{ pagination.total }} products
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
                    shop: @json($shopData),
                    shopsUrl: "{{ route('home') }}",
                    products: [],
                    searchQuery: @json($search),
                    appliedSearch: @json($search),
                    pagination: { current_page: 1, last_page: 1, per_page: 12, total: 0, from: 0, to: 0, prev_page_url: null, next_page_url: null },
                    loading: true,
                };
            },
            mounted() {
                this.fetchProducts();
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
                fetchProducts() {
                    this.loading = true;
                    this.appliedSearch = this.searchQuery;

                    axios.get("{{ route('shop.products.fetch', $shop) }}", {
                        params: { search: this.searchQuery, page: this.pagination.current_page }
                    }).then(response => {
                        this.products = response.data.data || [];
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
                        this.showToast('error', 'Could not load shop products. Please try again.');
                    }).finally(() => {
                        this.loading = false;
                    });
                },
                handleSearch: _.debounce(function () {
                    this.pagination.current_page = 1;
                    this.fetchProducts();
                }, 350),
                applySearch() {
                    this.pagination.current_page = 1;
                    this.fetchProducts();
                },
                clearSearch() {
                    this.searchQuery = '';
                    this.pagination.current_page = 1;
                    this.fetchProducts();
                },
                handlePageChange(page) {
                    if (page < 1 || page > this.pagination.last_page) return;
                    this.pagination.current_page = page;
                    this.fetchProducts();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                showToast(type, message) {
                    if (typeof toastr === 'undefined') return;
                    toastr.options = { closeButton: false, progressBar: true, positionClass: 'toast-top-center', timeOut: 4000 };
                    toastr[type](message, 'ALERT!');
                },
            },
        }).mount('#shop-show-vue');
    </script>
@endsection
