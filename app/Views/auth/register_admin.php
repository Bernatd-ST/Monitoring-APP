<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Monitoring App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/css/auth.css">
</head>
<body>
    <div class="main-container">
        <div class="branding-side d-none d-lg-flex">
            <div>
                <h1><i class="fas fa-user-shield me-2"></i>Create Admin Account</h1>
                <p>Register a new administrator account to manage the system.</p>
            </div>
        </div>
        <div class="form-side">
            <div class="login-card">
                <h2>Admin Registration</h2>
                <p class="form-text text-muted">Fill in the details to create an account.</p>

                <?php if(isset($validation)): ?>
                    <div class="alert alert-danger"><?= $validation->listErrors() ?></div>
                <?php endif; ?>

                <form action="/register/admin" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="username" class="form-control" id="username" placeholder="Choose a username" value="<?= set_value('username') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                             <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" id="password" placeholder="Create a password" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="passconf" class="form-label">Confirm Password</label>
                        <div class="input-group">
                             <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                            <input type="password" name="passconf" class="form-control" id="passconf" placeholder="Confirm your password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Register Admin</button>
                </form>
                <div class="footer-links">
                    <span>Already have an account? <a href="/login">Sign In</a></span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>