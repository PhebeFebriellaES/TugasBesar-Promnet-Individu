<?php
require_once __DIR__ . '/../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('/auth/register.php');
}

$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$password_confirm = (string)($_POST['password_confirm'] ?? '');

// Simpan old input kalau error
$_SESSION['flash_old'] = [
  'name' => $name,
  'email' => $email,
  'phone' => $phone,
];

// Validasi sederhana
if ($name === '' || $email === '' || $password === '' || $password_confirm === '') {
  $_SESSION['flash_error'] = 'Semua field wajib diisi kecuali No. HP.';
  redirect('/auth/register.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $_SESSION['flash_error'] = 'Format email tidak valid.';
  redirect('/auth/register.php');
}

if (strlen($password) < 6) {
  $_SESSION['flash_error'] = 'Password minimal 6 karakter.';
  redirect('/auth/register.php');
}

if ($password !== $password_confirm) {
  $_SESSION['flash_error'] = 'Konfirmasi password tidak sama.';
  redirect('/auth/register.php');
}

// Cek email sudah terdaftar?
$stmt = db()->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
if ($stmt->fetch()) {
  $_SESSION['flash_error'] = 'Email sudah terdaftar. Silakan login.';
  redirect('/auth/register.php');
}

// Insert user baru (role default user)
$hash = password_hash($password, PASSWORD_DEFAULT);

$insert = db()->prepare("
  INSERT INTO users (name, email, phone, password_hash, role, is_active)
  VALUES (?, ?, ?, ?, 'user', 1)
");

$insert->execute([$name, $email, ($phone !== '' ? $phone : null), $hash]);

// Auto login setelah register
$userId = (int)db()->lastInsertId();
login_user([
  'id' => $userId,
  'name' => $name,
  'email' => $email,
  'role' => 'user',
]);

// bersihkan flash old
unset($_SESSION['flash_old']);

redirect('/user/index.php');
