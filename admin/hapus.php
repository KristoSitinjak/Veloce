<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/path.php';

requireAdmin();

$id = $_GET['id'] ?? 0;
$kategori = $_GET['kategori'] ?? '';

if ($id) {
    // Determine which table to query
    $tables = ['sepatu', 'jersey', 'sarung_tangan'];
    $product = null;
    $table = null;
    
    if ($kategori && in_array($kategori, $tables)) {
        // Query from specific table
        $table = $kategori;
        $stmt = $pdo->prepare("SELECT gambar FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
    } else {
        // Try to find in all tables
        foreach ($tables as $tbl) {
            $stmt = $pdo->prepare("SELECT gambar FROM $tbl WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            if ($result) {
                $product = $result;
                $table = $tbl;
                break;
            }
        }
    }
    
    // Delete product if table found
    if ($table) {
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete image file if exists
        if ($product && $product['gambar']) {
            $imagePath = __DIR__ . '/../assets/img/' . $product['gambar'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    }
}

header('Location: ' . url('admin/dashboard.php?success=1'));
exit();
?>

