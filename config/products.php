<?php
// Helper Functions untuk Products
// Karena produk terpisah menjadi 3 tabel: jersey, sepatu, sarung_tangan

require_once __DIR__ . '/db.php';

/**
 * Get all products from all tables (jersey, sepatu, sarung_tangan)
 * Returns array with 'kategori' field added
 */
function getAllProducts($kategori = '', $search = '') {
    global $pdo;
    
    $products = [];
    
    // Get from jersey
    $sql = "SELECT *, 'jersey' as kategori FROM jersey WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (nama LIKE ? OR deskripsi LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if (empty($kategori) || $kategori === 'jersey') {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $jersey = $stmt->fetchAll();
        $products = array_merge($products, $jersey);
    }
    
    // Get from sepatu
    $sql = "SELECT *, 'sepatu' as kategori FROM sepatu WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (nama LIKE ? OR deskripsi LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if (empty($kategori) || $kategori === 'sepatu') {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $sepatu = $stmt->fetchAll();
        $products = array_merge($products, $sepatu);
    }
    
    // Get from sarung_tangan
    $sql = "SELECT *, 'sarung_tangan' as kategori FROM sarung_tangan WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (nama LIKE ? OR deskripsi LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if (empty($kategori) || $kategori === 'sarung_tangan') {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $sarung_tangan = $stmt->fetchAll();
        $products = array_merge($products, $sarung_tangan);
    }
    
    // Sort by created_at DESC
    usort($products, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    return $products;
}

/**
 * Get product by ID and kategori
 */
function getProductById($id, $kategori) {
    global $pdo;
    
    $table = $kategori; // jersey, sepatu, atau sarung_tangan
    $stmt = $pdo->prepare("SELECT *, ? as kategori FROM $table WHERE id = ?");
    $stmt->execute([$kategori, $id]);
    return $stmt->fetch();
}

/**
 * Insert product to specific table
 */
function insertProduct($kategori, $data) {
    global $pdo;
    
    $table = $kategori;
    $sql = "INSERT INTO $table (nama, deskripsi, jumlah, harga, gambar) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['nama'],
        $data['deskripsi'],
        $data['jumlah'] ?? 0,
        $data['harga'],
        $data['gambar'] ?? null
    ]);
}

/**
 * Update product in specific table
 */
function updateProduct($kategori, $id, $data) {
    global $pdo;
    
    $table = $kategori;
    $sql = "UPDATE $table SET nama = ?, deskripsi = ?, jumlah = ?, harga = ?, gambar = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['nama'],
        $data['deskripsi'],
        $data['jumlah'] ?? 0,
        $data['harga'],
        $data['gambar'] ?? null,
        $id
    ]);
}

/**
 * Delete product from specific table
 */
function deleteProduct($kategori, $id) {
    global $pdo;
    
    $table = $kategori;
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Get product image before delete
 */
function getProductImage($kategori, $id) {
    global $pdo;
    
    $table = $kategori;
    $stmt = $pdo->prepare("SELECT gambar FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    return $result ? $result['gambar'] : null;
}
?>

