<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $title ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('admin/packages') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Package Information</h5>
            </div>
            <div class="card-body">
                <?= form_open('admin/packages/create', ['id' => 'packageForm']) ?>
                    <?= csrf_field() ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="package_name" class="form-label">Package Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['package_name']) ? 'is-invalid' : '' ?>" 
                                   id="package_name" name="package_name" value="<?= old('package_name') ?>" required>
                            <?php if (isset($errors['package_name'])): ?>
                                <div class="invalid-feedback"><?= $errors['package_name'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="package_type" class="form-label">Package Type <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['package_type']) ? 'is-invalid' : '' ?>" 
                                    id="package_type" name="package_type" required>
                                <option value="">Select Type</option>
                                <option value="prepaid" <?= old('package_type') == 'prepaid' ? 'selected' : '' ?>>Prepaid</option>
                                <option value="postpaid" <?= old('package_type') == 'postpaid' ? 'selected' : '' ?>>Postpaid</option>
                                <option value="hotspot" <?= old('package_type') == 'hotspot' ? 'selected' : '' ?>>Hotspot</option>
                                <option value="pppoe" <?= old('package_type') == 'pppoe' ? 'selected' : '' ?>>PPPoE</option>
                            </select>
                            <?php if (isset($errors['package_type'])): ?>
                                <div class="invalid-feedback"><?= $errors['package_type'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="download_speed" class="form-label">Download Speed (Mbps) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?= isset($errors['download_speed']) ? 'is-invalid' : '' ?>" 
                                   id="download_speed" name="download_speed" value="<?= old('download_speed', 10) ?>" required min="1">
                            <?php if (isset($errors['download_speed'])): ?>
                                <div class="invalid-feedback"><?= $errors['download_speed'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="upload_speed" class="form-label">Upload Speed (Mbps) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?= isset($errors['upload_speed']) ? 'is-invalid' : '' ?>" 
                                   id="upload_speed" name="upload_speed" value="<?= old('upload_speed', 5) ?>" required min="1">
                            <?php if (isset($errors['upload_speed'])): ?>
                                <div class="invalid-feedback"><?= $errors['upload_speed'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price (৳) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" 
                                   id="price" name="price" value="<?= old('price', 500) ?>" required min="0" step="0.01">
                            <?php if (isset($errors['price'])): ?>
                                <div class="invalid-feedback"><?= $errors['price'] ?></div>
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
                            <label for="bandwidth_limit" class="form-label">Bandwidth Limit (GB)</label>
                            <input type="number" class="form-control" id="bandwidth_limit" name="bandwidth_limit" 
                                   value="<?= old('bandwidth_limit') ?>" min="0">
                            <small class="text-muted">Leave empty for unlimited</small>
                        </div>
                        <div class="col-md-6">
                            <label for="valid_days" class="form-label">Valid Days <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?= isset($errors['valid_days']) ? 'is-invalid' : '' ?>" 
                                   id="valid_days" name="valid_days" value="<?= old('valid_days', 30) ?>" required min="1">
                            <?php if (isset($errors['valid_days'])): ?>
                                <div class="invalid-feedback"><?= $errors['valid_days'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="radius_group" class="form-label">FreeRADIUS Group</label>
                            <input type="text" class="form-control" id="radius_group" name="radius_group" 
                                   value="<?= old('radius_group') ?>" placeholder="e.g., 10mb_package">
                            <small class="text-muted">This will be synced to RADIUS group for bandwidth control</small>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>" 
                                    id="status" name="status" required>
                                <option value="active" <?= old('status', 'active') == 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= old('status') == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Package
                        </button>
                        <a href="<?= base_url('admin/packages') ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('packageForm').addEventListener('submit', function(e) {
    const packageName = document.getElementById('package_name').value.trim();
    const radiusGroup = document.getElementById('radius_group').value.trim();
    
    if (radiusGroup) {
        // Auto-generate RADIUS group name based on speed if not provided
        const download = document.getElementById('download_speed').value;
        const upload = document.getElementById('upload_speed').value;
        const groupName = `${download}m_${upload}m_package`;
        document.getElementById('radius_group').value = groupName;
    }
});
</script>

<?= $this->endSection() ?>
