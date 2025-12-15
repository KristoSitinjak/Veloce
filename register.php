<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/path.php';
require_once __DIR__ . '/config/profile.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . url('index.php'));
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    
    if (
        empty($username) || empty($password) || empty($password_confirm) ||
        empty($full_name) || empty($phone) || empty($address)
    ) {
        $error = 'Semua field bertanda * wajib diisi!';
    } elseif (strlen($username) < 3) {
        $error = 'Username minimal 3 karakter!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $password_confirm) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM akun WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username sudah digunakan!';
        } else {
            // TANPA HASH: simpan password apa adanya (plain text)
            $stmt = $pdo->prepare("INSERT INTO akun (username, password, role) VALUES (?, ?, 'user')");
            
            if ($stmt->execute([$username, $password])) {
                $userId = $pdo->lastInsertId();

                saveUserProfile([
                    'full_name' => $full_name,
                    'phone' => $phone,
                    'address' => $address,
                    'city' => $city,
                    'postal_code' => $postal_code,
                    'notes' => ''
                ], $userId);

                $success = 'Registrasi berhasil! Silakan <a href="' . url('login.php') . '">login</a>.';
            } else {
                $error = 'Terjadi kesalahan saat registrasi!';
            }
        }
    }
}

$page_title = 'Register - Veloce';
include __DIR__ . '/partials/header.php';
?>

<div class="form-container">
    <h2>Daftar Akun</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username *</label>
            <input type="text" id="username" name="username" class="form-control" required autofocus minlength="3" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" class="form-control" required minlength="6">
        </div>
        
        <div class="form-group">
            <label for="password_confirm">Konfirmasi Password *</label>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control" required minlength="6">
        </div>

        <hr style="margin: 25px 0;">

        <div class="form-group">
            <label for="full_name">Nama Lengkap *</label>
            <input type="text" id="full_name" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="phone">No. Telepon / WhatsApp *</label>
            <input type="text" id="phone" name="phone" class="form-control" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="address">Alamat Lengkap *</label>
            <textarea id="address" name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="city">Kota / Kabupaten</label>
            <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="postal_code">Kode Pos</label>
            <input type="text" id="postal_code" name="postal_code" class="form-control" value="<?php echo htmlspecialchars($_POST['postal_code'] ?? ''); ?>">
        </div>
        
        <button type="submit" class="btn-submit">Daftar</button>
    </form>
    
    <div class="form-link">
        Sudah punya akun? <a href="<?php echo url('login.php'); ?>">Login disini</a>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
