<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/path.php';

requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $kategori = $_POST['kategori'] ?? '';
    $harga = $_POST['harga'] ?? '';
    $jumlah = $_POST['jumlah'] ?? 0;
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    
    if (empty($nama) || empty($kategori) || empty($harga)) {
        $error = 'Nama, kategori, dan harga harus diisi!';
    } elseif (!in_array($kategori, ['sepatu', 'jersey', 'sarung_tangan'])) {
        $error = 'Kategori tidak valid!';
    } elseif (!is_numeric($harga) || $harga <= 0) {
        $error = 'Harga harus berupa angka positif!';
    } elseif (!is_numeric($jumlah) || $jumlah < 0) {
        $error = 'Jumlah harus berupa angka positif atau nol!';
    } else {
        // Handle image upload
        $gambar = null;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/img/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {
                    $gambar = $fileName;
                } else {
                    $error = 'Gagal mengupload gambar!';
                }
            } else {
                $error = 'Format gambar tidak didukung! (Hanya: jpg, jpeg, png, gif, webp)';
            }
        }
        
        if (empty($error)) {
            // Insert into the appropriate table based on category
            $table = $kategori; // sepatu, jersey, or sarung_tangan
            $stmt = $pdo->prepare("INSERT INTO $table (nama, deskripsi, jumlah, harga, gambar) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$nama, $deskripsi, $jumlah, $harga, $gambar])) {
                $success = 'Produk berhasil ditambahkan!';
                header('Location: ' . url('admin/dashboard.php?success=1'));
                exit();
            } else {
                $error = 'Terjadi kesalahan saat menambahkan produk!';
            }
        }
    }
}

$page_title = 'Tambah Produk - Veloce';
include __DIR__ . '/../partials/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Tambah Produk</h1>
        <a href="<?php echo url('admin/dashboard.php'); ?>" class="btn-add">Kembali</a>
    </div>
    
    <div class="form-container" style="max-width: 700px;">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama">Nama Produk</label>
                <input type="text" id="nama" name="nama" class="form-control" required value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori" class="form-control" required>
                    <option value="">Pilih Kategori</option>
                    <option value="sepatu" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] === 'sepatu') ? 'selected' : ''; ?>>Sepatu</option>
                    <option value="jersey" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] === 'jersey') ? 'selected' : ''; ?>>Jersey</option>
                    <option value="sarung_tangan" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] === 'sarung_tangan') ? 'selected' : ''; ?>>Sarung Tangan</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="harga">Harga (Rp)</label>
                <input type="number" id="harga" name="harga" class="form-control" required min="0" step="0.01" value="<?php echo htmlspecialchars($_POST['harga'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="jumlah">Jumlah Stok</label>
                <input type="number" id="jumlah" name="jumlah" class="form-control" required min="0" value="<?php echo htmlspecialchars($_POST['jumlah'] ?? '0'); ?>">
            </div>
            
            <div class="form-group">
                <label for="gambar">Gambar Produk</label>
                <input type="file" id="gambar" name="gambar" class="form-control" accept="image/*">
                <small style="color: #666;">Format: JPG, PNG, GIF, WEBP (Maks: 5MB)</small>
            </div>
            
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" class="form-control" rows="5"><?php echo htmlspecialchars($_POST['deskripsi'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" class="btn-submit">Tambah Produk</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

