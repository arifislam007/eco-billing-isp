<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $title ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('admin/customers/view/' . $customer['id']) ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Customer
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <!-- Customer Summary -->
        <div class="card bg-light mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-1"><?= $customer['full_name'] ?></h5>
                        <p class="mb-0 text-muted"><?= $customer['customer_id'] ?> | <?= $customer['username'] ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="mb-0">Current Balance: <strong class="<?= $customer['balance'] < 0 ? 'text-danger' : 'text-success' ?>">
                            ৳<?= number_format($customer['balance'], 2) ?>
                        </strong></p>
                        <?php if (!empty($pendingInvoices)): ?>
                            <p class="mb-0 text-danger">Pending Due: ৳<?= number_format(array_sum(array_column($pendingInvoices, 'total_amount')) - array_sum(array_column($pendingInvoices, 'paid_amount')), 2) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <?= form_open('admin/payments/create/' . $customer['id'], ['id' => 'paymentForm']) ?>
                    <?= csrf_field() ?>
                    
                    <?php if (!empty($pendingInvoices)): ?>
                    <div class="mb-3">
                        <label for="invoice_id" class="form-label">Invoice (Optional)</label>
                        <select class="form-select" id="invoice_id" name="invoice_id">
                            <option value="">Select Invoice</option>
                            <?php foreach ($pendingInvoices as $inv): ?>
                                <?php $due = $inv['total_amount'] - $inv['paid_amount']; ?>
                                <option value="<?= $inv['id'] ?>" data-amount="<?= $due ?>">
                                    <?= $inv['invoice_number'] ?> - Due: ৳<?= number_format($due, 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Leave empty for balance payment</small>
                    </div>
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Amount (৳) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?= isset($errors['amount']) ? 'is-invalid' : '' ?>" 
                                   id="amount" name="amount" value="<?= old('amount', $defaultAmount ?? '') ?>" required min="1" step="0.01">
                            <?php if (isset($errors['amount'])): ?>
                                <div class="invalid-feedback"><?= $errors['amount'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['payment_method']) ? 'is-invalid' : '' ?>" 
                                    id="payment_method" name="payment_method" required>
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="mobile_banking">Mobile Banking</option>
                                <option value="card">Card</option>
                            </select>
                            <?php if (isset($errors['payment_method'])): ?>
                                <div class="invalid-feedback"><?= $errors['payment_method'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="transaction_id" class="form-label">Transaction ID</label>
                            <input type="text" class="form-control" id="transaction_id" name="transaction_id" 
                                   value="<?= old('transaction_id') ?>" placeholder="TXN123456">
                        </div>
                        <div class="col-md-6">
                            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control <?= isset($errors['payment_date']) ? 'is-invalid' : '' ?>" 
                                   id="payment_date" name="payment_date" value="<?= date('Y-m-d') ?>" required>
                            <?php if (isset($errors['payment_date'])): ?>
                                <div class="invalid-feedback"><?= $errors['payment_date'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?= old('notes') ?></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i>Record Payment
                        </button>
                        <a href="<?= base_url('admin/customers/view/' . $customer['id']) ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#invoice_id').change(function() {
        const amount = $(this).find(':selected').data('amount');
        if (amount) {
            $('#amount').val(amount);
        }
    });
});
</script>

<?= $this->endSection() ?>
