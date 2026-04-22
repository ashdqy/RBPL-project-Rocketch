
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 22, 2026 at 03:25 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rocket2`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_menu`, `jumlah`, `harga`, `subtotal`) VALUES
(1, 1, 8, 1, 13000.00, 13000.00),
(2, 2, 3, 3, 14000.00, 42000.00),
(3, 3, 3, 3, 14000.00, 42000.00);

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(11) NOT NULL,
  `jenis` enum('HARIAN','STOK','KEUANGAN') NOT NULL,
  `periode` varchar(50) NOT NULL,
  `id_spv` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_penjualan` decimal(12,2) DEFAULT NULL,
  `total_pengeluaran` decimal(12,2) DEFAULT NULL,
  `total_return` decimal(12,2) DEFAULT NULL,
  `laba_rugi` decimal(12,2) DEFAULT NULL,
  `status` enum('DRAFT','FINAL','REJECTED','REVISI') NOT NULL DEFAULT 'DRAFT'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laporan`
--

INSERT INTO `laporan` (`id_laporan`, `jenis`, `periode`, `id_spv`, `tanggal`, `total_penjualan`, `total_pengeluaran`, `total_return`, `laba_rugi`, `status`) VALUES
(1, 'KEUANGAN', '', 2, '2026-04-01 16:43:45', 0.00, 0.00, 0.00, 0.00, 'FINAL'),
(2, 'KEUANGAN', '2026-04-08 s/d 2026-04-09', 2, '2026-04-08 18:10:18', 42000.00, 0.00, 50000.00, -8000.00, 'FINAL'),
(3, 'STOK', '2026-04-09 s/d 2026-04-09', 2, '2026-04-08 18:10:36', 42000.00, 0.00, 50000.00, -8000.00, 'FINAL'),
(4, 'STOK', '2026-04-09 s/d 2026-04-09', 2, '2026-04-09 02:34:17', 20.00, 2.00, 0.00, 18.00, 'FINAL');

-- --------------------------------------------------------

--
-- Table structure for table `laporan_validasi`
--

