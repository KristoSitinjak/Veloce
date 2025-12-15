<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/path.php';
require_once __DIR__ . '/config/orders.php';

requireLogin();

$page_title = 'Riwayat Pesanan - Veloce';

// Handle cancellation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_cancellation') {
    $orderId = (int)$_POST['order_id'];
    $reason = trim($_POST['reason'] ?? '');
    
    if (empty($reason)) {
        $_SESSION['order_flash'] = [
            'type' => 'error',
            'message' => 'Alasan pembatalan wajib diisi.'
        ];
    } else {
        if (requestCancellation($orderId, $reason)) {
            $_SESSION['order_flash'] = [
                'type' => 'success',
                'message' => 'Permintaan pembatalan berhasil dikirim. Menunggu konfirmasi admin.'
            ];
        } else {
            $_SESSION['order_flash'] = [
                'type' => 'error',
                'message' => 'Gagal mengirim permintaan pembatalan.'
            ];
        }
    }
    header("Location: " . url('orders.php?id=' . $orderId));
    exit;
}

// Get order ID if viewing details
$viewOrderId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$orderDetails = null;

if ($viewOrderId) {
    $orderDetails = getOrderById($viewOrderId);
    if (!$orderDetails) {
        header("Location: " . url('orders.php'));
        exit;
    }
}

// Get user's orders
$orders = getUserOrders();

include __DIR__ . '/partials/header.php';
?>

