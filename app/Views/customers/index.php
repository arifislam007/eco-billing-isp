<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $title ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= base_url('admin/customers/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Add Customer
            </a>
        </div>
        <div class="btn-group me-2">
            <a href="<?= base_url('admin/customers/export') ?>" class="btn btn-sm btn-success">
                <i class="fas fa-file-export me-1"></i>Export
            </a>
        </div>
    </div>
</div>

<?php if (session()->has('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Package</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Expiration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?= $customer['customer_id'] ?></td>
                                <td><?= $customer['full_name'] ?></td>
                                <td><?= $customer['username'] ?></td>
                                <td><?= $customer['package_name'] ?? 'N/A' ?></td>
                                <td>৳<?= number_format($customer['price'] ?? 0, 2) ?></td>
                                <td>
                                    <span class="badge-status status-<?= $customer['status'] ?>">
                                        <?= ucfirst($customer['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($customer['expiration_date'])) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?= base_url('admin/customers/view/' . $customer['id']) ?>" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= base_url('admin/customers/edit/' . $customer['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= base_url('admin/customers/delete/' . $customer['id']) ?>" class="btn btn-sm btn-danger confirm-delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No customers found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
