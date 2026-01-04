<?php
require_once __DIR__ . '/../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

// Kalau sudah login, arahkan sesuai role
if (is_logged_in()) {
  if (current_user_role() === 'admin') {
    redirect('/admin/dashboard.php');
  }
  redirect('/user/index.php');
}

$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Sistem Donasi</title>

  <link rel="stylesheet" href="<?= e(url('/assets/css/auth.css')) ?>">
</head>
<body>
  <main class="shell shell--single">
    <section class="card">
      <h2>Login</h2>
      <p class="sub">Silakan masuk untuk melanjutkan.</p>

      <?php if ($error): ?>
        <div class="error"><?= e($error) ?></div>
      <?php endif; ?>

      <form action="<?= e(url('/auth/login_process.php')) ?>" method="post" autocomplete="off">
        <label>Email</label>
        <div class="field">
          <input type="email" name="email" placeholder="nama@email.com" required>
        </div>

        <label>Password</label>
        <div class="field">
          <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <div class="row">
          <span class="small-note">Pastikan data benar.</span>
          <!-- <a class="small-link" href="#" onclick="return false;">Lupa password?</a> -->
        </div>

        <button type="submit">Masuk</button>
      </form>

      <div class="foot">
        Belum punya akun? <a href="<?= e(url('/auth/register.php')) ?>">Register</a>
      </div>
    </section>
  </main>
</body>
</html>
