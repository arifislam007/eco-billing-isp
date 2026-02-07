<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Login' ?> - ISP Billing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body class="bg-gradient">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0"><i class="fas fa-network-wired me-2"></i>ISP Billing System</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if (session()->has('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= session('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (session()->has('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= session('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form action="<?= base_url('auth/login') ?>" method="post">
                            <?= csrf_field() ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control <?= $validation->hasError('username') ? 'is-invalid' : '' ?>" 
                                           name="username" value="<?= old('username') ?>" required>
                                    <?php if ($validation->hasError('username')): ?>
                                        <div class="invalid-feedback"><?= $validation->getError('username') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control <?= $validation->hasError('password') ? 'is-invalid' : '' ?>" 
                                           name="password" required>
                                    <?php if ($validation->hasError('password')): ?>
                                        <div class="invalid-feedback"><?= $validation->getError('password') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
