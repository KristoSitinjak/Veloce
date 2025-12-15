<?php
/**
 * Setup Script - Run this once to create admin user
 * Hapus file ini setelah setup selesai untuk keamanan
 */

require_once __DIR__ . '/config/db.php';

// Check if admin already exists
$stmt = $pdo->query("SELECT COUNT(*) as count FROM akun WHERE role = 'admin'");
$result = $stmt->fetch();

if ($result['count'] > 0) {
    echo "Admin user sudah ada. Hapus file setup.php untuk keamanan.\n";
    exit;
}

// Create admin user (password plain text)
$username = 'admin';
$password = 'admin123';

try {
    $stmt = $pdo->prepare("INSERT INTO akun (username, password, role) VALUES (?, ?, 'admin')");
    $stmt->execute([$username, $password]);
    
    echo "Setup berhasil!\n";
    echo "Admin user telah dibuat:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";
    echo "\nHAPUS FILE setup.php UNTUK KEAMANAN!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

