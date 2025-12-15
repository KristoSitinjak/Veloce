<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/path.php';
require_once __DIR__ . '/config/profile.php';
require_once __DIR__ . '/config/orders.php';

requireLogin();

$page_title = 'Profil - Veloce';

// Get user orders
$userOrders = getUserOrders(null, 5); // Get last 5 orders

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => $_POST['full_name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'city' => $_POST['city'] ?? '',
        'postal_code' => $_POST['postal_code'] ?? ''
    ];

    // Handle avatar upload (optional)
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        $fileSize = $_FILES['avatar']['size'];

        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileSize > 2 * 1024 * 1024) { // 2MB
            $error = 'Ukuran foto maksimal 2MB.';
        } elseif (!in_array($ext, $allowedExt, true)) {
            $error = 'Format foto harus JPG, JPEG, PNG, GIF, atau WEBP.';
        } else {
            $uploadDir = __DIR__ . '/assets/img/avatars';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $userId = getUserId();
            $newName = 'avatar-' . $userId . '-' . time() . '.' . $ext;
            $destPath = $uploadDir . '/' . $newName;

            if (move_uploaded_file($fileTmp, $destPath)) {
                $data['avatar'] = $newName;
            } else {
                $error = 'Gagal mengunggah foto. Silakan coba lagi.';
            }
        }
    }

    if (empty($error) && (empty(trim($data['full_name'])) || empty(trim($data['phone'])) || empty(trim($data['address'])))) {
        $error = 'Nama lengkap, nomor telepon, dan alamat wajib diisi.';
    } elseif (empty($error)) {
        if (saveUserProfile($data)) {
            $success = 'Profil berhasil diperbarui.';
        } else {
            $error = 'Gagal menyimpan profil. Silakan coba lagi.';
        }
    }
}

$profile = getUserProfile();

include __DIR__ . '/partials/header.php';
?>

