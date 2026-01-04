<?php
require_once __DIR__ . '/../../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_admin();

$success = $_SESSION['flash_success'] ?? '';
$error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$stmt = db()->query("
  SELECT c.id, c.title, c.slug, c.target_amount, c.collected_amount, c.status, c.end_date, c.created_at,
         cat.name AS category_name
  FROM campaigns c
  JOIN categories cat ON cat.id = c.category_id
  ORDER BY c.id DESC
");
$campaigns = $stmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kelola Campaign</title>

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
        Admin - Campaign
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

      <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <div>
          <h1 class="h4 mb-1">Kelola Campaign</h1>
          <div class="text-muted small">Tambah, edit, dan hapus campaign donasi.</div>
        </div>

        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-soft" href="<?= e(url('/admin/dashboard.php')) ?>">← Dashboard</a>
          <a class="btn btn-primary" href="<?= e(url('/admin/campaigns/create.php')) ?>">+ Tambah Campaign</a>
        </div>
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success" role="alert"><?= e($success) ?></div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:70px;">ID</th>
              <th>Judul</th>
              <th style="width:160px;">Kategori</th>
              <th style="width:170px;">Target</th>
              <th style="width:170px;">Terkumpul</th>
              <th style="width:140px;">Status</th>
              <th style="width:160px;">Deadline</th>
              <th style="width:240px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$campaigns): ?>
              <tr>
                <td colspan="8" class="text-muted">Belum ada campaign.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($campaigns as $c): ?>
                <?php
                  $status = strtolower((string)$c['status']);
                  // untuk class badge custom (opsional)
                  $badgeClass = 'badge-status ' . preg_replace('/[^a-z0-9_-]/', '', $status);
                ?>
                <tr>
                  <td><?= (int)$c['id'] ?></td>

                  <td>
                    <div class="fw-semibold"><?= e($c['title']) ?></div>
                    <div class="text-muted small">Slug: <?= e($c['slug']) ?></div>
                  </td>

                  <td><?= e($c['category_name']) ?></td>

                  <td>Rp <?= number_format((int)$c['target_amount'], 0, ',', '.') ?></td>
                  <td>Rp <?= number_format((int)$c['collected_amount'], 0, ',', '.') ?></td>

                  <td>
                    <span class="<?= e($badgeClass) ?>"><?= e($c['status']) ?></span>
                  </td>

                  <td><?= e($c['end_date'] ?? '-') ?></td>

                  <td>
                    <div class="d-flex flex-wrap gap-2">
                      <a class="btn btn-sm btn-outline-primary"
                         href="<?= e(url('/admin/campaigns/edit.php?id=' . (int)$c['id'])) ?>">
                        Edit
                      </a>

                      <form class="d-inline"
                            action="<?= e(url('/admin/campaigns/delete.php')) ?>"
                            method="post"
                            onsubmit="return confirm('Hapus campaign ini?');">
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
