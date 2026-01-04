<?php
require_once __DIR__ . '/../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('/user/history.php');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  $_SESSION['flash_error'] = 'Donasi tidak valid.';
  redirect('/user/history.php');
}

// cek donasi milik user & masih pending
$stmt = db()->prepare("SELECT id, status, proof_path FROM donations WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$id, current_user_id()]);
$don = $stmt->fetch();

if (!$don) {
  $_SESSION['flash_error'] = 'Donasi tidak ditemukan.';
  redirect('/user/history.php');
}
if ($don['status'] !== 'pending') {
  $_SESSION['flash_error'] = 'Donasi sudah diproses, bukti tidak bisa diubah.';
  redirect('/user/upload_proof.php?id=' . $id);
}

try {
  if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== UPLOAD_ERR_OK) {
    throw new RuntimeException('Upload bukti gagal.');
  }

  if ((int)$_FILES['proof']['size'] > 2 * 1024 * 1024) {
    throw new RuntimeException('Ukuran file maksimal 2MB.');
  }

  $tmp = $_FILES['proof']['tmp_name'];

  // validasi gambar
  $info = @getimagesize($tmp);
  if ($info === false || empty($info['mime'])) {
    throw new RuntimeException('File harus berupa gambar.');
  }

  $allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
  ];
  if (!isset($allowed[$info['mime']])) {
    throw new RuntimeException('Format harus jpg/png/webp.');
  }

  $dir = APP_ROOT . '/uploads/proofs';
  if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
  }

  $filename = 'proof_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$info['mime']];
  $dest = $dir . '/' . $filename;

  if (!move_uploaded_file($tmp, $dest)) {
    throw new RuntimeException('Gagal menyimpan file bukti.');
  }

  // hapus bukti lama kalau ada
  if (!empty($don['proof_path'])) {
    $oldFull = APP_ROOT . '/' . ltrim($don['proof_path'], '/');
    if (is_file($oldFull)) @unlink($oldFull);
  }

  $path = 'uploads/proofs/' . $filename;

  $upd = db()->prepare("UPDATE donations SET proof_path = ? WHERE id = ? AND user_id = ?");
  $upd->execute([$path, $id, current_user_id()]);

  $_SESSION['flash_success'] = 'Bukti berhasil diupload. Menunggu verifikasi admin.';
  redirect('/user/upload_proof.php?id=' . $id);

} catch (RuntimeException $e) {
  $_SESSION['flash_error'] = $e->getMessage();
  redirect('/user/upload_proof.php?id=' . $id);
}
