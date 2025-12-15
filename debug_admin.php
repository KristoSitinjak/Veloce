<?php
require_once __DIR__ . '/config/db.php';

echo "<pre>";

try {
    $stmt = $pdo->prepare("SELECT * FROM akun WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch();

    if (!$user) {
        echo "TIDAK ADA user dengan username 'admin' di tabel akun.\n";
        echo "Silakan cek di phpMyAdmin: database 'veloce', tabel 'akun'.\n";
        exit;
    }

    echo "Data admin dari database:\n";
    echo "id      : " . $user['id'] . "\n";
    echo "username: " . $user['username'] . "\n";
    echo "role    : " . $user['role'] . "\n";
    echo "password (hash): " . $user['password'] . "\n\n";

    $testPassword = 'admin123';
    $ok = password_verify($testPassword, $user['password']);

    echo "Cek password_verify('admin123', hash_di_db):\n";
    echo $ok ? "HASIL: BENAR ✅\n" : "HASIL: SALAH ❌\n";

    if (!$ok) {
        echo "\nArtinya password di database TIDAK cocok dengan 'admin123'.\n";
        echo "Jalankan query berikut di phpMyAdmin (database veloce) untuk reset password admin:\n\n";
        echo "UPDATE akun\n";
        echo "SET password = '\$2y\$10\$kUm8FqT/B6s1uV0ymO59/.i8dBn4r3kNtVw.xSu1As.gHoN8YZJci',\n";
        echo "    role = 'admin'\n";
        echo "WHERE username = 'admin';\n\n";
        echo "Setelah itu, login dengan:\n";
        echo "- Username: admin\n";
        echo "- Password: admin123\n";
    } else {
        echo "\nHash password di database SUDAH cocok dengan 'admin123'.\n";
        echo "Jika masih tidak bisa login, pastikan:\n";
        echo "- Di form login, ketik username persis: admin (tanpa spasi, huruf kecil semua)\n";
        echo "- Anda benar-benar membuka URL: /login.php dari project Veloce ini\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";

