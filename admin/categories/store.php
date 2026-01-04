<?php
require_once __DIR__ . '/../../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('/admin/categories/index.php');
}

$name = trim((string)($_POST['name'] ?? ''));

$_SESSION['flash_old_name'] = $name;

if ($name === '') {
  $_SESSION['flash_error'] = 'Nama kategori wajib diisi.';
  redirect('/admin/categories/create.php');
}

if (mb_strlen($name) > 80) {
  $_SESSION['flash_error'] = 'Nama kategori maksimal 80 karakter.';
  redirect('/admin/categories/create.php');
}

try {
  $stmt = db()->prepare("INSERT INTO categories (name) VALUES (?)");
  $stmt->execute([$name]);

  unset($_SESSION['flash_old_name']);
  $_SESSION['flash_success'] = 'Kategori berhasil ditambahkan.';
  redirect('/admin/categories/index.php');
} catch (PDOException $e) {
  // Duplicate (UNIQUE)
  if ($e->getCode() === '23000') {
    $_SESSION['flash_error'] = 'Nama kategori sudah ada.';
    redirect('/admin/categories/create.php');
  }
  $_SESSION['flash_error'] = 'Gagal menyimpan kategori.';
  redirect('/admin/categories/create.php');
}
