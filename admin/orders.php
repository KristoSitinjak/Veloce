<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/path.php';
require_once __DIR__ . '/../config/orders.php';

requireAdmin();

$page_title = 'Kelola Pesanan - Admin Veloce';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['status'];
    
    if (updateOrderStatus($orderId, $newStatus)) {
        $_SESSION['admin_flash'] = [
            'type' => 'success',
            'message' => 'Status pesanan berhasil diperbarui!'
        ];
    } else {
        $_SESSION['admin_flash'] = [
            'type' => 'error',
            'message' => 'Gagal memperbarui status pesanan.'
        ];
    }
    
    header("Location: " . url('admin/orders.php'));
    exit;
}

// Get filter
$statusFilter = $_GET['status'] ?? 'all';

// Get orders
$orders = getAllOrders($statusFilter);

// Get flash message
$flash = null;
if (isset($_SESSION['admin_flash'])) {
    $flash = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);
}

include __DIR__ . '/../partials/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Kelola Pesanan</h1>
        <a href="<?php echo url('admin/dashboard.php'); ?>" class="btn-add">Kembali ke Dashboard</a>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="filter-section">
        <label>Filter Status:</label>
        <select onchange="window.location.href='<?php echo url('admin/orders.php?status='); ?>' + this.value">
            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
            <option value="verified" <?php echo $statusFilter === 'verified' ? 'selected' : ''; ?>>Terverifikasi</option>
            <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Diproses</option>
            <option value="shipped" <?php echo $statusFilter === 'shipped' ? 'selected' : ''; ?>>Dikirim</option>
            <option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Selesai</option>
            <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
        </select>
    </div>

    <?php if (empty($orders)): ?>
        <div class="admin-table">
            <p style="text-align: center; padding: 40px; color: #666;">Tidak ada pesanan ditemukan.</p>
        </div>
    <?php else: ?>
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Username</th>
                        <th>Nama Penerima</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr <?php echo $order['cancellation_requested'] ? 'style="background: #fff3cd;"' : ''; ?>>
                            <td>
                                <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                <?php if ($order['cancellation_requested']): ?>
                                    <br><span style="color: #856404; font-size: 12px; font-weight: 600;">
                                        <i class="fas fa-exclamation-triangle"></i> Permintaan Pembatalan
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($order['username'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td><strong>Rp. <?php echo number_format($order['total'], 0, ',', '.'); ?></strong></td>
                            <td><?php echo getPaymentMethodLabel($order['payment_method']); ?></td>
                            <td>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" onchange="if(confirm('Ubah status pesanan ini?')) this.form.submit();" 
                                            class="status-select status-<?php echo $order['status']; ?>">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                                        <option value="verified" <?php echo $order['status'] === 'verified' ? 'selected' : ''; ?>>Terverifikasi</option>
                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Diproses</option>
                                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Dikirim</option>
                                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <a href="<?php echo url('admin/order-detail.php?id=' . $order['id']); ?>" class="btn-edit">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
.status-select {
    padding: 6px 12px;
    border-radius: 5px;
    border: 1px solid #ddd;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.status-select.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-select.status-verified {
    background: #d1ecf1;
    color: #0c5460;
}

.status-select.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-select.status-shipped {
    background: #d4edda;
    color: #155724;
}

.status-select.status-delivered {
    background: #d4edda;
    color: #155724;
}

.status-select.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.alert {
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-success {
    background: #e6f9ef;
    color: #1a7f4b;
    border: 1px solid #b5e3cb;
}

.alert-error {
    background: #fdecea;
    color: #b0413e;
    border: 1px solid #f5c6cb;
}
</style>

<?php include __DIR__ . '/../partials/footer.php'; ?>
