# 🔥 Rocket Chicken — Sistem Manajemen Internal

## Struktur Aplikasi
```
rocket_system/
├── config/database.php         ← Konfigurasi koneksi database
├── middleware/
│   ├── auth.php                ← Cek sesi login
│   ├── role_admin.php          ← Hanya SUPER ADMIN
│   ├── role_spv.php            ← SUPER ADMIN + SPV
│   └── role_karyawan.php       ← Semua role
├── includes/
│   ├── layout_start.php        ← Header + Sidebar
│   └── layout_end.php          ← Footer
├── view/
│   ├── admin/                  ← Dashboard Super Admin
│   │   ├── dashboard.php       
│   │   ├── users/              ← CRUD User
│   │   ├── log/                ← Log Aktivitas
│   │   ├── transaksi/          ← Audit Transaksi
│   │   ├── laporan/            ← Monitor Laporan
│   │   └── backup/             ← Backup Database
│   ├── spv/                    ← Dashboard SPV
│   │   ├── dashboard.php
│   │   ├── laporan.php         ← Generate Laporan
│   │   ├── validasi.php        ← Validasi Laporan
│   │   ├── transaksi.php       ← Lihat Transaksi
│   │   └── stok.php            ← Lihat Stok
│   ├── kasir/                  ← Dashboard Kasir
│   │   ├── dashboard.php
│   │   ├── transaksi.php       ← Buat Transaksi (dengan menu picker)
│   │   └── riwayat.php         ← Riwayat Transaksi Sendiri
│   ├── training/               ← Dashboard Training
│   │   ├── dashboard.php
│   │   ├── stok.php            ← Input Barang Masuk
│   │   └── return.php          ← Input Return Barang
│   └── cooker/                 ← Dashboard Cooker
│       ├── dashboard.php
│       ├── penggunaan.php      ← Input Penggunaan Bahan
│       └── sisa.php            ← Update/Koreksi Sisa Stok
├── login.php
├── logout.php
├── dashboard.php               ← Router berdasarkan role
├── index.php                   ← Redirect ke login
└── hash.php                    ← Utility generate hash (hapus setelah setup)
```

## Cara Setup

### 1. Import Database
- Buka phpMyAdmin
- Import file `rocketch.sql`

### 2. Konfigurasi Database
Edit `config/database.php`:
```php
$host     = 'localhost';
$dbname   = 'rocketch';
$user     = 'root';     // sesuaikan
$password = '';          // sesuaikan
```

### 3. Upload ke Server / Localhost
Taruh folder `rocket_system` di:
- XAMPP: `htdocs/rocket_system/`
- WAMP: `www/rocket_system/`

### 4. Akses Aplikasi
Buka browser: `http://localhost/rocket_system/`

## Default Login
| Username   | Password     | Role        |
|------------|--------------|-------------|
| superadmin | (dari DB)    | SUPER ADMIN |

**Login super admin:** gunakan password yang sudah ada di database.  
Buka `hash.php` untuk generate password baru jika diperlukan, lalu update manual di tabel `users`.

## Menambah User
Login sebagai Super Admin → Kelola User → Tambah User

## Fitur per Role

### 👑 Super Admin
- Dashboard dengan statistik lengkap
- CRUD User (tambah, edit role, nonaktifkan, reset password)
- Log Aktivitas Stok
- Audit semua transaksi (filter tanggal)
- Monitor semua laporan
- Backup database (download SQL)

### 🧑‍💼 SPV
- Dashboard dengan ringkasan
- Generate laporan (harian/stok/keuangan)
- Validasi & finalisasi laporan draft
- Lihat semua transaksi (filter tanggal)
- Lihat semua stok + status

### 💰 Kasir
- Dashboard pribadi
- Buat transaksi (pilih menu interaktif)
- Riwayat transaksi sendiri

### 📦 Training
- Dashboard pribadi
- Input barang masuk (existing atau bahan baru)
- Input return barang (layak/tidak layak)

### 🍳 Cooker
- Dashboard pribadi + status stok
- Input penggunaan bahan (otomatis kurangi stok)
- Koreksi/update sisa stok (stock opname)

---
*Sistem ini dibuat untuk keperluan tugas kuliah Rocket Chicken Management System*
