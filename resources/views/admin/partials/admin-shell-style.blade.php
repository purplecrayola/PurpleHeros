<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap');

    body.admin-dashboard-shell {
        background: #fcfcfd;
        color: #171327;
        font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    body.admin-dashboard-shell .header {
        background: #fcfcfd;
        border-bottom: 1px solid #eae7f2;
        height: 72px;
        box-shadow: none;
    }
    body.admin-dashboard-shell .header-left {
        display: none;
    }
    body.admin-dashboard-shell #toggle_btn,
    body.admin-dashboard-shell #mobile_btn {
        display: none !important;
    }
    body.admin-dashboard-shell .page-title-box h3 {
        color: #5e5873;
        font-size: 14px;
        font-weight: 500;
        margin: 0;
    }
    body.admin-dashboard-shell .top-nav-search .form-control {
        border: 1px solid #d9d4e5;
        border-radius: 12px;
        background: #fff;
        min-height: 40px;
    }
    body.admin-dashboard-shell .top-nav-search .btn {
        color: #6d28d9;
    }

    body.admin-dashboard-shell .sidebar {
        width: 240px;
        border-right: 1px solid #eae7f2;
        background: #fafafc;
        box-shadow: none;
    }
    body.admin-dashboard-shell .sidebar .sidebar-inner,
    body.admin-dashboard-shell .sidebar .slimScrollDiv,
    body.admin-dashboard-shell .sidebar .slimScrollDiv > .sidebar-inner,
    body.admin-dashboard-shell .sidebar .slimScrollDiv > .sidebar-inner > #sidebar-menu {
        height: 100% !important;
    }
    body.admin-dashboard-shell .sidebar .menu-title {
        display: none;
    }
    body.admin-dashboard-shell .sidebar #sidebar-menu {
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }
    body.admin-dashboard-shell .sidebar .admin-dashboard-brand {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 24px 20px 14px;
    }
    body.admin-dashboard-shell .sidebar .admin-dashboard-brand a {
        display: inline-flex;
        align-items: center;
    }
    body.admin-dashboard-shell .sidebar .admin-dashboard-brand img {
        width: auto;
        height: 38px;
        max-width: 152px;
    }
    body.admin-dashboard-shell .sidebar .admin-shell-menu-btn {
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
    body.admin-dashboard-shell .sidebar .admin-shell-menu-btn:hover,
    body.admin-dashboard-shell .sidebar .admin-shell-menu-btn:focus {
        border-color: #d9d4e5;
        background: #f7f2ff;
        color: #6d28d9;
        outline: none;
    }
    body.admin-dashboard-shell .sidebar .employee-shell-menu-icon {
        width: 16px;
        display: inline-flex;
        flex-direction: column;
        gap: 3px;
    }
    body.admin-dashboard-shell .sidebar .employee-shell-menu-icon span {
        display: block;
        width: 100%;
        height: 1.5px;
        border-radius: 999px;
        background: currentColor;
    }
    body.admin-dashboard-shell .sidebar .admin-dashboard-nav {
        margin: 0;
        padding: 8px 12px 20px;
        list-style: none;
    }
    body.admin-dashboard-shell .sidebar .admin-dashboard-nav li + li {
        margin-top: 6px;
    }
    body.admin-dashboard-shell .sidebar .admin-dashboard-nav a {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        border-radius: 12px;
        padding: 12px 14px;
        color: #5e5873;
        text-decoration: none;
        transition: background-color .15s ease, color .15s ease;
        font-size: 15px;
        font-weight: 500;
        line-height: 22px;
    }
    body.admin-dashboard-shell .sidebar .admin-dashboard-nav a i {
        width: 20px;
        font-size: 18px;
        color: #8ba3c7;
        text-align: center;
        transition: color .15s ease;
    }
    body.admin-dashboard-shell .sidebar .admin-dashboard-nav li.active a,
    body.admin-dashboard-shell .sidebar .admin-dashboard-nav a:hover {
        background: #f2ecff;
        color: #6d28d9;
    }
    body.admin-dashboard-shell .sidebar .admin-dashboard-nav li.active a::before {
        content: "";
        position: absolute;
        left: -12px;
        top: 8px;
        bottom: 8px;
        width: 3px;
        border-radius: 999px;
        background: #6d28d9;
    }
    body.admin-dashboard-shell .sidebar .admin-dashboard-nav li.active a i,
    body.admin-dashboard-shell .sidebar .admin-dashboard-nav a:hover i {
        color: #6d28d9;
    }
    body.admin-dashboard-shell .sidebar .admin-dashboard-support {
        margin-top: auto;
        padding: 12px 20px 22px;
        border-top: 1px solid #eae7f2;
    }
    body.admin-dashboard-shell .sidebar .admin-dashboard-support a {
        border: 1px solid #e0d9ee;
        border-radius: 16px;
        background: #fff;
        color: #171327;
        padding: 14px 16px;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        font-weight: 600;
        font-size: 16px;
        line-height: 1.2;
    }
    body.admin-dashboard-shell .sidebar .admin-dashboard-support small {
        display: block;
        margin-top: 4px;
        color: #5e5873;
        font-size: 13px;
        font-weight: 400;
    }

    body.admin-dashboard-shell .page-wrapper {
        margin-left: 240px;
        padding-top: 72px;
        background: #fcfcfd;
    }
    body.admin-dashboard-shell .content.container-fluid {
        max-width: 1240px;
        margin: 0 auto;
        padding: 32px 32px 40px;
    }
    body.admin-dashboard-shell .page-header {
        margin-bottom: 24px;
        border: 0;
        padding: 0;
    }
    body.admin-dashboard-shell .page-header .page-title {
        color: #171327;
        font-family: "Playfair Display", serif;
        font-size: 54px;
        line-height: 1.04;
        letter-spacing: -0.02em;
        font-weight: 700;
        margin-bottom: 8px;
    }
    body.admin-dashboard-shell .page-header .breadcrumb {
        margin-bottom: 10px;
        padding: 0;
        background: transparent;
    }
    body.admin-dashboard-shell .page-header .breadcrumb-item,
    body.admin-dashboard-shell .page-header .breadcrumb-item.active {
        color: #8c869e;
        font-size: 14px;
    }
    body.admin-dashboard-shell .page-header .breadcrumb-item a {
        color: #6d28d9;
        font-weight: 500;
    }

    body.admin-dashboard-shell .card,
    body.admin-dashboard-shell .panel,
    body.admin-dashboard-shell .table-responsive {
        border: 1px solid #eae7f2;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: none;
    }
    body.admin-dashboard-shell .card .card-header {
        background: #fff;
        border-bottom: 1px solid #eae7f2;
    }
    body.admin-dashboard-shell .card-title {
        color: #171327;
        font-size: 18px;
        line-height: 28px;
        font-weight: 600;
    }
    body.admin-dashboard-shell .btn,
    body.admin-dashboard-shell .form-control,
    body.admin-dashboard-shell .custom-select,
    body.admin-dashboard-shell .select2-container--default .select2-selection--single {
        border-radius: 12px;
    }
    body.admin-dashboard-shell .form-control,
    body.admin-dashboard-shell .custom-select,
    body.admin-dashboard-shell .select2-container--default .select2-selection--single {
        border-color: #d9d4e5;
        min-height: 40px;
        background: #fff;
        color: #171327;
    }
    body.admin-dashboard-shell .form-control:focus,
    body.admin-dashboard-shell .custom-select:focus,
    body.admin-dashboard-shell .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #6d28d9;
        box-shadow: 0 0 0 0.15rem rgba(109, 40, 217, 0.12);
    }
    body.admin-dashboard-shell textarea.form-control {
        min-height: 104px;
    }
    body.admin-dashboard-shell .form-group label {
        color: #5e5873;
        font-size: 12px;
        line-height: 16px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 8px;
    }
    body.admin-dashboard-shell .btn.btn-primary,
    body.admin-dashboard-shell .btn.btn-success,
    body.admin-dashboard-shell .btn.add-btn {
        background: #6d28d9;
        border-color: #6d28d9;
        color: #fff;
        min-height: 40px;
        padding: 0 18px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }
    body.admin-dashboard-shell .btn.btn-primary:hover,
    body.admin-dashboard-shell .btn.btn-success:hover,
    body.admin-dashboard-shell .btn.add-btn:hover {
        background: #5b2de1;
        border-color: #5b2de1;
    }
    body.admin-dashboard-shell .btn.btn-outline-primary,
    body.admin-dashboard-shell .btn.btn-outline-secondary {
        border-color: #d9d4e5;
        color: #5e5873;
        background: #fff;
        min-height: 40px;
        padding: 0 18px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }
    body.admin-dashboard-shell .btn.btn-outline-primary:hover,
    body.admin-dashboard-shell .btn.btn-outline-secondary:hover {
        color: #6d28d9;
        border-color: #6d28d9;
        background: #f7f2ff;
    }
    body.admin-dashboard-shell a:focus-visible,
    body.admin-dashboard-shell button:focus-visible,
    body.admin-dashboard-shell .btn:focus-visible,
    body.admin-dashboard-shell input:focus-visible,
    body.admin-dashboard-shell select:focus-visible,
    body.admin-dashboard-shell textarea:focus-visible {
        outline: 2px solid #6d28d9;
        outline-offset: 2px;
        box-shadow: none !important;
    }
    body.admin-dashboard-shell .text-muted,
    body.admin-dashboard-shell small.text-muted {
        color: #8c869e !important;
    }
    body.admin-dashboard-shell .pc-filter-shell {
        border: 1px solid #eae7f2;
        border-radius: 16px;
        background: #f8f8fb;
        padding: 14px;
    }
    body.admin-dashboard-shell .pc-action-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }
    body.admin-dashboard-shell .pc-action-row.right {
        justify-content: flex-end;
    }
    body.admin-dashboard-shell .pc-empty-state {
        text-align: center;
        color: #5e5873 !important;
        font-size: 14px;
        line-height: 20px;
        padding: 16px 10px !important;
        background: linear-gradient(180deg, #ffffff 0%, #fbfaff 100%);
    }
    body.admin-dashboard-shell .filter-row {
        border: 1px solid #eae7f2;
        border-radius: 16px;
        background: #f8f8fb;
        padding: 14px 8px 4px;
        margin-bottom: 20px;
    }

    body.admin-dashboard-shell .table {
        margin-bottom: 0;
    }
    body.admin-dashboard-shell .table thead th {
        border-top: 0;
        border-bottom: 1px solid #eae7f2;
        background: #f8f8fb;
        color: #5e5873;
        font-size: 12px;
        line-height: 16px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-weight: 600;
        white-space: nowrap;
    }
    body.admin-dashboard-shell .table tbody td {
        border-top: 1px solid #f0edf6;
        color: #171327;
        vertical-align: middle;
        padding-top: 12px;
        padding-bottom: 12px;
    }
    body.admin-dashboard-shell .table-responsive {
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }
    body.admin-dashboard-shell .modal-content {
        border: 1px solid #eae7f2;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(40, 24, 82, 0.08);
    }
    body.admin-dashboard-shell .modal-header {
        border-bottom: 1px solid #eae7f2;
        padding: 14px 18px;
    }
    body.admin-dashboard-shell .modal-header .modal-title {
        color: #171327;
        font-size: 18px;
        line-height: 28px;
        font-weight: 600;
    }
    body.admin-dashboard-shell .modal-body {
        padding: 18px;
    }
    body.admin-dashboard-shell .modal-footer {
        border-top: 1px solid #eae7f2;
        padding: 12px 18px;
    }
    body.admin-dashboard-shell .modal .submit-section {
        margin-top: 18px;
    }
    body.admin-dashboard-shell .modal .submit-btn,
    body.admin-dashboard-shell .modal .continue-btn,
    body.admin-dashboard-shell .modal .cancel-btn {
        min-height: 40px;
        border-radius: 12px;
    }
    body.admin-dashboard-shell .page-header .text-muted {
        font-size: 16px;
        line-height: 24px;
    }

    @media (max-width: 991px) {
        body.admin-dashboard-shell .page-title-box {
            display: none;
        }
        body.admin-dashboard-shell #mobile_btn {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border: 1px solid #eae7f2;
            border-radius: 12px;
            background: #ffffff;
            color: #5e5873;
            margin-left: 10px;
            text-decoration: none;
        }
        body.admin-dashboard-shell #mobile_btn i {
            font-size: 18px;
        }
        body.admin-dashboard-shell .sidebar {
            margin-left: -240px;
            transition: all 0.25s ease;
            z-index: 1041;
        }
        body.admin-dashboard-shell .page-wrapper {
            margin-left: 0;
            padding-top: 72px;
        }
        body.admin-dashboard-shell.admin-shell-sidebar-open .sidebar {
            margin-left: 0;
        }
        body.admin-dashboard-shell .content.container-fluid {
            padding: 24px 16px 32px;
        }
        body.admin-dashboard-shell .page-header .page-title {
            font-size: 42px;
            line-height: 1.08;
        }
        body.admin-dashboard-shell .sidebar .admin-shell-menu-btn {
            display: none;
        }
        body.admin-dashboard-shell .filter-row {
            padding: 12px 8px 6px;
        }
    }
    @media (max-width: 767px) {
        body.admin-dashboard-shell .page-header .page-title {
            font-size: 36px;
        }
        body.admin-dashboard-shell .sidebar .admin-dashboard-brand {
            padding-top: 18px;
        }
        body.admin-dashboard-shell .page-header .col-auto,
        body.admin-dashboard-shell .page-header .col-auto .btn {
            width: 100%;
        }
        body.admin-dashboard-shell .pc-action-row .btn,
        body.admin-dashboard-shell .pc-action-row.right .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
    (function () {
        document.addEventListener('DOMContentLoaded', function () {
            if (!document.body.classList.contains('admin-dashboard-shell')) {
                return;
            }

            var body = document.body;
            var menuButtons = document.querySelectorAll('.admin-shell-menu-btn');
            var desktopTrigger = document.getElementById('toggle_btn');
            var mobileTrigger = document.getElementById('mobile_btn');
            var sidebarLinks = document.querySelectorAll('#sidebar .admin-dashboard-nav a');

            var isMobile = function () {
                return window.matchMedia('(max-width: 991px)').matches;
            };

            var syncState = function () {
                if (!isMobile()) {
                    body.classList.remove('admin-shell-sidebar-open');
                }
            };

            menuButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    if (isMobile()) {
                        body.classList.toggle('admin-shell-sidebar-open');
                        return;
                    }
                    if (desktopTrigger) {
                        desktopTrigger.click();
                    }
                });
            });

            if (mobileTrigger) {
                mobileTrigger.addEventListener('click', function (event) {
                    event.preventDefault();
                    body.classList.toggle('admin-shell-sidebar-open');
                });
            }

            sidebarLinks.forEach(function (link) {
                link.addEventListener('click', function () {
                    if (isMobile()) {
                        body.classList.remove('admin-shell-sidebar-open');
                    }
                });
            });

            window.addEventListener('resize', syncState);
            syncState();
        });
    })();
</script>
