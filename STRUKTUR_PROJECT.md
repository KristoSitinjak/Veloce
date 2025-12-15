# Struktur Project Veloce E-commerce

## 📁 Struktur Folder Lengkap

```
Veloce/
├── admin/                          # Folder Admin (CRUD Produk)
│   ├── dashboard.php               # Dashboard admin - list semua produk
│   ├── tambah.php                 # Form tambah produk baru
│   ├── edit.php                   # Form edit produk
│   └── hapus.php                  # Script hapus produk
│
├── assets/                         # Folder Assets
│   ├── css/
│   │   └── style.css              # Stylesheet utama (tema biru modern)
│   └── img/                       # Folder gambar produk
│       └── .htaccess              # Konfigurasi akses gambar
│
├── config/                        # Folder Konfigurasi
│   ├── db.php                     # Konfigurasi koneksi database
│   ├── auth.php                   # Helper functions authentication
│   └── path.php                   # Helper functions untuk URL/path
│
├── database/                      # Folder Database
│   └── toko_bola.sql              # Script SQL untuk import database
│
├── partials/                      # Folder Template
│   ├── header.php                 # Header template (navbar, dll)
│   └── footer.php                 # Footer template
│
├── index.php                      # Halaman utama (landing page)
├── produk.php                     # Halaman katalog produk per kategori
├── detail.php                     # Halaman detail produk
├── profile.php                    # Halaman profil user
├── login.php                      # Halaman login
├── register.php                   # Halaman registrasi
├── logout.php                     # Script logout
├── setup.php                      # Script setup admin user (hapus setelah digunakan)
└── README.md                      # Dokumentasi project
```

## 📋 File-file Utama

### 1. Konfigurasi
- **config/db.php**: Koneksi database MySQL
- **config/auth.php**: Helper functions untuk authentication dan proteksi halaman
- **config/path.php**: Helper functions untuk generate URL yang fleksibel

### 2. Authentication
- **login.php**: Form login untuk admin dan user
- **register.php**: Form registrasi user baru
- **logout.php**: Script logout dan destroy session

### 3. Halaman User
- **index.php**: Halaman utama menampilkan produk dengan filter kategori dan search
- **detail.php**: Halaman detail produk
- **profile.php**: Halaman profil user (hanya bisa diakses user yang login)

### 4. Halaman Admin
- **admin/dashboard.php**: Dashboard admin menampilkan semua produk dalam tabel
- **admin/tambah.php**: Form tambah produk baru dengan upload gambar
- **admin/edit.php**: Form edit produk dengan update gambar
- **admin/hapus.php**: Script hapus produk dan gambar

### 5. Template
- **partials/header.php**: Header dengan navbar dinamis berdasarkan role
- **partials/footer.php**: Footer dengan informasi kontak

### 6. Assets
- **assets/css/style.css**: Stylesheet dengan tema biru modern (Adidas/Nike style)

### 7. Database
- **database/toko_bola.sql**: Script SQL untuk membuat database dan tabel

## 🗄️ Struktur Database

### Tabel: akun
- `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
- `username` (VARCHAR(100), UNIQUE)
- `password` (VARCHAR(255)) - hashed dengan password_hash()
- `role` (ENUM: 'admin', 'user')
- `created_at` (TIMESTAMP)

### Tabel: jersey / sepatu / sarung_tangan
- `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
- `nama` (VARCHAR(150))
- `deskripsi` (TEXT)
- `jumlah` (INT)
- `harga` (DECIMAL(12,2))
- `gambar` (VARCHAR(255)) - nama file gambar
- `created_at` (TIMESTAMP)

### Tabel: kontak
- `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
- `nama` (VARCHAR(150))
- `email` (VARCHAR(150))
- `pesan` (TEXT)
- `created_at` (TIMESTAMP)

## 🔐 Login Default

**Admin:**
- Username: `admin`
- Password: `admin123`

*Catatan: Jalankan `setup.php` setelah import database untuk membuat admin user*

## 🎨 Fitur Website

### Fitur User:
- ✅ Melihat daftar produk
- ✅ Filter produk berdasarkan kategori
- ✅ Pencarian produk
- ✅ Melihat detail produk
- ✅ Register akun baru
- ✅ Login
- ✅ Melihat profil

### Fitur Admin:
- ✅ Dashboard admin
- ✅ Tambah produk (dengan upload gambar)
- ✅ Edit produk (dengan update gambar)
- ✅ Hapus produk (dengan hapus file gambar)
- ✅ Lihat semua produk dalam tabel

## 🛠️ Teknologi

- **Backend**: PHP Native (tanpa framework)
- **Database**: MySQL
- **Frontend**: HTML5, CSS3
- **Icons**: Font Awesome 6.0
- **Fonts**: Google Fonts (Poppins)

## 📝 Catatan Penting

1. **Setup Database**: Import `database/toko_bola.sql` ke phpMyAdmin
2. **Setup Admin**: Jalankan `setup.php` di browser, lalu hapus file tersebut
3. **Folder Gambar**: Pastikan folder `assets/img/` memiliki permission write
4. **Konfigurasi**: Edit `config/db.php` jika username/password MySQL berbeda

## 🚀 Cara Menjalankan

1. Pastikan Laragon/XAMPP sudah running
2. Import database `toko_bola.sql`
3. Jalankan `setup.php` untuk membuat admin
4. Akses `http://localhost/Veloce`
5. Login sebagai admin atau register sebagai user baru

