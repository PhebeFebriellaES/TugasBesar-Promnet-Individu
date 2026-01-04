<?php
require_once __DIR__ . '/../../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('/admin/campaigns/index.php');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  $_SESSION['flash_error'] = 'Campaign tidak valid.';
  redirect('/admin/campaigns/index.php');
}

try {
  // ambil image_path dulu biar bisa hapus file
  $stmt = db()->prepare("SELECT image_path FROM campaigns WHERE id = ? LIMIT 1");
  $stmt->execute([$id]);
  $row = $stmt->fetch();

  $del = db()->prepare("DELETE FROM campaigns WHERE id = ?");
  $del->execute([$id]);

  if ($row && !empty($row['image_path'])) {
    $full = APP_ROOT . '/' . ltrim($row['image_path'], '/');
    if (is_file($full)) {
      @unlink($full);
    }
  }

  $_SESSION['flash_success'] = 'Campaign berhasil dihapus.';
  redirect('/admin/campaigns/index.php');

} catch (PDOException $e) {
  // biasanya gagal karena ada donations (FK restrict)
  $_SESSION['flash_error'] = 'Tidak bisa hapus: campaign sudah punya donasi.';
  redirect('/admin/campaigns/index.php');
}
