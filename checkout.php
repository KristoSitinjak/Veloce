<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/path.php';
require_once __DIR__ . '/config/cart.php';
require_once __DIR__ . '/config/orders.php';

// Temporary error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

requireLogin();

$items = getCartItems();
$totals = getCartTotals();
$shipping = getShippingInfo();

// Redirect back to cart if no shipping info (but allow empty cart for success page)
// if (empty($items)) {
//     header("Location: " . url('cart.php'));
//     exit;
// }

if (empty($shipping['full_name']) || empty($shipping['payment_method'])) {
    // Only redirect if not on success page
    if (!isset($_GET['success'])) {
        header("Location: " . url('cart.php'));
        exit;
    }
}

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_payment') {
    // Save order to database
    $orderData = [
        'full_name' => $shipping['full_name'],
        'phone' => $shipping['phone'],
        'address' => $shipping['address'],
        'city' => $shipping['city'] ?? '',
        'postal_code' => $shipping['postal_code'] ?? '',
        'notes' => $shipping['notes'] ?? '',
        'delivery_type' => $shipping['delivery_type'] ?? 'regular',
        'payment_method' => $shipping['payment_method'],
        'bank_account' => $shipping['bank_account'] ?? '',
        'ewallet_number' => $shipping['ewallet_number'] ?? '',
        'subtotal' => $totals['subtotal'],
        'shipping_cost' => $totals['shipping'],
        'total' => $totals['grand_total']
    ];
    
    $orderId = saveOrder($orderData, $items);
    
    if ($orderId) {
        // Clear cart after successful order save
        clearCart();
        unset($_SESSION['shipping_info']);
        $_SESSION['order_confirmed'] = true;
        $_SESSION['last_order_id'] = $orderId;
        header("Location: " . url('checkout.php?success=1'));
        exit;
    } else {
        // Error message already set by saveOrder() in session
        if (!isset($_SESSION['cart_flash'])) {
            $_SESSION['cart_flash'] = [
                'type' => 'error',
                'message' => 'Gagal menyimpan pesanan. Silakan coba lagi.'
            ];
        }
        header("Location: " . url('checkout.php'));
        exit;
    }
}

$page_title = 'Konfirmasi Pembayaran - Veloce';
include __DIR__ . '/partials/header.php';
?>

