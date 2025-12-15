<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/path.php';

$kategori = $_GET['kategori'] ?? '';
$search = $_GET['search'] ?? '';

// Build query from multiple tables using UNION
$queries = [];
$params = [];

// Determine which tables to query
$tables = [];
if (empty($kategori)) {
    // Query all tables
    $tables = ['sepatu', 'jersey', 'sarung_tangan'];
} else {
    // Query specific table based on category
    if ($kategori === 'sepatu') {
        $tables = ['sepatu'];
    } elseif ($kategori === 'jersey') {
        $tables = ['jersey'];
    } elseif ($kategori === 'sarung_tangan') {
        $tables = ['sarung_tangan'];
    } else {
        $tables = ['sepatu', 'jersey', 'sarung_tangan'];
    }
}

// Build UNION query
foreach ($tables as $table) {
    $tableQuery = "SELECT id, nama, deskripsi, harga, gambar, created_at, '$table' as kategori FROM $table WHERE 1=1";
    
    if (!empty($search)) {
        $tableQuery .= " AND (nama LIKE ? OR deskripsi LIKE ?)";
    }
    
    $queries[] = $tableQuery;
}

$sql = implode(' UNION ', $queries) . " ORDER BY created_at DESC";

// Prepare and execute
if (!empty($search)) {
    $searchParam = "%$search%";
    $params = array_fill(0, count($tables) * 2, $searchParam);
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Highlight best products (top 3 by price)
$bestProducts = $products;
usort($bestProducts, function ($a, $b) {
    return ($b['harga'] ?? 0) <=> ($a['harga'] ?? 0);
});
$bestProducts = array_slice($bestProducts, 0, 3);

// Static testimonials
$testimonials = [
    [
        'name' => 'Julián Alvarez',
        'role' => 'Kapten Klub Amatir',
        'message' => 'Perlengkapan lengkap dengan kualitas premium. Pengiriman cepat, tim kami selalu siap bertanding!',
        'rating' => 5
    ],
    [
        'name' => 'Lionel Messi',
        'role' => 'Pelatih Akademi Muda',
        'message' => 'Jersey custom dan sepatu terbaru selalu tersedia. Anak-anak akademi sangat puas.',
        'rating' => 5
    ],
    [
        'name' => 'Cristiano Penaldo',
        'role' => 'Sport Enthusiast',
        'message' => 'Belanja di Veloce menyenangkan: desain modern, harga transparan, dan support yang ramah.',
        'rating' => 4
    ]
];

// Get categories for filter
$categories = [
    'sepatu' => 'Sepatu',
    'jersey' => 'Jersey',
    'sarung_tangan' => 'Sarung Tangan'
];

$page_title = 'Veloce - Perlengkapan Bola Terbaik';
include __DIR__ . '/partials/header.php';
?>

<header class="hero">
    <h1>TEMUKAN<br>PERLENGKAPAN BOLA<br>TERBAIK</h1>
    <p>Koleksi Sepatu, Jersey, dan Aksesori Resmi.</p>
    <a href="<?php echo url('produk.php'); ?>" class="btn-primary">Lihat Semua Produk</a>
</header>

<section class="about-section">
    <div class="about-content">
        <div class="about-text">
            <span class="section-eyebrow">Mengapa Veloce?</span>
            <h2>Performa Puncak untuk Setiap Pertandingan</h2>
            <p>Veloce menghadirkan perlengkapan sepak bola terkurasi untuk klub profesional, komunitas futsal, hingga pemain kasual. Setiap produk melewati quality control ketat sehingga siap mendukung performa Anda di lapangan.</p>
            <ul class="about-list">
                <li><i class="fas fa-check-circle"></i> Koleksi sepatu, jersey, dan perlengkapan kiper terlengkap.</li>
                <li><i class="fas fa-check-circle"></i> Garansi produk resmi dan layanan purna jual.</li>
                <li><i class="fas fa-check-circle"></i> Program kemitraan untuk klub dan akademi muda.</li>
            </ul>
        </div>
        <div class="about-highlights">
            <div class="highlight-card">
                <strong>120+</strong>
                <span>Klub & Akademi Mitra</span>
            </div>
            <div class="highlight-card">
                <strong>4.9/5</strong>
                <span>Tingkat Kepuasan Pelanggan</span>
            </div>
            <div class="highlight-card">
                <strong>15+</strong>
                <span>Brand Premium Global</span>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($bestProducts)): ?>
<section class="best-products-section">
    <div class="section-header">
        <span class="section-eyebrow">Pilihan Unggulan</span>
        <h2>Produk Terbaik Veloce</h2>
        <p>Best seller pilihan pelanggan dan rekomendasi gear specialist kami.</p>
    </div>
    <div class="best-products-grid">
        <?php foreach ($bestProducts as $best): ?>
            <div class="best-card">
                <div class="best-badge"><i class="fas fa-star"></i> Best</div>
                <img src="<?php echo url('assets/img/' . ($best['gambar'] ?: 'no-image.jpg')); ?>" 
                     alt="<?php echo htmlspecialchars($best['nama']); ?>"
                     onerror="this.src='https://via.placeholder.com/300x200/23398c/ffffff?text=No+Image'">
                <div class="best-info">
                    <span class="best-category"><?php echo ucfirst(str_replace('_', ' ', $best['kategori'])); ?></span>
                    <h3><?php echo htmlspecialchars($best['nama']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($best['deskripsi'], 0, 90)) . (strlen($best['deskripsi']) > 90 ? '...' : ''); ?></p>
                    <div class="best-bottom">
                        <span class="best-price">Rp. <?php echo number_format($best['harga'], 0, ',', '.'); ?></span>
                        <a href="<?php echo url('detail.php?id=' . $best['id'] . '&kategori=' . $best['kategori']); ?>" class="btn-outline">Lihat Detail</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="testimonial-section">
    <div class="section-header">
        <span class="section-eyebrow">Testimoni Pelanggan</span>
        <h2>Apa Kata Tim & Atlet</h2>
        <p>Kepercayaan komunitas sepak bola menjadi energi kami untuk terus berkembang.</p>
    </div>
    <div class="testimonial-grid">
        <?php foreach ($testimonials as $testi): ?>
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <?php for ($i = 0; $i < $testi['rating']; $i++): ?>
                        <i class="fas fa-star"></i>
                    <?php endfor; ?>
                </div>
                <p class="testimonial-message">"<?php echo $testi['message']; ?>"</p>
                <div class="testimonial-profile">
                    <div class="avatar-placeholder">
                        <span><?php echo strtoupper(substr($testi['name'], 0, 1)); ?></span>
                    </div>
                    <div>
                        <strong><?php echo $testi['name']; ?></strong>
                        <span><?php echo $testi['role']; ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
