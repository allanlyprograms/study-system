<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/auth.php';
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';
    if (login($email, $pass)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Неверный email или пароль';
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login - Testing System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow">
        <div class="card-body">
          <h3 class="card-title mb-4">Вход в систему</h3>
          <?php if ($error): ?>
            <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
          <?php endif; ?>
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Пароль</label>
              <input name="password" type="password" class="form-control" required>
            </div>
            <button class="btn btn-primary">Войти</button>
          </form>
          <hr>
          <p class="small text-muted">Seeded admin: <strong>admin@example.com</strong> / <strong>admin123</strong></p>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
