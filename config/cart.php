<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/products.php';
require_once __DIR__ . '/profile.php';

const CART_SESSION_KEY = 'veloce_cart';
const SHIPPING_SESSION_KEY = 'veloce_shipping';

/**
 * Valid categories to prevent arbitrary table access
 */
function allowedCartCategories() {
    return ['sepatu', 'jersey', 'sarung_tangan'];
}

function normaliseKategori($kategori) {
    $kategori = strtolower(trim($kategori));
    return in_array($kategori, allowedCartCategories(), true) ? $kategori : null;
}

function cartKey($id, $kategori) {
    return $kategori . '-' . $id;
}

function ensureCartSession() {
    if (!isset($_SESSION[CART_SESSION_KEY])) {
        $_SESSION[CART_SESSION_KEY] = [];
    }
}

function getCartItems() {
    ensureCartSession();
    return $_SESSION[CART_SESSION_KEY];
}

function getCartCount() {
    $count = 0;
    foreach (getCartItems() as $item) {
        $count += (int) $item['qty'];
    }
    return $count;
}

function getCartTotals() {
    $subtotal = 0;
    $count = 0;

    foreach (getCartItems() as $item) {
        $line = (int) $item['qty'] * (int) $item['harga'];
        $subtotal += $line;
        $count += (int) $item['qty'];
    }

    $shipping = getShippingCost();

    return [
        'subtotal' => $subtotal,
        'count' => $count,
        'shipping' => $shipping,
        'grand_total' => $subtotal + $shipping
    ];
}

/**
 * Get shipping cost based on delivery type
 */
function getShippingCost() {
    $shipping = getShippingInfo();
    $deliveryType = $shipping['delivery_type'] ?? 'regular';
    
    $costs = [
        'regular' => 10000,
        'express' => 25000,
        'instant' => 50000
    ];
    
    return $costs[$deliveryType] ?? $costs['regular'];
}

function addToCart($id, $kategori, $qty = 1) {
    // Require login before adding to cart
    if (!isLoggedIn()) {
        return 'login_required';
    }
    
    ensureCartSession();

    $kategori = normaliseKategori($kategori);
    $qty = max(1, (int) $qty);
    $id = (int) $id;

    if ($id <= 0 || !$kategori) {
        return false;
    }

    $product = getProductById($id, $kategori);
    if (!$product) {
        return false;
    }

    $key = cartKey($id, $kategori);
    if (!isset($_SESSION[CART_SESSION_KEY][$key])) {
        $_SESSION[CART_SESSION_KEY][$key] = [
            'id' => $product['id'],
            'kategori' => $product['kategori'],
            'nama' => $product['nama'],
            'harga' => (int) $product['harga'],
            'gambar' => $product['gambar'] ?? null,
            'qty' => 0
        ];
    }

    $_SESSION[CART_SESSION_KEY][$key]['qty'] += $qty;
    return true;
}

function updateCartQty($id, $kategori, $qty) {
    ensureCartSession();

    $kategori = normaliseKategori($kategori);
    $id = (int) $id;
    $qty = (int) $qty;

    if ($id <= 0 || !$kategori) {
        return false;
    }

    $key = cartKey($id, $kategori);

    if (!isset($_SESSION[CART_SESSION_KEY][$key])) {
        return false;
    }

    if ($qty <= 0) {
        unset($_SESSION[CART_SESSION_KEY][$key]);
        return true;
    }

    $_SESSION[CART_SESSION_KEY][$key]['qty'] = $qty;
    return true;
}

function removeCartItem($id, $kategori) {
    ensureCartSession();
    $kategori = normaliseKategori($kategori);
    $id = (int) $id;
    if ($id <= 0 || !$kategori) {
        return false;
    }
    $key = cartKey($id, $kategori);
    if (isset($_SESSION[CART_SESSION_KEY][$key])) {
        unset($_SESSION[CART_SESSION_KEY][$key]);
        return true;
    }
    return false;
}

function clearCart() {
    unset($_SESSION[CART_SESSION_KEY]);
}

function getShippingInfo() {
    $defaults = [
        'full_name' => '',
        'phone' => '',
        'address' => '',
        'city' => '',
        'postal_code' => '',
        'notes' => '',
        'delivery_type' => 'regular',
        'payment_method' => 'cod',
        'bank_account' => '',
        'ewallet_number' => ''
    ];

    $sessionData = $_SESSION[SHIPPING_SESSION_KEY] ?? [];

    // Jika user login dan belum ada data shipping di session, isi dari profil user
    if (isLoggedIn() && empty($sessionData)) {
        $profile = getUserProfile();
        if ($profile) {
            $sessionData = [
                'full_name' => $profile['full_name'] ?? '',
                'phone' => $profile['phone'] ?? '',
                'address' => $profile['address'] ?? '',
                'city' => $profile['city'] ?? '',
                'postal_code' => $profile['postal_code'] ?? '',
                'notes' => '',
                'payment_method' => 'cod',
                'payment_method' => 'cod',
                'bank_account' => '',
                'ewallet_number' => ''
            ];
        }
    }

    return array_merge($defaults, $sessionData);
}

function saveShippingInfo($data) {
    $allowed = ['full_name', 'phone', 'address', 'city', 'postal_code', 'notes', 'delivery_type', 'payment_method', 'bank_account', 'ewallet_number'];
    $info = [];
    foreach ($allowed as $field) {
        $info[$field] = trim($data[$field] ?? '');
    }
    $_SESSION[SHIPPING_SESSION_KEY] = $info;

    // Simpan ke profil user juga jika sedang login (supaya bisa dipakai ulang)
    if (isLoggedIn()) {
        saveUserProfile($info);
    }

    return $info;
}

