<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap');

    body.employee-dashboard-shell .page-wrapper {
        background: #fcfcfd;
    }
    body.employee-dashboard-shell .content.container-fluid {
        max-width: 1100px;
        margin: 0 auto;
        padding: 40px;
    }
    body.employee-dashboard-shell .employee-content-topbar {
        min-height: 64px;
        border-bottom: 1px solid #eae7f2;
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        color: #5e5873;
    }
    body.employee-dashboard-shell .employee-topbar-left {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }
    body.employee-dashboard-shell .employee-shell-menu-btn {
        width: 38px;
        height: 38px;
        border: 1px solid #eae7f2;
        border-radius: 12px;
        background: #ffffff;
        color: #5e5873;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: border-color .15s ease, background-color .15s ease, color .15s ease;
        flex-shrink: 0;
    }
    body.employee-dashboard-shell .employee-shell-menu-btn:hover,
    body.employee-dashboard-shell .employee-shell-menu-btn:focus {
        border-color: #d9d4e5;
        background: #f7f2ff;
        color: #6d28d9;
        outline: none;
    }
    body.employee-dashboard-shell .employee-shell-menu-icon {
        width: 16px;
        display: inline-flex;
        flex-direction: column;
        gap: 3px;
    }
    body.employee-dashboard-shell .employee-shell-menu-icon span {
        display: block;
        width: 100%;
        height: 1.5px;
        border-radius: 999px;
        background: currentColor;
    }
    body.employee-dashboard-shell .employee-topbar-context {
        color: #8c869e;
        font-size: 14px;
        line-height: 20px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    body.employee-dashboard-shell .employee-topbar-right {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    body.employee-dashboard-shell .employee-topbar-bell {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #eae7f2;
        border-radius: 999px;
        color: #171327;
        background: #fff;
        position: relative;
        text-decoration: none;
    }
    body.employee-dashboard-shell .employee-topbar-bell-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        min-width: 18px;
        height: 18px;
        border-radius: 999px;
        background: #6d28d9;
        color: #fff;
        font-size: 10px;
        line-height: 18px;
        text-align: center;
        padding: 0 4px;
    }
    body.employee-dashboard-shell .employee-profile-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #171327;
        font-size: 16px;
        font-weight: 500;
        text-decoration: none;
        border: 1px solid transparent;
        border-radius: 999px;
        padding: 4px 8px 4px 4px;
        transition: border-color .15s ease, background-color .15s ease, color .15s ease;
    }
    body.employee-dashboard-shell .employee-profile-pill:hover,
    body.employee-dashboard-shell .employee-profile-pill:focus {
        color: #171327;
        background: #f8f8fb;
        border-color: #eae7f2;
        text-decoration: none;
        outline: none;
    }
    body.employee-dashboard-shell .employee-profile-caret {
        color: #5e5873;
        font-size: 16px;
        line-height: 1;
        transition: transform .15s ease;
    }
    body.employee-dashboard-shell .js-employee-profile-dropdown.show .employee-profile-caret {
        transform: rotate(180deg);
    }
    body.employee-dashboard-shell .employee-profile-avatar {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        object-fit: cover;
        border: 1px solid #eae7f2;
    }
    body.employee-dashboard-shell .employee-profile-menu {
        min-width: 200px;
        border: 1px solid #eae7f2;
        border-radius: 12px;
        box-shadow: 0 10px 24px rgba(40, 24, 82, 0.08);
        padding: 6px;
        margin-top: 8px;
    }
    body.employee-dashboard-shell .employee-profile-menu .dropdown-item {
        color: #171327;
        border-radius: 8px;
        font-size: 14px;
        line-height: 20px;
        padding: 9px 10px;
        background: transparent;
        width: 100%;
        text-align: left;
    }
    body.employee-dashboard-shell .employee-profile-menu .dropdown-item:hover,
    body.employee-dashboard-shell .employee-profile-menu .dropdown-item:focus {
        background: #f7f2ff;
        color: #6d28d9;
        text-decoration: none;
        outline: none;
    }
    body.employee-dashboard-shell .employee-profile-menu form {
        margin: 0;
    }
    body.employee-dashboard-shell .page-header {
        margin-bottom: 32px;
        border: 0;
        padding-bottom: 0;
    }
    body.employee-dashboard-shell .page-header .page-title {
        color: #171327;
        font-family: "Playfair Display", serif;
        font-size: 56px;
        line-height: 1.04;
        letter-spacing: -0.02em;
        font-weight: 700;
        margin-bottom: 8px;
    }
    body.employee-dashboard-shell .page-header .breadcrumb {
        margin-bottom: 8px;
        padding: 0;
        background: transparent;
    }
    body.employee-dashboard-shell .page-header .breadcrumb-item,
    body.employee-dashboard-shell .page-header .breadcrumb-item.active {
        color: #8c869e;
        font-size: 14px;
    }
    body.employee-dashboard-shell .page-header .breadcrumb-item a {
        color: #5e5873;
        font-weight: 500;
    }
    body.employee-dashboard-shell .section-intro {
        color: #5e5873;
        font-size: 16px;
        line-height: 24px;
        margin: 0;
        max-width: 680px;
    }
    body.employee-dashboard-shell .card,
    body.employee-dashboard-shell .panel-card,
    body.employee-dashboard-shell .stats-info {
        border: 1px solid #eae7f2;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: none;
    }
    body.employee-dashboard-shell .card .card-header,
    body.employee-dashboard-shell .panel-card .panel-head {
        background: #ffffff;
        border-bottom: 1px solid #eae7f2;
    }
    body.employee-dashboard-shell .panel-card .panel-head,
    body.employee-dashboard-shell .panel-card .panel-body {
        padding: 16px 20px;
    }
    body.employee-dashboard-shell .panel-card .panel-title,
    body.employee-dashboard-shell .card-title {
        color: #171327;
        font-size: 18px;
        line-height: 28px;
        font-weight: 600;
        margin: 0;
    }
    body.employee-dashboard-shell .panel-card .panel-meta {
        color: #8c869e;
        font-size: 14px;
        line-height: 20px;
        font-weight: 500;
    }
    body.employee-dashboard-shell .stats-info {
        padding: 18px;
    }
    body.employee-dashboard-shell .stats-info h6 {
        color: #5e5873;
        font-size: 12px;
        line-height: 16px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        margin-bottom: 10px;
        font-weight: 600;
    }
    body.employee-dashboard-shell .stats-info h4,
    body.employee-dashboard-shell .stats-info h3 {
        color: #171327;
        font-size: 24px;
        line-height: 1.2;
        letter-spacing: -0.01em;
        font-weight: 600;
        margin-bottom: 0;
    }
    body.employee-dashboard-shell .stats-info h4 span {
        color: #8c869e;
        font-size: 14px;
        font-weight: 400;
    }
    body.employee-dashboard-shell .btn.add-btn,
    body.employee-dashboard-shell .btn.btn-primary,
    body.employee-dashboard-shell .btn.btn-success {
        background: #6d28d9;
        border: 1px solid #6d28d9;
        color: #ffffff;
        border-radius: 12px;
        font-weight: 500;
        box-shadow: none;
        transition: background-color .15s ease, border-color .15s ease;
    }
    body.employee-dashboard-shell .btn.add-btn:hover,
    body.employee-dashboard-shell .btn.btn-primary:hover,
    body.employee-dashboard-shell .btn.btn-success:hover {
        background: #5b2de1;
        border-color: #5b2de1;
    }
    body.employee-dashboard-shell .btn.btn-outline-primary {
        border-color: #d9d4e5;
        color: #5e5873;
        border-radius: 12px;
        background: #ffffff;
    }
    body.employee-dashboard-shell .btn.btn-outline-primary:hover {
        color: #6d28d9;
        border-color: #6d28d9;
        background: #f7f2ff;
    }
    body.employee-dashboard-shell a:focus-visible,
    body.employee-dashboard-shell button:focus-visible,
    body.employee-dashboard-shell .btn:focus-visible,
    body.employee-dashboard-shell input:focus-visible,
    body.employee-dashboard-shell select:focus-visible,
    body.employee-dashboard-shell textarea:focus-visible {
        outline: 2px solid #6d28d9;
        outline-offset: 2px;
        box-shadow: none !important;
    }
    body.employee-dashboard-shell .btn,
    body.employee-dashboard-shell .employee-dashboard-nav a,
    body.employee-dashboard-shell .employee-shell-menu-btn,
    body.employee-dashboard-shell .employee-topbar-bell {
        min-height: 40px;
    }
    body.employee-dashboard-shell .text-muted,
    body.employee-dashboard-shell small.text-muted {
        color: #8c869e !important;
    }
    body.employee-dashboard-shell .table-responsive {
        border: 1px solid #eae7f2;
        border-radius: 12px;
        overflow-x: auto;
        overflow-y: hidden;
        background: #ffffff;
        box-shadow: none;
    }
    body.employee-dashboard-shell table.table {
        margin-bottom: 0;
    }
    body.employee-dashboard-shell table.table thead th {
        border-top: 0;
        border-bottom: 1px solid #eae7f2;
        background: #f8f8fb;
        color: #5e5873;
        font-size: 12px;
        line-height: 16px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-weight: 600;
    }
    body.employee-dashboard-shell table.table tbody td {
        color: #171327;
        border-top: 1px solid #f0edf6;
        vertical-align: middle;
        padding-top: 12px;
        padding-bottom: 12px;
    }
    body.employee-dashboard-shell .form-group label {
        color: #5e5873;
        font-size: 12px;
        line-height: 16px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 8px;
    }
    body.employee-dashboard-shell .form-control,
    body.employee-dashboard-shell .select2-container--default .select2-selection--single {
        border-color: #d9d4e5;
        border-radius: 12px;
        min-height: 44px;
        color: #171327;
        background: #fff;
    }
    body.employee-dashboard-shell .form-control:focus,
    body.employee-dashboard-shell .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #6d28d9;
        box-shadow: 0 0 0 0.15rem rgba(109, 40, 217, 0.12);
    }
    body.employee-dashboard-shell textarea.form-control {
        min-height: 104px;
    }
    body.employee-dashboard-shell .table-empty {
        color: #8c869e !important;
        font-weight: 400;
    }
    body.employee-dashboard-shell .badge.bg-inverse-success,
    body.employee-dashboard-shell .badge.bg-inverse-info,
    body.employee-dashboard-shell .badge.bg-inverse-warning,
    body.employee-dashboard-shell .badge.bg-inverse-danger,
    body.employee-dashboard-shell .badge.bg-inverse-secondary {
        border-radius: 999px;
        padding: 6px 10px;
        font-weight: 500;
    }
    body.employee-dashboard-shell .profile-tab-shell {
        border: 1px solid #eae7f2;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: none;
    }
    body.employee-dashboard-shell .profile-tab-shell .user-tabs .line-tabs {
        padding: 0 20px;
    }
    body.employee-dashboard-shell .profile-tab-shell .nav-tabs {
        border-bottom: 1px solid #eae7f2;
        padding: 12px 0;
        gap: 10px;
    }
    body.employee-dashboard-shell .profile-tab-shell .nav-link {
        border: 1px solid transparent;
        border-radius: 999px;
        color: #5e5873;
        font-weight: 500;
        font-size: 15px;
        line-height: 22px;
        padding: 8px 14px;
    }
    body.employee-dashboard-shell .profile-tab-shell .nav-link.active,
    body.employee-dashboard-shell .profile-tab-shell .nav-link:hover {
        color: #6d28d9;
        border-color: #e0d6f7;
        background: #f7f2ff;
    }
    @media (max-width: 991px) {
        body.employee-dashboard-shell .content.container-fluid {
            padding: 24px 16px;
        }
        body.employee-dashboard-shell .employee-content-topbar {
            margin-bottom: 24px;
            min-height: 56px;
        }
        body.employee-dashboard-shell .page-header .page-title {
            font-size: 42px;
            line-height: 1.08;
        }
    }
    @media (max-width: 767px) {
        body.employee-dashboard-shell .page-header .page-title {
            font-size: 36px;
        }
        body.employee-dashboard-shell .employee-topbar-context {
            display: none;
        }
        body.employee-dashboard-shell .employee-content-topbar {
            gap: 10px;
        }
        body.employee-dashboard-shell .employee-shell-menu-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
        }
        body.employee-dashboard-shell .employee-profile-pill span {
            max-width: 120px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        body.employee-dashboard-shell .employee-profile-pill {
            font-size: 14px;
        }
        body.employee-dashboard-shell .page-header .row.align-items-center {
            row-gap: 12px;
        }
        body.employee-dashboard-shell .page-header .col-auto.float-right.ml-auto {
            width: 100%;
            margin-left: 0 !important;
        }
        body.employee-dashboard-shell .page-header .col-auto.float-right.ml-auto .btn {
            width: 100%;
            justify-content: center;
        }
        body.employee-dashboard-shell .panel-card .panel-head {
            align-items: flex-start;
            flex-direction: column;
            gap: 6px;
        }
    }
</style>
