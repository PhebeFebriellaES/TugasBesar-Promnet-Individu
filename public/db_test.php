<?php
require __DIR__ . '/../includes/init.php';

try {
  $stmt = db()->query("SELECT 1 AS ok");
  $row = $stmt->fetch();
  echo "✅ Koneksi DB OK. Hasil: " . $row['ok'];
} catch (Throwable $e) {
  echo "❌ Koneksi DB GAGAL: " . $e->getMessage();
}
