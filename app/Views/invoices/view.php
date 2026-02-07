<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Invoice: <?= $invoice['invoice_number'] ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= base_url('admin/invoices/pdf/' . $invoice['id']) ?>" class="btn btn-sm btn-secondary" target="_blank">
                <i class="fas fa-file-pdf me-1"></i>Download PDF
            </a>
            <a href="<?= base_url('admin/invoices') ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
        <?php if ($invoice['payment_status'] == 'unpaid'): ?>
            <a href="<?= base_url('admin/payments/create/' . $invoice['customer_id'] . '?invoice=' . $invoice['id']) ?>" class="btn btn-sm btn-success">
                <i class="fas fa-money-bill me-1"></i>Record Payment
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Invoice Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Invoice Number:</strong><br>
                        <?= $invoice['invoice_number'] ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        <span class="badge-status status-<?= $invoice['payment_status'] ?>">
                            <?= ucfirst($invoice['payment_status']) ?>
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Created Date:</strong><br>
                        <?= date('M d, Y', strtotime($invoice['created_date'])) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Due Date:</strong><br>
                        <?= date('M d, Y', strtotime($invoice['due_date'])) ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Package:</strong><br>
                        <?= $invoice['package_name'] ?? 'N/A' ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Payment Method:</strong><br>
                        <?= $invoice['payment_method'] ? ucfirst(str_replace('_', ' ', $invoice['payment_method'])) : '-' ?>
                    </div>
                </div>
                <?php if ($invoice['notes']): ?>
                <div class="mb-3">
                    <strong>Notes:</strong><br>
                    <?= $invoice['notes'] ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Amount Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-end">৳<?= number_format($invoice['amount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td>Tax:</td>
                        <td class="text-end">৳<?= number_format($invoice['tax_amount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td>Discount:</td>
                        <td class="text-end">-৳<?= number_format($invoice['discount'], 2) ?></td>
                    </tr>
                    <tr class="border-top">
                        <td><strong>Total Amount:</strong></td>
                        <td class="text-end"><strong>৳<?= number_format($invoice['total_amount'], 2) ?></strong></td>
                    </tr>
                    <tr>
                        <td>Paid:</td>
                        <td class="text-end">৳<?= number_format($invoice['paid_amount'], 2) ?></td>
                    </tr>
                    <tr class="border-top">
                        <td><strong>Due:</strong></td>
                        <td class="text-end"><strong>৳<?= number_format($invoice['total_amount'] - $invoice['paid_amount'], 2) ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Customer Info -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Customer Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <strong>Customer ID:</strong><br>
                <a href="<?= base_url('admin/customers/view/' . $invoice['customer_id']) ?>">
                    <?= $invoice['customer_id'] ?>
                </a>
            </div>
            <div class="col-md-3">
                <strong>Name:</strong><br>
                <?= $invoice['customer_name'] ?>
            </div>
            <div class="col-md-3">
                <strong>Phone:</strong><br>
                <?= $invoice['phone'] ?>
            </div>
            <div class="col-md-3">
                <strong>Email:</strong><br>
                <?= $invoice['email'] ?? '-' ?>
            </div>
        </div>
    </div>
</div>

<!-- Payment History -->
<?php if (!empty($payments)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Payment History</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Received By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= $payment['payment_id'] ?></td>
                            <td><?= date('M d, Y H:i', strtotime($payment['created_at'])) ?></td>
                            <td>৳<?= number_format($payment['amount'], 2) ?></td>
                            <td><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></td>
                            <td><?= $payment['received_by_name'] ?? '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
