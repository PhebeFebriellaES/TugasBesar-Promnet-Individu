<?php
require_once __DIR__ . '/../../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_admin();

$error = $_SESSION['flash_error'] ?? '';
$old = $_SESSION['flash_old'] ?? [];
unset($_SESSION['flash_error'], $_SESSION['flash_old']);

$cats = db()->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tambah Campaign</title>

  <!-- Bootstrap 5 (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Tema custom (dipisah) -->
  <link rel="stylesheet" href="<?= e(url('/assets/css/admin_bootstrap.css')) ?>">
</head>
<body>

  <!-- Topbar -->
  <nav class="navbar navbar-expand-lg navbar-dark topbar-gradient sticky-top">
    <div class="container">
      <a class="navbar-brand fw-semibold" href="<?= e(url('/admin/dashboard.php')) ?>">Admin - Campaign</a>

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
    <div class="glass-card p-3 p-md-4" style="max-width: 980px; margin: 0 auto;">

      <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <div>
          <h1 class="h4 mb-1">Tambah Campaign</h1>
          <div class="text-muted small">Buat campaign donasi baru.</div>
        </div>
        <a class="btn btn-soft" href="<?= e(url('/admin/campaigns/index.php')) ?>">← Kembali</a>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
          <?= e($error) ?>
        </div>
      <?php endif; ?>

      <?php if (!$cats): ?>
        <div class="alert alert-warning" role="alert">
          Kategori belum ada. Buat kategori dulu di menu <b>Kelola Kategori</b>.
        </div>
      <?php endif; ?>

      <form action="<?= e(url('/admin/campaigns/store.php')) ?>" method="post" enctype="multipart/form-data">
        <div class="row g-3">

          <div class="col-12 col-md-6">
            <label class="form-label">Kategori</label>
            <select name="category_id" class="form-select" required <?= !$cats ? 'disabled' : '' ?>>
              <option value="">-- Pilih --</option>
              <?php foreach ($cats as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>"
                  <?= ((int)($old['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                  <?= e($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Status</label>
            <?php
              $st = $old['status'] ?? 'active';
              $opts = [
                'active' => 'active',
                'inactive' => 'inactive',
                'completed' => 'completed',
              ];
            ?>
            <select name="status" class="form-select" required>
              <?php foreach ($opts as $v => $lbl): ?>
                <option value="<?= e($v) ?>" <?= ($st === $v) ? 'selected' : '' ?>>
                  <?= e($lbl) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Judul</label>
            <input
              type="text"
              name="title"
              class="form-control"
              required
              maxlength="180"
              placeholder="Contoh: Bantuan Biaya Pengobatan"
              value="<?= e($old['title'] ?? '') ?>"
              <?= !$cats ? 'disabled' : '' ?>
            >
            <div class="form-text">Maksimal 180 karakter.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Deskripsi</label>
            <textarea
              name="description"
              class="form-control"
              rows="6"
              required
              placeholder="Tulis deskripsi campaign..."
              <?= !$cats ? 'disabled' : '' ?>
            ><?= e($old['description'] ?? '') ?></textarea>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Target (angka)</label>
            <input
              type="number"
              name="target_amount"
              class="form-control"
              required
              min="1"
              placeholder="Contoh: 5000000"
              value="<?= e($old['target_amount'] ?? '') ?>"
              <?= !$cats ? 'disabled' : '' ?>
            >
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Gambar Campaign (opsional)</label>
            <input
              type="file"
              name="image"
              class="form-control"
              accept=".jpg,.jpeg,.png,.webp"
              <?= !$cats ? 'disabled' : '' ?>
            >
            <div class="form-text">jpg/png/webp, maksimal 2MB.</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Tanggal Mulai (opsional)</label>
            <input
              type="date"
              name="start_date"
              class="form-control"
              value="<?= e($old['start_date'] ?? '') ?>"
              <?= !$cats ? 'disabled' : '' ?>
            >
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Tanggal Selesai / Deadline (opsional)</label>
            <input
              type="date"
              name="end_date"
              class="form-control"
              value="<?= e($old['end_date'] ?? '') ?>"
              <?= !$cats ? 'disabled' : '' ?>
            >
          </div>

          <div class="col-12 d-flex flex-wrap gap-2 mt-2">
            <button class="btn btn-primary" type="submit" <?= !$cats ? 'disabled' : '' ?>>
              Simpan
            </button>
            <a class="btn btn-outline-secondary" href="<?= e(url('/admin/campaigns/index.php')) ?>">
              Batal
            </a>
          </div>

        </div>
      </form>

    </div>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
