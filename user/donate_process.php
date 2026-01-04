<?php
require_once __DIR__ . '/../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('/user/index.php');
}

function generate_invoice_number(): string {
  // Aman & unik untuk tugas kuliah: INV-YYYYMMDDHHMMSS-XXXX
  return 'INV-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(2)));
}

$campaign_id = (int)($_POST['campaign_id'] ?? 0);
$amount = (int)($_POST['amount'] ?? 0);
$payment_method = trim((string)($_POST['payment_method'] ?? ''));

if ($campaign_id <= 0 || $amount <= 0 || $payment_method === '') {
  $_SESSION['flash_error'] = 'Data donasi tidak lengkap.';
  redirect('/user/donate.php?campaign_id=' . $campaign_id);
}

$allowed_methods = ['Transfer Bank', 'E-Wallet'];
if (!in_array($payment_method, $allowed_methods, true)) {
  $_SESSION['flash_error'] = 'Metode pembayaran tidak valid.';
  redirect('/user/donate.php?campaign_id=' . $campaign_id);
}

// Pastikan campaign ada & aktif
$stmt = db()->prepare("SELECT id, status FROM campaigns WHERE id = ? LIMIT 1");
$stmt->execute([$campaign_id]);
$camp = $stmt->fetch();
if (!$camp) {
  $_SESSION['flash_error'] = 'Campaign tidak ditemukan.';
  redirect('/user/index.php');
}
if ($camp['status'] !== 'active') {
  $_SESSION['flash_error'] = 'Campaign tidak aktif.';
  redirect('/user/index.php');
}

try {
  $invoice = generate_invoice_number();

  $ins = db()->prepare("
    INSERT INTO donations (invoice_number, user_id, campaign_id, amount, payment_method, status)
    VALUES (?, ?, ?, ?, ?, 'pending')
  ");
  $ins->execute([
    $invoice,
    current_user_id(),
    $campaign_id,
    $amount,
    $payment_method
  ]);

  $donation_id = (int)db()->lastInsertId();
  $_SESSION['flash_success'] = 'Donasi dibuat. Silakan upload bukti pembayaran.';
  redirect('/user/upload_proof.php?id=' . $donation_id);

} catch (PDOException $e) {
  $_SESSION['flash_error'] = 'Gagal membuat donasi.';
  redirect('/user/donate.php?campaign_id=' . $campaign_id);
}
