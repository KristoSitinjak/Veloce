<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/path.php';
require_once __DIR__ . '/../config/profile.php';

requireAdmin();

$page_title = 'Profil Admin - Veloce';

$error = '';
$success = '';

// Handle avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_avatar') {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/img/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $fileType = $_FILES['avatar']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $filename = 'admin_' . getUserId() . '_' . time() . '.' . $extension;
            $targetPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
                // Delete old avatar if exists
                $oldProfile = getUserProfile();
                if (!empty($oldProfile['avatar']) && file_exists($uploadDir . $oldProfile['avatar'])) {
                    unlink($uploadDir . $oldProfile['avatar']);
                }
                
                // Save to profile
                if (saveUserProfile(['avatar' => $filename])) {
                    $success = 'Foto profil berhasil diperbarui!';
                } else {
                    $error = 'Gagal menyimpan foto profil.';
                }
            } else {
                $error = 'Gagal mengupload file.';
            }
        } else {
            $error = 'Format file tidak didukung. Gunakan JPG atau PNG.';
        }
    } else {
        $error = 'Tidak ada file yang diupload.';
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $profileData = [
        'full_name' => $_POST['full_name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'city' => $_POST['city'] ?? '',
        'postal_code' => $_POST['postal_code'] ?? ''
    ];

    if (saveUserProfile($profileData)) {
        $success = 'Profil berhasil diperbarui!';
    } else {
        $error = 'Gagal memperbarui profil.';
    }
}

$profile = getUserProfile();
$isEditMode = isset($_GET['action']) && $_GET['action'] === 'edit';

include __DIR__ . '/../partials/header.php';
?>

<section class="profile-section">
    <div class="profile-container">
        <div class="profile-left-card">
            <div class="profile-avatar" onclick="openAvatarModal()" style="cursor: pointer;">
                <?php if (!empty($profile['avatar'])): ?>
                    <img src="<?php echo url('assets/img/avatars/' . $profile['avatar']); ?>"
                         alt="Foto Profil Admin"
                         id="avatar-img-src"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <?php endif; ?>
                <span <?php echo !empty($profile['avatar']) ? 'style="display:none;"' : ''; ?>>
                    <i class="fas fa-user-shield" style="font-size: 60px;"></i>
                </span>
            </div>
            <p class="profile-subtitle" style="font-size: 18px; font-weight: bold; color: #dc3545; margin-top: 10px;">
                <?php echo htmlspecialchars(getUsername() ?? 'Admin'); ?>
            </p>
            <p style="color: #666; font-size: 14px; margin-top: 5px;">
                <i class="fas fa-crown" style="color: #f7b500;"></i> Administrator
            </p>

            <!-- Upload Avatar -->
            <form method="POST" enctype="multipart/form-data" style="margin-top: 20px; width: 100%;">
                <input type="hidden" name="action" value="upload_avatar">
                <label for="avatar-upload" class="btn-upload-avatar" style="width: 100%; padding: 10px; background: #6c757d; color: white; border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fas fa-camera"></i> Ganti Foto
                </label>
                <input type="file" id="avatar-upload" name="avatar" accept="image/*" style="display: none;" onchange="this.form.submit()">
            </form>

            <!-- Admin Actions -->
            <div style="margin-top: 20px; width: 100%;">
                <a href="<?php echo url('admin/dashboard.php'); ?>" class="btn-admin-action" style="margin-bottom: 10px; width: 100%; padding: 12px; background: #1f3b83; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; transition: all 0.3s;">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="<?php echo url('admin/orders.php'); ?>" class="btn-admin-action" style="margin-bottom: 10px; width: 100%; padding: 12px; background: #5b8af0; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; transition: all 0.3s;">
                    <i class="fas fa-receipt"></i> Kelola Pesanan
                </a>
            </div>

            <!-- Logout Button -->
            <a href="<?php echo url('admin/logout.php'); ?>" class="btn-logout" style="margin-top: 15px; width: 100%; padding: 12px; background: #dc3545; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; transition: background 0.3s;" onmouseover="this.style.background='#c82333'" onmouseout="this.style.background='#dc3545'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="profile-right-card">
            <h2>Data Admin</h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($isEditMode): ?>
                <!-- Edit Mode -->
                <form method="POST" action="<?php echo url('admin/profile.php'); ?>" class="profile-form">
                    <input type="hidden" name="action" value="update_profile">

                    <label>Nama Lengkap</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>">

                    <label>No. Telepon</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">

                    <label>Alamat</label>
                    <textarea name="address" rows="3"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>

                    <div class="split-field">
                        <div>
                            <label>Kota</label>
                            <input type="text" name="city" value="<?php echo htmlspecialchars($profile['city'] ?? ''); ?>">
                        </div>
                        <div>
                            <label>Kode Pos</label>
                            <input type="text" name="postal_code" value="<?php echo htmlspecialchars($profile['postal_code'] ?? ''); ?>">
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn-submit">Simpan Perubahan</button>
                        <a href="<?php echo url('admin/profile.php'); ?>" class="btn-cancel" style="flex: 1; text-align: center; padding: 12px; background: #6c757d; color: white; border-radius: 10px; text-decoration: none; font-weight: 600;">Batal</a>
                    </div>
                </form>
            <?php else: ?>
                <!-- View Mode -->
                <div class="profile-view">
                    <div class="view-group">
                        <label>Nama Lengkap</label>
                        <p><?php echo htmlspecialchars($profile['full_name'] ?: '-'); ?></p>
                    </div>

                    <div class="view-group">
                        <label>No. Telepon</label>
                        <p><?php echo htmlspecialchars($profile['phone'] ?: '-'); ?></p>
                    </div>

                    <div class="view-group">
                        <label>Alamat</label>
                        <p><?php echo htmlspecialchars($profile['address'] ?: '-'); ?></p>
                    </div>

                    <div class="view-group">
                        <label>Kota</label>
                        <p><?php echo htmlspecialchars($profile['city'] ?: '-'); ?></p>
                    </div>

                    <div class="view-group">
                        <label>Kode Pos</label>
                        <p><?php echo htmlspecialchars($profile['postal_code'] ?: '-'); ?></p>
                    </div>
                </div>

                <a href="<?php echo url('admin/profile.php?action=edit'); ?>" class="btn-submit" style="display: block; text-align: center; text-decoration: none; margin-top: 20px;">Ubah Profil</a>
            <?php endif; ?>
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
</script>

<style>
.profile-section {
    padding: 40px 20px;
    min-height: calc(100vh - 80px);
    background: #f8faff;
}

.profile-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 30px;
}