<section class="profile-page">
    <div class="profile-left-card">
        <div class="profile-avatar" onclick="openAvatarModal()">
            <?php if (!empty($profile['avatar'])): ?>
                <img src="<?php echo url('assets/img/avatars/' . $profile['avatar']); ?>"
                     alt="Foto Profil"
                     id="avatar-img-src"
                     onerror="this.style.display='none'; this.previousElementSibling.style.display='flex';">
            <?php endif; ?>
            <span <?php echo !empty($profile['avatar']) ? 'style="display:none;"' : ''; ?>>
                <?php
                    $username = getUsername() ?? '';
                    echo $username ? strtoupper(substr($username, 0, 1)) : 'U';
                ?>
            </span>
        </div>
        <p class="profile-subtitle" style="font-size: 18px; font-weight: bold; color: #1f3b83; margin-top: 10px;">
            <?php echo htmlspecialchars(getUsername() ?? 'User'); ?>
        </p>

        <!-- Link to Orders Page -->
        <a href="<?php echo url('orders.php'); ?>" style="display: block; width: 100%; padding: 12px; background: #1f3b83; color: white; border-radius: 10px; text-align: center; text-decoration: none; font-weight: 600; margin-top: 15px;">
            <i class="fas fa-shopping-bag"></i> Lihat Riwayat Pesanan
        </a>

        <!-- Logout Button -->
        <a href="<?php echo url('logout.php'); ?>" class="btn-logout" style="margin-top: 15px; width: 100%; padding: 12px; background: #dc3545; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; transition: background 0.3s;" onmouseover="this.style.background='#c82333'" onmouseout="this.style.background='#dc3545'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="profile-right-card">
        <h2>Data Pengiriman Utama</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php
        $isEditMode = (isset($_GET['action']) && $_GET['action'] === 'edit');
        ?>

        <?php if ($isEditMode): ?>
            <!-- Mode Edit -->
            <form method="POST" action="<?php echo url('profile.php'); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <div class="form-group">
                    <label for="avatar">Foto Profil</label>
                    <input type="file" id="avatar" name="avatar" class="form-control" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="full_name">Nama Lengkap *</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required
                           value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="phone">No. Telepon / WhatsApp *</label>
                    <input type="text" id="phone" name="phone" class="form-control" required
                           value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="address">Alamat Lengkap *</label>
                    <textarea id="address" name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="city">Kota / Kabupaten</label>
                    <input type="text" id="city" name="city" class="form-control"
                           value="<?php echo htmlspecialchars($profile['city'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="postal_code">Kode Pos</label>
                    <input type="text" id="postal_code" name="postal_code" class="form-control"
                           value="<?php echo htmlspecialchars($profile['postal_code'] ?? ''); ?>">
                </div>

                <div class="btn-group" style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-submit">Simpan Perubahan</button>
                    <a href="<?php echo url('profile.php'); ?>" class="btn-outline" style="text-align: center; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Batal</a>
                </div>
            </form>
        <?php else: ?>
            <!-- Mode Lihat (Read-Only) -->
            <div class="profile-view">
                <div class="view-group">
                    <label>Nama Lengkap</label>
                    <p><?php echo htmlspecialchars($profile['full_name'] ?? '-'); ?></p>
                </div>
                <div class="view-group">
                    <label>No. Telepon / WhatsApp</label>
                    <p><?php echo htmlspecialchars($profile['phone'] ?? '-'); ?></p>
                </div>
                <div class="view-group">
                    <label>Alamat Lengkap</label>
                    <p><?php echo nl2br(htmlspecialchars($profile['address'] ?? '-')); ?></p>
                </div>
                <div class="view-group">
                    <label>Kota / Kabupaten</label>
                    <p><?php echo htmlspecialchars($profile['city'] ?? '-'); ?></p>
                </div>
                <div class="view-group">
                    <label>Kode Pos</label>
                    <p><?php echo htmlspecialchars($profile['postal_code'] ?? '-'); ?></p>
                </div>

                <a href="<?php echo url('profile.php?action=edit'); ?>" class="btn-submit" style="display: block; text-align: center; text-decoration: none; margin-top: 20px;">Ubah Profil</a>
            </div>
            
            <style>
                .view-group {
                    margin-bottom: 15px;
                    border-bottom: 1px solid #eee;
                    padding-bottom: 10px;
                }
                .view-group label {
                    font-weight: bold;
                    color: #555;
                    font-size: 0.9em;
                    display: block;
                    margin-bottom: 5px;
                }
                .view-group p {
                    margin: 0;
                    color: #333;
                    font-size: 1.1em;
                }
            </style>
        <?php endif; ?>

        <div class="form-link" style="text-align: left; margin-top: 15px;">
            <a href="<?php echo url('cart.php'); ?>">Lihat Keranjang Belanja</a> &middot;
            <a href="<?php echo url('index.php'); ?>">Kembali ke Beranda</a>
        </div>
    </div>
</section>

<!-- Avatar Modal -->
<div id="avatarModal" class="avatar-modal">
    <span class="close-modal" onclick="closeAvatarModal()">&times;</span>
    <img class="avatar-modal-content" id="modal-img">
</div>

<script>
    function openAvatarModal() {
        const avatarSrc = document.getElementById('avatar-img-src');
        if (avatarSrc && avatarSrc.getAttribute('src')) {
            const modal = document.getElementById('avatarModal');
            const modalImg = document.getElementById('modal-img');
            modal.style.display = "flex";
            modalImg.src = avatarSrc.src;
        }
    }

    function closeAvatarModal() {
        document.getElementById('avatarModal').style.display = "none";
    }

    // Close when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('avatarModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Toggle order history visibility
    function toggleOrderHistory() {
        const section = document.getElementById('order-history-section');
        const icon = document.getElementById('order-toggle-icon');
        
        if (section.style.display === 'none') {
            section.style.display = 'block';
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        } else {
            section.style.display = 'none';
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
