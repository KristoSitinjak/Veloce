<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/path.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: ' . url('admin/dashboard.php'));
    } else {
        header('Location: ' . url('index.php'));
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM akun WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        // TANPA HASH: bandingkan langsung dengan password di database
        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                header('Location: ' . url('admin/dashboard.php'));
            } else {
                header('Location: ' . url('index.php'));
            }
            exit();
        } else {
            $error = 'Username atau password salah!';
        }
    }
}

$page_title = 'Login - Veloce';
include __DIR__ . '/partials/header.php';
?>

<section class="auth-section">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Masuk ke Veloce</h2>
            <p>Akses dashboard dan kelola perlengkapan favoritmu.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-with-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" class="form-control" required autofocus>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Masuk</button>
        </form>
        
        <div class="auth-footer">
            <p>Belum punya akun? <a href="<?php echo url('register.php'); ?>">Daftar sekarang</a></p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>

