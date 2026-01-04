<?php
require_once __DIR__ . '/../../includes/init.php';
require_once APP_ROOT . '/includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('/admin/campaigns/index.php');
}

function slugify(string $text): string {
  $text = trim($text);
  $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
  $text = trim((string)$text, '-');
  $text = strtolower($text);
  return $text !== '' ? $text : 'campaign';
}

function generate_unique_slug(string $title, ?int $excludeId = null): string {
  $base = slugify($title);
  $slug = $base;
  $i = 2;

  while (true) {
    $stmt = db()->prepare("SELECT id FROM campaigns WHERE slug = ? AND id <> ? LIMIT 1");
    $stmt->execute([$slug, $excludeId]);
    if (!$stmt->fetch()) return $slug;

    $slug = $base . '-' . $i;
    $i++;
    if ($i > 200) return $base . '-' . time();
  }
}

function is_valid_date(?string $date): bool {
  if ($date === null || $date === '') return true;
  $d = DateTime::createFromFormat('Y-m-d', $date);
  return $d && $d->format('Y-m-d') === $date;
}

function handle_image_upload_and_replace(?string $oldPath): ?string {
  if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    return $oldPath; // tidak upload baru
  }
  if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    throw new RuntimeException('Upload gambar gagal.');
  }

  $maxBytes = 2 * 1024 * 1024;
  if ((int)$_FILES['image']['size'] > $maxBytes) {
    throw new RuntimeException('Ukuran gambar maksimal 2MB.');
  }

  $tmp = $_FILES['image']['tmp_name'];
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($tmp);

  $allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
  ];

  if (!isset($allowed[$mime])) {
    throw new RuntimeException('Format gambar harus jpg/png/webp.');
  }

  $dir = APP_ROOT . '/uploads/campaigns';
  if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
  }

  $filename = 'camp_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
  $dest = $dir . '/' . $filename;

  if (!move_uploaded_file($tmp, $dest)) {
    throw new RuntimeException('Gagal menyimpan file gambar.');
  }

  // hapus gambar lama jika ada
  if ($oldPath) {
    $oldFull = APP_ROOT . '/' . ltrim($oldPath, '/');
    if (is_file($oldFull)) {
      @unlink($oldFull);
    }
  }

  return 'uploads/campaigns/' . $filename;
}

// Input
$id            = (int)($_POST['id'] ?? 0);
$category_id   = (int)($_POST['category_id'] ?? 0);
$title         = trim((string)($_POST['title'] ?? ''));
$description   = trim((string)($_POST['description'] ?? ''));
$target_amount = (int)($_POST['target_amount'] ?? 0);
$status        = (string)($_POST['status'] ?? 'active');
$start_date    = trim((string)($_POST['start_date'] ?? ''));
$end_date      = trim((string)($_POST['end_date'] ?? ''));

if ($id <= 0) {
  $_SESSION['flash_error'] = 'Campaign tidak valid.';
  redirect('/admin/campaigns/index.php');
}

$allowed_status = ['active', 'inactive', 'completed'];
if ($category_id <= 0 || $title === '' || $description === '' || $target_amount <= 0) {
  $_SESSION['flash_error'] = 'Kategori, judul, deskripsi, dan target wajib diisi.';
  redirect('/admin/campaigns/edit.php?id=' . $id);
}
if (!in_array($status, $allowed_status, true)) {
  $_SESSION['flash_error'] = 'Status tidak valid.';
  redirect('/admin/campaigns/edit.php?id=' . $id);
}
if (!is_valid_date($start_date) || !is_valid_date($end_date)) {
  $_SESSION['flash_error'] = 'Format tanggal tidak valid.';
  redirect('/admin/campaigns/edit.php?id=' . $id);
}
if ($start_date !== '' && $end_date !== '' && $start_date > $end_date) {
  $_SESSION['flash_error'] = 'Tanggal mulai tidak boleh lebih besar dari deadline.';
  redirect('/admin/campaigns/edit.php?id=' . $id);
}

// ambil campaign lama
$stmt = db()->prepare("SELECT id, image_path FROM campaigns WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$old = $stmt->fetch();
if (!$old) {
  $_SESSION['flash_error'] = 'Campaign tidak ditemukan.';
  redirect('/admin/campaigns/index.php');
}

// pastikan kategori ada
$cek = db()->prepare("SELECT id FROM categories WHERE id = ? LIMIT 1");
$cek->execute([$category_id]);
if (!$cek->fetch()) {
  $_SESSION['flash_error'] = 'Kategori tidak ditemukan.';
  redirect('/admin/campaigns/edit.php?id=' . $id);
}

try {
  $slug = generate_unique_slug($title, $id);
  $newImagePath = handle_image_upload_and_replace($old['image_path'] ?? null);

  $upd = db()->prepare("
    UPDATE campaigns
    SET category_id = ?, title = ?, slug = ?, description = ?, target_amount = ?, start_date = ?, end_date = ?, status = ?, image_path = ?
    WHERE id = ?
  ");
  $upd->execute([
    $category_id,
    $title,
    $slug,
    $description,
    $target_amount,
    ($start_date !== '' ? $start_date : null),
    ($end_date !== '' ? $end_date : null),
    $status,
    $newImagePath,
    $id
  ]);

  $_SESSION['flash_success'] = 'Campaign berhasil diupdate.';
  redirect('/admin/campaigns/index.php');

} catch (RuntimeException $e) {
  $_SESSION['flash_error'] = $e->getMessage();
  redirect('/admin/campaigns/edit.php?id=' . $id);
} catch (PDOException $e) {
  $_SESSION['flash_error'] = 'Gagal update campaign.';
  redirect('/admin/campaigns/edit.php?id=' . $id);
}
