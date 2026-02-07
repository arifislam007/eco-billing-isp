<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $title ?></h1>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-body">
                <?php if (session()->has('errors')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            <?php foreach (session('errors') as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form action="<?= base_url('admin/customers/store') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" name="username" value="<?= old('username') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="full_name" value="<?= old('full_name') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone *</label>
                            <input type="text" class="form-control" name="phone" value="<?= old('phone') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= old('email') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Connection Type *</label>
                            <select class="form-select" name="connection_type" required>
                                <option value="">Select Type</option>
                                <option value="pppoe" <?= old('connection_type') == 'pppoe' ? 'selected' : '' ?>>PPPoE</option>
                                <option value="hotspot" <?= old('connection_type') == 'hotspot' ? 'selected' : '' ?>>Hotspot</option>
                                <option value="static" <?= old('connection_type') == 'static' ? 'selected' : '' ?>>Static IP</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Package *</label>
                            <select class="form-select" name="package_id" required>
                                <option value="">Select Package</option>
                                <?php if (!empty($packages)): ?>
                                    <?php foreach ($packages as $package): ?>
                                        <option value="<?= $package['id'] ?>" <?= old('package_id') == $package['id'] ? 'selected' : '' ?>>
                                            <?= $package['package_name'] ?> - ৳<?= number_format($package['price'], 2) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Router</label>
                            <select class="form-select" name="router_id">
                                <option value="">Select Router</option>
                                <?php if (!empty($routers)): ?>
                                    <?php foreach ($routers as $router): ?>
                                        <option value="<?= $router['id'] ?>" <?= old('router_id') == $router['id'] ? 'selected' : '' ?>>
                                            <?= $router['router_name'] ?> (<?= $router['ip_address'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Static IP</label>
                            <input type="text" class="form-control" name="static_ip" value="<?= old('static_ip') ?>" placeholder="e.g., 192.168.1.100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">MAC Address</label>
                            <input type="text" class="form-control" name="mac_address" value="<?= old('mac_address') ?>" placeholder="e.g., AA:BB:CC:DD:EE:FF">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="3"><?= old('address') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">NID Number</label>
                        <input type="text" class="form-control" name="nid_number" value="<?= old('nid_number') ?>">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Customer
                        </button>
                        <a href="<?= base_url('admin/customers') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
