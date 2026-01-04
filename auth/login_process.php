<?php
require_once __DIR__ . '/../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

// Hanya boleh POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('/auth/login.php');
}

// Ambil & rapikan input
$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($email === '' || $password === '') {
  $_SESSION['flash_error'] = 'Email dan password wajib diisi.';
  redirect('/auth/login.php');
}

// Cari user berdasarkan email
$stmt = db()->prepare("SELECT id, name, email, password_hash, role, is_active FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
  $_SESSION['flash_error'] = 'Email atau password salah.';
  redirect('/auth/login.php');
}

if ((int)$user['is_active'] !== 1) {
  $_SESSION['flash_error'] = 'Akun kamu nonaktif. Hubungi admin.';
  redirect('/auth/login.php');
}

// Verifikasi password hash
if (!password_verify($password, $user['password_hash'])) {
  $_SESSION['flash_error'] = 'Email atau password salah.';
  redirect('/auth/login.php');
}

// Login sukses: simpan session
login_user($user);

// Redirect sesuai role
if ($user['role'] === 'admin') {
  redirect('/admin/dashboard.php');
}
redirect('/user/index.php');