CREATE TABLE `laporan_validasi` (
  `id_validasi` int(11) NOT NULL,
  `id_laporan` int(11) NOT NULL,
  `id_validator` int(11) NOT NULL,
  `aksi` enum('APPROVE','REJECT','REVISI') NOT NULL,
  `catatan` text DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laporan_validasi`
--

INSERT INTO `laporan_validasi` (`id_validasi`, `id_laporan`, `id_validator`, `aksi`, `catatan`, `tanggal`) VALUES
(1, 3, 2, 'APPROVE', '', '2026-04-08 18:11:05');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id_menu` int(11) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id_menu`, `nama_menu`, `harga`, `kategori`, `is_active`) VALUES
(1, 'Ayam Goreng Original (Paha Bawah)', 13000.00, 'Ayam Goreng', 1),
(2, 'Ayam Goreng Original (Paha Atas)', 14000.00, 'Ayam Goreng', 1),
(3, 'Ayam Goreng Original (Dada)', 14000.00, 'Ayam Goreng', 1),
(4, 'Ayam Goreng Original (Sayap)', 11000.00, 'Ayam Goreng', 1),
(5, 'Ayam Hot Spicy (Paha Bawah)', 14000.00, 'Ayam Goreng', 1),
(6, 'Ayam Hot Spicy (Dada)', 15000.00, 'Ayam Goreng', 1),
(7, 'Ayam Utuh', 70000.00, 'Ayam Goreng', 1),
(8, 'Geprek Original (Sayap)', 13000.00, 'Ayam Geprek', 1),
(9, 'Geprek Original (Paha Bawah)', 14000.00, 'Ayam Geprek', 1),
(10, 'Geprek Sambal Matah (Paha Bawah)', 15000.00, 'Ayam Geprek', 1),
(11, 'Geprek Sambal Matah (Dada)', 16000.00, 'Ayam Geprek', 1),
(12, 'Geprek Cheese Level (Sayap)', 14000.00, 'Ayam Geprek', 1),
(13, 'Geprek Cheese Level (Paha Atas)', 16000.00, 'Ayam Geprek', 1),
(14, 'Chicken Strip 2 pcs', 10000.00, 'Chicken Strip', 1),
(15, 'Chicken Strip 3 pcs', 13000.00, 'Chicken Strip', 1),
(16, 'Chicken Strip 5 pcs', 20000.00, 'Chicken Strip', 1),
(17, 'Chicken Steak', 18000.00, 'Steak', 1),
(18, 'Chicken Steak + Nasi', 22000.00, 'Steak', 1),
(19, 'Nasi Goreng Biasa', 12000.00, 'Nasi Goreng', 1),
(20, 'Nasi Goreng + Telur Ceplok', 14000.00, 'Nasi Goreng', 1),
(21, 'Nasi Goreng + Ayam', 18000.00, 'Nasi Goreng', 1),
(22, 'Nasi Putih', 4000.00, 'Nasi Goreng', 1),
(23, 'Burger Ayam', 13000.00, 'Burger', 1),
(24, 'Burger Ayam Keju', 15000.00, 'Burger', 1),
(25, 'Burger Ayam Spicy', 14000.00, 'Burger', 1),
(26, 'Kentang Goreng', 9000.00, 'Snack', 1),
(27, 'Kentang Goreng + Saus', 10000.00, 'Snack', 1),
(28, 'Perkedel', 4000.00, 'Snack', 1),
(29, 'Telur Ceplok', 5000.00, 'Snack', 1),
(30, 'Spaghetti Bolognese', 14000.00, 'Snack', 1),
(31, 'Es Teh Manis', 4000.00, 'Minuman', 1),
(32, 'Es Jeruk', 5000.00, 'Minuman', 1),
(33, 'Nestea Botol', 7000.00, 'Minuman', 1),
(34, 'Nestle Orange', 7000.00, 'Minuman', 1),
(35, 'Milo', 7000.00, 'Minuman', 1),
(36, 'Happy Jus', 6000.00, 'Minuman', 1),
(37, 'Air Mineral', 4000.00, 'Minuman', 1),
(38, 'Thai Green Tea', 9000.00, 'Minuman', 1),
(39, 'Milky Mango', 9000.00, 'Minuman', 1),
(40, 'Red Velvet', 9000.00, 'Minuman', 1),
(41, 'Paket Rocket 1 (Nasi + Sayap + Es Teh)', 13000.00, 'Paket Hemat', 1),
(42, 'Paket Rocket 2 (Nasi + Paha Bawah + Es Teh)', 14000.00, 'Paket Hemat', 1),
(43, 'Paket Rocket 3 (Nasi + Paha Atas/Dada + Es Teh)', 15000.00, 'Paket Hemat', 1),
(44, 'Paket Rocket 4 (Nasi + Paha Bawah + Milo)', 15000.00, 'Paket Hemat', 1),
(45, 'Paket Rocket 5 (Nasi Goreng + Telur + Es Teh)', 15000.00, 'Paket Hemat', 1),
(46, 'Paket Rocket 6 (Nasi + Chicken Strip 3pcs + Nestea)', 15000.00, 'Paket Hemat', 1),
(47, 'Paket Rocket 7 (Nasi + Dada/Paha Atas + Es Teh)', 15000.00, 'Paket Hemat', 1),
(48, 'Paket Rocket 8 (Nasi + Paha Bawah + Milo)', 15000.00, 'Paket Hemat', 1),
(49, 'Paket Rocket 9 (Nasi + Chicken Steak + Nestea)', 19700.00, 'Paket Hemat', 1),
(50, 'Paket Rocket 10 (Nasi + Dada/Paha Atas + Nestle Orange)', 16000.00, 'Paket Hemat', 1),
(51, 'Paket Roma 1 (Nasi Goreng + Es Teh)', 14000.00, 'Paket Roma', 1),
(52, 'Paket Roma 2 (Nasi Goreng + Ayam + Es Teh)', 17000.00, 'Paket Roma', 1),
(53, 'Paket Roma 3 (Nasi Goreng + Ayam + Milo)', 18000.00, 'Paket Roma', 1),
(54, 'Paket Cheesy 1 (Nasi + Sayap Cheese Level + Es Teh)', 13000.00, 'Paket Cheesy', 1),
(55, 'Paket Cheesy 2 (Nasi + Paha Bawah Cheese Level + Es Teh)', 15000.00, 'Paket Cheesy', 1),
(56, 'Paket Cheesy 3 (Nasi + Paha Atas Cheese Level + Es Teh)', 17000.00, 'Paket Cheesy', 1),
(57, 'Paket Geprek 1 (Nasi + Sayap Geprek + Es Teh)', 15000.00, 'Paket Geprek', 1),
(58, 'Paket Geprek 2 (Nasi + Paha Bawah Geprek + Es Teh)', 16000.00, 'Paket Geprek', 1),
(59, 'Paket Geprek 3 (Nasi + Paha Atas Geprek + Es Teh)', 17000.00, 'Paket Geprek', 1),
(60, 'Paket Sehat 1 (Nasi + Sayap + Milo)', 12000.00, 'Paket Sehat', 1),
(61, 'Paket Sehat 2 (Burger Ayam + Milo)', 15000.00, 'Paket Sehat', 1),
(62, 'Paket Sehat 3 (Nasi + Dada/Paha Atas + Milo)', 17400.00, 'Paket Sehat', 1),
(63, 'Paket Sehat 4 (Nasi + Chicken Strip 3pcs + Milo)', 17400.00, 'Paket Sehat', 1),
(64, 'Paket Kids 1 (Nasi + Paha Bawah + Happy Jus)', 16000.00, 'Paket Kids', 1),
(65, 'Paket Kids 2 (Nasi + Dada + Happy Jus)', 17000.00, 'Paket Kids', 1);

-- --------------------------------------------------------

--
-- Table structure for table `promo`
--

CREATE TABLE `promo` (
  `id_promo` int(11) NOT NULL,
  `nama_promo` varchar(100) NOT NULL,
  `jenis` enum('PERSEN','NOMINAL','BOGO') NOT NULL,
  `nilai` decimal(12,2) NOT NULL DEFAULT 0.00,
  `id_menu` int(11) DEFAULT NULL COMMENT 'NULL = berlaku semua menu',
  `min_transaksi` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `dibuat_oleh` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo`
--

INSERT INTO `promo` (`id_promo`, `nama_promo`, `jenis`, `nilai`, `id_menu`, `min_transaksi`, `tanggal_mulai`, `tanggal_selesai`, `is_active`, `dibuat_oleh`, `created_at`, `updated_at`) VALUES
(1, 'promo coba', 'NOMINAL', 5000.00, 3, 30000.00, '2026-04-01', '2026-12-01', 1, 1, '2026-04-01 16:42:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `return_barang`
--

CREATE TABLE `return_barang` (
  `id_return` int(11) NOT NULL,
  `id_stok` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `alasan` text DEFAULT NULL,
  `status` enum('LAYAK','TIDAK_LAYAK') NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_barang`
--

INSERT INTO `return_barang` (`id_return`, `id_stok`, `id_user`, `jumlah`, `alasan`, `status`, `tanggal`) VALUES
(1, 2, 4, 5, 'kebanyakan', 'LAYAK', '2026-04-01 16:46:45'),
(2, 2, 4, 10, 'kebanyakan', 'TIDAK_LAYAK', '2026-04-01 16:47:12'),
(3, 3, 4, 2, 'rusak', 'TIDAK_LAYAK', '2026-04-08 17:59:12'),
(4, 3, 4, 2, 'berjamur', 'TIDAK_LAYAK', '2026-04-09 02:36:48'),
(5, 2, 4, 4, 'meledak', 'TIDAK_LAYAK', '2026-04-09 02:37:12');

-- --------------------------------------------------------

--
-- Table structure for table `stok`
--

CREATE TABLE `stok` (
  `id_stok` int(11) NOT NULL,
  `nama_bahan` varchar(100) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `satuan` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stok`
--

INSERT INTO `stok` (`id_stok`, `nama_bahan`, `jumlah`, `satuan`) VALUES
(1, 'Ayam', 9, 'kg'),
(2, 'balon', 10, 'pcs'),
(3, 'roti', 16, 'pcs');

-- --------------------------------------------------------

--
-- Table structure for table `stok_log`
--

CREATE TABLE `stok_log` (
  `id_log` int(11) NOT NULL,
  `id_stok` int(11) NOT NULL,
  `perubahan` int(11) NOT NULL,
  `alasan` text NOT NULL,
  `id_user` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `jenis` enum('MASUK','KELUAR','RETURN','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stok_log`
--

INSERT INTO `stok_log` (`id_log`, `id_stok`, `perubahan`, `alasan`, `id_user`, `tanggal`, `jenis`) VALUES
(1, 1, 12, 'Stok awal - Ayam pak bejo', 4, '2026-03-11 09:20:14', 'MASUK'),
(2, 1, -2, 'Masak Ayam batch pagi', 5, '2026-03-11 09:21:37', 'KELUAR'),
(3, 2, 10, 'Stok awal - warung madura', 4, '2026-04-01 16:46:25', 'MASUK'),
(4, 2, 5, 'RETURN: kebanyakan', 4, '2026-04-01 16:46:45', 'RETURN'),
(5, 1, -1, 'Masak Ayam batch pagi', 5, '2026-04-02 02:19:35', 'KELUAR'),
(6, 2, -1, 'ultah', 5, '2026-04-02 02:19:52', 'KELUAR'),
(7, 3, 20, 'Stok awal - rotisari', 4, '2026-04-08 17:58:47', 'MASUK'),
(8, 3, -2, 'burger', 5, '2026-04-08 18:08:43', 'KELUAR'),
(9, 3, -2, 'BUANG/TIDAK LAYAK: berjamur', 4, '2026-04-09 02:36:48', 'KELUAR'),
(10, 2, -4, 'BUANG/TIDAK LAYAK: meledak', 4, '2026-04-09 02:37:12', 'KELUAR');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` int(11) NOT NULL,
  `nama_supplier` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_log`
--

CREATE TABLE `system_log` (
  `id_log` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `modul` varchar(50) NOT NULL,
  `aksi` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_log`
--

INSERT INTO `system_log` (`id_log`, `id_user`, `modul`, `aksi`, `deskripsi`, `ip_address`, `tanggal`) VALUES
(1, 1, 'PROMO', 'CREATE', 'Buat promo baru: promo coba (ID: 1)', '::1', '2026-04-01 16:42:58'),
(2, 2, 'LAPORAN', 'CREATE', 'Generate laporan KEUANGAN periode , status FINAL (ID: 1)', '::1', '2026-04-01 16:43:45'),
(3, 2, 'LAPORAN', 'CREATE', 'Generate laporan KEUANGAN periode 2026-04-08 s/d 2026-04-09, status FINAL (ID: 2)', '::1', '2026-04-08 18:10:18'),
(4, 2, 'LAPORAN', 'CREATE', 'Generate laporan STOK periode 2026-04-09 s/d 2026-04-09, status DRAFT (ID: 3)', '::1', '2026-04-08 18:10:36'),
(5, 2, 'LAPORAN', 'APPROVE', 'Laporan ID 3 di-APPROVE', '::1', '2026-04-08 18:11:05'),
(6, 2, 'LAPORAN', 'CREATE', 'Generate laporan STOK periode 2026-04-09 s/d 2026-04-09, status FINAL (ID: 4)', '::1', '2026-04-09 02:34:17'),
(7, 1, 'BACKUP', 'CREATE', 'Backup database: FULL → backup_full_20260417_110331.sql', '::1', '2026-04-17 09:03:31');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `no_struk` varchar(50) NOT NULL,
  `id_kasir` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(12,2) NOT NULL,
  `status_pembayaran` enum('PENDING','PAID','FAILED') NOT NULL,
  `metode_pembayaran` enum('CASH','QRIS','TRANSFER') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `no_struk`, `id_kasir`, `tanggal`, `total`, `status_pembayaran`, `metode_pembayaran`) VALUES
(1, 'TRX-20260401184015-973', 3, '2026-04-01 16:40:15', 13000.00, 'PAID', 'CASH'),
(2, 'TRX-20260408195531-191', 3, '2026-04-08 17:55:31', 42000.00, 'PAID', 'CASH'),
(3, 'TRX-20260409043824-599', 3, '2026-04-09 02:38:24', 37000.00, 'PAID', 'CASH');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(225) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `role` enum('KASIR','TRAINING','COOKER','SPV','SUPER ADMIN') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama`, `role`, `created_at`, `is_active`, `updated_at`) VALUES
(1, 'superadmin', '$2y$10$uj1SSCZf1qZnuDG7TSVGK.vvFnHeGvw.kEJHDyRJSw7XJ99dkf2lq', 'super admin', 'SUPER ADMIN', '2026-02-23 17:39:05', 1, NULL),
(2, 'spv1', '$2y$10$er95Bmmn7RI66szQ0F1RMeGyY.3YoEajrFVQECLG08SYmsNZddwai', 'coba spv', 'SPV', '2026-02-24 13:39:58', 1, NULL),
(3, 'kasir0', '$2y$10$CTD3xu55NYz96jLnrUsWXeXeXDzYVqluQWwYHpPXW1kxOvs39cpPi', 'kasircoba', 'KASIR', '2026-03-04 17:23:21', 1, NULL),
(4, 'training0', '$2y$10$0cnVTCzMuJYkUOZxyLRWqO3Ksl8dTSLf8xkSROOwGBLxjshaukqiu', 'training coba', 'TRAINING', '2026-03-04 17:23:53', 1, NULL),
(5, 'cooker0', '$2y$10$h62cA8Dmv2ZhibyaQ3c2wucKoUIcZcFy/4WzDy6yuzpthoVbyNx76', 'cooker coba', 'COOKER', '2026-03-04 17:24:24', 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_menu` (`id_menu`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_spv` (`id_spv`);

--
-- Indexes for table `laporan_validasi`
--
ALTER TABLE `laporan_validasi`
  ADD PRIMARY KEY (`id_validasi`),
  ADD KEY `id_laporan` (`id_laporan`),
  ADD KEY `id_validator` (`id_validator`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indexes for table `promo`
--
ALTER TABLE `promo`
  ADD PRIMARY KEY (`id_promo`),
  ADD KEY `id_menu` (`id_menu`),
  ADD KEY `dibuat_oleh` (`dibuat_oleh`);

--
-- Indexes for table `return_barang`
--
ALTER TABLE `return_barang`
  ADD PRIMARY KEY (`id_return`),
  ADD KEY `id_stok` (`id_stok`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `stok`
--
ALTER TABLE `stok`
  ADD PRIMARY KEY (`id_stok`);

--
-- Indexes for table `stok_log`
--
ALTER TABLE `stok_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_stok` (`id_stok`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indexes for table `system_log`
--
ALTER TABLE `system_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_kasir` (`id_kasir`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username_unique` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `laporan_validasi`
--
ALTER TABLE `laporan_validasi`
  MODIFY `id_validasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `promo`
--
ALTER TABLE `promo`
  MODIFY `id_promo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `return_barang`
--
ALTER TABLE `return_barang`
  MODIFY `id_return` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stok`
--
ALTER TABLE `stok`
  MODIFY `id_stok` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stok_log`
--
ALTER TABLE `stok_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_log`
--
ALTER TABLE `system_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`) ON UPDATE CASCADE;

--
-- Constraints for table `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_spv`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;

--
-- Constraints for table `laporan_validasi`
--
ALTER TABLE `laporan_validasi`
  ADD CONSTRAINT `lv_ibfk_1` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lv_ibfk_2` FOREIGN KEY (`id_validator`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;

--
-- Constraints for table `promo`
--
ALTER TABLE `promo`
  ADD CONSTRAINT `promo_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `promo_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;

--
-- Constraints for table `return_barang`
--
ALTER TABLE `return_barang`
  ADD CONSTRAINT `return_barang_ibfk_1` FOREIGN KEY (`id_stok`) REFERENCES `stok` (`id_stok`),
  ADD CONSTRAINT `return_barang_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `stok_log`
--
ALTER TABLE `stok_log`
  ADD CONSTRAINT `stok_log_ibfk_1` FOREIGN KEY (`id_stok`) REFERENCES `stok` (`id_stok`) ON UPDATE CASCADE,
  ADD CONSTRAINT `stok_log_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;

--
-- Constraints for table `system_log`
--
ALTER TABLE `system_log`
  ADD CONSTRAINT `sl_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_kasir`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
