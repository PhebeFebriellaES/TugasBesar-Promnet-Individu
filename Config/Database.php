<?php
// config/database.php
// Default Laragon biasanya: user=root, password kosong.

$DB_HOST = '127.0.0.1';
$DB_NAME = 'sistem_donasi';
$DB_USER = 'root';
$DB_PASS = '';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";

$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
  // Agar jelas saat error koneksi
  exit("Koneksi database gagal: " . $e->getMessage());
}

return $pdo;
