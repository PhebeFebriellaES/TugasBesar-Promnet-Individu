<?php
require_once __DIR__ . '/../../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('/admin/donations/pending.php');
}

$id = (int)($_POST['id'] ?? 0);
$note = trim((string)($_POST['admin_note'] ?? ''));

if ($id <= 0) {
  $_SESSION['flash_error'] = 'Donasi tidak valid.';
  redirect('/admin/donations/pending.php');
}

try {
  $stmt = db()->prepare("SELECT id, status FROM donations WHERE id = ? LIMIT 1");
  $stmt->execute([$id]);
  $don = $stmt->fetch();

  if (!$don) {
    $_SESSION['flash_error'] = 'Donasi tidak ditemukan.';
    redirect('/admin/donations/pending.php');
  }

  if ($don['status'] !== 'pending') {
    $_SESSION['flash_error'] = 'Donasi sudah diproses.';
    redirect('/admin/donations/pending.php');
  }

  $upd = db()->prepare("
    UPDATE donations
    SET status='rejected', verified_by=?, verified_at=NOW(), admin_note=?
    WHERE id=?
  ");
  $upd->execute([current_user_id(), ($note !== '' ? $note : null), $id]);

  $_SESSION['flash_success'] = 'Donasi berhasil direject.';
  redirect('/admin/donations/pending.php');

} catch (Throwable $e) {
  $_SESSION['flash_error'] = 'Gagal reject donasi.';
  redirect('/admin/donations/pending.php');
}
