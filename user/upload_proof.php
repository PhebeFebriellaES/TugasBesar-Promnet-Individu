<?php
require_once __DIR__ . '/../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_login();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  $_SESSION['flash_error'] = 'Donasi tidak valid.';
  redirect('/user/history.php');
}

$stmt = db()->prepare("
  SELECT d.*, c.title AS campaign_title
  FROM donations d
  JOIN campaigns c ON c.id = d.campaign_id
  WHERE d.id = ? AND d.user_id = ?
  LIMIT 1
");
$stmt->execute([$id, current_user_id()]);
$donation = $stmt->fetch();

if (!$donation) {
  $_SESSION['flash_error'] = 'Donasi tidak ditemukan.';
  redirect('/user/history.php');
}

$error = $_SESSION['flash_error'] ?? '';
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$status = strtolower((string)$donation['status']);
$badgeClass = 'badge-status ' . preg_replace('/[^a-z0-9_-]/', '', $status);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Upload Bukti - <?= e($donation['invoice_number']) ?></title>

  <!-- URL CSS yang tepat untuk project kamu -->
  <link rel="stylesheet" href="<?= e(url('/assets/css/user.css?v=2')) ?>">
</head>
<body>
  <header class="topbar">
    <div class="topbar-inner">
      <div class="brand">
        <b>Sistem Donasi</b>
        <span>Upload Bukti</span>
      </div>

      <div class="actions">
        <div class="user-badge">Halo, <?= e($_SESSION['user_name'] ?? 'User') ?></div>
        <a class="toplink" href="<?= e(url('/auth/logout.php')) ?>">Logout</a>
      </div>
    </div>
  </header>

  <main class="container">
    <section class="card proof-wrap">
      <div class="row" style="justify-content:space-between; align-items:center;">
        <a class="btn" href="<?= e(url('/user/history.php')) ?>">← Riwayat</a>
      </div>

      <?php if ($success): ?>
        <div class="msg-ok" style="margin-top:12px;"><?= e($success) ?></div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert-err" style="margin-top:12px;"><?= e($error) ?></div>
      <?php endif; ?>

      <h1 class="summary-title" style="margin-top:12px;">
        Invoice: <?= e($donation['invoice_number']) ?>
      </h1>

      <div class="proof-grid">
        <!-- Kiri: info -->
        <div>
          <div class="kv">Campaign: <b><?= e($donation['campaign_title']) ?></b></div>
          <div class="kv">Nominal: <b>Rp <?= number_format((int)$donation['amount'], 0, ',', '.') ?></b></div>
          <div class="kv">
            Status:
            <span class="<?= e($badgeClass) ?>"><?= e($donation['status']) ?></span>
          </div>

          <?php if ($donation['status'] !== 'pending'): ?>
            <div class="msg-ok" style="margin-top:12px;">
              Donasi sudah diproses admin, bukti tidak bisa diubah.
            </div>
          <?php else: ?>
            <form action="<?= e(url('/user/upload_proof_process.php')) ?>" method="post" enctype="multipart/form-data" style="margin-top:12px;">
              <input type="hidden" name="id" value="<?= (int)$donation['id'] ?>">

              <label class="form-label">Upload Bukti (jpg/png/webp, max 2MB)</label>
              <input class="form-input" type="file" name="proof" accept=".jpg,.jpeg,.png,.webp" required>

              <div class="row" style="margin-top:14px;">
                <button class="btn primary" type="submit">⬆️ Upload</button>
                <a class="btn" href="<?= e(url('/user/history.php')) ?>">Batal</a>
              </div>
            </form>
          <?php endif; ?>
        </div>

        <!-- Kanan: preview -->
        <div>
          <?php if (!empty($donation['proof_path'])): ?>
            <div class="kv">Bukti saat ini:</div>
            <img class="preview" src="<?= e(url('/' . $donation['proof_path'])) ?>" alt="Bukti">
          <?php else: ?>
            <div class="kv">Belum ada bukti yang diupload.</div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>
</body>
</html>
