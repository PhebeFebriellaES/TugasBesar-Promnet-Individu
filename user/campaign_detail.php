<?php
require_once __DIR__ . '/../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_login();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  $_SESSION['flash_error'] = 'Campaign tidak valid.';
  redirect('/user/index.php');
}

$stmt = db()->prepare("
  SELECT c.*,
         cat.name AS category_name
  FROM campaigns c
  JOIN categories cat ON cat.id = c.category_id
  WHERE c.id = ?
  LIMIT 1
");
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c) {
  $_SESSION['flash_error'] = 'Campaign tidak ditemukan.';
  redirect('/user/index.php');
}

$target = (int)$c['target_amount'];
$collected = (int)$c['collected_amount'];
$percent = 0;
if ($target > 0) {
  $percent = (int) floor(($collected / $target) * 100);
  if ($percent > 100) $percent = 100;
  if ($percent < 0) $percent = 0;
}

// Ambil 10 donasi terverifikasi terakhir
$stmt2 = db()->prepare("
  SELECT d.amount, d.verified_at, u.name AS donor_name
  FROM donations d
  JOIN users u ON u.id = d.user_id
  WHERE d.campaign_id = ? AND d.status = 'verified'
  ORDER BY d.verified_at DESC
  LIMIT 10
");
$stmt2->execute([$id]);
$recent = $stmt2->fetchAll();

$error = $_SESSION['flash_error'] ?? '';
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$status = strtolower((string)$c['status']);
$badgeClass = 'badge ' . preg_replace('/[^a-z0-9_-]/', '', $status);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($c['title']) ?> - Detail Campaign</title>

  <!-- CSS dipisah (URL tepat untuk project kamu) -->
  <link rel="stylesheet" href="<?= e(url('/assets/css/user.css?v=1')) ?>">
</head>
<body>
  <header class="topbar">
    <div class="topbar-inner">
      <div class="brand">
        <b>Sistem Donasi</b>
        <span>Detail Campaign</span>
      </div>

      <div class="actions">
        <div class="user-badge">Halo, <?= e($_SESSION['user_name'] ?? 'User') ?></div>
        <a class="toplink" href="<?= e(url('/auth/logout.php')) ?>">Logout</a>
      </div>
    </div>
  </header>

  <main class="container">

    <div class="detail-actions">
      <a class="btn" href="<?= e(url('/user/index.php')) ?>">← Kembali</a>
      <a class="btn" href="<?= e(url('/user/history.php')) ?>">Riwayat Donasi</a>
    </div>

    <?php if ($success): ?>
      <div class="msg-ok"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert-err"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="detail-grid">
      <!-- Kiri: konten utama -->
      <section class="card">
        <h1 class="summary-title"><?= e($c['title']) ?></h1>

        <div class="kv">
          Kategori: <b><?= e($c['category_name']) ?></b>
          <span class="<?= e($badgeClass) ?>"><?= e($c['status']) ?></span>
        </div>

        <?php if (!empty($c['image_path'])): ?>
          <img class="camp-image" src="<?= e(url('/' . $c['image_path'])) ?>" alt="Gambar Campaign">
        <?php endif; ?>

        <div class="desc">
          <?= nl2br(e($c['description'])) ?>
        </div>
      </section>

      <!-- Kanan: ringkasan -->
      <aside class="card">
        <h2 class="form-title">Ringkasan</h2>

        <div class="kpi-grid">
          <div class="kpi">
            <div class="k">Target</div>
            <div class="v">Rp <?= number_format($target, 0, ',', '.') ?></div>
          </div>
          <div class="kpi">
            <div class="k">Terkumpul</div>
            <div class="v">Rp <?= number_format($collected, 0, ',', '.') ?></div>
          </div>
        </div>

        <div class="progress" style="margin-top:12px;">
          <div class="progress-top">
            <span>Progress</span>
            <span><?= (int)$percent ?>%</span>
          </div>
          <div class="bar">
            <div class="fill" style="width: <?= (int)$percent ?>%;"></div>
          </div>
        </div>

        <div class="kv" style="margin-top:10px;">
          Deadline: <b><?= !empty($c['end_date']) ? e($c['end_date']) : '-' ?></b>
        </div>

        <div class="row" style="margin-top:14px;">
          <?php if ($c['status'] === 'active'): ?>
            <a class="btn primary" href="<?= e(url('/user/donate.php?campaign_id=' . (int)$c['id'])) ?>">
              💙 Donasi Sekarang
            </a>
          <?php else: ?>
            <span class="badge">Campaign tidak aktif</span>
          <?php endif; ?>
        </div>
      </aside>
    </div>

    <section class="card" style="margin-top:14px;">
      <h2 class="form-title">Donasi Terverifikasi Terbaru</h2>
      <p class="form-sub">Menampilkan 10 donasi terakhir yang sudah diverifikasi admin.</p>

      <?php if (!$recent): ?>
        <div class="kv">Belum ada donasi terverifikasi.</div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Donatur</th>
                <th>Nominal</th>
                <th>Waktu Verifikasi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent as $r): ?>
                <tr>
                  <td><?= e($r['donor_name']) ?></td>
                  <td>Rp <?= number_format((int)$r['amount'], 0, ',', '.') ?></td>
                  <td><?= e((string)$r['verified_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

  </main>
</body>
</html>
