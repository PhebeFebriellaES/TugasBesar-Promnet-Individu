<?php
require_once __DIR__ . '/../../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('/admin/categories/index.php');
}

$id = (int)($_POST['id'] ?? 0);
$name = trim((string)($_POST['name'] ?? ''));

if ($id <= 0) {
  $_SESSION['flash_error'] = 'Kategori tidak valid.';
  redirect('/admin/categories/index.php');
}

if ($name === '') {
  $_SESSION['flash_error'] = 'Nama kategori wajib diisi.';
  redirect('/admin/categories/edit.php?id=' . $id);
}

if (mb_strlen($name) > 80) {
  $_SESSION['flash_error'] = 'Nama kategori maksimal 80 karakter.';
  redirect('/admin/categories/edit.php?id=' . $id);
}

try {
  $stmt = db()->prepare("UPDATE categories SET name = ? WHERE id = ?");
  $stmt->execute([$name, $id]);

  $_SESSION['flash_success'] = 'Kategori berhasil diupdate.';
  redirect('/admin/categories/index.php');
} catch (PDOException $e) {
  if ($e->getCode() === '23000') {
    $_SESSION['flash_error'] = 'Nama kategori sudah ada.';
    redirect('/admin/categories/edit.php?id=' . $id);
  }
  $_SESSION['flash_error'] = 'Gagal update kategori.';
  redirect('/admin/categories/edit.php?id=' . $id);
}
