<?php
require_once __DIR__ . '/../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_login();

$success = $_SESSION['flash_success'] ?? '';
$error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$stmt = db()->prepare("
  SELECT d.id, d.invoice_number, d.amount, d.status, d.created_at, d.proof_path,
         c.title AS campaign_title
  FROM donations d
  JOIN campaigns c ON c.id = d.campaign_id
  WHERE d.user_id = ?
  ORDER BY d.created_at DESC
");
$stmt->execute([current_user_id()]);
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Riwayat Donasi</title>

  <!-- CSS dipisah (URL tepat untuk project kamu) -->
  <link rel="stylesheet" href="<?= e(url('/assets/css/user.css?v=2')) ?>">
</head>
<body>
  <header class="topbar">
    <div class="topbar-inner">
      <div class="brand">
        <b>Sistem Donasi</b>
        <span>Riwayat Donasi</span>
      </div>

      <div class="actions">
        <div class="user-badge">Halo, <?= e($_SESSION['user_name'] ?? 'User') ?></div>
        <a class="toplink" href="<?= e(url('/auth/logout.php')) ?>">Logout</a>
      </div>
    </div>
  </header>

  <main class="container">
    <section class="card">
      <div class="row" style="justify-content:space-between; align-items:center;">
        <a class="btn" href="<?= e(url('/user/index.php')) ?>">← Home</a>
      </div>

      <?php if ($success): ?>
        <div class="msg-ok"><?= e($success) ?></div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert-err"><?= e($error) ?></div>
      <?php endif; ?>

      <h1 class="summary-title" style="margin-top:12px;">Riwayat Donasi</h1>
      <div class="kv">Daftar donasi kamu. Upload bukti saat status masih <b>pending</b>.</div>

      <div class="table-wrap" style="margin-top:12px;">
        <table class="table">
          <thead>
            <tr>
              <th>Invoice</th>
              <th>Campaign</th>
              <th>Nominal</th>
              <th>Status</th>
              <th>Bukti</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$rows): ?>
              <tr>
                <td colspan="6" class="small-muted">Belum ada donasi.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($rows as $r): ?>
                <?php
                  $st = strtolower((string)$r['status']);
                  $badgeClass = 'badge-status ' . preg_replace('/[^a-z0-9_-]/', '', $st);
                ?>
                <tr>
                  <td>
                    <div class="title" style="font-size:14px;"><?= e($r['invoice_number']) ?></div>
                    <div class="small-muted"><?= e($r['created_at']) ?></div>
                  </td>

                  <td><?= e($r['campaign_title']) ?></td>

                  <td>
                    Rp <?= number_format((int)$r['amount'], 0, ',', '.') ?>
                  </td>

                  <td>
                    <span class="<?= e($badgeClass) ?>"><?= e($r['status']) ?></span>
                  </td>

                  <td>
                    <?= $r['proof_path'] ? 'Ada' : 'Belum' ?>
                  </td>

                  <td>
                    <a class="btn"
                       href="<?= e(url('/user/upload_proof.php?id=' . (int)$r['id'])) ?>">
                      <?= $r['proof_path'] ? 'Lihat/Update' : 'Upload' ?>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