<section class="orders-page">
    <div class="orders-container">
        <?php if ($orderDetails): ?>
            <!-- Order Details View -->
            <div class="order-details-header">
                <a href="<?php echo url('orders.php'); ?>" class="btn-back-link">
                    <i class="fas fa-arrow-left"></i> Kembali ke Riwayat Pesanan
                </a>
                <h1>Detail Pesanan #<?php echo htmlspecialchars($orderDetails['order_number']); ?></h1>
                <p>Tanggal: <?php echo date('d F Y, H:i', strtotime($orderDetails['created_at'])); ?></p>
            </div>

            <div class="order-details-layout">
                <div class="order-details-main">
                    <div class="order-section">
                        <h3><i class="fas fa-shopping-bag"></i> Produk yang Dipesan</h3>
                        <div class="order-items-list">
                            <?php foreach ($orderDetails['items'] as $item): ?>
                                <div class="order-item-row">
                                    <div class="order-item-image">
                                        <img src="<?php echo url('assets/img/' . ($item['product_image'] ?: 'no-image.jpg')); ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                             onerror="this.src='https://via.placeholder.com/80x80/23398c/ffffff?text=No+Image'">
                                    </div>
                                    <div class="order-item-details">
                                        <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                        <span class="item-category"><?php echo ucfirst(str_replace('_', ' ', $item['product_category'])); ?></span>
                                        <div class="item-qty">Jumlah: <?php echo $item['quantity']; ?> x Rp. <?php echo number_format($item['price'], 0, ',', '.'); ?></div>
                                    </div>
                                    <div class="order-item-price">
                                        <strong>Rp. <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="order-section">
                        <h3><i class="fas fa-shipping-fast"></i> Informasi Pengiriman</h3>
                        <div class="shipping-info-grid">
                            <div class="info-item">
                                <span class="info-label">Nama Penerima</span>
                                <span class="info-value"><?php echo htmlspecialchars($orderDetails['full_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">No. Telepon</span>
                                <span class="info-value"><?php echo htmlspecialchars($orderDetails['phone']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Alamat</span>
                                <span class="info-value"><?php echo nl2br(htmlspecialchars($orderDetails['address'])); ?></span>
                            </div>
                            <?php if (!empty($orderDetails['city'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Kota</span>
                                    <span class="info-value"><?php echo htmlspecialchars($orderDetails['city']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($orderDetails['postal_code'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Kode Pos</span>
                                    <span class="info-value"><?php echo htmlspecialchars($orderDetails['postal_code']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($orderDetails['notes'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Catatan</span>
                                    <span class="info-value"><?php echo htmlspecialchars($orderDetails['notes']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="order-details-sidebar">
                    <div class="order-summary-card">
                        <h3>Ringkasan Pesanan</h3>
                        
                        <div class="status-badge status-<?php echo $orderDetails['status']; ?>">
                            <?php echo getOrderStatusLabel($orderDetails['status']); ?>
                        </div>

                        <div class="summary-info">
                            <div class="summary-row">
                                <span>Metode Pembayaran</span>
                                <strong><?php echo getPaymentMethodLabel($orderDetails['payment_method']); ?></strong>
                            </div>

                            <?php if ($orderDetails['payment_method'] === 'bank_transfer' && !empty($orderDetails['payment_details']['bank_account'])): ?>
                                <div class="summary-row">
                                    <span>Rekening</span>
                                    <strong><?php echo htmlspecialchars($orderDetails['payment_details']['bank_account']); ?></strong>
                                </div>
                            <?php elseif ($orderDetails['payment_method'] === 'ewallet' && !empty($orderDetails['payment_details']['ewallet_number'])): ?>
                                <div class="summary-row">
                                    <span>No. E-Wallet</span>
                                    <strong><?php echo htmlspecialchars($orderDetails['payment_details']['ewallet_number']); ?></strong>
                                </div>
                            <?php endif; ?>

                            <div class="summary-row">
                                <span>Subtotal</span>
                                <strong>Rp. <?php echo number_format($orderDetails['subtotal'], 0, ',', '.'); ?></strong>
                            </div>
                            <div class="summary-row">
                                <span>Ongkir</span>
                                <strong>Rp. <?php echo number_format($orderDetails['shipping_cost'], 0, ',', '.'); ?></strong>
                            </div>
                            <div class="summary-row total">
                                <span>Total</span>
                                <strong>Rp. <?php echo number_format($orderDetails['total'], 0, ',', '.'); ?></strong>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['order_flash'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['order_flash']['type']; ?>" style="margin-top: 20px;">
                            <?php echo htmlspecialchars($_SESSION['order_flash']['message']); ?>
                        </div>
                        <?php unset($_SESSION['order_flash']); ?>
                    <?php endif; ?>

                    <?php if ($orderDetails['cancellation_requested']): ?>
                        <div class="cancellation-status" style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 10px; border: 1px solid #ffc107;">
                            <h4 style="color: #856404; margin-bottom: 10px;"><i class="fas fa-exclamation-triangle"></i> Permintaan Pembatalan</h4>
                            <p style="color: #856404; margin-bottom: 5px;"><strong>Status:</strong> Menunggu konfirmasi admin</p>
                            <p style="color: #856404; margin: 0;"><strong>Alasan:</strong> <?php echo htmlspecialchars($orderDetails['cancellation_reason']); ?></p>
                        </div>
                    <?php elseif ($orderDetails['status'] !== 'cancelled' && $orderDetails['status'] !== 'delivered'): ?>
                        <div class="cancellation-request" style="margin-top: 20px;">
                            <button onclick="document.getElementById('cancellation-form').style.display='block'; this.style.display='none';" class="btn-cancel-request" style="width: 100%; padding: 12px; background: #dc3545; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">
                                <i class="fas fa-times-circle"></i> Request Pembatalan
                            </button>
                            
                            <form id="cancellation-form" method="POST" style="display: none; margin-top: 15px;">
                                <input type="hidden" name="action" value="request_cancellation">
                                <input type="hidden" name="order_id" value="<?php echo $orderDetails['id']; ?>">
                                
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Alasan Pembatalan *</label>
                                <select name="reason" required style="width: 100%; padding: 10px; border: 2px solid #ecf0ff; border-radius: 10px; margin-bottom: 15px;">
                                    <option value="">Pilih alasan...</option>
                                    <option value="Salah pesan produk">Salah pesan produk</option>
                                    <option value="Ingin ganti metode pembayaran">Ingin ganti metode pembayaran</option>
                                    <option value="Ingin ganti alamat pengiriman">Ingin ganti alamat pengiriman</option>
                                    <option value="Berubah pikiran">Berubah pikiran</option>
                                    <option value="Menemukan harga lebih murah">Menemukan harga lebih murah</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                
                                <div style="display: flex; gap: 10px;">
                                    <button type="submit" class="btn-primary" style="flex: 1;">Kirim Permintaan</button>
                                    <button type="button" onclick="this.closest('form').style.display='none'; document.querySelector('.btn-cancel-request').style.display='block';" class="btn-outline" style="flex: 1;">Batal</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Order History List -->
            <div class="orders-header">
                <h1>Riwayat Pesanan</h1>
                <p>Lihat semua pesanan yang pernah Anda buat</p>
            </div>

            <?php if (empty($orders)): ?>
                <div class="orders-empty">
                    <i class="fas fa-receipt"></i>
                    <h3>Belum Ada Pesanan</h3>
                    <p>Anda belum pernah melakukan pemesanan. Mulai belanja sekarang!</p>
                    <a href="<?php echo url('produk.php'); ?>" class="btn-primary">Lihat Produk</a>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-card-header">
                                <div class="order-number">
                                    <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    <span class="order-date"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="order-status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo getOrderStatusLabel($order['status']); ?>
                                </div>
                            </div>
                            <div class="order-card-body">
                                <div class="order-info-row">
                                    <div class="order-info-item">
                                        <i class="fas fa-user"></i>
                                        <span><?php echo htmlspecialchars($order['full_name']); ?></span>
                                    </div>
                                    <div class="order-info-item">
                                        <i class="fas fa-credit-card"></i>
                                        <span><?php echo getPaymentMethodLabel($order['payment_method']); ?></span>
                                    </div>
                                    <div class="order-info-item">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <strong>Rp. <?php echo number_format($order['total'], 0, ',', '.'); ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="order-card-footer">
                                <a href="<?php echo url('orders.php?id=' . $order['id']); ?>" class="btn-view-order">
                                    Lihat Detail <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<style>
.orders-page {
    padding: 40px 8%;
    background: #f4f6fd;
    min-height: calc(100vh - 200px);
}

.orders-container {
    max-width: 1400px;
    margin: 0 auto;
}

.orders-header {
    text-align: center;
    margin-bottom: 40px;
}

.orders-header h1 {
    color: #1f3b83;
    font-size: 32px;
    margin-bottom: 10px;
}

.orders-header p {
    color: #666;
    font-size: 16px;
}

.orders-empty {
    background: white;
    border-radius: 20px;
    padding: 60px 40px;
    text-align: center;
    box-shadow: 0 15px 30px rgba(31, 59, 131, 0.08);
}

.orders-empty i {
    font-size: 80px;
    color: #cbd5f5;
    margin-bottom: 20px;
}

.orders-empty h3 {
    color: #1f3b83;
    margin-bottom: 10px;
    font-size: 24px;
}

.orders-empty p {
    color: #666;
    margin-bottom: 30px;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.order-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: transform 0.2s;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.12);
}

.order-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    background: #f8faff;
    border-bottom: 1px solid #ecf0ff;
}

.order-number strong {
    color: #1f3b83;
    font-size: 18px;
    display: block;
    margin-bottom: 5px;
}

.order-date {
    color: #777;
    font-size: 13px;
}

.order-status-badge {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
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

.order-card-body {
    padding: 20px 25px;
}

.order-info-row {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

.order-info-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
}

.order-info-item i {
    color: #5b8af0;
}

.order-card-footer {
    padding: 15px 25px;
    background: #f8faff;
    border-top: 1px solid #ecf0ff;
    text-align: right;
}

.btn-view-order {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: #1f3b83;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: background 0.3s;
}

.btn-view-order:hover {
    background: #4a75d4;
}

/* Order Details */
.order-details-header {
    margin-bottom: 30px;
}

.btn-back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #1f3b83;
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 15px;
    transition: color 0.3s;
}

.btn-back-link:hover {
    color: #5b8af0;
}

.order-details-header h1 {
    color: #1f3b83;
    font-size: 28px;
    margin-bottom: 5px;
}

.order-details-header p {
    color: #666;
}

.order-details-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.order-details-main {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.order-section {
    background: white;
    border-radius: 18px;
    padding: 25px;
    box-shadow: 0 15px 30px rgba(31, 59, 131, 0.08);
}

.order-section h3 {
    color: #1f3b83;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 20px;
}

.order-section h3 i {
    color: #5b8af0;
}

.order-items-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.order-item-row {
    display: grid;
    grid-template-columns: 80px 1fr auto;
    gap: 15px;
    padding: 15px;
    border: 1px solid #ecf0ff;
    border-radius: 12px;
    align-items: center;
}

.order-item-image img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.order-item-details h4 {
    color: #1f3b83;
    margin-bottom: 5px;
    font-size: 16px;
}

.item-category {
    font-size: 12px;
    color: #5b8af0;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: block;
    margin-bottom: 5px;
}

.item-qty {
    font-size: 14px;
    color: #666;
}

.order-item-price {
    text-align: right;
    font-size: 18px;
    color: #1f3b83;
}

.shipping-info-grid {
    display: grid;
    gap: 15px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 12px 0;
    border-bottom: 1px solid #ecf0ff;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #555;
    font-size: 14px;
}

.info-value {
    color: #333;
    font-size: 15px;
}

.order-summary-card {
    background: white;
    border-radius: 18px;
    padding: 25px;
    box-shadow: 0 15px 30px rgba(31, 59, 131, 0.08);
    position: sticky;
    top: 20px;
}

.order-summary-card h3 {
    color: #1f3b83;
    margin-bottom: 15px;
    font-size: 20px;
}

.status-badge {
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 20px;
}

.summary-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    color: #333;
}

.summary-row.total {
    font-size: 18px;
    color: #1f3b83;
    border-top: 1px solid #ecf0ff;
    padding-top: 12px;
    margin-top: 8px;
}

@media (max-width: 900px) {
    .order-details-layout {
        grid-template-columns: 1fr;
    }

    .order-item-row {
        grid-template-columns: 60px 1fr;
        grid-template-rows: auto auto;
    }

    .order-item-price {
        grid-column: span 2;
        text-align: left;
        margin-top: 10px;
    }

    .order-summary-card {
        position: static;
    }
}
</style>

<?php include __DIR__ . '/partials/footer.php'; ?>
