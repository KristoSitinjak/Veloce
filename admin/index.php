<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/path.php';

// If admin is logged in, go to dashboard
// If not logged in, go to admin login
if (isLoggedIn() && isAdmin()) {
    header('Location: ' . url('admin/dashboard.php'));
} else {
    header('Location: ' . url('admin/login.php'));
}
exit();
?>
