<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/path.php';
require_once __DIR__ . '/../config/orders.php';

requireAdmin();

$page_title = 'Detail Pesanan - Admin Veloce';

// Handle cancellation approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $orderId = (int)$_POST['order_id'];
        
        if ($_POST['action'] === 'approve_cancellation') {
            if (approveCancellation($orderId)) {
                $_SESSION['admin_flash'] = [
                    'type' => 'success',
                    'message' => 'Pembatalan pesanan berhasil disetujui.'
                ];
            } else {
                $_SESSION['admin_flash'] = [
                    'type' => 'error',
                    'message' => 'Gagal menyetujui pembatalan.'
                ];
            }
        } elseif ($_POST['action'] === 'reject_cancellation') {
            if (rejectCancellation($orderId)) {
                $_SESSION['admin_flash'] = [
                    'type' => 'success',
                    'message' => 'Permintaan pembatalan ditolak.'
                ];
            } else {
                $_SESSION['admin_flash'] = [
                    'type' => 'error',
                    'message' => 'Gagal menolak pembatalan.'
                ];
            }
        }
        header("Location: " . url('admin/order-detail.php?id=' . $orderId));
        exit;
    }
}

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$order = getOrderByIdAdmin($orderId);

if (!$order) {
    header("Location: " . url('admin/orders.php'));
    exit;
}

include __DIR__ . '/../partials/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Detail Pesanan #<?php echo htmlspecialchars($order['order_number']); ?></h1>
        <a href="<?php echo url('admin/orders.php'); ?>" class="btn-add">Kembali ke Daftar Pesanan</a>
    </div>

    <?php if (isset($_SESSION['admin_flash'])): ?>
        <div class="alert alert-<?php echo $_SESSION['admin_flash']['type']; ?>" style="margin-top: 20px;">
            <?php echo htmlspecialchars($_SESSION['admin_flash']['message']); ?>
        </div>
        <?php unset($_SESSION['admin_flash']); ?>
    <?php endif; ?>

    <?php if ($order['cancellation_requested']): ?>
        <div class="cancellation-alert" style="margin-top: 20px; padding: 20px; background: #fff3cd; border-radius: 10px; border-left: 4px solid #ffc107;">
            <h3 style="color: #856404; margin-bottom: 10px;"><i class="fas fa-exclamation-triangle"></i> Permintaan Pembatalan Pesanan</h3>
            <p style="color: #856404; margin-bottom: 10px;"><strong>Alasan:</strong> <?php echo htmlspecialchars($order['cancellation_reason']); ?></p>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="approve_cancellation">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <button type="submit" class="btn-success" onclick="return confirm('Setujui pembatalan pesanan ini?');" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                        <i class="fas fa-check"></i> Setujui Pembatalan
                    </button>
                </form>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="reject_cancellation">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <button type="submit" class="btn-danger" onclick="return confirm('Tolak permintaan pembatalan?');" style="background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                        <i class="fas fa-times"></i> Tolak Pembatalan
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="order-detail-grid">
        <div class="order-detail-section">
            <h3>Informasi Pesanan</h3>
            <table class="detail-table">
                <tr>
                    <td><strong>No. Pesanan</strong></td>
                    <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                </tr>
                <tr>
                    <td><strong>Username</strong></td>
                    <td><?php echo htmlspecialchars($order['username'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td><strong>Tanggal</strong></td>
                    <td><?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?></td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo getOrderStatusLabel($order['status']); ?></span></td>
                </tr>
            </table>
        </div>

        <div class="order-detail-section">
            <h3>Informasi Pengiriman</h3>
            <table class="detail-table">
                <tr>
                    <td><strong>Nama Penerima</strong></td>
                    <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>No. Telepon</strong></td>
                    <td><?php echo htmlspecialchars($order['phone']); ?></td>
                </tr>
                <tr>
                    <td><strong>Alamat</strong></td>
                    <td><?php echo nl2br(htmlspecialchars($order['address'])); ?></td>
                </tr>
                <?php if (!empty($order['city'])): ?>
                <tr>
                    <td><strong>Kota</strong></td>
                    <td><?php echo htmlspecialchars($order['city']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($order['postal_code'])): ?>
                <tr>
                    <td><strong>Kode Pos</strong></td>
                    <td><?php echo htmlspecialchars($order['postal_code']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($order['notes'])): ?>
                <tr>
                    <td><strong>Catatan</strong></td>
                    <td><?php echo htmlspecialchars($order['notes']); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="order-detail-section">
            <h3>Informasi Pembayaran</h3>
            <table class="detail-table">
                <tr>
                    <td><strong>Metode</strong></td>
                    <td><?php echo getPaymentMethodLabel($order['payment_method']); ?></td>
                </tr>
                <?php if ($order['payment_method'] === 'bank_transfer' && is_array($order['payment_details']) && !empty($order['payment_details']['bank_account'])): ?>
                <tr>
                    <td><strong>Rekening</strong></td>
                    <td><?php echo htmlspecialchars($order['payment_details']['bank_account']); ?></td>
                </tr>
                <?php elseif ($order['payment_method'] === 'ewallet' && is_array($order['payment_details']) && !empty($order['payment_details']['ewallet_number'])): ?>
                <tr>
                    <td><strong>No. E-Wallet</strong></td>
                    <td><?php echo htmlspecialchars($order['payment_details']['ewallet_number']); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="order-detail-section full-width">
            <h3>Produk yang Dipesan</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $item['product_category'])); ?></td>
                            <td>Rp. <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><strong>Rp. <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right;"><strong>Subtotal:</strong></td>
                        <td><strong>Rp. <?php echo number_format($order['subtotal'], 0, ',', '.'); ?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align: right;"><strong>Ongkir:</strong></td>
                        <td><strong>Rp. <?php echo number_format($order['shipping_cost'], 0, ',', '.'); ?></strong></td>
                    </tr>
                    <tr style="background: #f8faff;">
                        <td colspan="4" style="text-align: right;"><strong>Total:</strong></td>
                        <td><strong style="color: #1f3b83; font-size: 18px;">Rp. <?php echo number_format($order['total'], 0, ',', '.'); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
.order-detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.order-detail-section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.order-detail-section.full-width {
    grid-column: 1 / -1;
}

.order-detail-section h3 {
    color: #1f3b83;
    margin-bottom: 15px;
    font-size: 18px;
}

.detail-table {
    width: 100%;
    border-collapse: collapse;
}

.detail-table td {
    padding: 10px 0;
    border-bottom: 1px solid #ecf0ff;
}

.detail-table td:first-child {
    width: 40%;
    color: #666;
}

.detail-table tr:last-child td {
    border-bottom: none;
}

.status-badge {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-block;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-verified {
    background: #d1ecf1;
    color: #0c5460;
}

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-shipped {
    background: #d4edda;
    color: #155724;
}

.status-delivered {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}
</style>

<?php include __DIR__ . '/../partials/footer.php'; ?>
