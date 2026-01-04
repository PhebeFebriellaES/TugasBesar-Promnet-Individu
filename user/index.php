<?php
require_once __DIR__ . '/../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_login();

// ====== FILTER KATEGORI ======
$category_id = (int)($_GET['category_id'] ?? 0);

// Ambil list kategori untuk dropdown
$categories = db()->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

// Ambil daftar campaign (dengan filter kategori jika dipilih)
$sql = "
  SELECT c.id, c.title, c.slug, c.target_amount, c.collected_amount, c.status, c.end_date,
         cat.name AS category_name
  FROM campaigns c
  JOIN categories cat ON cat.id = c.category_id
";
$params = [];

if ($category_id > 0) {
  $sql .= " WHERE c.category_id = ? ";
  $params[] = $category_id;
}

$sql .= " ORDER BY c.created_at DESC LIMIT 20";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$campaigns = $stmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Home - Sistem Donasi</title>

  <!-- CSS khusus index user (tema + grid + filter) -->
  <link rel="stylesheet" href="<?= e(url('/assets/css/user_index.css?v=1')) ?>">
</head>
<body>
  <header class="topbar">
    <div class="topbar-inner">
      <div class="brand">
        <b>Sistem Donasi</b>
        <span>Home</span>
      </div>

      <div class="actions">
        <div class="user-badge">Halo, <?= e($_SESSION['user_name'] ?? 'User') ?></div>

        <?php if (current_user_role() === 'admin'): ?>
          <a class="toplink" href="<?= e(url('/admin/dashboard.php')) ?>">Admin</a>
        <?php endif; ?>

        <a class="toplink" href="<?= e(url('/auth/logout.php')) ?>">Logout</a>
      </div>
    </div>
  </header>

  <main class="container">
    <div class="page-head">
      <div>
        <h1>Daftar Campaign</h1>
        <p>Pilih campaign yang ingin kamu bantu, lalu klik Donasi.</p>
      </div>
    </div>

    <!-- Tombol utama -->
    <div class="row">
      <a class="btn primary" href="<?= e(url('/user/history.php')) ?>">🧾 Riwayat Donasi</a>
    </div>

    <!-- FILTER BAR (lebih rapi, responsive, tanpa inline style) -->
    <div class="filter-bar">
      <div class="filter-left">
        <form method="get" action="">
          <label class="filter-label" for="category_id">
            <span class="dot"></span>
            Filter Kategori
          </label>

          <select class="filter-select" id="category_id" name="category_id" onchange="this.form.submit()">
            <option value="0" <?= ($category_id === 0) ? 'selected' : '' ?>>Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= (int)$cat['id'] ?>" <?= ($category_id === (int)$cat['id']) ? 'selected' : '' ?>>
                <?= e($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>

          <!-- kalau JS mati -->
          <noscript>
            <button class="btn btn-mini" type="submit">Terapkan</button>
          </noscript>
        </form>
      </div>

      <div class="filter-hint">
        Otomatis ter-filter saat kategori dipilih.
      </div>
    </div>

    <?php if (!$campaigns): ?>
      <div class="card" style="margin-top:14px;">
        Tidak ada campaign untuk filter yang dipilih.
      </div>
    <?php else: ?>
      <section class="grid">
        <?php foreach ($campaigns as $c): ?>
          <?php
            $target = (int)$c['target_amount'];
            $collected = (int)$c['collected_amount'];
            $pct = 0;
            if ($target > 0) {
              $pct = (int) floor(($collected / $target) * 100);
              if ($pct < 0) $pct = 0;
              if ($pct > 100) $pct = 100;
            }

            $status = strtolower((string)$c['status']);
            $badgeClass = '';
            if ($status === 'active') $badgeClass = 'active';
            if ($status === 'inactive') $badgeClass = 'inactive';
            if ($status === 'completed') $badgeClass = 'completed';
          ?>
          <article class="card">
            <h3 class="title"><?= e($c['title']) ?></h3>

            <div class="meta">
              Kategori: <?= e($c['category_name']) ?>
              <span class="badge <?= e($badgeClass) ?>"><?= e($c['status']) ?></span>
            </div>

            <div class="meta">
              Terkumpul: Rp <?= number_format($collected, 0, ',', '.') ?>
              dari Rp <?= number_format($target, 0, ',', '.') ?>
              <?php if (!empty($c['end_date'])): ?>
                | Deadline: <?= e($c['end_date']) ?>
              <?php endif; ?>
            </div>

            <div class="progress">
              <div class="progress-top">
                <span>Progress</span>
                <span><?= $pct ?>%</span>
              </div>
              <div class="bar">
                <div class="fill" style="width: <?= $pct ?>%;"></div>
              </div>
            </div>

            <div class="row" style="margin-top:12px;">
              <a class="btn" href="<?= e(url('/user/campaign_detail.php?id=' . (int)$c['id'])) ?>">📄 Detail</a>

              <?php if ($status === 'active'): ?>
                <a class="btn" href="<?= e(url('/user/donate.php?campaign_id=' . (int)$c['id'])) ?>">💸 Donasi</a>
              <?php else: ?>
                <span class="btn" style="opacity:.6; pointer-events:none;">💸 Donasi</span>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>

    <p class="note">
      Tips: Gunakan filter kategori agar cepat menemukan campaign yang kamu cari.
    </p>
  </main>
</body>
</html>
