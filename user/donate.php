<?php
require_once __DIR__ . '/../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_login();

$campaign_id = (int)($_GET['campaign_id'] ?? 0);
if ($campaign_id <= 0) {
  $_SESSION['flash_error'] = 'Campaign tidak valid.';
  redirect('/user/index.php');
}

$stmt = db()->prepare("
  SELECT c.*, cat.name AS category_name
  FROM campaigns c
  JOIN categories cat ON cat.id = c.category_id
  WHERE c.id = ?
  LIMIT 1
");
$stmt->execute([$campaign_id]);
$campaign = $stmt->fetch();

if (!$campaign) {
  $_SESSION['flash_error'] = 'Campaign tidak ditemukan.';
  redirect('/user/index.php');
}

if ($campaign['status'] !== 'active') {
  $_SESSION['flash_error'] = 'Campaign tidak aktif.';
  redirect('/user/index.php');
}

$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

$target = (int)$campaign['target_amount'];
$collected = (int)$campaign['collected_amount'];
$pct = 0;
if ($target > 0) {
  $pct = (int) floor(($collected / $target) * 100);
  if ($pct < 0) $pct = 0;
  if ($pct > 100) $pct = 100;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Donasi - <?= e($campaign['title']) ?></title>

  <link rel="stylesheet" href="<?= e(url('/assets/css/user.css?v=2')) ?>">

</head>
<body>
  <header class="topbar">
    <div class="topbar-inner">
      <div class="brand">
        <b>Sistem Donasi</b>
        <span>Donasi</span>
      </div>

      <div class="actions">
        <div class="user-badge">Halo, <?= e($_SESSION['user_name'] ?? 'User') ?></div>
        <a class="toplink" href="<?= e(url('/auth/logout.php')) ?>">Logout</a>
      </div>
    </div>
  </header>

  <main class="container">
    <div class="donate-layout">

      <!-- Kiri: Ringkasan campaign -->
      <section class="card">
        <a class="btn" href="<?= e(url('/user/index.php')) ?>">← Kembali</a>

        <?php if ($error): ?>
          <div class="alert-err"><?= e($error) ?></div>
        <?php endif; ?>

        <h1 class="summary-title" style="margin-top:12px;"><?= e($campaign['title']) ?></h1>

        <div class="summary-meta">
          <span class="pill">📁 <?= e($campaign['category_name']) ?></span>
        </div>

        <div class="progress" style="margin-top:14px;">
          <div class="progress-top">
            <span>Progress</span>
            <span><?= $pct ?>%</span>
          </div>
          <div class="bar">
            <div class="fill" style="width: <?= $pct ?>%;"></div>
          </div>
        </div>

        <div class="kpi-grid">
          <div class="kpi">
            <div class="k">Terkumpul</div>
            <div class="v">Rp <?= number_format($collected, 0, ',', '.') ?></div>
          </div>
          <div class="kpi">
            <div class="k">Target</div>
            <div class="v">Rp <?= number_format($target, 0, ',', '.') ?></div>
          </div>
        </div>

        <div class="summary-meta" style="margin-top:10px;">
          Tips: pilih nominal cepat di kanan supaya lebih praktis.
        </div>
      </section>

      <!-- Kanan: Form donasi -->
      <section class="card">
        <h2 class="form-title">Form Donasi</h2>
        <p class="form-sub">Isi nominal dan pilih metode pembayaran (simulasi).</p>

        <div class="amount-picks">
          <button class="pick" type="button" data-amt="10000">Rp10.000</button>
          <button class="pick" type="button" data-amt="25000">Rp25.000</button>
          <button class="pick" type="button" data-amt="50000">Rp50.000</button>
          <button class="pick" type="button" data-amt="100000">Rp100.000</button>
          <button class="pick" type="button" data-amt="200000">Rp200.000</button>
        </div>

        <form action="<?= e(url('/user/donate_process.php')) ?>" method="post" style="margin-top:12px;">
          <input type="hidden" name="campaign_id" value="<?= (int)$campaign['id'] ?>">

          <label class="form-label">Nominal Donasi (angka)</label>
          <input id="amount" class="form-input" type="number" name="amount" min="1000" required placeholder="Contoh: 50000">

          <label class="form-label">Metode Pembayaran (simulasi)</label>
          <select class="form-select" name="payment_method" required>
            <option value="">-- Pilih --</option>
            <option value="Transfer Bank">Transfer Bank</option>
            <option value="E-Wallet">E-Wallet</option>
          </select>

          <div class="row" style="margin-top:14px;">
            <button class="btn primary" type="submit">💸 Buat Donasi</button>
            <a class="btn" href="<?= e(url('/user/index.php')) ?>">Batal</a>
          </div>
        </form>
      </section>

    </div>
  </main>

  <script>
    // quick fill nominal
    const amount = document.getElementById('amount');
    document.querySelectorAll('[data-amt]').forEach(btn => {
      btn.addEventListener('click', () => {
        amount.value = btn.dataset.amt;
        amount.focus();
      });
    });
  </script>
</body>
</html>
