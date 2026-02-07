<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $title ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('admin/routers') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Router Information</h5>
            </div>
            <div class="card-body">
                <?= form_open('admin/routers/edit/' . $router['id'], ['id' => 'routerForm']) ?>
                    <?= csrf_field() ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="router_name" class="form-label">Router Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['router_name']) ? 'is-invalid' : '' ?>" 
                                   id="router_name" name="router_name" value="<?= old('router_name', $router['router_name']) ?>" required>
                            <?php if (isset($errors['router_name'])): ?>
                                <div class="invalid-feedback"><?= $errors['router_name'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="router_type" class="form-label">Router Type <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['router_type']) ? 'is-invalid' : '' ?>" 
                                    id="router_type" name="router_type" required>
                                <option value="">Select Type</option>
                                <option value="mikrotik" <?= old('router_type', $router['router_type']) == 'mikrotik' ? 'selected' : '' ?>>MikroTik</option>
                                <option value="cisco" <?= old('router_type', $router['router_type']) == 'cisco' ? 'selected' : '' ?>>Cisco</option>
                                <option value="ubiquiti" <?= old('router_type', $router['router_type']) == 'ubiquiti' ? 'selected' : '' ?>>Ubiquiti</option>
                                <option value="other" <?= old('router_type', $router['router_type']) == 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                            <?php if (isset($errors['router_type'])): ?>
                                <div class="invalid-feedback"><?= $errors['router_type'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ip_address" class="form-label">IP Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['ip_address']) ? 'is-invalid' : '' ?>" 
                                   id="ip_address" name="ip_address" value="<?= old('ip_address', $router['ip_address']) ?>" required>
                            <?php if (isset($errors['ip_address'])): ?>
                                <div class="invalid-feedback"><?= $errors['ip_address'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="port" class="form-label">API Port</label>
                            <input type="text" class="form-control" id="port" name="port" 
                                   value="<?= old('port', $router['port']) ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">API Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= old('username', $router['username']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">API Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?= old('location', $router['location']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>" 
                                    id="status" name="status" required>
                                <option value="active" <?= old('status', $router['status']) == 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= old('status', $router['status']) == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="api_token" class="form-label">API Token (Optional)</label>
                        <input type="text" class="form-control" id="api_token" name="api_token" 
                               value="<?= old('api_token', $router['api_token']) ?>">
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Router
                        </button>
                        <a href="<?= base_url('admin/routers') ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
