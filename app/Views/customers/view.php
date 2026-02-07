<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $title ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= base_url('admin/customers/edit/' . $customer['id']) ?>" class="btn btn-sm btn-warning">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
        </div>
        <a href="<?= base_url('admin/customers') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Customer ID:</strong></td>
                        <td><?= $customer['customer_id'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td><?= $customer['full_name'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Username:</strong></td>
                        <td><?= $customer['username'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td><?= $customer['phone'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?= $customer['email'] ?? '-' ?></td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <span class="badge-status status-<?= $customer['status'] ?>">
                                <?= ucfirst($customer['status']) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Package Information</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($customer['package'])): ?>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Package:</strong></td>
                            <td><?= $customer['package']['package_name'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Type:</strong></td>
                            <td><?= ucfirst($customer['package']['package_type']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Price:</strong></td>
                            <td>৳<?= number_format($customer['package']['price'], 2) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Speed:</strong></td>
                            <td><?= $customer['package']['download_speed'] ?>M / <?= $customer['package']['upload_speed'] ?>M</td>
                        </tr>
                        <tr>
                            <td><strong>Valid Days:</strong></td>
                            <td><?= $customer['package']['valid_days'] ?> days</td>
                        </tr>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No package assigned</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Account Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Activation Date:</strong></td>
                        <td><?= date('M d, Y', strtotime($customer['activation_date'])) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Expiration Date:</strong></td>
                        <td><?= date('M d, Y', strtotime($customer['expiration_date'])) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Balance:</strong></td>
                        <td>৳<?= number_format($customer['balance'], 2) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Connection:</strong></td>
                        <td><?= ucfirst($customer['connection_type']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Static IP:</strong></td>
                        <td><?= $customer['static_ip'] ?? '-' ?></td>
                    </tr>
                    <tr>
                        <td><strong>MAC Address:</strong></td>
                        <td><?= $customer['mac_address'] ?? '-' ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Recent Invoices -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Recent Invoices</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customer['invoices'])): ?>
                        <?php foreach ($customer['invoices'] as $invoice): ?>
                            <tr>
                                <td><?= $invoice['invoice_number'] ?></td>
                                <td><?= date('M d, Y', strtotime($invoice['created_at'])) ?></td>
                                <td>৳<?= number_format($invoice['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge-status status-<?= $invoice['payment_status'] ?>">
                                        <?= ucfirst($invoice['payment_status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No invoices found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Activity Log -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Activity Log</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customer['logs'])): ?>
                        <?php foreach ($customer['logs'] as $log): ?>
                            <tr>
                                <td><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></td>
                                <td><?= ucfirst(str_replace('_', ' ', $log['action'])) ?></td>
                                <td><?= $log['description'] ?? '-' ?></td>
                                <td><?= $log['ip_address'] ?? '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No activity logs found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
