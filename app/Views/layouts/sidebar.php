<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= uri_string() == 'admin/dashboard' ? 'active' : '' ?>" href="<?= base_url('admin/dashboard') ?>">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Management</span>
        </h6>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= strpos(uri_string(), 'customers') !== false ? 'active' : '' ?>" href="<?= base_url('admin/customers') ?>">
                    <i class="fas fa-users me-2"></i>
                    Customers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos(uri_string(), 'packages') !== false ? 'active' : '' ?>" href="<?= base_url('admin/packages') ?>">
                    <i class="fas fa-box me-2"></i>
                    Packages
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos(uri_string(), 'routers') !== false ? 'active' : '' ?>" href="<?= base_url('admin/routers') ?>">
                    <i class="fas fa-server me-2"></i>
                    Routers
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Billing</span>
        </h6>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= strpos(uri_string(), 'invoices') !== false ? 'active' : '' ?>" href="<?= base_url('admin/invoices') ?>">
                    <i class="fas fa-file-invoice me-2"></i>
                    Invoices
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos(uri_string(), 'payments') !== false ? 'active' : '' ?>" href="<?= base_url('admin/payments') ?>">
                    <i class="fas fa-money-bill me-2"></i>
                    Payments
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Support</span>
        </h6>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= strpos(uri_string(), 'tickets') !== false ? 'active' : '' ?>" href="<?= base_url('admin/tickets') ?>">
                    <i class="fas fa-ticket-alt me-2"></i>
                    Tickets
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Reports</span>
        </h6>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= strpos(uri_string(), 'reports') !== false ? 'active' : '' ?>" href="<?= base_url('admin/reports') ?>">
                    <i class="fas fa-chart-bar me-2"></i>
                    Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos(uri_string(), 'radius') !== false ? 'active' : '' ?>" href="<?= base_url('admin/radius') ?>">
                    <i class="fas fa-wifi me-2"></i>
                    RADIUS
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Settings</span>
        </h6>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= strpos(uri_string(), 'settings') !== false ? 'active' : '' ?>" href="<?= base_url('admin/settings') ?>">
                    <i class="fas fa-cog me-2"></i>
                    Settings
                </a>
            </li>
        </ul>
    </div>
</nav>
