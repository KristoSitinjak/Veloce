<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/path.php';

requireAdmin();

// Query from all product tables using UNION
$sql = "SELECT id, nama, deskripsi, harga, gambar, created_at, 'sepatu' as kategori FROM sepatu
        UNION ALL
        SELECT id, nama, deskripsi, harga, gambar, created_at, 'jersey' as kategori FROM jersey
        UNION ALL
        SELECT id, nama, deskripsi, harga, gambar, created_at, 'sarung_tangan' as kategori FROM sarung_tangan
        ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll();

$page_title = 'Admin Dashboard - Veloce';
include __DIR__ . '/../partials/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Dashboard Admin</h1>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="<?php echo url('admin/laporan.php'); ?>" class="btn-add" style="background: #ffc107; color: #333;">
                <i class="fas fa-chart-bar"></i> Laporan Penjualan
            </a>
            <a href="<?php echo url('admin/orders.php'); ?>" class="btn-add" style="background: #5b8af0;">
                <i class="fas fa-receipt"></i> Kelola Pesanan
            </a>
            <a href="<?php echo url('admin/users.php'); ?>" class="btn-add" style="background: #28a745;">
                <i class="fas fa-users"></i> Kelola User
            </a>
            <a href="<?php echo url('admin/tambah.php'); ?>" class="btn-add">
                <i class="fas fa-plus"></i> Tambah Produk
            </a>
        </div>
    </div>
    
    <div class="admin-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            Belum ada produk. <a href="<?php echo url('admin/tambah.php'); ?>">Tambah produk pertama</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <img src="<?php echo url('assets/img/' . ($product['gambar'] ?: 'no-image.jpg')); ?>" 
                                     alt="<?php echo htmlspecialchars($product['nama']); ?>" 
                                     class="table-image"
                                     onerror="this.src='https://via.placeholder.com/60x60/23398c/ffffff?text=No+Image'">
                            </td>
                            <td><?php echo htmlspecialchars($product['nama']); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $product['kategori'])); ?></td>
                            <td>Rp. <?php echo number_format($product['harga'], 0, ',', '.'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></td>
                            <td>
                                <a href="<?php echo url('admin/edit.php?id=' . $product['id'] . '&kategori=' . $product['kategori']); ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="<?php echo url('admin/hapus.php?id=' . $product['id'] . '&kategori=' . $product['kategori']); ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

