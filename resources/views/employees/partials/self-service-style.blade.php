@php($companySettings = \App\Models\CompanySettings::current())
@php($brandPrimary = $companySettings->brand_primary_color ?? '#8A00FF')
@php($brandDark = $companySettings->brand_dark_color ?? '#00163F')
<style>
    .self-service-modern {
        background:
            radial-gradient(circle at 10% 8%, rgba({{ $companySettings->colorRgb('brand_primary_color', '#8A00FF') }}, 0.08), transparent 34%),
            radial-gradient(circle at 100% 0%, rgba(0, 22, 63, 0.08), transparent 36%),
            linear-gradient(180deg, rgba(247, 249, 255, 0.92), rgba(241, 244, 252, 0.98));
    }
    .self-service-modern .content.container-fluid {
        max-width: 1460px;
        margin: 0 auto;
        padding: 28px 24px 34px;
    }
    .self-service-modern .page-header .page-title {
        font-size: 2.15rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
        margin-bottom: 4px;
    }
    .self-service-modern .breadcrumb {
        margin-bottom: 0;
        background: transparent;
        padding: 0;
        font-size: 0.94rem;
    }
    .self-service-modern .breadcrumb .breadcrumb-item a {
        color: {{ $brandPrimary }};
        font-weight: 700;
    }
    .self-service-modern .breadcrumb .breadcrumb-item.active {
        color: rgba(15, 23, 42, 0.66);
    }
    .self-service-modern .btn.add-btn,
    .self-service-modern .btn.btn-primary,
    .self-service-modern .btn.btn-success {
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, {{ $brandPrimary }}, {{ $brandDark }});
        box-shadow: 0 12px 24px rgba(138, 0, 255, 0.24);
        font-weight: 700;
    }
    .self-service-modern .btn.add-btn:hover,
    .self-service-modern .btn.btn-primary:hover,
    .self-service-modern .btn.btn-success:hover {
        transform: translateY(-1px);
    }
    .self-service-modern .stats-info,
    .self-service-modern .card.punch-status,
    .self-service-modern .card.att-statistics,
    .self-service-modern .card.recent-activity,
    .self-service-modern .card {
        border-radius: 18px;
        border: 1px solid rgba(0, 22, 63, 0.08);
        box-shadow: 0 12px 28px rgba(0, 22, 63, 0.08);
        background: rgba(255, 255, 255, 0.96);
    }
    .self-service-modern .stats-info h6 {
        color: rgba(15, 23, 42, 0.64);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-size: 0.78rem;
        margin-bottom: 8px;
    }
    .self-service-modern .section-intro {
        color: rgba(15, 23, 42, 0.62);
        font-size: 0.95rem;
        margin-top: 2px;
        margin-bottom: 0;
    }
    .self-service-modern .panel-card {
        border-radius: 18px;
        border: 1px solid rgba(0, 22, 63, 0.08);
        box-shadow: 0 12px 28px rgba(0, 22, 63, 0.08);
        background: rgba(255, 255, 255, 0.96);
        margin-bottom: 16px;
    }
    .self-service-modern .panel-card .panel-head {
        padding: 14px 18px;
        border-bottom: 1px solid rgba(0, 22, 63, 0.08);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .self-service-modern .panel-card .panel-title {
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: -0.01em;
        color: #0f172a;
        margin: 0;
    }
    .self-service-modern .panel-card .panel-meta {
        color: rgba(15, 23, 42, 0.58);
        font-size: 0.86rem;
        font-weight: 600;
    }
    .self-service-modern .panel-card .panel-body {
        padding: 14px 16px 16px;
    }
    .self-service-modern .stats-info h4 {
        color: #0f172a;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .self-service-modern .stats-info h4 span {
        color: rgba(15, 23, 42, 0.58);
        font-weight: 600;
    }
    .self-service-modern .table-responsive {
        border: 1px solid rgba(0, 22, 63, 0.08);
        border-radius: 16px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 10px 24px rgba(0, 22, 63, 0.06);
    }
    .self-service-modern .table-empty {
        color: rgba(15, 23, 42, 0.56) !important;
        font-weight: 600;
    }
    .self-service-modern table.table {
        margin-bottom: 0;
    }
    .self-service-modern table.table thead th {
        border-top: 0;
        background: rgba(0, 22, 63, 0.03);
        color: rgba(15, 23, 42, 0.64);
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .self-service-modern table.table tbody td {
        color: rgba(15, 23, 42, 0.82);
        font-weight: 500;
        vertical-align: middle;
    }
    .self-service-modern .badge.bg-inverse-success,
    .self-service-modern .badge.bg-inverse-info,
    .self-service-modern .badge.bg-inverse-warning,
    .self-service-modern .badge.bg-inverse-danger,
    .self-service-modern .badge.bg-inverse-secondary {
        border-radius: 999px;
        padding: 6px 10px;
        font-weight: 700;
    }
    .self-service-modern .modal .modal-content {
        border: 1px solid rgba(0, 22, 63, 0.1);
        border-radius: 16px;
        box-shadow: 0 18px 34px rgba(0, 22, 63, 0.18);
    }
    .self-service-modern .modal .modal-title {
        font-weight: 800;
        color: #0f172a;
    }
    .self-service-modern .filter-row .form-focus {
        border-radius: 12px;
        border: 1px solid rgba(0, 22, 63, 0.12);
        background: rgba(255, 255, 255, 0.95);
    }
    @media (max-width: 991px) {
        .self-service-modern .content.container-fluid {
            padding: 20px 14px 24px;
        }
        .self-service-modern .page-header .page-title {
            font-size: 1.85rem;
        }
    }
</style>
