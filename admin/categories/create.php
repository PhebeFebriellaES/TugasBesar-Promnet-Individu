<?php
require_once __DIR__ . '/../../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_admin();

$error = $_SESSION['flash_error'] ?? '';
$oldName = $_SESSION['flash_old_name'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_old_name']);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tambah Kategori</title>

  <!-- Bootstrap 5 (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tema custom (dipisah) -->
  <link rel="stylesheet" href="<?= e(url('/assets/css/admin_bootstrap.css')) ?>">
</head>
<body>

  <!-- Topbar -->
  <nav class="navbar navbar-expand-lg navbar-dark topbar-gradient sticky-top">
    <div class="container">
      <a class="navbar-brand fw-semibold" href="<?= e(url('/admin/dashboard.php')) ?>">Admin - Kategori</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="topNav">
        <div class="navbar-nav ms-auto align-items-lg-center gap-2">
          <span class="navbar-text text-white-50"><?= e($_SESSION['user_name'] ?? 'Admin') ?></span>
          <a class="btn btn-sm btn-outline-light" href="<?= e(url('/auth/logout.php')) ?>">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <div class="glass-card p-3 p-md-4" style="max-width: 760px; margin: 0 auto;">
      <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <div>
          <h1 class="h4 mb-1">Tambah Kategori</h1>
          <div class="text-muted small">Buat kategori baru untuk campaign.</div>
        </div>
        <a class="btn btn-soft" href="<?= e(url('/admin/categories/index.php')) ?>">← Kembali</a>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
          <?= e($error) ?>
        </div>
      <?php endif; ?>

      <form action="<?= e(url('/admin/categories/store.php')) ?>" method="post" class="mt-3">
        <div class="mb-3">
          <label class="form-label">Nama Kategori</label>
          <input
            type="text"
            name="name"
            class="form-control"
            required
            maxlength="80"
            placeholder="Contoh: Kemanusiaan"
            value="<?= e($oldName) ?>"
          >
          <div class="form-text">Maksimal 80 karakter.</div>
        </div>

        <div class="d-flex flex-wrap gap-2">
          <button class="btn btn-primary" type="submit">Simpan</button>
          <a class="btn btn-outline-secondary" href="<?= e(url('/admin/categories/index.php')) ?>">Batal</a>
        </div>
      </form>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
