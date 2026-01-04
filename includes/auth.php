<?php
// includes/auth.php
// Pastikan init.php sudah di-include sebelum file ini.

function is_logged_in(): bool
{
  return isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']);
}

function current_user_id(): ?int
{
  return is_logged_in() ? (int)$_SESSION['user_id'] : null;
}

function current_user_role(): ?string
{
  return is_logged_in() ? (string)($_SESSION['user_role'] ?? null) : null;
}

function login_user(array $user): void
{
  // Minimal data user dari DB: id, name, email, role
  $_SESSION['user_id'] = (int)$user['id'];
  $_SESSION['user_name'] = (string)$user['name'];
  $_SESSION['user_email'] = (string)$user['email'];
  $_SESSION['user_role'] = (string)$user['role'];
}

function logout_user(): void
{
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params['path'], $params['domain'],
      $params['secure'], $params['httponly']
    );
  }
  session_destroy();
}

function require_login(): void
{
  if (!is_logged_in()) {
    redirect('/auth/login.php');
  }
}

function require_admin(): void
{
  require_login();
  if (current_user_role() !== 'admin') {
    // kalau user biasa coba akses admin, tendang ke halaman user
    redirect('/user/index.php');
  }
}
