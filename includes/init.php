<?php
// includes/init.php
// Selalu include file ini di bagian PALING ATAS setiap halaman (sebelum output HTML).

declare(strict_types=1);

date_default_timezone_set('Asia/Jakarta');

// Root project: sistem_donasi/
define('APP_ROOT', dirname(__DIR__));

// Sesuaikan jika nama folder project kamu beda
// contoh jika url: http://localhost/sistem_donasi maka BASE_URL = /sistem_donasi
define('BASE_URL', '/sistem_donasi');

// Start session aman
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Load PDO
$pdo = require APP_ROOT . '/config/database.php';
$GLOBALS['pdo'] = $pdo;

/**
 * Ambil koneksi PDO dari mana saja
 */
function db(): PDO
{
  return $GLOBALS['pdo'];
}

/**
 * Buat URL berbasis BASE_URL
 * contoh: url('/auth/login.php')
 */
function url(string $path): string
{
  $path = '/' . ltrim($path, '/');
  return rtrim(BASE_URL, '/') . $path;
}

/**
 * Redirect aman
 */
function redirect(string $path): void
{
  header('Location: ' . url($path));
  exit;
}

/**
 * Escape HTML (hindari XSS)
 */
function e(?string $str): string
{
  return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
