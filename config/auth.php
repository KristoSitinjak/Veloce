<?php
// Authentication Helper Functions

// Determine if we're in admin area
$isAdminArea = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;

// Use different session names for admin and user
if ($isAdminArea) {
    session_name('VELOCE_ADMIN_SESSION');
} else {
    session_name('VELOCE_USER_SESSION');
}

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        // Use relative path for user login
        require_once __DIR__ . '/path.php';
        header('Location: ' . url('login.php'));
        exit();
    }
}

function requireAdmin() {
    if (!isLoggedIn()) {
        // Redirect to admin login if not logged in
        require_once __DIR__ . '/path.php';
        header('Location: ' . url('admin/login.php'));
        exit();
    }
    if (!isAdmin()) {
        // Redirect to home if logged in but not admin
        require_once __DIR__ . '/path.php';
        header('Location: ' . url('index.php'));
        exit();
    }
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUsername() {
    return $_SESSION['username'] ?? null;
}
?>

