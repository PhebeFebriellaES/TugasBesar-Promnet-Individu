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
  db()->beginTransaction();

  // kunci data donasi
  $stmt = db()->prepare("SELECT id, campaign_id, amount, status FROM donations WHERE id = ? FOR UPDATE");
  $stmt->execute([$id]);
  $don = $stmt->fetch();

  if (!$don) {
    db()->rollBack();
    $_SESSION['flash_error'] = 'Donasi tidak ditemukan.';
    redirect('/admin/donations/pending.php');
  }

  if ($don['status'] !== 'pending') {
    db()->rollBack();
    $_SESSION['flash_error'] = 'Donasi sudah diproses.';
    redirect('/admin/donations/pending.php');
  }

  // update donations
  $upd = db()->prepare("
    UPDATE donations
    SET status='verified', verified_by=?, verified_at=NOW(), admin_note=?
    WHERE id=?
  ");
  $upd->execute([current_user_id(), ($note !== '' ? $note : null), $id]);

  // update collected_amount
  $updCamp = db()->prepare("
    UPDATE campaigns
    SET collected_amount = collected_amount + ?
    WHERE id = ?
  ");
  $updCamp->execute([(int)$don['amount'], (int)$don['campaign_id']]);

  db()->commit();

  $_SESSION['flash_success'] = 'Donasi berhasil diverifikasi.';
  redirect('/admin/donations/pending.php');

} catch (Throwable $e) {
  if (db()->inTransaction()) db()->rollBack();
  $_SESSION['flash_error'] = 'Gagal verifikasi donasi.';
  redirect('/admin/donations/pending.php');
}
