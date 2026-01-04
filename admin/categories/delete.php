<?php
require_once __DIR__ . '/../../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('/admin/categories/index.php');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  $_SESSION['flash_error'] = 'Kategori tidak valid.';
  redirect('/admin/categories/index.php');
}

try {
  $stmt = db()->prepare("DELETE FROM categories WHERE id = ?");
  $stmt->execute([$id]);

  $_SESSION['flash_success'] = 'Kategori berhasil dihapus.';
  redirect('/admin/categories/index.php');
} catch (PDOException $e) {
  // kemungkinan besar gagal karena masih dipakai campaigns (FK RESTRICT)
  $_SESSION['flash_error'] = 'Tidak bisa hapus: kategori masih digunakan oleh campaign.';
  redirect('/admin/categories/index.php');
}
