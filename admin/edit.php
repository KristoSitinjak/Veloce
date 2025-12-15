<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/path.php';

requireAdmin();

$id = $_GET['id'] ?? 0;
$kategori = $_GET['kategori'] ?? '';

// Determine which table to query
if (empty($kategori)) {
    // Try to find in all tables
    $tables = ['sepatu', 'jersey', 'sarung_tangan'];
    $product = null;
    
    foreach ($tables as $tbl) {
        $stmt = $pdo->prepare("SELECT *, '$tbl' as kategori FROM $tbl WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        if ($result) {
            $product = $result;
            $kategori = $tbl;
            break;
        }
    }
} else {
    $table = $kategori;
    $stmt = $pdo->prepare("SELECT *, '$table' as kategori FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
}

if (!$product) {
    header('Location: ' . url('admin/dashboard.php'));
    exit();
}

$kategori = $product['kategori']; // Use kategori from product

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
        $gambar = $product['gambar']; // Keep existing image
        
        // Handle new image upload
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/img/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                // Delete old image if exists
                if ($product['gambar'] && file_exists($uploadDir . $product['gambar'])) {
                    unlink($uploadDir . $product['gambar']);
                }
                
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
            // Update in the appropriate table
            $table = $kategori;
            $stmt = $pdo->prepare("UPDATE $table SET nama = ?, deskripsi = ?, jumlah = ?, harga = ?, gambar = ? WHERE id = ?");
            if ($stmt->execute([$nama, $deskripsi, $jumlah, $harga, $gambar, $id])) {
                header('Location: ' . url('admin/dashboard.php?success=1'));
                exit();
            } else {
                $error = 'Terjadi kesalahan saat mengupdate produk!';
            }
        }
    }
    
    // Update product data for form
    if (isset($nama)) {
        $product['nama'] = $nama;
        $product['kategori'] = $kategori;
        $product['harga'] = $harga;
        $product['jumlah'] = $jumlah;
        $product['gambar'] = $gambar ?? $product['gambar'];
        $product['deskripsi'] = $deskripsi;
    }
}

$page_title = 'Edit Produk - Veloce';
include __DIR__ . '/../partials/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Edit Produk</h1>
        <a href="<?php echo url('admin/dashboard.php'); ?>" class="btn-add">Kembali</a>
    </div>
    
    <div class="form-container" style="max-width: 700px;">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama">Nama Produk</label>
                <input type="text" id="nama" name="nama" class="form-control" required value="<?php echo htmlspecialchars($product['nama']); ?>">
            </div>
            
            <div class="form-group">
                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori" class="form-control" required>
                    <option value="sepatu" <?php echo $product['kategori'] === 'sepatu' ? 'selected' : ''; ?>>Sepatu</option>
                    <option value="jersey" <?php echo $product['kategori'] === 'jersey' ? 'selected' : ''; ?>>Jersey</option>
                    <option value="sarung_tangan" <?php echo $product['kategori'] === 'sarung_tangan' ? 'selected' : ''; ?>>Sarung Tangan</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="harga">Harga (Rp)</label>
                <input type="number" id="harga" name="harga" class="form-control" required min="0" step="0.01" value="<?php echo htmlspecialchars($product['harga']); ?>">
            </div>
            
            <div class="form-group">
                <label for="jumlah">Jumlah Stok</label>
                <input type="number" id="jumlah" name="jumlah" class="form-control" required min="0" value="<?php echo htmlspecialchars($product['jumlah'] ?? 0); ?>">
            </div>
            
            <div class="form-group">
                <label>Gambar Saat Ini</label>
                <?php if ($product['gambar']): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?php echo url('assets/img/' . $product['gambar']); ?>" 
                             alt="Current image" 
                             style="max-width: 200px; border-radius: 5px;"
                             onerror="this.src='https://via.placeholder.com/200x200/23398c/ffffff?text=No+Image'">
                    </div>
                <?php else: ?>
                    <p style="color: #666;">Tidak ada gambar</p>
                <?php endif; ?>
                <label for="gambar">Ganti Gambar</label>
                <input type="file" id="gambar" name="gambar" class="form-control" accept="image/*">
                <small style="color: #666;">Format: JPG, PNG, GIF, WEBP (Maks: 5MB). Kosongkan jika tidak ingin mengganti.</small>
            </div>
            
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" class="form-control" rows="5"><?php echo htmlspecialchars($product['deskripsi']); ?></textarea>
            </div>
            
            <button type="submit" class="btn-submit">Update Produk</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