.profile-left-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    height: fit-content;
    position: sticky;
    top: 100px;
}

.profile-right-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.profile-avatar {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    background: linear-gradient(135deg, #dc3545, #c82333);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(220, 53, 69, 0.3);
    transition: transform 0.3s;
}

.profile-avatar:hover {
    transform: scale(1.05);
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-avatar span {
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-admin-action:hover {
    opacity: 0.9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

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

@media (max-width: 968px) {
    .profile-container {
        grid-template-columns: 1fr;
    }
    
    .profile-left-card {
        position: relative;
        top: 0;
    }
}

.profile-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.profile-form input,
.profile-form textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #ecf0ff;
    border-radius: 10px;
    font-size: 14px;
    margin-bottom: 20px;
    transition: border-color 0.3s;
}

.profile-form input:focus,
.profile-form textarea:focus {
    outline: none;
    border-color: #5b8af0;
}

.split-field {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.btn-submit {
    background: #1f3b83;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    flex: 1;
}

.btn-submit:hover {
    background: #152a5e;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(31, 59, 131, 0.3);
}

.btn-cancel {
    background: #6c757d;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.btn-cancel:hover {
    background: #5a6268;
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

.profile-right-card h2 {
    color: #1f3b83;
    margin-bottom: 20px;
    font-size: 24px;
}
</style>

<?php include __DIR__ . '/../partials/footer.php'; ?>
