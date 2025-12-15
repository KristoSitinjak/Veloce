<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/path.php';

// Destroy admin session
session_destroy();

// Redirect to admin login
header('Location: ' . url('admin/login.php'));
exit();
?>
