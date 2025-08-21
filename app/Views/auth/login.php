<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Monitoring App</title>
    <link href="<?= base_url('assets/vendor/bootstrap/bootstrap-5.3.3-dist/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/auth.css') ?>">
    <link rel="shortcut icon" type="image/png" href="<?= base_url('image/icon.ico') ?>">
</head>
<body>
    <div class="main-container">
        <div class="auth-container">
            <div class="branding-side">
                <div class="branding-overlay">
                    <h1><i class="fas fa-chart-pie me-2"></i>Monitoring App</h1>
                    <p>Manajemen Stok Supplier Anda dengan Mudah dan Efisien.</p>
                </div>
            </div>
            <div class="form-side">
                <div class="login-card">
                    <h2>Welcome Back!</h2>
                    <p class="form-text text-muted">Please enter your details to sign in.</p>

                <?php if(session()->getFlashdata('msg')):?>
                    <div class="alert alert-danger"><?= session()->getFlashdata('msg') ?></div>
                <?php endif;?>

                <form action="/login" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="username" class="form-control" id="username" placeholder="Enter your username" value="<?= set_value('username') ?>" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                             <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" id="password" placeholder="Enter your password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Sign In</button>
                </form>
                <div class="footer-links">
                    <a href="/register/admin">Register as Admin</a> | 
                    <a href="/register/user">Register as User</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>