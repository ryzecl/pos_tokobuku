# POS Toko Buku (Daebook)

Sistem Point of Sale sederhana untuk toko buku menggunakan PHP + PDO.

## Ringkasan proyek
- Nama aplikasi: Daebook
- Lokasi project: c:\laragon\www\pos_tokobuku
- Database default di repo: pos_daebook (file SQL: database/pos_daebook.sql)

## Fitur utama (sesuai kode)
- Autentikasi & role-based access (admin, kasir, gudang)
- Manajemen buku (CRUD) — file: buku.php, models/Buku.php
- Pembelian gudang (CRUD + terima) — file: pembelian.php, models/Pembelian.php
- Penjualan / POS — file: penjualan.php, models/Penjualan.php
- Manajemen stok & penyesuaian — file: stok.php
- Kategori buku — file: kategori.php, models/KategoriBuku.php
- Penerbit — file: penerbit.php, models/Penerbit.php
- Customer — file: customer.php, models/Customer.php
- Users / manajemen user — file: users.php, models/User.php
- Pengaturan umum — file: models/Pengaturan.php (digunakan di penjualan)
- Halaman cetak struk publik: struk.php
- Assets: assets/css/style.css dan assets/produk/ untuk cover buku

## Struktur file (sesuai repo)
```
Daebook/
├── assets/
│   ├── css/style.css
│   └── produk/ (cover upload)
├── config/
│   ├── config.php        # helper, session, helper functions
│   └── database.php      # class Database::getConnection() -> PDO
├── database/
│   └── pos_daebook.sql
├── models/
│   ├── Buku.php
│   ├── KategoriBuku.php
│   ├── Pembelian.php
│   ├── Penjualan.php
│   ├── Customer.php
│   ├── User.php
│   ├── Penerbit.php
│   └── Pengaturan.php
├── buku.php
├── pembelian.php
├── penjualan.php
├── stok.php
├── kategori.php
├── penerbit.php
├── customer.php
├── users.php
├── login.php
├── dashboard.php
├── struk.php
└── README.md
```

## Persyaratan & setup cepat
1. PHP 7.4+ dan ekstensi PDO_MYSQL.
2. Jalankan MySQL (Laragon).
3. Import database: c:\laragon\www\pos_tokobuku\database\pos_daebook.sql ke MySQL.
4. Sesuaikan config/database.php:
   - host, db_name (pos_daebook), username, password.
   - Pastikan Database::getConnection() mengembalikan PDO (lihat contoh di repo jika perlu).
5. Jalankan melalui Laragon atau:
   ```powershell
   cd c:\laragon\www\pos_tokobuku
   php -S localhost:8000
   ```
   Akses: http://localhost:8000/ atau URL Laragon Anda.

## Catatan teknis & perbaikan penting (telah diterapkan / perlu dicek)
- Pastikan config/config.php memanggil session_start() dan menyediakan helper:
  - sanitizeInput(), requireLogin(), requireRole(), formatCurrency().
- Database class harus mengembalikan PDO dengan opsi:
  - ERRMODE_EXCEPTION, DEFAULT_FETCH_MODE=ASSOC, EMULATE_PREPARES=false.
- Masalah umum: PDOStatement::bindParam() butuh variabel reference. Jika Anda mengikat literal/ekspresi, gunakan bindValue() atau variabel sementara.
  - Contoh perbaikan sudah diterapkan di models/Buku.php::updateStok() (bindValue dan casting ke int).
- Pastikan semua model mempunyai konstruktor menerima $db:
  ```php
  public function __construct($db) { $this->conn = $db; }
  ```
- Folder upload assets/produk harus writable oleh webserver.

## Langkah pengujian cepat (prioritas)
1. Login sebagai admin.
2. Tambah buku baru (buku.php) — coba upload cover, cek file ada di assets/produk dan kolom foto_cover di tabel buku.
3. Tambah pembelian (pembelian.php) lalu klik "Terima" — cek stok bertambah (models/Buku::updateStok tidak error).
4. Proses transaksi penjualan (penjualan.php) — cek nomor transaksi, pengurangan stok, pembuatan struk.
5. Penyesuaian stok manual (stok.php) — submit adjust positif/negatif, cek tidak muncul fatal error.
6. Hapus buku — cek file cover dihapus bila ada.

## Troubleshooting cepat
- Jika muncul error PDO bindParam(): ubah pemanggilan bindParam(..., <expression>) → bindValue(...) atau gunakan variabel.
- Error koneksi DB → periksa config/database.php dan apakah MySQL berjalan.
- Cek log PHP / display_errors saat development.

## Rekomendasi
- Konsisten gunakan bindValue jika tidak perlu binding by-reference.
- Tambahkan logging error (error_log) di model untuk debugging.
- Buat backup sebelum mengganti banyak file otomatis.

Jika Anda ingin, saya bisa:
- 1) Scan seluruh repo dan ubah kasus bindParam bermasalah menjadi bindValue (otomatis) — saya terapkan perubahan; atau
- 2) Kirim daftar file yang perlu diperbaiki satu-per-satu agar Anda review.

Pilih: "otomatis lakukan" atau "tinjau dulu".
