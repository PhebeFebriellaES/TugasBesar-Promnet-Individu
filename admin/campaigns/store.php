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
    if ($excludeId) {
      $stmt = db()->prepare("SELECT id FROM campaigns WHERE slug = ? AND id <> ? LIMIT 1");
      $stmt->execute([$slug, $excludeId]);
    } else {
      $stmt = db()->prepare("SELECT id FROM campaigns WHERE slug = ? LIMIT 1");
      $stmt->execute([$slug]);
    }
    $exists = $stmt->fetch();
    if (!$exists) return $slug;

    $slug = $base . '-' . $i;
    $i++;
    if ($i > 200) return $base . '-' . time(); // fallback
  }
}

function is_valid_date(?string $date): bool {
  if ($date === null || $date === '') return true;
  $d = DateTime::createFromFormat('Y-m-d', $date);
  return $d && $d->format('Y-m-d') === $date;
}

function handle_image_upload(): ?string {
  if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    return null;
  }

  if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    throw new RuntimeException('Upload gambar gagal.');
  }

  $maxBytes = 2 * 1024 * 1024; // 2MB
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

  // simpan path relatif
  return 'uploads/campaigns/' . $filename;
}

// Ambil input
$category_id   = (int)($_POST['category_id'] ?? 0);
$title         = trim((string)($_POST['title'] ?? ''));
$description   = trim((string)($_POST['description'] ?? ''));
$target_amount = (int)($_POST['target_amount'] ?? 0);
$status        = (string)($_POST['status'] ?? 'active');
$start_date    = trim((string)($_POST['start_date'] ?? ''));
$end_date      = trim((string)($_POST['end_date'] ?? ''));

// simpan old jika error
$_SESSION['flash_old'] = [
  'category_id' => $category_id,
  'title' => $title,
  'description' => $description,
  'target_amount' => $target_amount,
  'status' => $status,
  'start_date' => $start_date,
  'end_date' => $end_date,
];

$allowed_status = ['active', 'inactive', 'completed'];

if ($category_id <= 0 || $title === '' || $description === '' || $target_amount <= 0) {
  $_SESSION['flash_error'] = 'Kategori, judul, deskripsi, dan target wajib diisi.';
  redirect('/admin/campaigns/create.php');
}

if (!in_array($status, $allowed_status, true)) {
  $_SESSION['flash_error'] = 'Status tidak valid.';
  redirect('/admin/campaigns/create.php');
}

if (!is_valid_date($start_date) || !is_valid_date($end_date)) {
  $_SESSION['flash_error'] = 'Format tanggal tidak valid.';
  redirect('/admin/campaigns/create.php');
}

if ($start_date !== '' && $end_date !== '' && $start_date > $end_date) {
  $_SESSION['flash_error'] = 'Tanggal mulai tidak boleh lebih besar dari deadline.';
  redirect('/admin/campaigns/create.php');
}

// pastikan kategori ada
$cek = db()->prepare("SELECT id FROM categories WHERE id = ? LIMIT 1");
$cek->execute([$category_id]);
if (!$cek->fetch()) {
  $_SESSION['flash_error'] = 'Kategori tidak ditemukan.';
  redirect('/admin/campaigns/create.php');
}

try {
  $slug = generate_unique_slug($title);
  $image_path = handle_image_upload();
  $created_by = current_user_id();

  $stmt = db()->prepare("
    INSERT INTO campaigns
      (category_id, title, slug, description, target_amount, collected_amount, start_date, end_date, status, image_path, created_by)
    VALUES
      (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([
    $category_id,
    $title,
    $slug,
    $description,
    $target_amount,
    ($start_date !== '' ? $start_date : null),
    ($end_date !== '' ? $end_date : null),
    $status,
    $image_path,
    $created_by,
  ]);

  unset($_SESSION['flash_old']);
  $_SESSION['flash_success'] = 'Campaign berhasil ditambahkan.';
  redirect('/admin/campaigns/index.php');

} catch (RuntimeException $e) {
  $_SESSION['flash_error'] = $e->getMessage();
  redirect('/admin/campaigns/create.php');
} catch (PDOException $e) {
  $_SESSION['flash_error'] = 'Gagal menyimpan campaign.';
  redirect('/admin/campaigns/create.php');
}
