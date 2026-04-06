<style>
    body.employee-dashboard-shell {
        background: #fcfcfd;
    }
    body.employee-dashboard-shell .header {
        display: none;
    }
    body.employee-dashboard-shell .sidebar {
        width: 240px !important;
        top: 0;
        bottom: 0;
        left: 0;
        margin-left: 0 !important;
        border-right: 1px solid #eae7f2;
        background: #fafafc;
        box-shadow: none;
        transition: left .2s ease;
        z-index: 1040;
    }
    body.employee-dashboard-shell .sidebar .sidebar-inner {
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    body.employee-dashboard-shell .sidebar .slimScrollDiv,
    body.employee-dashboard-shell .sidebar .slimScrollDiv > .sidebar-inner,
    body.employee-dashboard-shell .sidebar .slimScrollDiv > .sidebar-inner > #sidebar-menu {
        height: 100% !important;
    }
    body.employee-dashboard-shell .sidebar .menu-title {
        display: none;
    }
    body.employee-dashboard-shell .sidebar .employee-dashboard-brand {
        padding: 28px 24px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    body.employee-dashboard-shell .sidebar .employee-dashboard-brand img {
        max-width: 182px;
        height: auto;
    }
    body.employee-dashboard-shell .sidebar .employee-dashboard-brand a {
        display: inline-flex;
        align-items: center;
        min-width: 0;
        flex: 1 1 auto;
    }
    body.employee-dashboard-shell .sidebar .employee-dashboard-brand .employee-shell-menu-btn {
        flex-shrink: 0;
    }
    body.employee-dashboard-shell .sidebar .employee-dashboard-nav {
        margin: 0;
        padding: 10px 14px 12px;
        list-style: none;
    }
    body.employee-dashboard-shell .sidebar .employee-dashboard-nav li + li {
        margin-top: 6px;
    }
    body.employee-dashboard-shell .sidebar .employee-dashboard-nav a {
        border-radius: 12px;
        color: #5e5873 !important;
        font-size: 15px;
        line-height: 22px;
        font-weight: 500;
        padding: 11px 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
    }
    body.employee-dashboard-shell .sidebar .employee-dashboard-nav a i {
        width: 20px;
        text-align: center;
        font-size: 20px;
        color: #8da1bc !important;
    }
    body.employee-dashboard-shell .sidebar .employee-dashboard-nav a span {
        color: #5e5873 !important;
        opacity: 1 !important;
    }
    body.employee-dashboard-shell .sidebar .employee-dashboard-nav li.active a,
    body.employee-dashboard-shell .sidebar .employee-dashboard-nav a:hover {
        color: #6d28d9 !important;
        background: #f7f2ff;
    }
    body.employee-dashboard-shell .sidebar .employee-dashboard-nav li.active a::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 9px;
        bottom: 9px;
        width: 3px;
        border-radius: 999px;
        background: #6d28d9;
    }
    body.employee-dashboard-shell .sidebar .employee-dashboard-nav li.active a i,
    body.employee-dashboard-shell .sidebar .employee-dashboard-nav a:hover i,
    body.employee-dashboard-shell .sidebar .employee-dashboard-nav li.active a span,
    body.employee-dashboard-shell .sidebar .employee-dashboard-nav a:hover span {
        color: #6d28d9 !important;
    }
    body.employee-dashboard-shell .sidebar .employee-dashboard-support {
        margin-top: auto;
        padding: 14px;
        border-top: 1px solid #eae7f2;
    }
    body.employee-dashboard-shell .sidebar .employee-dashboard-support a {
        color: #171327;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        font-weight: 500;
        border-radius: 12px;
        border: 1px solid #eae7f2;
        background: #fff;
        padding: 11px 12px;
    }
    body.employee-dashboard-shell .sidebar .employee-dashboard-support small {
        display: block;
        color: #8c869e;
        font-size: 12px;
        line-height: 16px;
        margin-top: 1px;
    }
    body.employee-dashboard-shell .page-wrapper {
        margin-left: 240px;
        padding-top: 0;
        transition: margin-left .2s ease;
    }
    body.employee-dashboard-shell .employee-shell-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(23, 19, 39, 0.18);
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s ease;
        z-index: 1035;
    }
    @media (min-width: 992px) {
        body.employee-dashboard-shell.employee-shell-sidebar-collapsed .sidebar {
            left: -240px;
        }
        body.employee-dashboard-shell.employee-shell-sidebar-collapsed .page-wrapper {
            margin-left: 0;
        }
        body.employee-dashboard-shell.employee-shell-sidebar-collapsed .sidebar .employee-dashboard-brand .employee-shell-menu-btn {
            position: fixed;
            top: 18px;
            left: 16px;
            z-index: 1045;
        }
    }
    @media (max-width: 991px) {
        body.employee-dashboard-shell .sidebar {
            left: -240px;
            width: 240px !important;
            margin-left: 0 !important;
        }
        body.employee-dashboard-shell .page-wrapper {
            margin-left: 0;
        }
        body.employee-dashboard-shell.employee-shell-sidebar-open .sidebar {
            left: 0;
        }
        body.employee-dashboard-shell.employee-shell-sidebar-open .employee-shell-backdrop {
            opacity: 1;
            pointer-events: auto;
        }
        body.employee-dashboard-shell:not(.employee-shell-sidebar-open) .sidebar .employee-dashboard-brand .employee-shell-menu-btn {
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 1045;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!document.body.classList.contains('employee-dashboard-shell')) {
            return;
        }

        var body = document.body;
        var menuButtons = Array.prototype.slice.call(document.querySelectorAll('.employee-shell-menu-btn'));
        var sidebar = document.getElementById('sidebar');

        if (!sidebar || !menuButtons.length) {
            return;
        }

        var backdrop = document.createElement('button');
        backdrop.type = 'button';
        backdrop.className = 'employee-shell-backdrop';
        backdrop.setAttribute('aria-label', 'Close navigation menu');
        body.appendChild(backdrop);

        var isMobileViewport = function () {
            return window.matchMedia('(max-width: 991px)').matches;
        };

        var syncExpandedState = function () {
            var expanded = isMobileViewport()
                ? body.classList.contains('employee-shell-sidebar-open')
                : !body.classList.contains('employee-shell-sidebar-collapsed');

            menuButtons.forEach(function (button) {
                button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            });
        };

        var closeSidebar = function () {
            if (isMobileViewport()) {
                body.classList.remove('employee-shell-sidebar-open');
            } else {
                body.classList.add('employee-shell-sidebar-collapsed');
            }
            syncExpandedState();
        };

        var toggleSidebar = function () {
            if (isMobileViewport()) {
                body.classList.toggle('employee-shell-sidebar-open');
            } else {
                body.classList.toggle('employee-shell-sidebar-collapsed');
            }
            syncExpandedState();
        };

        menuButtons.forEach(function (button) {
            button.addEventListener('click', toggleSidebar);
        });

        backdrop.addEventListener('click', function () {
            body.classList.remove('employee-shell-sidebar-open');
            syncExpandedState();
        });

        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (isMobileViewport()) {
                    body.classList.remove('employee-shell-sidebar-open');
                    syncExpandedState();
                }
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        });

        window.addEventListener('resize', function () {
            body.classList.remove('employee-shell-sidebar-open');
            syncExpandedState();
        });

        syncExpandedState();
    });
</script>
