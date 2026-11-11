<style>
    [v-cloak] {
        display: none !important;
    }

    /* ── List Wrapper ── */
    .list-wrapper {
        margin-top: 1.5rem;
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e9edf4;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .list-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem 1rem;
        padding: 1rem 1.25rem;
        background: #f8fafc;
        border-bottom: 1px solid #e9edf4;
        justify-content: space-between;
    }

    .list-toolbar .left-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem 1rem;
        flex: 1;
    }

    .list-toolbar .right-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .list-toolbar .search-list {
        flex: 1;
        min-width: 200px;
    }

    .list-toolbar .search-list input {
        width: 100%;
        padding: 0.4rem 0.7rem 0.4rem 2rem;
        border: 1px solid #dce1ea;
        border-radius: 6px;
        font-size: 0.8rem;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E") no-repeat 0.6rem center;
        background-size: 16px;
        transition: 0.2s;
    }

    .list-toolbar .search-list input:focus {
        outline: none;
        border-color: #7c8db0;
        box-shadow: 0 0 0 3px rgba(124, 141, 176, 0.15);
    }

    .list-toolbar .btn-add {
        background: #2d6a9f;
        color: #fff;
        border: none;
        padding: 0.4rem 1.2rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: 0.2s;
        white-space: nowrap;
    }

    .list-toolbar .btn-add:hover {
        background: #1f4f7a;
    }

    .list-toolbar .btn-add i {
        margin-right: 0.4rem;
    }

    /* ── Table ── */
    .list-table-wrap {
        position: relative;
        overflow-x: auto;
        padding: 0 0.25rem 0.25rem 0.25rem;
    }

    .list-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
        min-width: 700px;
    }

    .list-table thead th {
        text-align: left;
        padding: 0.75rem 0.75rem;
        background: #f1f4f9;
        color: #1e293b;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .list-table tbody td {
        padding: 0.65rem 0.75rem;
        border-bottom: 1px solid #f1f4f9;
        vertical-align: middle;
        color: #1e293b;
    }

    .list-table tbody tr:hover {
        background: #fafbfc;
    }

    .list-table .status-badge {
        display: inline-block;
        padding: 0.1rem 0.6rem;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 500;
    }

    .list-table .status-badge.active {
        background: #e6f7e6;
        color: #1e6f3f;
    }

    .list-table .status-badge.inactive {
        background: #fce4ec;
        color: #7f2d3f;
    }

    .list-table .info-badge {
        display: inline-block;
        padding: 0.1rem 0.6rem;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 500;
        background: #e8f0fe;
        color: #2d6a9f;
    }

    .list-table .sort-badge {
        display: inline-block;
        padding: 0.1rem 0.6rem;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 500;
        background: #eef2f6;
        color: #475569;
    }

    .list-table .product-thumb {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
        border: 1px solid #eef2f6;
    }

    .list-table .action-icons {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .list-table .action-icons a {
        color: #94a3b8;
        transition: 0.15s;
        text-decoration: none;
        cursor: pointer;
    }

    .list-table .action-icons a:hover {
        color: #2d6a9f;
    }

    .list-table .action-icons a.btn-del:hover {
        color: #dc2626;
    }

    .empty-msg {
        padding: 20px 14px;
        color: #94a3b8;
        text-align: center;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    /* ── Pagination ── */
    .list-pagination {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.75rem 1.25rem;
        background: #f8fafc;
        border-top: 1px solid #e9edf4;
    }

    .list-pagination .pagination-info {
        font-size: 0.8rem;
        color: #475569;
    }

    .list-pagination .pagination-buttons {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
    }

    .list-pagination .page-btn {
        min-width: 32px;
        height: 32px;
        padding: 0 0.5rem;
        border: 1px solid #dce1ea;
        border-radius: 6px;
        background: #fff;
        color: #1e293b;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: 0.15s;
    }

    .list-pagination .page-btn:hover:not(:disabled) {
        border-color: #7c8db0;
        background: #f1f4f9;
    }

    .list-pagination .page-btn.active {
        background: #2d6a9f;
        border-color: #2d6a9f;
        color: #fff;
    }

    .list-pagination .page-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    /* ── Page Header (exact match with Settings) ── */
    .page-title {
        font-size: 22px;
        font-weight: 600;
        color: #0b1a2e;
        margin: 0 0 4px 0;
        letter-spacing: -0.2px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .page-title .badge-box {
        background: #eef2f7;
        border-radius: 100px;
        padding: 4px 18px;
        font-size: 13px;
        font-weight: 500;
        color: #1f3a6b;
        border: 1px solid #d6dfe9;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .page-subtitle {
        font-size: 14px;
        color: #6b7a8f;
        margin: 0 0 24px 0;
        font-weight: 400;
    }

    .page-subtitle .breadcrumb {
        display: inline;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .page-subtitle .breadcrumb li {
        display: inline;
        color: #7a8ba3;
    }

    .page-subtitle .breadcrumb li+li::before {
        content: " / ";
        color: #b8c5d4;
        margin: 0 6px;
    }

    .page-subtitle .breadcrumb li.active {
        color: #1f3a6b;
        font-weight: 500;
    }

    /* ── MODAL STYLES (matching CPT modal) ── */
    .modal-content.service-modal {
        border: none;
        border-radius: 16px;
        box-shadow: 0 12px 48px rgba(0, 0, 0, 0.08);
    }

    .modal-content.service-modal .modal-header {
        border-bottom: 2px solid #eef2f6;
        padding: 1rem 1.5rem;
    }

    .modal-content.service-modal .modal-header .modal-title {
        font-weight: 700;
        color: #0b1a33;
    }

    .modal-content.service-modal .modal-header .modal-title i {
        margin-right: 0.5rem;
        color: #3b82f6;
    }

    .modal-content.service-modal .modal-body {
        padding: 1.5rem;
    }

    .modal-content.service-modal .modal-footer {
        border-top: 2px solid #eef2f6;
        padding: 0.75rem 1.5rem;
    }

    .modal-content.service-modal .service-modal-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 0.3rem;
    }

    .modal-content.service-modal .form-control,
    .modal-content.service-modal .form-select {
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.85rem;
        padding: 0.45rem 0.8rem;
        background: #ffffff;
        transition: all 0.2s ease;
        color: #0f172a;
    }

    .modal-content.service-modal .form-control:focus,
    .modal-content.service-modal .form-select:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
    }

    .modal-content.service-modal .form-control.is-invalid,
    .modal-content.service-modal .form-select.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.10);
        background-image: none !important;
        padding-right: 0.8rem !important;
    }

    .modal-content.service-modal .form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    .modal-content.service-modal .btn {
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.78rem;
        padding: 0.4rem 1.2rem;
        transition: all 0.2s ease;
        border: none;
    }

    .modal-content.service-modal .btn-primary {
        background: #1a3a6b;
        color: #fff;
    }

    .modal-content.service-modal .btn-primary:hover {
        background: #0f2a4f;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(26, 58, 107, 0.25);
    }

    .modal-content.service-modal .btn-secondary {
        background: #eef2f6;
        color: #1a3a6b;
    }

    .modal-content.service-modal .btn-secondary:hover {
        background: #e2e8f0;
        color: #0f2a4f;
    }

    .modal-content.service-modal .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Responsive tweaks */
    @media (max-width: 768px) {
        .list-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .list-toolbar .left-group {
            flex-direction: column;
            align-items: stretch;
        }

        .list-toolbar .right-group {
            align-self: flex-start;
        }

        .list-pagination {
            flex-direction: column;
            align-items: center;
        }

        .page-title {
            font-size: 18px;
        }

        .page-title .badge-box {
            font-size: 12px;
            padding: 2px 12px;
        }
    }
</style>