<section class="checkout-page">
    <div class="checkout-container">
        <?php if (isset($_SESSION['cart_flash'])): ?>
            <div class="alert alert-<?php echo $_SESSION['cart_flash']['type']; ?>" style="margin-bottom: 20px;">
                <?php echo htmlspecialchars($_SESSION['cart_flash']['message']); ?>
            </div>
            <?php unset($_SESSION['cart_flash']); ?>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="checkout-success">
                <i class="fas fa-check-circle"></i>
                <h2>Pembayaran Berhasil Dikonfirmasi!</h2>
                <p>Terima kasih atas pesanan Anda. Pesanan Anda sedang <strong>menunggu verifikasi dari admin</strong>.</p>
                <p>Tim kami akan segera memeriksa dan memproses pesanan Anda. Kami akan menghubungi Anda melalui nomor telepon yang telah Anda berikan untuk konfirmasi lebih lanjut.</p>
                
                <div class="order-status-info">
                    <div class="status-step">
                        <i class="fas fa-check-circle"></i>
                        <span>Pesanan Dikonfirmasi</span>
                    </div>
                    <div class="status-arrow">→</div>
                    <div class="status-step active">
                        <i class="fas fa-clock"></i>
                        <span>Menunggu Verifikasi Admin</span>
                    </div>
                    <div class="status-arrow">→</div>
                    <div class="status-step">
                        <i class="fas fa-shipping-fast"></i>
                        <span>Pesanan Diproses</span>
                    </div>
                </div>

                <div class="success-actions">
                    <a href="<?php echo url('index.php'); ?>" class="btn-primary">Kembali ke Beranda</a>
                    <a href="<?php echo url('produk.php'); ?>" class="btn-outline">Lanjut Belanja</a>
                </div>
            </div>
        <?php else: ?>
            <div class="checkout-header">
                <h1>Konfirmasi Pembayaran</h1>
                <p>Periksa kembali pesanan Anda sebelum melakukan konfirmasi pembayaran.</p>
            </div>

            <div class="checkout-layout">
                <div class="checkout-main">
                    <div class="checkout-section">
                        <h3><i class="fas fa-shopping-bag"></i> Ringkasan Pesanan</h3>
                        <div class="order-items">
                            <?php foreach ($items as $item): ?>
                                <?php $line_total = (int) $item['qty'] * (int) $item['harga']; ?>
                                <div class="order-item">
                                    <div class="order-item-image">
                                        <img src="<?php echo url('assets/img/' . ($item['gambar'] ?: 'no-image.jpg')); ?>" 
                                             alt="<?php echo htmlspecialchars($item['nama']); ?>"
                                             onerror="this.src='https://via.placeholder.com/80x80/23398c/ffffff?text=No+Image'">
                                    </div>
                                    <div class="order-item-info">
                                        <h4><?php echo htmlspecialchars($item['nama']); ?></h4>
                                        <span class="order-item-category"><?php echo ucfirst(str_replace('_', ' ', $item['kategori'])); ?></span>
                                        <div class="order-item-qty">Jumlah: <?php echo $item['qty']; ?> x Rp. <?php echo number_format($item['harga'], 0, ',', '.'); ?></div>
                                    </div>
                                    <div class="order-item-total">
                                        <strong>Rp. <?php echo number_format($line_total, 0, ',', '.'); ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="checkout-section">
                        <h3><i class="fas fa-shipping-fast"></i> Informasi Pengiriman</h3>
                        <div class="shipping-details">
                            <div class="detail-row">
                                <span class="detail-label">Nama Lengkap</span>
                                <span class="detail-value"><?php echo htmlspecialchars($shipping['full_name']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">No. Telepon</span>
                                <span class="detail-value"><?php echo htmlspecialchars($shipping['phone']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Alamat</span>
                                <span class="detail-value"><?php echo nl2br(htmlspecialchars($shipping['address'])); ?></span>
                            </div>
                            <?php if (!empty($shipping['city'])): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Kota</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($shipping['city']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($shipping['postal_code'])): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Kode Pos</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($shipping['postal_code']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($shipping['notes'])): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Catatan</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($shipping['notes']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="checkout-sidebar">
                    <div class="payment-info-card">
                        <h3><i class="fas fa-credit-card"></i> Informasi Pembayaran</h3>
                        
                        <div class="payment-method-display">
                            <span class="payment-label">Metode Pembayaran</span>
                            <span class="payment-value">
                                <?php
                                    $method = $shipping['payment_method'] ?? 'cod';
                                    if ($method === 'bank_transfer') {
                                        echo 'Transfer Bank';
                                    } elseif ($method === 'ewallet') {
                                        echo 'E-Wallet';
                                    } else {
                                        echo 'COD (Bayar di Tempat)';
                                    }
                                ?>
                            </span>
                        </div>

                        <?php if ($method === 'bank_transfer'): ?>
                            <div class="payment-details">
                                <p class="payment-instruction">Silakan transfer ke rekening berikut:</p>
                                <div class="bank-account-box">
                                    <i class="fas fa-university"></i>
                                    <div>
                                        <strong>Bank BCA</strong><br>
                                        <span style="font-size: 18px; font-weight: 700;">1234567890</span><br>
                                        <span>a.n. Veloce Store</span>
                                    </div>
                                </div>
                                <div class="bank-account-box" style="margin-top: 10px;">
                                    <i class="fas fa-university"></i>
                                    <div>
                                        <strong>Bank Mandiri</strong><br>
                                        <span style="font-size: 18px; font-weight: 700;">9876543210</span><br>
                                        <span>a.n. Veloce Store</span>
                                    </div>
                                </div>
                                <p class="payment-note">Setelah transfer, harap konfirmasi pembayaran dengan menekan tombol di bawah.</p>
                            </div>
                        <?php elseif ($method === 'ewallet'): ?>
                            <div class="payment-details">
                                <p class="payment-instruction">Silakan transfer ke nomor e-wallet berikut:</p>
                                <div class="ewallet-box">
                                    <i class="fas fa-mobile-alt"></i>
                                    <div>
                                        <strong>GoPay / OVO / Dana</strong><br>
                                        <span style="font-size: 18px; font-weight: 700;">0812-3456-7890</span><br>
                                        <span>a.n. Veloce Store</span>
                                    </div>
                                </div>
                                <p class="payment-note">Setelah transfer, harap konfirmasi pembayaran dengan menekan tombol di bawah.</p>
                            </div>
                        <?php else: ?>
                            <div class="payment-details">
                                <p class="payment-instruction">Pembayaran akan dilakukan saat barang diterima (COD).</p>
                                <p class="payment-note">Pastikan Anda memiliki uang tunai yang cukup saat barang tiba.</p>
                            </div>
                        <?php endif; ?>

                        <div class="order-summary">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <strong>Rp. <?php echo number_format($totals['subtotal'], 0, ',', '.'); ?></strong>
                            </div>
                            <div class="summary-row">
                                <span>Ongkir</span>
                                <strong>Rp. <?php echo number_format($totals['shipping'], 0, ',', '.'); ?></strong>
                            </div>
                            <div class="summary-row total">
                                <span>Total Pembayaran</span>
                                <strong>Rp. <?php echo number_format($totals['grand_total'], 0, ',', '.'); ?></strong>
                            </div>
                        </div>

                        <form method="POST" action="<?php echo url('checkout.php'); ?>">
                            <input type="hidden" name="action" value="confirm_payment">
                            <button type="submit" class="btn-confirm">
                                <i class="fas fa-check"></i> Konfirmasi Pembayaran
                            </button>
                        </form>

                        <a href="<?php echo url('cart.php'); ?>" class="btn-back">
                            <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.checkout-page {
    padding: 40px 8%;
    background: #f4f6fd;
    min-height: calc(100vh - 200px);
}

.checkout-container {
    max-width: 1400px;
    margin: 0 auto;
}

.checkout-success {
    background: white;
    border-radius: 20px;
    padding: 60px 40px;
    text-align: center;
    box-shadow: 0 15px 30px rgba(31, 59, 131, 0.08);
}

.checkout-success i {
    font-size: 80px;
    color: #1a7f4b;
    margin-bottom: 20px;
}

.checkout-success h2 {
    color: #1f3b83;
    margin-bottom: 15px;
    font-size: 32px;
}

.checkout-success p {
    color: #666;
    font-size: 16px;
    margin-bottom: 30px;
}

.success-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.order-status-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin: 30px 0;
    padding: 25px;
    background: #f8faff;
    border-radius: 12px;
}

.status-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 15px;
    border-radius: 10px;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    min-width: 140px;
}

.status-step i {
    font-size: 32px;
    color: #cbd5f5;
}

.status-step span {
    font-size: 13px;
    color: #666;
    text-align: center;
    font-weight: 500;
}

.status-step.active {
    background: linear-gradient(135deg, #23398c, #1f3b83);
}

.status-step.active i {
    color: #f7b500;
}

.status-step.active span {
    color: white;
}

.status-arrow {
    font-size: 24px;
    color: #cbd5f5;
    font-weight: bold;
}


.checkout-header {
    text-align: center;
    margin-bottom: 40px;
}

.checkout-header h1 {
    color: #1f3b83;
    font-size: 32px;
    margin-bottom: 10px;
}

.checkout-header p {
    color: #666;
    font-size: 16px;
}

.checkout-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.checkout-main {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.checkout-section {
    background: white;
    border-radius: 18px;
    padding: 25px;
    box-shadow: 0 15px 30px rgba(31, 59, 131, 0.08);
}

.checkout-section h3 {
    color: #1f3b83;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 20px;
}

.checkout-section h3 i {
    color: #5b8af0;
}

.order-items {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.order-item {
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

.order-item-info h4 {
    color: #1f3b83;
    margin-bottom: 5px;
    font-size: 16px;
}

.order-item-category {
    font-size: 12px;
    color: #5b8af0;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: block;
    margin-bottom: 5px;
}

.order-item-qty {
    font-size: 14px;
    color: #666;
}

.order-item-total {
    text-align: right;
    font-size: 18px;
    color: #1f3b83;
}

.shipping-details {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #ecf0ff;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #555;
    min-width: 120px;
}

.detail-value {
    color: #333;
    text-align: right;
    flex: 1;
}

.payment-info-card {
    background: white;
    border-radius: 18px;
    padding: 25px;
    box-shadow: 0 15px 30px rgba(31, 59, 131, 0.08);
    position: sticky;
    top: 20px;
}

.payment-info-card h3 {
    color: #1f3b83;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 20px;
}

.payment-info-card h3 i {
    color: #5b8af0;
}

.payment-method-display {
    display: flex;
    justify-content: space-between;
    padding: 15px;
    background: #f8faff;
    border-radius: 10px;
    margin-bottom: 20px;
}

.payment-label {
    font-weight: 600;
    color: #555;
}

.payment-value {
    color: #1f3b83;
    font-weight: 700;
}

.payment-details {
    margin-bottom: 20px;
}

.payment-instruction {
    color: #666;
    margin-bottom: 15px;
    font-size: 14px;
}

.bank-account-box,
.ewallet-box {
    background: linear-gradient(135deg, #23398c, #1f3b83);
    color: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 15px;
}

.bank-account-box i,
.ewallet-box i {
    font-size: 24px;
    margin-bottom: 10px;
    display: block;
}

.bank-account-box strong,
.ewallet-box strong {
    font-size: 18px;
    display: block;
}

.payment-note {
    font-size: 13px;
    color: #777;
    font-style: italic;
}

.order-summary {
    border-top: 2px solid #ecf0ff;
    padding-top: 15px;
    margin-bottom: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    color: #333;
}

.summary-row.total {
    font-size: 18px;
    color: #1f3b83;
    border-top: 1px solid #ecf0ff;
    padding-top: 10px;
    margin-top: 10px;
}

.btn-confirm {
    width: 100%;
    background: #1a7f4b;
    color: white;
    padding: 15px;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 10px;
}

.btn-confirm:hover {
    background: #156b3f;
}

.btn-back {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    border: 1px solid #1f3b83;
    color: #1f3b83;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-back:hover {
    background: #1f3b83;
    color: white;
}

@media (max-width: 900px) {
    .checkout-layout {
        grid-template-columns: 1fr;
    }

    .order-item {
        grid-template-columns: 60px 1fr;
        grid-template-rows: auto auto;
    }

    .order-item-total {
        grid-column: span 2;
        text-align: left;
        margin-top: 10px;
    }

    .payment-info-card {
        position: static;
    }
    
    .order-status-info {
        flex-direction: column;
        gap: 15px;
    }
    
    .status-arrow {
        transform: rotate(90deg);
    }
    
    .status-step {
        width: 100%;
        max-width: 250px;
    }
}
</style>

<?php include __DIR__ . '/partials/footer.php'; ?>
