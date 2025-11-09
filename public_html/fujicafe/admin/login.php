<?php
require_once dirname(__DIR__) . '/includes/boot.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

if (is_admin_logged_in()) {
    redirect('/fujicafe/admin/');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $admin_username = getenv('ADMIN_USERNAME') ?: 'admin';
        $admin_password_hash = getenv('ADMIN_PASSWORD_HASH');
        
        if (!$admin_password_hash) {
            $admin_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        }
        
        if ($username === $admin_username && password_verify($password, $admin_password_hash)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            redirect('/fujicafe/admin/');
        } else {
            $error = 'Invalid username or password';
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Fuji Cafe</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/app.css">
</head>
<body class="ui">
    <div class="container narrow" style="margin-top: 80px;">
        <div class="card" style="max-width: 420px; margin: 0 auto;">
            <h1 style="text-align: center; margin-bottom: 8px;">Admin Login</h1>
            <p class="muted" style="text-align: center; margin-bottom: 24px;">Fuji Cafe Menu Management</p>

            <?php if ($error): ?>
                <div class="error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div style="margin-bottom: 18px;">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" required autofocus>
                </div>

                <div style="margin-bottom: 24px;">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" style="width: 100%; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500;">
                    Sign In
                </button>
            </form>

            <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border); font-size: 13px; color: var(--muted); text-align: center;">
                <p>Default credentials: admin / admin123</p>
                <p style="margin-top: 8px;">Set ADMIN_USERNAME and ADMIN_PASSWORD_HASH environment variables to customize.</p>
            </div>
        </div>
    </div>
</body>
</html>
