# 🛒 Veloce - E-commerce Perlengkapan Sepak Bola

> Platform e-commerce modern untuk penjualan perlengkapan sepak bola (sepatu, jersey, sarung tangan, dan aksesoris) yang dibangun dengan PHP Native dan MySQL.

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-Educational-green)

---

## ✨ Fitur Utama

### 👤 **Sistem Authentication & Role**
- ✅ Login & Register dengan validasi
- ✅ Role-based access (Admin & User)
- ✅ Session management yang aman
- ✅ Logout functionality

### 🛍️ **Fitur Customer/User**
- ✅ **Katalog Produk**
  - Tampilan grid responsif
  - Filter berdasarkan kategori (Sepatu, Jersey, Sarung Tangan, Aksesoris)
  - Pencarian produk real-time
  - Detail produk dengan lightbox gallery
  
- ✅ **Shopping Cart**
  - Tambah/kurang quantity produk
  - Hapus item dari cart
  - Total harga otomatis
  - Persistent cart per user

- ✅ **Checkout & Orders**
  - Form checkout dengan validasi
  - Pilihan metode pembayaran (COD / Transfer Bank)
  - Alamat pengiriman
  - Order history dengan status tracking
  - Order detail view
  - Request pembatalan order

- ✅ **Profil User**
  - Edit profil (nama, email, alamat, telepon)
  - Upload avatar/foto profil
  - View order history
  - Responsive profile page

- ✅ **About Page**
  - Informasi tentang Veloce
  - Misi dan visi perusahaan

### 🎛️ **Fitur Admin**
- ✅ **Dashboard Admin**
  - Overview statistik penjualan
  - Quick access menu

- ✅ **Product Management (CRUD)**
  - Tambah produk baru dengan upload gambar
  - Edit informasi produk
  - Hapus produk
  - Preview gambar produk
  - Kategori management

- ✅ **Order Management**
  - View semua pesanan
  - Update status pesanan (Pending → Processing → Shipped → Delivered)
  - Alert visual untuk cancellation request
  - Order detail dengan info customer
  - Filter dan pencarian order

- ✅ **User Management**
  - Daftar semua user terdaftar
  - View detail user & profile
  - Riwayat pesanan per user
  - User statistics

- ✅ **Sales Report (Laporan Penjualan)**
  - Total penjualan & revenue
  - Produk terlaris
  - Statistik kategori
  - Filter berdasarkan periode
  - Grafik penjualan

---

## 🚀 Instalasi

### 1️⃣ **Prerequisites**
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web Server (Apache/Nginx) - Laragon/XAMPP/WAMP
- Extension PHP: `pdo_mysql`, `mysqli`, `gd` (untuk upload gambar)

### 2️⃣ **Clone Repository**
```bash
git clone https://github.com/KristoSitinjak/Veloce.git
cd Veloce
```

### 3️⃣ **Database Setup**

1. Buat database baru di phpMyAdmin:
   ```sql
   CREATE DATABASE veloce;
   ```

2. Import database schema:
   - Buka phpMyAdmin
   - Pilih database `veloce`
   - Import file `database/veloce.sql`

   **Atau via command line:**
   ```bash
   mysql -u root -p veloce < database/veloce.sql
   ```

### 4️⃣ **Konfigurasi Database**

1. Copy file konfigurasi:
   ```bash
   cp config/db.example.php config/db.php
   ```

2. Edit `config/db.php` sesuai environment Anda:
   ```php
   $host = 'localhost';
   $dbname = 'veloce';
   $username = 'root';     // Sesuaikan dengan username MySQL
   $password = '';         // Sesuaikan dengan password MySQL
   ```

### 5️⃣ **Folder Permissions**

Pastikan folder berikut memiliki write permission:
```bash
chmod -R 755 assets/img/
chmod -R 755 assets/img/avatars/
```

### 6️⃣ **Akses Website**

Buka browser dan akses:
- **Local**: `http://localhost/Veloce`
- **Laragon**: `http://veloce.test` (jika menggunakan virtual host)

---

## 👥 Login Default

Setelah import database, gunakan kredensial berikut:

### 🔑 **Admin Account**
- **Username**: `admin`
- **Password**: `admin123`
- **Access**: `http://localhost/Veloce/admin/`

### 🔑 **User Account (Demo)**
Silakan register user baru atau gunakan salah satu yang sudah ada di database.

---

## 📁 Struktur Project

