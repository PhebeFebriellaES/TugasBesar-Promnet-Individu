<?php
require_once __DIR__ . '/../../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_admin();

$success = $_SESSION['flash_success'] ?? '';
$error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// ambil kategori + jumlah campaign per kategori
$stmt = db()->query("
  SELECT cat.id, cat.name, cat.created_at,
         COUNT(c.id) AS campaign_count
  FROM categories cat
  LEFT JOIN campaigns c ON c.category_id = cat.id
  GROUP BY cat.id, cat.name, cat.created_at
  ORDER BY cat.id DESC
");
$categories = $stmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kelola Kategori</title>

  <!-- Bootstrap 5 (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Tema custom (dipisah) -->
  <link rel="stylesheet" href="<?= e(url('/assets/css/admin_bootstrap.css')) ?>">
</head>
<body>

  <!-- Topbar -->
  <nav class="navbar navbar-expand-lg navbar-dark topbar-gradient sticky-top">
    <div class="container">
      <a class="navbar-brand fw-semibold" href="<?= e(url('/admin/dashboard.php')) ?>">
        Admin - Kategori
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="topNav">
        <div class="navbar-nav ms-auto align-items-lg-center gap-2">
          <span class="navbar-text text-white-50">
            <?= e($_SESSION['user_name'] ?? 'Admin') ?>
          </span>
          <a class="btn btn-sm btn-outline-light" href="<?= e(url('/auth/logout.php')) ?>">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <div class="glass-card p-3 p-md-4">

      <!-- Header actions -->
      <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <a class="btn btn-soft" href="<?= e(url('/admin/dashboard.php')) ?>">← Dashboard</a>

        <a class="btn btn-primary" href="<?= e(url('/admin/categories/create.php')) ?>">
          + Tambah Kategori
        </a>
      </div>

      <!-- Flash messages -->
      <?php if ($success): ?>
        <div class="alert alert-success mb-3" role="alert">
          <?= e($success) ?>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-danger mb-3" role="alert">
          <?= e($error) ?>
        </div>
      <?php endif; ?>

      <!-- Table (responsive) -->
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:80px;">ID</th>
              <th>Nama</th>
              <th style="width:160px;">Jumlah Campaign</th>
              <th style="width:190px;">Dibuat</th>
              <th style="width:230px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$categories): ?>
              <tr>
                <td colspan="5" class="text-muted">Belum ada kategori.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($categories as $cat): ?>
                <tr>
                  <td><?= (int)$cat['id'] ?></td>
                  <td class="fw-semibold"><?= e($cat['name']) ?></td>
                  <td>
                    <span class="badge badge-soft rounded-pill">
                      <?= (int)$cat['campaign_count'] ?>
                    </span>
                  </td>
                  <td class="text-muted"><?= e($cat['created_at']) ?></td>
                  <td>
                    <div class="d-flex flex-wrap gap-2">
                      <a class="btn btn-sm btn-outline-primary"
                         href="<?= e(url('/admin/categories/edit.php?id=' . (int)$cat['id'])) ?>">
                        Edit
                      </a>

                      <form class="d-inline"
                            action="<?= e(url('/admin/categories/delete.php')) ?>"
                            method="post"
                            onsubmit="return confirm('Hapus kategori ini?');">
                        <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </main>
  <!-- Bootstrap JS (untuk navbar toggler) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
