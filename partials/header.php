<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/path.php';
require_once __DIR__ . '/../config/cart.php';

$current_page = basename($_SERVER['PHP_SELF']);
$cart_count = getCartCount();
$css_version = file_exists(__DIR__ . '/../assets/css/style.css')
    ? filemtime(__DIR__ . '/../assets/css/style.css')
    : time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Veloce - Perlengkapan Bola Terbaik'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css?v=' . $css_version); ?>">
</head>
<body>
    <nav class="top-nav">
        <div class="logo">
            <a href="<?php echo url('index.php'); ?>">
                <img src="<?php echo url('assets/img/veloce.png'); ?>" alt="Veloce" class="logo-icon">
                VELOCE
            </a>
        </div>
        <div class="nav-icons">
            <a href="<?php echo url('index.php'); ?>" class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a>
            <a href="<?php echo url('produk.php'); ?>" class="nav-link <?php echo $current_page == 'produk.php' ? 'active' : ''; ?>">Produk</a>
            <a href="<?php echo url('about.php'); ?>" class="nav-link <?php echo $current_page == 'about.php' ? 'active' : ''; ?>">Tentang Kami</a>
            <?php if (isLoggedIn() && isAdmin()): ?>
                <a href="<?php echo url('admin/dashboard.php'); ?>" class="nav-link">Dashboard</a>
            <?php endif; ?>
            <div class="nav-icon-buttons">
                <?php if (isLoggedIn()): ?>
                    <?php 
                    // In user area, always show user profile link even if admin is logged in elsewhere
                    $inAdminArea = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
                    if ($inAdminArea && isAdmin()) {
                        $profileLink = url('admin/profile.php');
                        $profileTitle = 'Profil Admin';
                    } else {
                        $profileLink = url('profile.php');
                        $profileTitle = 'Profil Saya';
                    }
                    ?>
                    <a href="<?php echo $profileLink; ?>" class="user-icon user-icon-button" title="<?php echo $profileTitle; ?>">
                        <i class="fas fa-user"></i>
                    </a>
                <?php else: ?>
                    <a href="<?php echo url('login.php'); ?>" class="user-icon user-icon-button" title="Masuk / Daftar">
                        <i class="fas fa-user"></i>
                    </a>
                <?php endif; ?>
                <?php if (!isAdmin()): ?>
                    <a href="<?php echo url('cart.php'); ?>" class="cart-link <?php echo $current_page == 'cart.php' ? 'active' : ''; ?>" title="Keranjang Belanja">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

