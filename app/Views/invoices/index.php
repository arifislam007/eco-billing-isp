<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $title ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= base_url('admin/invoices/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Create Invoice
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <div class="row">
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Partial</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" id="dateFrom" placeholder="From Date">
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" id="dateTo" placeholder="To Date">
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-secondary w-100" onclick="filterInvoices()">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="invoicesTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Package</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?= $invoice['invoice_number'] ?></td>
                            <td>
                                <a href="<?= base_url('admin/customers/view/' . $invoice['customer_id']) ?>">
                                    <?= $invoice['customer_name'] ?>
                                </a>
                            </td>
                            <td><?= $invoice['package_name'] ?? '-' ?></td>
                            <td>৳<?= number_format($invoice['total_amount'], 2) ?></td>
                            <td><?= date('M d, Y', strtotime($invoice['due_date'])) ?></td>
                            <td>
                                <span class="badge-status status-<?= $invoice['payment_status'] ?>">
                                    <?= ucfirst($invoice['payment_status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('admin/invoices/view/' . $invoice['id']) ?>" class="btn btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($invoice['payment_status'] == 'unpaid'): ?>
                                        <a href="<?= base_url('admin/payments/create/' . $invoice['customer_id'] . '?invoice=' . $invoice['id']) ?>" class="btn btn-success" title="Add Payment">
                                            <i class="fas fa-money-bill"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('admin/invoices/pdf/' . $invoice['id']) ?>" class="btn btn-secondary" title="Download PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterInvoices() {
    const status = $('#statusFilter').val();
    const fromDate = $('#dateFrom').val();
    const toDate = $('#dateTo').val();
    
    // Reload DataTable with filters
    $('#invoicesTable').DataTable().ajax.reload();
}

$(document).ready(function() {
    $('#invoicesTable').DataTable({
        responsive: true,
        dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        language: {
            search: 'Search:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ invoices',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        },
        order: [[0, 'desc']]
    });
});
</script>

<?= $this->endSection() ?>
