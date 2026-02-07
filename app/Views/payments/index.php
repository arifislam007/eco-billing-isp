<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $title ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= base_url('admin/payments/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>New Payment
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table id="paymentsTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Customer</th>
                        <th>Invoice</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Date</th>
                        <th>Received By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= $payment['payment_id'] ?></td>
                            <td>
                                <a href="<?= base_url('admin/customers/view/' . $payment['customer_id']) ?>">
                                    <?= $payment['customer_name'] ?>
                                </a>
                            </td>
                            <td><?= $payment['invoice_number'] ?? '-' ?></td>
                            <td><strong>৳<?= number_format($payment['amount'], 2) ?></strong></td>
                            <td><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></td>
                            <td><?= date('M d, Y', strtotime($payment['created_at'])) ?></td>
                            <td><?= $payment['received_by_name'] ?? '-' ?></td>
                            <td>
                                <a href="<?= base_url('admin/customers/view/' . $payment['customer_id']) ?>" class="btn btn-sm btn-info" title="View Customer">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#paymentsTable').DataTable({
        responsive: true,
        dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        language: {
            search: 'Search:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ payments',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        },
        order: [[5, 'desc']]
    });
});
</script>

<?= $this->endSection() ?>
