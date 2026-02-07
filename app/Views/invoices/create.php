<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $title ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('admin/invoices') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Create New Invoice</h5>
            </div>
            <div class="card-body">
                <?= form_open('admin/invoices/create', ['id' => 'invoiceForm']) ?>
                    <?= csrf_field() ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                            <select class="form-select select2 <?= isset($errors['customer_id']) ? 'is-invalid' : '' ?>" 
                                    id="customer_id" name="customer_id" required>
                                <option value="">Select Customer</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['id'] ?>" 
                                            data-package="<?= $customer['package_id'] ?>"
                                            data-price="<?= $customer['package_price'] ?? 0 ?>">
                                        <?= $customer['customer_id'] ?> - <?= $customer['full_name'] ?> (<?= $customer['username'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['customer_id'])): ?>
                                <div class="invalid-feedback"><?= $errors['customer_id'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="package_id" class="form-label">Package <span class="text-danger">*</span></label>
                            <select class="form-select select2 <?= isset($errors['package_id']) ? 'is-invalid' : '' ?>" 
                                    id="package_id" name="package_id" required>
                                <option value="">Select Package</option>
                                <?php foreach ($packages as $package): ?>
                                    <option value="<?= $package['id'] ?>" data-price="<?= $package['price'] ?>">
                                        <?= $package['package_name'] ?> - ৳<?= number_format($package['price'], 2) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['package_id'])): ?>
                                <div class="invalid-feedback"><?= $errors['package_id'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="created_date" class="form-label">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control <?= isset($errors['created_date']) ? 'is-invalid' : '' ?>" 
                                   id="created_date" name="created_date" value="<?= date('Y-m-d') ?>" required>
                            <?php if (isset($errors['created_date'])): ?>
                                <div class="invalid-feedback"><?= $errors['created_date'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control <?= isset($errors['due_date']) ? 'is-invalid' : '' ?>" 
                                   id="due_date" name="due_date" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                            <?php if (isset($errors['due_date'])): ?>
                                <div class="invalid-feedback"><?= $errors['due_date'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Amount (৳) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?= isset($errors['amount']) ? 'is-invalid' : '' ?>" 
                                   id="amount" name="amount" value="<?= old('amount', 0) ?>" required min="0" step="0.01">
                            <?php if (isset($errors['amount'])): ?>
                                <div class="invalid-feedback"><?= $errors['amount'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="tax_percentage" class="form-label">Tax Percentage (%)</label>
                            <input type="number" class="form-control" id="tax_percentage" name="tax_percentage" 
                                   value="<?= old('tax_percentage', 0) ?>" min="0" step="0.01">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="discount" class="form-label">Discount (৳)</label>
                            <input type="number" class="form-control" id="discount" name="discount" 
                                   value="<?= old('discount', 0) ?>" min="0" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="">Not Paid</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="mobile_banking">Mobile Banking</option>
                                <option value="card">Card</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?= old('notes') ?></textarea>
                    </div>

                    <!-- Invoice Summary -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6>Invoice Summary</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1">Subtotal: <strong>৳<span id="subtotalDisplay">0.00</span></strong></p>
                                    <p class="mb-1">Tax: <strong>৳<span id="taxDisplay">0.00</span></strong></p>
                                    <p class="mb-1">Discount: <strong>-৳<span id="discountDisplay">0.00</span></strong></p>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-0">Total: <strong>৳<span id="totalDisplay">0.00</span></strong></h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Create Invoice
                        </button>
                        <a href="<?= base_url('admin/invoices') ?>" class="btn btn-secondary">
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
    // Initialize select2
    $('.select2').select2();
    
    // Package price change
    $('#package_id').change(function() {
        const price = $(this).find(':selected').data('price') || 0;
        $('#amount').val(price);
        calculateTotal();
    });
    
    // Amount change
    $('#amount').change(function() {
        calculateTotal();
    });
    
    // Tax change
    $('#tax_percentage').change(function() {
        calculateTotal();
    });
    
    // Discount change
    $('#discount').change(function() {
        calculateTotal();
    });
    
    function calculateTotal() {
        const amount = parseFloat($('#amount').val()) || 0;
        const taxPercent = parseFloat($('#tax_percentage').val()) || 0;
        const discount = parseFloat($('#discount').val()) || 0;
        
        const subtotal = amount;
        const tax = (subtotal * taxPercent) / 100;
        const total = subtotal + tax - discount;
        
        $('#subtotalDisplay').text(subtotal.toFixed(2));
        $('#taxDisplay').text(tax.toFixed(2));
        $('#discountDisplay').text(discount.toFixed(2));
        $('#totalDisplay').text(total.toFixed(2));
    }
});
</script>

<?= $this->endSection() ?>
