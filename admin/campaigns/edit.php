<?php
require_once __DIR__ . '/../../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  $_SESSION['flash_error'] = 'Campaign tidak valid.';
  redirect('/admin/campaigns/index.php');
}

$stmt = db()->prepare("SELECT * FROM campaigns WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$campaign = $stmt->fetch();

if (!$campaign) {
  $_SESSION['flash_error'] = 'Campaign tidak ditemukan.';
  redirect('/admin/campaigns/index.php');
}

$cats = db()->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Campaign</title>

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
          <h1 class="h4 mb-1">Edit Campaign</h1>
          <div class="text-muted small">Update data campaign yang dipilih.</div>
        </div>
        <a class="btn btn-soft" href="<?= e(url('/admin/campaigns/index.php')) ?>">← Kembali</a>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
          <?= e($error) ?>
        </div>
      <?php endif; ?>

      <form action="<?= e(url('/admin/campaigns/update.php')) ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= (int)$campaign['id'] ?>">

        <div class="row g-3">

          <div class="col-12 col-md-6">
            <label class="form-label">Kategori</label>
            <select name="category_id" class="form-select" required>
              <option value="">-- Pilih --</option>
              <?php foreach ($cats as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>"
                  <?= ((int)$campaign['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                  <?= e($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
              <?php foreach (['active','inactive','completed'] as $st): ?>
                <option value="<?= e($st) ?>" <?= ($campaign['status'] === $st) ? 'selected' : '' ?>>
                  <?= e($st) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Status untuk menandai campaign masih berjalan / berhenti / selesai.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Judul</label>
            <input
              type="text"
              name="title"
              class="form-control"
              required
              maxlength="180"
              value="<?= e($campaign['title']) ?>"
            >
            <div class="form-text">Slug otomatis akan ikut menyesuaikan jika judul berubah.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control" rows="6" required><?= e($campaign['description']) ?></textarea>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Target (angka)</label>
            <input
              type="number"
              name="target_amount"
              class="form-control"
              required
              min="1"
              value="<?= (int)$campaign['target_amount'] ?>"
            >
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Gambar Campaign (opsional)</label>
            <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            <div class="form-text">Jika upload baru, gambar lama akan diganti.</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Tanggal Mulai (opsional)</label>
            <input type="date" name="start_date" class="form-control" value="<?= e($campaign['start_date'] ?? '') ?>">
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Tanggal Selesai / Deadline (opsional)</label>
            <input type="date" name="end_date" class="form-control" value="<?= e($campaign['end_date'] ?? '') ?>">
          </div>

          <?php if (!empty($campaign['image_path'])): ?>
            <div class="col-12">
              <div class="mt-2">
                <div class="text-muted small mb-2">Gambar saat ini:</div>
                <img
                  src="<?= e(url('/' . $campaign['image_path'])) ?>"
                  alt="Campaign"
                  class="img-fluid rounded border"
                  style="max-width: 320px;"
                >
              </div>
            </div>
          <?php endif; ?>

          <div class="col-12 d-flex flex-wrap gap-2 mt-2">
            <button class="btn btn-primary" type="submit">Update</button>
            <a class="btn btn-outline-secondary" href="<?= e(url('/admin/campaigns/index.php')) ?>">Batal</a>
          </div>

        </div>
      </form>

      <div class="mt-4 text-muted">
        Terkumpul saat ini:
        <span class="fw-semibold">
          Rp <?= number_format((int)$campaign['collected_amount'], 0, ',', '.') ?>
        </span>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
