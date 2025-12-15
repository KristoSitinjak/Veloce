# Veloce - E-commerce Perlengkapan Sepak Bola

Website e-commerce sederhana untuk menjual perlengkapan sepak bola (sepatu, jersey, sarung tangan, dan aksesoris) menggunakan HTML, CSS, PHP Native, dan MySQL.

## Fitur

- **Role & Authentication**
  - Admin: Mengelola produk (CRUD)
  - User: Melihat produk dan profil
  - Sistem login dan register

- **Fitur Admin**
  - Dashboard untuk mengelola produk
  - Tambah, Edit, Hapus produk
  - Upload gambar produk

- **Fitur User**
  - Melihat daftar produk
  - Filter berdasarkan kategori
  - Pencarian produk
  - Melihat detail produk
  - Login & Register

## Instalasi

### 1. Database Setup

1. Buka phpMyAdmin di Laragon/XAMPP
2. Import file `database/veloce.sql`
3. Atau copy-paste isi file `database/veloce.sql` ke phpMyAdmin SQL tab

### 2. Konfigurasi Database

Edit file `config/db.php` jika diperlukan:

```php
$host = 'localhost';
$dbname = 'veloce';
$username = 'root';  // Sesuaikan dengan username MySQL Anda
$password = '';      // Sesuaikan dengan password MySQL Anda
```

**Catatan**: Admin user sudah otomatis dibuat saat import database dengan:
- Username: `admin`
- Password: `admin123`

### 4. Struktur Folder

Pastikan folder berikut ada dan memiliki permission write:
- `assets/img/` - Untuk menyimpan gambar produk

### 5. Akses Website

- Buka browser dan akses: `http://localhost/Veloce`
- Atau jika di subfolder: `http://localhost/Veloce/index.php`

## Login Default

**Admin (sudah dibuat otomatis saat import database):**
- Username: `admin`
- Password: `admin123`

## Struktur Project

```
Veloce/
├── admin/
│   ├── dashboard.php    # Dashboard admin
│   ├── tambah.php       # Tambah produk
│   ├── edit.php         # Edit produk
│   └── hapus.php        # Hapus produk
├── assets/
│   ├── css/
│   │   └── style.css    # Stylesheet utama
│   └── img/             # Folder gambar produk
├── config/
│   ├── db.php           # Konfigurasi database
│   ├── auth.php         # Helper authentication
│   └── path.php         # Helper path/URL
├── database/
│   └── veloce.sql       # Script SQL database
├── partials/
│   ├── header.php       # Header template
│   └── footer.php       # Footer template
├── index.php            # Halaman utama
├── produk.php           # Halaman katalog produk per kategori
├── detail.php           # Detail produk
├── profile.php          # Profil user
├── login.php            # Halaman login
├── register.php         # Halaman registrasi
└── logout.php           # Logout
```

## Teknologi

- PHP 7.4+
- MySQL 5.7+
- HTML5
- CSS3
- Font Awesome Icons
- Google Fonts (Poppins)

## Catatan

- Pastikan PHP extension `pdo_mysql` aktif
- Pastikan folder `assets/img/` memiliki permission write untuk upload gambar
- Ukuran maksimal upload gambar: 5MB (dapat diubah di php.ini)

## Lisensi

Project ini dibuat untuk keperluan pembelajaran.

