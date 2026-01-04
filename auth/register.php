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
$old = $_SESSION['flash_old'] ?? [];
unset($_SESSION['flash_error'], $_SESSION['flash_old']);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register - Sistem Donasi</title>

  <link rel="stylesheet" href="<?= e(url('/assets/css/auth.css')) ?>">
</head>
<body>
  <main class="shell shell--single">
    <section class="card">
      <h2>Register</h2>
      <p class="sub">Isi data di bawah untuk membuat akun.</p>

      <?php if ($error): ?>
        <div class="error"><?= e($error) ?></div>
      <?php endif; ?>

      <form action="<?= e(url('/auth/register_process.php')) ?>" method="post" autocomplete="off">
        <label>Nama</label>
        <div class="field">
          <input type="text" name="name" required value="<?= e($old['name'] ?? '') ?>" placeholder="Nama lengkap">
        </div>

        <label>Email</label>
        <div class="field">
          <input type="email" name="email" required value="<?= e($old['email'] ?? '') ?>" placeholder="nama@email.com">
        </div>

        <label>No. HP (opsional)</label>
        <div class="field">
          <input type="text" name="phone" value="<?= e($old['phone'] ?? '') ?>" placeholder="08xxxxxxxxxx">
        </div>
        <div class="help">Boleh dikosongkan.</div>

        <label>Password</label>
        <div class="field">
          <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter">
        </div>

        <label>Konfirmasi Password</label>
        <div class="field">
          <input type="password" name="password_confirm" required minlength="6" placeholder="Ulangi password">
        </div>

        <button type="submit">Daftar</button>
      </form>

      <div class="foot">
        Sudah punya akun? <a href="<?= e(url('/auth/login.php')) ?>">Login</a>
      </div>
    </section>
  </main>
</body>
</html>
