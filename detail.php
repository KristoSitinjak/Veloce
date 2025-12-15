<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/path.php';

$id = $_GET['id'] ?? 0;
$kategori = $_GET['kategori'] ?? '';

// Determine which table to query
$table = '';
if ($kategori === 'sepatu') {
    $table = 'sepatu';
} elseif ($kategori === 'jersey') {
    $table = 'jersey';
} elseif ($kategori === 'sarung_tangan') {
    $table = 'sarung_tangan';
} else {
    // Try to find in all tables
    $tables = ['sepatu', 'jersey', 'sarung_tangan'];
    $product = null;
    
    foreach ($tables as $tbl) {
        $stmt = $pdo->prepare("SELECT *, '$tbl' as kategori FROM $tbl WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        if ($result) {
            $product = $result;
            $table = $tbl;
            break;
        }
    }
}

// If not found yet, query from the determined table
if (!isset($product) && $table) {
    $stmt = $pdo->prepare("SELECT *, '$table' as kategori FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
}

if (!$product) {
    header('Location: ' . url('index.php'));
    exit();
}

$page_title = $product['nama'] . ' - Veloce';
include __DIR__ . '/partials/header.php';

$categories = [
    'sepatu' => 'Sepatu',
    'jersey' => 'Jersey',
    'sarung_tangan' => 'Sarung Tangan'
];
?>

<section class="product-detail">
    <div class="product-detail-image">
        <img src="<?php echo url('assets/img/' . ($product['gambar'] ?: 'no-image.jpg')); ?>" 
             alt="<?php echo htmlspecialchars($product['nama']); ?>"
             onerror="this.src='https://via.placeholder.com/500x500/23398c/ffffff?text=No+Image'">
    </div>
    
    <div class="product-detail-info">
        <span class="category"><?php echo $categories[$product['kategori']] ?? $product['kategori']; ?></span>
        <h1><?php echo htmlspecialchars($product['nama']); ?></h1>
        <div class="price">Rp. <?php echo number_format($product['harga'], 0, ',', '.'); ?></div>
        <?php if (isset($product['jumlah'])): ?>
            <div style="margin-bottom: 20px;">
                <strong>Stok:</strong> <?php echo $product['jumlah']; ?> unit
            </div>
        <?php endif; ?>
        <div class="description">
            <?php echo nl2br(htmlspecialchars($product['deskripsi'])); ?>
        </div>
        <?php if (!isAdmin()): ?>
            <form action="<?php echo url('cart.php'); ?>" method="POST" class="add-to-cart-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="kategori" value="<?php echo $product['kategori']; ?>">
                <label for="qty" class="quantity-label">Jumlah</label>
                <input type="number" id="qty" name="qty" min="1" value="1" class="quantity-input">
                <button type="submit" class="btn-primary" style="padding: 15px 40px; font-size: 18px;">
                    <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                </button>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>

