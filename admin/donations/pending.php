<?php
require_once __DIR__ . '/../../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_admin();

$success = $_SESSION['flash_success'] ?? '';
$error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$stmt = db()->query("
  SELECT d.id, d.invoice_number, d.amount, d.created_at, d.proof_path,
         u.name AS user_name, u.email AS user_email,
         c.title AS campaign_title
  FROM donations d
  JOIN users u ON u.id = d.user_id
  JOIN campaigns c ON c.id = d.campaign_id
  WHERE d.status = 'pending'
  ORDER BY d.created_at DESC
");
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Donasi Pending</title>

  <!-- Bootstrap 5 (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- CSS admin (dipisah) -->
  <link rel="stylesheet" href="<?= e(url('/assets/css/admin_bootstrap.css')) ?>">
</head>
<body>

  <!-- Topbar -->
  <nav class="navbar navbar-expand-lg navbar-dark topbar-gradient sticky-top">
    <div class="container">
      <a class="navbar-brand fw-semibold" href="<?= e(url('/admin/dashboard.php')) ?>">
        Admin - Donasi Pending
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
          <h1 class="h4 mb-1">Donasi Pending</h1>
          <div class="text-muted small">Daftar donasi yang menunggu verifikasi admin.</div>
        </div>

        <a class="btn btn-soft" href="<?= e(url('/admin/dashboard.php')) ?>">← Dashboard</a>
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success" role="alert"><?= e($success) ?></div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <div class="table-responsive table-wrap">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Invoice</th>
              <th>User</th>
              <th>Campaign</th>
              <th style="width:160px;">Nominal</th>
              <th style="width:120px;">Bukti</th>
              <th style="width:120px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$rows): ?>
              <tr>
                <td colspan="6" class="text-muted">Tidak ada donasi pending.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($rows as $r): ?>
                <?php
                  $proofBadge = $r['proof_path'] ? 'badge-soft ok' : 'badge-soft warn';
                  $proofText  = $r['proof_path'] ? 'Ada' : 'Belum';
                ?>
                <tr>
                  <td>
                    <div class="fw-semibold"><?= e($r['invoice_number']) ?></div>
                    <div class="text-muted small"><?= e($r['created_at']) ?></div>
                  </td>

                  <td>
                    <div class="fw-semibold"><?= e($r['user_name']) ?></div>
                    <div class="text-muted small"><?= e($r['user_email']) ?></div>
                  </td>

                  <td><?= e($r['campaign_title']) ?></td>

                  <td>Rp <?= number_format((int)$r['amount'], 0, ',', '.') ?></td>

                  <td>
                    <span class="badge <?= e($proofBadge) ?> rounded-pill"><?= e($proofText) ?></span>
                  </td>

                  <td>
                    <a class="btn btn-sm btn-outline-primary"
                       href="<?= e(url('/admin/donations/show.php?id=' . (int)$r['id'])) ?>">
                      Detail
                    </a>
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
