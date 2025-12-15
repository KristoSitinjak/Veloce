<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/path.php';
require_once __DIR__ . '/config/cart.php';

function setCartFlash($type, $message) {
    $_SESSION['cart_flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $redirect = url('cart.php');
    $success = false;
    $message = 'Aksi tidak dikenal.';

    switch ($action) {
        case 'add':
            $result = addToCart($_POST['product_id'] ?? 0, $_POST['kategori'] ?? '', $_POST['qty'] ?? 1);
            if ($result === 'login_required') {
                $_SESSION['login_redirect'] = url('cart.php');
                $_SESSION['cart_flash'] = [
                    'type' => 'error',
                    'message' => 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.'
                ];
                header("Location: " . url('login.php'));
                exit;
            }
            $success = $result;
            $message = $success ? 'Produk berhasil ditambahkan ke keranjang.' : 'Gagal menambahkan produk ke keranjang.';
            break;

        case 'update_qty':
            $success = updateCartQty($_POST['product_id'] ?? 0, $_POST['kategori'] ?? '', $_POST['qty'] ?? 1);
            $message = $success ? 'Jumlah produk diperbarui.' : 'Gagal memperbarui jumlah produk.';
            break;

        case 'remove':
            $success = removeCartItem($_POST['product_id'] ?? 0, $_POST['kategori'] ?? '');
            $message = $success ? 'Produk dihapus dari keranjang.' : 'Gagal menghapus produk dari keranjang.';
            break;

        case 'clear':
            clearCart();
            $success = true;
            $message = 'Keranjang dikosongkan.';
            break;

        case 'save_shipping':
            $payment = $_POST['payment_method'] ?? 'cod';
            $allowedPayments = ['cod', 'bank_transfer', 'ewallet'];
            if (!in_array($payment, $allowedPayments, true)) {
                $payment = 'cod';
            }

            $shippingData = [
                'full_name' => $_POST['full_name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'city' => $_POST['city'] ?? '',
                'postal_code' => $_POST['postal_code'] ?? '',
                'notes' => $_POST['notes'] ?? '',
                'delivery_type' => $_POST['delivery_type'] ?? 'regular',
                'payment_method' => $payment,
                'bank_account' => $_POST['bank_account'] ?? '',
                'ewallet_number' => $_POST['ewallet_number'] ?? ''
            ];

            $required = ['full_name', 'phone', 'address'];
            $missing = array_filter($required, function ($field) use ($shippingData) {
                return trim($shippingData[$field]) === '';
            });

            if ($payment === 'bank_transfer' && trim($shippingData['bank_account']) === '') {
                $success = false;
                $message = 'Nomor rekening wajib diisi untuk pembayaran transfer bank.';
            } elseif ($payment === 'ewallet' && trim($shippingData['ewallet_number']) === '') {
                $success = false;
                $message = 'Nomor E-Wallet wajib diisi.';
            } elseif (!empty($missing)) {
                $success = false;
                $message = 'Nama lengkap, nomor telepon, dan alamat wajib diisi.';
            } else {
                saveShippingInfo($shippingData);
                $success = true;
                $message = 'Data pengiriman dan metode pembayaran disimpan.';
                $redirect = url('checkout.php');
            }
            break;
    }

    setCartFlash($success ? 'success' : 'error', $message);
    
    // Don't show flash message on checkout page, only on cart
    if ($redirect === url('checkout.php')) {
        unset($_SESSION['cart_flash']);
    }
    
    header("Location: $redirect");
    exit;
}

$flash = $_SESSION['cart_flash'] ?? null;
unset($_SESSION['cart_flash']);

$items = getCartItems();
$totals = getCartTotals();
$shipping = getShippingInfo();

$page_title = 'Keranjang Belanja - Veloce';
include __DIR__ . '/partials/header.php';
?>

<section class="cart-page">
    <div class="cart-page-header">
        <div>
            <h1>Keranjang Belanja</h1>
            <p>Lihat ringkasan pesanan dan lengkapi data pengiriman Anda.</p>
        </div>
        <?php if (!empty($items)): ?>
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="<?php echo url('produk.php'); ?>" class="btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fas fa-arrow-left"></i> Lanjut Belanja
                </a>
                <form action="<?php echo url('cart.php'); ?>" method="POST" style="margin: 0;">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn-outline" onclick="return confirm('Kosongkan seluruh keranjang?');" style="display: inline-flex; align-items: center; gap: 5px;">
                        <i class="fas fa-trash"></i> Kosongkan
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($flash): ?>
        <div class="alert <?php echo $flash['type'] === 'success' ? 'alert-success' : 'alert-error'; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="cart-layout">
        <div class="cart-main">
            <?php if (empty($items)): ?>
                <div class="cart-empty">
                    <i class="fas fa-shopping-basket"></i>
                    <h3>Keranjang masih kosong</h3>
                    <p>Mulai belanja dan tambahkan produk favorit Anda.</p>
                    <a href="<?php echo url('produk.php'); ?>" class="btn-primary">Lihat Produk</a>
                </div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <?php
                        $line_total = (int) $item['qty'] * (int) $item['harga'];
                    ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <img src="<?php echo url('assets/img/' . ($item['gambar'] ?: 'no-image.jpg')); ?>" 
                                 alt="<?php echo htmlspecialchars($item['nama']); ?>"
                                 onerror="this.src='https://via.placeholder.com/80x80/23398c/ffffff?text=No+Image'">
                        </div>
                        <div class="cart-item-info">
                            <h3><?php echo htmlspecialchars($item['nama']); ?></h3>
                            <span class="cart-item-category"><?php echo ucfirst(str_replace('_', ' ', $item['kategori'])); ?></span>
                            <div class="cart-item-price">Rp. <?php echo number_format($item['harga'], 0, ',', '.'); ?></div>
                        </div>
                        <div class="cart-item-actions">
                            <form action="<?php echo url('cart.php'); ?>" method="POST" class="qty-form">
                                <input type="hidden" name="action" value="update_qty">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="kategori" value="<?php echo $item['kategori']; ?>">
                                <label for="qty-<?php echo $item['id']; ?>">Jumlah</label>
                                <input type="number" id="qty-<?php echo $item['id']; ?>" name="qty" min="1" value="<?php echo $item['qty']; ?>" onchange="this.form.submit()">
                            </form>
                            <form action="<?php echo url('cart.php'); ?>" method="POST">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="kategori" value="<?php echo $item['kategori']; ?>">
                                <button type="submit" class="link-button" onclick="return confirm('Hapus produk ini dari keranjang?');">
                                    Hapus
                                </button>
                            </form>
                        </div>
                        <div class="cart-item-total">
                            <strong>Rp. <?php echo number_format($line_total, 0, ',', '.'); ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="cart-sidebar">
            <div class="shipping-card">
                <h3>Data Pengiriman</h3>
                <p>Isi informasi pengiriman agar kami dapat memproses pesanan Anda.</p>
                <form action="<?php echo url('cart.php'); ?>" method="POST" id="shipping-form" class="shipping-form">
                    <input type="hidden" name="action" value="save_shipping">
                    
                    <label>Nama Lengkap *</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($shipping['full_name']); ?>" required>

                    <label>No. Telepon / WhatsApp *</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($shipping['phone']); ?>" required>

                    <label>Alamat Lengkap *</label>
                    <textarea name="address" rows="3" required><?php echo htmlspecialchars($shipping['address']); ?></textarea>

                    <div class="split-field">
                        <div>
                            <label>Kota / Kabupaten</label>
                            <input type="text" name="city" value="<?php echo htmlspecialchars($shipping['city']); ?>">
                        </div>
                        <div>
                            <label>Kode Pos</label>
                            <input type="text" name="postal_code" value="<?php echo htmlspecialchars($shipping['postal_code']); ?>">
                        </div>
                    </div>

                    <label>Catatan (opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Catatan untuk kurir atau penjual"><?php echo htmlspecialchars($shipping['notes']); ?></textarea>

                    <label>Metode Pengiriman *</label>
                    <div class="delivery-methods">
                        <label class="delivery-option">
                            <input type="radio" name="delivery_type" value="regular" <?php echo ($shipping['delivery_type'] ?? 'regular') === 'regular' ? 'checked' : ''; ?> onchange="updateShippingCost()">
                            <div class="delivery-info">
                                <span class="delivery-name">Regular</span>
                                <span class="delivery-time">3-5 hari</span>
                                <span class="delivery-price">Rp 10.000</span>
                            </div>
                        </label>
                        <label class="delivery-option">
                            <input type="radio" name="delivery_type" value="express" <?php echo ($shipping['delivery_type'] ?? 'regular') === 'express' ? 'checked' : ''; ?> onchange="updateShippingCost()">
                            <div class="delivery-info">
                                <span class="delivery-name">Express</span>
                                <span class="delivery-time">1-2 hari</span>
                                <span class="delivery-price">Rp 25.000</span>
                            </div>
                        </label>
                        <label class="delivery-option">
                            <input type="radio" name="delivery_type" value="instant" <?php echo ($shipping['delivery_type'] ?? 'regular') === 'instant' ? 'checked' : ''; ?> onchange="updateShippingCost()">
                            <div class="delivery-info">
                                <span class="delivery-name">Instant</span>
                                <span class="delivery-time">Same day</span>
                                <span class="delivery-price">Rp 50.000</span>
                            </div>
                        </label>
                    </div>

                    <label>Metode Pembayaran *</label>
                    <div class="payment-methods">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="cod" <?php echo ($shipping['payment_method'] ?? 'cod') === 'cod' ? 'checked' : ''; ?>>
                            <span>COD (Bayar di tempat)</span>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="bank_transfer" <?php echo ($shipping['payment_method'] ?? 'cod') === 'bank_transfer' ? 'checked' : ''; ?>>
                            <span>Transfer Bank</span>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="ewallet" <?php echo ($shipping['payment_method'] ?? 'cod') === 'ewallet' ? 'checked' : ''; ?>>
                            <span>E-Wallet (Dana, OVO, ShopeePay, dll)</span>
                        </label>
                    </div>

                    <div id="bank-account-container" style="display: <?php echo ($shipping['payment_method'] ?? '') === 'bank_transfer' ? 'block' : 'none'; ?>;">
                        <label>Nomor Rekening / Bank (jika transfer bank)</label>
                        <input type="text" name="bank_account" value="<?php echo htmlspecialchars($shipping['bank_account'] ?? ''); ?>" placeholder="Contoh: BCA 123456789 a.n. Nama Anda">
                    </div>

                    <div id="ewallet-container" style="display: <?php echo ($shipping['payment_method'] ?? '') === 'ewallet' ? 'block' : 'none'; ?>;">
                        <label>Nomor E-Wallet</label>
                        <input type="text" name="ewallet_number" value="<?php echo htmlspecialchars($shipping['ewallet_number'] ?? ''); ?>" placeholder="Contoh: 08123456789">
                    </div>

                    <label>Catatan Tambahan</label>
                    <textarea name="notes" rows="2" placeholder="Contoh: tolong kirim di jam kerja."><?php echo htmlspecialchars($shipping['notes']); ?></textarea>
                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
                        const bankContainer = document.getElementById('bank-account-container');
                        const ewalletContainer = document.getElementById('ewallet-container');

                        function togglePaymentInput() {
                            const selected = document.querySelector('input[name="payment_method"]:checked');
                            
                            // Reset
                            bankContainer.style.display = 'none';
                            ewalletContainer.style.display = 'none';

                            if (selected) {
                                if (selected.value === 'bank_transfer') {
                                    bankContainer.style.display = 'block';
                                } else if (selected.value === 'ewallet') {
                                    ewalletContainer.style.display = 'block';
                                }
                            }
                        }

                        paymentRadios.forEach(radio => {
                            radio.addEventListener('change', togglePaymentInput);
                        });
                        
                        // Run once on load just in case
                        togglePaymentInput();

                        // Update shipping cost when delivery type changes
                        const deliveryRadios = document.querySelectorAll('input[name="delivery_type"]');
                        
                        function updateShippingCost() {
                            const selected = document.querySelector('input[name="delivery_type"]:checked');
                            if (!selected) return;

                            const costs = {
                                'regular': 10000,
                                'express': 25000,
                                'instant': 50000
                            };

                            const shippingCost = costs[selected.value] || 0;
                            const subtotal = <?php echo $totals['subtotal']; ?>;
                            const grandTotal = subtotal + shippingCost;

                            // Update display in shipping form summary
                            const shippingElement = document.querySelector('.shipping-cost');
                            const totalElement = document.querySelector('.grand-total');
                            
                            if (shippingElement) {
                                shippingElement.textContent = 'Rp. ' + shippingCost.toLocaleString('id-ID');
                            }
                            if (totalElement) {
                                totalElement.textContent = 'Rp. ' + grandTotal.toLocaleString('id-ID');
                            }

                            // Update display in sidebar summary
                            const sidebarShipping = document.querySelector('#sidebar-shipping-cost');
                            const sidebarTotal = document.querySelector('#sidebar-grand-total');
                            
                            if (sidebarShipping) {
                                sidebarShipping.textContent = 'Rp. ' + shippingCost.toLocaleString('id-ID');
                            }
                            if (sidebarTotal) {
                                sidebarTotal.textContent = 'Rp. ' + grandTotal.toLocaleString('id-ID');
                            }
                        }

                        deliveryRadios.forEach(radio => {
                            radio.addEventListener('change', updateShippingCost);
                        });

                        // Initialize on page load
                        updateShippingCost();
                    });
                </script>
            </div>

            <div class="cart-summary">
                <h3>Ringkasan Pesanan</h3>
                <div class="summary-row">
                    <span>Metode Pembayaran</span>
                    <strong>
                        <?php
                            $method = $shipping['payment_method'] ?? 'cod';
                            if ($method === 'bank_transfer') {
                                echo 'Transfer Bank';
                            } elseif ($method === 'ewallet') {
                                echo 'E-Wallet';
                            } else {
                                echo 'COD (Bayar di tempat)';
                            }
                        ?>
                    </strong>
                </div>
                <?php if (($shipping['payment_method'] ?? '') === 'bank_transfer' && !empty($shipping['bank_account'])): ?>
                    <div class="summary-row">
                        <span>Detail Rekening</span>
                        <strong><?php echo htmlspecialchars($shipping['bank_account']); ?></strong>
                    </div>
                <?php endif; ?>
                <?php if (($shipping['payment_method'] ?? '') === 'ewallet' && !empty($shipping['ewallet_number'])): ?>
                    <div class="summary-row">
                        <span>Nomor E-Wallet</span>
                        <strong><?php echo htmlspecialchars($shipping['ewallet_number']); ?></strong>
                    </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span>Jumlah Item</span>
                    <strong><?php echo $totals['count']; ?> produk</strong>
                </div>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <strong>Rp. <?php echo number_format($totals['subtotal'], 0, ',', '.'); ?></strong>
                </div>
                <div class="summary-row">
                    <span>Estimasi Ongkir</span>
                    <strong id="sidebar-shipping-cost">Rp. <?php echo number_format($totals['shipping'], 0, ',', '.'); ?></strong>
                </div>
                <div class="summary-row total">
                    <span>Total Pembayaran</span>
                    <strong id="sidebar-grand-total">Rp. <?php echo number_format($totals['grand_total'], 0, ',', '.'); ?></strong>
                </div>
                <p class="summary-note">
                    Setelah data disimpan, tim kami akan menghubungi Anda untuk konfirmasi pembayaran dan pengiriman.
                </p>
                <button type="submit" form="shipping-form" class="btn-primary" style="width: 100%; margin-top: 15px;" <?php echo empty($items) ? 'disabled' : ''; ?>>
                    <?php echo empty($items) ? 'Keranjang Kosong' : 'Simpan Data Pengiriman'; ?>
                </button>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>

