<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/path.php';

$kategori = $_GET['kategori'] ?? '';
$search = $_GET['search'] ?? '';

// Build query from multiple tables using UNION
$tables = ['sepatu', 'jersey', 'sarung_tangan'];
$queries = [];

if (!empty($kategori) && in_array($kategori, $tables)) {
    $tables = [$kategori];
}

foreach ($tables as $table) {
    $queries[] = "SELECT id, nama, deskripsi, harga, gambar, created_at, '$table' as kategori FROM $table";
}

$sql = implode(' UNION ', $queries) . " ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$allProducts = $stmt->fetchAll();

// Filter by search term if provided
$products = $allProducts;
if (!empty($search)) {
    $search = strtolower($search);
    $products = array_filter($allProducts, function($product) use ($search) {
        return stripos($product['nama'], $search) !== false || 
               stripos($product['deskripsi'], $search) !== false;
    });
}

$categories = [
    '' => 'Semua',
    'sepatu' => 'Sepatu',
    'jersey' => 'Jersey',
    'sarung_tangan' => 'Sarung Tangan'
];

$page_title = 'Produk Veloce';
include __DIR__ . '/partials/header.php';
?>

<section class="product-page-hero">
    <div>
        <span class="section-eyebrow">Katalog Lengkap</span>
        <h1>Temukan Gear yang Tepat</h1>
        <p>Pilih kategori favorit Anda untuk melihat koleksi terbaru dari Veloce.</p>
    </div>
</section>

<!-- Search Bar -->
<div class="search-container" style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">
    <form method="GET" action="<?php echo url('produk.php'); ?>" style="display: flex; gap: 10px; align-items: center;">
        <?php if (!empty($kategori)): ?>
            <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($kategori); ?>">
        <?php endif; ?>
        <div style="flex: 1; position: relative;">
            <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
            <input 
                type="text" 
                name="search" 
                placeholder="Cari produk berdasarkan nama atau deskripsi..." 
                value="<?php echo htmlspecialchars($search); ?>"
                style="width: 100%; padding: 12px 15px 12px 45px; border: 2px solid #ecf0ff; border-radius: 50px; font-size: 15px; outline: none; transition: border-color 0.3s;"
                onfocus="this.style.borderColor='#5b8af0'"
                onblur="this.style.borderColor='#ecf0ff'"
            >
        </div>
        <button 
            type="submit" 
            style="padding: 12px 30px; background: #1f3b83; color: white; border: none; border-radius: 50px; font-weight: 600; cursor: pointer; transition: background 0.3s;"
            onmouseover="this.style.background='#5b8af0'"
            onmouseout="this.style.background='#1f3b83'"
        >
            <i class="fas fa-search"></i> Cari
        </button>
        <?php if (!empty($search)): ?>
            <a 
                href="<?php echo url('produk.php' . (!empty($kategori) ? '?kategori=' . $kategori : '')); ?>" 
                style="padding: 12px 20px; background: #dc3545; color: white; border-radius: 50px; text-decoration: none; font-weight: 600; transition: background 0.3s;"
                onmouseover="this.style.background='#c82333'"
                onmouseout="this.style.background='#dc3545'"
            >
                <i class="fas fa-times"></i> Reset
            </a>
        <?php endif; ?>
    </form>
    <?php if (!empty($search)): ?>
        <p style="margin-top: 15px; color: #666; font-size: 14px;">
            <i class="fas fa-info-circle"></i> Menampilkan hasil pencarian untuk: <strong>"<?php echo htmlspecialchars($search); ?>"</strong> 
            (<?php echo count($products); ?> produk ditemukan)
        </p>
    <?php endif; ?>
</div>

<nav class="category-nav">
    <ul>
        <?php foreach ($categories as $key => $label): ?>
            <li>
                <a href="<?php echo url('produk.php' . ($key ? '?kategori=' . $key : '')); ?>" class="<?php echo ($kategori === $key) ? 'active' : ''; ?>">
                    <?php echo $label; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

<section class="products-section" style="padding-top: 20px;">
    <h2 class="section-title">
        <?php echo $kategori && isset($categories[$kategori]) ? $categories[$kategori] : 'Semua Produk'; ?>
    </h2>

    <?php if (empty($products)): ?>
        <div style="text-align: center; padding: 40px;">
            <p style="font-size: 18px; color: #666;">Belum ada produk tersedia pada kategori ini.</p>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <a href="<?php echo url('detail.php?id=' . $product['id'] . '&kategori=' . $product['kategori']); ?>">
                        <img src="<?php echo url('assets/img/' . ($product['gambar'] ?: 'no-image.jpg')); ?>" 
                             alt="<?php echo htmlspecialchars($product['nama']); ?>" 
                             class="product-image"
                             onerror="this.src='https://via.placeholder.com/200x200/23398c/ffffff?text=No+Image'">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['nama']); ?></h3>
                        <span class="product-price">Rp. <?php echo number_format($product['harga'], 0, ',', '.'); ?></span>
                    </a>
                    <a href="<?php echo url('detail.php?id=' . $product['id'] . '&kategori=' . $product['kategori']); ?>">
                        <button class="btn-buy">Lihat Detail</button>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>

