<?php
require_once __DIR__ . '/../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_admin();

// Data ringkas untuk dashboard
$totalCampaigns = (int) db()->query("SELECT COUNT(*) AS c FROM campaigns")->fetch()['c'];
$totalDonations = (int) db()->query("SELECT COUNT(*) AS c FROM donations")->fetch()['c'];
$totalPending   = (int) db()->query("SELECT COUNT(*) AS c FROM donations WHERE status='pending'")->fetch()['c'];
$totalVerified  = (int) db()->query("SELECT COUNT(*) AS c FROM donations WHERE status='verified'")->fetch()['c'];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard - Sistem Donasi</title>

  <link rel="stylesheet" href="<?= e(url('/assets/css/admin_dashboard.css')) ?>">
</head>
<body>
  <header class="topbar">
    <div class="topbar-inner">
      <div class="brand-title">
        <b>Admin Dashboard</b>
        <span>Sistem Donasi</span>
      </div>

      <div class="topbar-right">
        <div class="user-badge">
          Halo, <?= e($_SESSION['user_name'] ?? 'Admin') ?>
        </div>
        <a class="logout" href="<?= e(url('/auth/logout.php')) ?>">Logout</a>
      </div>
    </div>
  </header>

  <main class="container">
    <div class="header">
      <div>
        <h1>Ringkasan</h1>
        <p>Pantau data campaign & donasi secara cepat.</p>
      </div>
    </div>

    <section class="cards">
      <div class="card">
        <div class="label">Total Campaign</div>
        <div class="value"><?= $totalCampaigns ?></div>
        <div class="badge">📌 Data keseluruhan</div>
      </div>

      <div class="card">
        <div class="label">Total Donasi</div>
        <div class="value"><?= $totalDonations ?></div>
        <div class="badge">💰 Semua transaksi</div>
      </div>

      <div class="card">
        <div class="label">Pending Verifikasi</div>
        <div class="value"><?= $totalPending ?></div>
        <div class="badge">⏳ Perlu dicek</div>
      </div>

      <div class="card">
        <div class="label">Terverifikasi</div>
        <div class="value"><?= $totalVerified ?></div>
        <div class="badge">✅ Sudah valid</div>
      </div>
    </section>

    <nav class="menu">
      <a class="btn" href="<?= e(url('/admin/categories/index.php')) ?>">📁 Kelola Kategori</a>
      <a class="btn" href="<?= e(url('/admin/campaigns/index.php')) ?>">🎯 Kelola Campaign</a>
      <a class="btn" href="<?= e(url('/admin/donations/pending.php')) ?>">🧾 Donasi Pending</a>
      <a class="btn" href="<?= e(url('/user/index.php')) ?>">👀 Lihat Halaman User</a>
    </nav>

    <p class="note">
      Catatan: 
    </p>
  </main>
</body>
</html>
