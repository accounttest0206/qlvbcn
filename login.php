<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$error = '';

if (Auth::check()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!';
    } else {
        $result = $auth->login($username, $password);
        if ($result['success']) {
            header('Location: index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="assets/js/theme.js" defer></script>
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background-color: var(--bs-tertiary-bg); }
        .login-card { width: 100%; max-width: 420px; border-radius: 12px; }
    </style>
</head>
<body>
    <div class="container p-3">
        <div class="card login-card shadow-lg mx-auto border-0">
            <div class="card-body p-4 p-sm-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-file-earmark-text fs-2"></i>
                    </div>
                    <h4 class="fw-bold"><?= APP_NAME ?></h4>
                    <p class="text-muted small">Đăng nhập vào hệ thống để quản lý văn bản</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label for="username" class="form-label font-weight-bold">Tên đăng nhập</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="username" name="username" value="admin" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label font-weight-bold">Mật khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="password" name="password" value="Admin@123" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Đăng Nhập
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top">
                    <p class="small text-muted mb-1"><strong>Tài khoản Demo thử nghiệm:</strong></p>
                    <div class="d-flex justify-content-between small text-secondary bg-body-tertiary p-2 rounded">
                        <span>Admin: <code>admin</code> / <code>Admin@123</code></span>
                        <span>User: <code>nguyenvana</code> / <code>Admin@123</code></span>
                    </div>
                </div>

                <div class="text-center mt-4 d-flex justify-content-between align-items-center">
                    <button id="themeToggleBtn" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-moon-stars-fill"></i> Tối
                    </button>
                    <span class="small text-muted"><?= COPYRIGHT ?></span>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
