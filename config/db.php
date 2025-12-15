<?php
// Database Configuration
$host = 'localhost';
$dbname = 'veloce';
$username = 'root';
$password = '';

// PDO Connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// MySQLi Connection (for orders.php)
$conn = mysqli_connect($host, $username, $password, $dbname);
if (!$conn) {
    die("Koneksi mysqli gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');
?>

