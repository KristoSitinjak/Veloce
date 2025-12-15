-- -------------------------------------------------------
-- CREATE DATABASE
-- -------------------------------------------------------
CREATE DATABASE IF NOT EXISTS veloce;

USE veloce;

-- -------------------------------------------------------
-- TABLE: akun (admin & user)
-- -------------------------------------------------------
CREATE TABLE akun (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- TABLE: user_profile
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    full_name VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    postal_code VARCHAR(20) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_profile_user FOREIGN KEY (user_id) REFERENCES akun(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- TABLE: jersey
-- -------------------------------------------------------
CREATE TABLE jersey (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    jumlah INT DEFAULT 0,
    harga DECIMAL(12,2) NOT NULL,
    gambar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- TABLE: sepatu
-- -------------------------------------------------------
CREATE TABLE sepatu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    jumlah INT DEFAULT 0,
    harga DECIMAL(12,2) NOT NULL,
    gambar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- TABLE: sarung_tangan
-- -------------------------------------------------------
CREATE TABLE sarung_tangan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    jumlah INT DEFAULT 0,
    harga DECIMAL(12,2) NOT NULL,
    gambar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- TABLE: kontak (saran & masukan)
-- -------------------------------------------------------
CREATE TABLE kontak (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    pesan TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- TABLE: orders
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) DEFAULT NULL,
    postal_code VARCHAR(20) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    delivery_type ENUM('regular', 'express', 'instant') DEFAULT 'regular',
    payment_method ENUM('cod', 'bank_transfer', 'ewallet') NOT NULL,
    payment_details TEXT DEFAULT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    shipping_cost DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'verified', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    cancellation_requested BOOLEAN DEFAULT FALSE,
    cancellation_reason TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES akun(id) ON DELETE CASCADE,
    INDEX idx_user_orders (user_id),
    INDEX idx_order_status (status)
);


-- -------------------------------------------------------
-- TABLE: order_items
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_category VARCHAR(50) NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    product_image VARCHAR(255) DEFAULT NULL,
    quantity INT NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_items (order_id)
);

-- -------------------------------------------------------
-- INSERT ADMIN DEFAULT (Opsional)
-- Username: admin
-- Password: admin123 (plain text, TANPA HASH)
-- -------------------------------------------------------
INSERT INTO akun (username, password, role) VALUES
('admin', 'admin123', 'admin');