```
Veloce/
├── 📂 admin/                    # Admin panel
│   ├── dashboard.php           # Dashboard admin
│   ├── orders.php              # Order management
│   ├── order-detail.php        # Detail pesanan
│   ├── users.php               # User management
│   ├── user-detail.php         # Detail user
│   ├── laporan.php             # Sales report
│   ├── tambah.php              # Tambah produk
│   ├── edit.php                # Edit produk
│   ├── hapus.php               # Hapus produk
│   ├── login.php               # Admin login
│   └── logout.php              # Admin logout
│
├── 📂 assets/
│   ├── 📂 css/
│   │   ├── style.css           # Main stylesheet
│   │   ├── order-status.css    # Order status styling
│   │   ├── profile-orders.css  # Profile & orders styling
│   │   ├── delivery-options.css# Delivery options styling
│   │   └── lightbox.css        # Lightbox gallery
│   └── 📂 img/
│       ├── logo.svg            # Logo Veloce
│       ├── veloce.png          # Brand image
│       └── avatars/            # User avatars
│
├── 📂 config/
│   ├── db.php                  # Database connection (PDO & MySQLi)
│   ├── db.example.php          # Database config template
│   ├── auth.php                # Authentication helpers
│   ├── path.php                # Path/URL helpers
│   ├── cart.php                # Cart management
│   ├── orders.php              # Orders helpers
│   ├── products.php            # Products helpers
│   └── profile.php             # Profile helpers
│
├── 📂 database/
│   └── veloce.sql              # Database schema & sample data
│
├── 📂 partials/
│   ├── header.php              # Header template (navbar)
│   └── footer.php              # Footer template
│
├── 📄 index.php                # Homepage (featured products)
├── 📄 produk.php               # Product catalog
├── 📄 detail.php               # Product detail
├── 📄 cart.php                 # Shopping cart
├── 📄 checkout.php             # Checkout page
├── 📄 orders.php               # Order history (user)
├── 📄 profile.php              # User profile
├── 📄 about.php                # About Veloce
├── 📄 login.php                # User login
├── 📄 register.php             # User registration
├── 📄 logout.php               # User logout
├── 📄 .gitignore               # Git ignore rules
└── 📄 README.md                # Documentation
```

---

## 🛠️ Teknologi yang Digunakan

| Teknologi | Versi | Kegunaan |
|-----------|-------|----------|
| **PHP** | 7.4+ | Server-side logic |
| **MySQL** | 5.7+ | Database management |
| **PDO & MySQLi** | - | Database connections |
| **HTML5** | - | Markup structure |
| **CSS3** | - | Styling & animations |
| **JavaScript** | ES6+ | Client interactions |
| **Font Awesome** | 6.x | Icons |
| **Google Fonts** | - | Typography (Poppins) |

---

## 📸 Screenshots

> *Coming soon - Tambahkan screenshot aplikasi Anda di sini*

---

## 🔒 Keamanan

- ✅ Prepared statements (PDO) untuk mencegah SQL Injection
- ✅ Password hashing dengan `password_hash()`
- ✅ Session-based authentication
- ✅ CSRF protection pada form critical
- ✅ Input validation & sanitization
- ✅ File upload validation (type, size)

---

## 📝 Development Notes

### Database Tables:
- `users` - Data pengguna (admin & customer)
- `produk` - Data produk
- `cart` - Shopping cart items
- `orders` - Data pesanan
- `order_items` - Detail item pesanan

### Session Variables:
- `user_id` - ID user yang login
- `username` - Username user
- `role` - Role user (admin/user)

---

## 🐛 Known Issues & Limitations

- File upload terbatas 5MB (sesuai php.ini)
- Belum ada email notification untuk order
- Payment gateway belum terintegrasi

---

## 🚧 Future Improvements

- [ ] Integration dengan payment gateway (Midtrans, etc)
- [ ] Email notifications untuk order status
- [ ] Wishlist functionality
- [ ] Product reviews & ratings
- [ ] Inventory management
- [ ] Coupon/discount system
- [ ] Export report to PDF/Excel
- [ ] Real-time chat customer support

---

## 👨‍💻 Developer

**Kristo Sitinjak**
- GitHub: [@KristoSitinjak](https://github.com/KristoSitinjak)

---

## 📄 License

Project ini dibuat untuk keperluan **pembelajaran** dan **portofolio**.

---

## 🙏 Acknowledgments

- Font Awesome untuk icon library
- Google Fonts untuk typography
- Komunitas PHP & MySQL Indonesia

---

<p align="center">Made with ❤️ for learning purposes</p>

