<?php
require_once __DIR__ . '/../../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  $_SESSION['flash_error'] = 'Donasi tidak valid.';
  redirect('/admin/donations/pending.php');
}

$stmt = db()->prepare("
  SELECT d.*,
         u.name AS user_name, u.email AS user_email,
         c.title AS campaign_title
  FROM donations d
  JOIN users u ON u.id = d.user_id
  JOIN campaigns c ON c.id = d.campaign_id
  WHERE d.id = ?
  LIMIT 1
");
$stmt->execute([$id]);
$don = $stmt->fetch();

if (!$don) {
  $_SESSION['flash_error'] = 'Donasi tidak ditemukan.';
  redirect('/admin/donations/pending.php');
}

$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

$status = strtolower((string)$don['status']);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detail Donasi</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- CSS admin (dipisah) -->
  <link rel="stylesheet" href="<?= e(url('/assets/css/admin_bootstrap.css')) ?>">
</head>
<body>

  <!-- Topbar -->
  <nav class="navbar navbar-expand-lg navbar-dark topbar-gradient sticky-top">
    <div class="container">
      <a class="navbar-brand fw-semibold" href="<?= e(url('/admin/dashboard.php')) ?>">
        Admin - Detail Donasi
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

      <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <div>
          <h1 class="h4 mb-1">Detail Donasi</h1>
          <div class="text-muted small">Invoice dan bukti pembayaran.</div>
        </div>
        <a class="btn btn-soft" href="<?= e(url('/admin/donations/pending.php')) ?>">← Kembali</a>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <!-- Info utama + bukti -->
      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="p-3 rounded-4 border bg-white bg-opacity-75">
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
              <div class="fw-semibold">Invoice: <?= e($don['invoice_number']) ?></div>
              <span class="badge rounded-pill <?= $status === 'pending' ? 'text-bg-warning' : ($status === 'verified' ? 'text-bg-success' : 'text-bg-secondary') ?>">
                <?= e($don['status']) ?>
              </span>
            </div>

            <div class="text-muted small mt-2">
              User: <b><?= e($don['user_name']) ?></b> (<?= e($don['user_email']) ?>)
            </div>
            <div class="text-muted small">
              Campaign: <b><?= e($don['campaign_title']) ?></b>
            </div>

            <hr class="my-3">

            <div class="d-flex flex-wrap gap-3">
              <div>
                <div class="text-muted small">Nominal</div>
                <div class="fw-semibold">Rp <?= number_format((int)$don['amount'], 0, ',', '.') ?></div>
              </div>

              <div>
                <div class="text-muted small">Metode</div>
                <div class="fw-semibold"><?= e($don['payment_method']) ?></div>
              </div>
            </div>

            <?php if ($don['status'] !== 'pending'): ?>
              <hr class="my-3">
              <div class="text-muted small">
                Diproses oleh admin ID: <?= e((string)($don['verified_by'] ?? '-')) ?><br>
                Waktu: <?= e((string)($don['verified_at'] ?? '-')) ?><br>
                Catatan: <?= e((string)($don['admin_note'] ?? '-')) ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-12 col-lg-6">
          <div class="p-3 rounded-4 border bg-white bg-opacity-75">
            <div class="fw-semibold mb-2">Bukti Pembayaran</div>

            <?php if (!empty($don['proof_path'])): ?>
              <div class="d-flex flex-wrap gap-2 mb-3">
                <a class="btn btn-sm btn-outline-primary"
                   href="<?= e(url('/' . $don['proof_path'])) ?>" target="_blank" rel="noopener">
                  Buka Bukti
                </a>
              </div>

              <img class="proof-img" src="<?= e(url('/' . $don['proof_path'])) ?>" alt="Bukti">
            <?php else: ?>
              <div class="text-muted small">Belum ada bukti.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Aksi Verify/Reject -->
      <?php if ($don['status'] === 'pending'): ?>
        <hr class="my-4">

        <div class="row g-3">
          <div class="col-12 col-lg-8">
            <label class="form-label fw-semibold">Catatan Admin (opsional)</label>
            <textarea id="adminNote" class="form-control" rows="3" placeholder="Contoh: Bukti valid, terima kasih."></textarea>
            <div class="form-text">Catatan ini akan tersimpan saat Verify/Reject.</div>
          </div>

          <div class="col-12 col-lg-4">
            <div class="d-flex flex-wrap gap-2 align-items-end h-100">
              <form id="formVerify" action="<?= e(url('/admin/donations/verify.php')) ?>" method="post"
                    onsubmit="return confirm('Verifikasi donasi ini?');" class="w-100 w-lg-auto">
                <input type="hidden" name="id" value="<?= (int)$don['id'] ?>">
                <input type="hidden" name="admin_note" id="noteVerify" value="">
                <button class="btn btn-success w-100" type="submit">✅ Verify</button>
              </form>

              <form id="formReject" action="<?= e(url('/admin/donations/reject.php')) ?>" method="post"
                    onsubmit="return confirm('Reject donasi ini?');" class="w-100 w-lg-auto">
                <input type="hidden" name="id" value="<?= (int)$don['id'] ?>">
                <input type="hidden" name="admin_note" id="noteReject" value="">
                <button class="btn btn-outline-danger w-100" type="submit">⛔ Reject</button>
              </form>
            </div>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // 1 textarea dipakai untuk kedua form
    const note = document.getElementById('adminNote');
    const noteVerify = document.getElementById('noteVerify');
    const noteReject = document.getElementById('noteReject');

    document.getElementById('formVerify').addEventListener('submit', () => {
      noteVerify.value = note.value || '';
    });

    document.getElementById('formReject').addEventListener('submit', () => {
      noteReject.value = note.value || '';
    });
  </script>
</body>
</html>
