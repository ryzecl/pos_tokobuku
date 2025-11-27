-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 18 Nov 2025 pada 23.57
-- Versi server: 9.5.0
-- Versi PHP: 8.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `pos_rooty`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `customer`
--

CREATE TABLE `customer` (
  `id` int NOT NULL,
  `nama_customer` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
  `telepon` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `customer`
--

-- INSERT INTO `customer` (`id`, `nama_customer`, `alamat`, `telepon`, `email`) VALUES
-- (1, 'Hildan', 'Karawang', '081234567890', 'hildan@gmail.com');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pembelian`
--

CREATE TABLE `detail_pembelian` (
  `id` int NOT NULL,
  `pembelian_id` int DEFAULT NULL,
  `buku_id` int DEFAULT NULL,
  `jumlah` int NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_penjualan`
--

CREATE TABLE `detail_penjualan` (
  `id` int NOT NULL,
  `penjualan_id` int DEFAULT NULL,
  `buku_id` int DEFAULT NULL,
  `jumlah` int NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_penjualan`
--

-- INSERT INTO `detail_penjualan` (`id`, `penjualan_id`, `buku_id`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
-- (1, 1, 6, 1, 60000.00, 58500.00),
-- (2, 1, 5, 1, 10000.00, 9500.00),
-- (3, 2, 2, 1, 18000.00, 16000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_buku`
--

CREATE TABLE `kategori_buku` (
  `id` int PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `nama_kategori` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori_buku`
--

-- INSERT INTO kategori_buku (nama_kategori, deskripsi) VALUES
-- ('Romance', 'Novel bertema cinta dan hubungan emosional'),
-- ('Fantasy', 'Cerita imajinatif dengan dunia magis atau makhluk supernatural'),
-- ('Mistery', 'Kisah penuh teka-teki, investigasi, dan rahasia yang harus diungkap'),
-- ('Thriller', 'Cerita menegangkan dengan konflik intens dan penuh kejutan'),
-- ('Biography', 'Kisah nyata perjalanan hidup seseorang, ditulis secara detail');
-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelian`
--

CREATE TABLE `pembelian` (
  `id` int NOT NULL,
  `no_faktur` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `penerbit_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `total_harga` decimal(12,2) NOT NULL,
  `tanggal_pembelian` date NOT NULL,
  `status` enum('pending','completed') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nilai` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama`, `nilai`) VALUES
(1, 'ppn_persen', '12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

CREATE TABLE `penjualan` (
  `id` int NOT NULL,
  `no_transaksi` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `diskon` decimal(12,2) NOT NULL DEFAULT '0.00',
  `ppn` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_harga` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_bayar` decimal(12,2) NOT NULL DEFAULT '0.00',
  `kembalian` decimal(12,2) NOT NULL DEFAULT '0.00',
  `note` text COLLATE utf8mb4_general_ci,
  `tanggal_penjualan` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `metode_pembayaran` enum('CASH','QRIS','TRANSFER') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'CASH'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penjualan`
--

-- INSERT INTO `penjualan` (`id`, `no_transaksi`, `user_id`, `customer_id`, `diskon`, `ppn`, `total_harga`, `total_bayar`, `kembalian`, `note`, `tanggal_penjualan`, `metode_pembayaran`) VALUES
-- (1, 'TRX202511180001', 1, 1, 1000.00, 6700.00, 73700.00, 100000.00, 26300.00, 'terimaksaih', '2025-11-18 16:12:12', 'CASH'),
-- (2, 'TRX202511180002', 1, 1, 0.00, 1600.00, 17600.00, 20000.00, 2400.00, 'terimakasih', '2025-11-18 16:23:33', 'CASH');

-- --------------------------------------------------------

--
-- Struktur dari tabel `buku`
--

CREATE TABLE `buku` (
  `id` int NOT NULL,
  `kode_buku` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_buku` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `kategori_id` int DEFAULT NULL,
  `satuan` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `harga_beli` decimal(10,2) NOT NULL,
  `harga_jual` decimal(10,2) NOT NULL,
  `diskon` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stok` int DEFAULT '0',
  `stok_minimum` int DEFAULT '0',
  `tanggal_expired` date DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `buku`
--

-- INSERT INTO `buku` (`id`, `kode_buku`, `nama_buku`, `kategori_id`, `satuan`, `harga_beli`, `harga_jual`, `diskon`, `stok`, `stok_minimum`, `tanggal_expired`, `deskripsi`, `created_at`, `updated_at`) VALUES
-- (2, 'RT01', 'Manis Lumer Coklat', 1, 'pcs', 12000.00, 18000.00, 2000.00, 998, 1, '2026-01-18', 'buku yang manis', '2025-11-18 14:20:23', '2025-11-18 16:23:33'),
-- (4, 'RT02', 'buku tawar Bulat', 2, 'pcs', 14000.00, 20000.00, 1000.00, 999, 1, '2026-01-18', 'buku yang tidak memiliki rasa', '2025-11-18 14:21:36', '2025-11-18 14:21:36'),
-- (5, 'RT03', 'kwasong', 3, 'pcs', 8000.00, 10000.00, 500.00, 998, 1, '2026-02-18', 'buku khas prancis', '2025-11-18 14:24:17', '2025-11-18 16:12:12'),
-- (6, 'RT04', 'Cake Hallowen', 4, 'pcs', 50000.00, 60000.00, 1500.00, 998, 1, '2025-12-18', 'untuk hallowen', '2025-11-18 14:25:50', '2025-11-18 16:12:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','kasir','gudang') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `email`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@apotek.com', 'admin', '2025-11-06 12:34:38', '2025-11-06 12:34:38'),
(2, 'kasir1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kasir Utama', 'kasir@apotek.com', 'kasir', '2025-11-06 12:34:38', '2025-11-06 12:34:38'),
(3, 'gudang1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff Gudang', 'gudang@apotek.com', 'gudang', '2025-11-06 12:34:38', '2025-11-06 12:34:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penerbit`
--

CREATE TABLE `penerbit` (
  `id` int NOT NULL,
  `nama_penerbit` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
  `telepon` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penerbit`
--

-- INSERT INTO `penerbit` (`id`, `nama_penerbit`, `alamat`, `telepon`, `email`, `created_at`) VALUES
-- (5, 'H.AdulMalik', 'Jl.Amanah', '081234567890', 'test2@gmail.com', '2025-11-18 14:06:51');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembelian_id` (`pembelian_id`),
  ADD KEY `buku_id` (`buku_id`);

--
-- Indeks untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penjualan_id` (`penjualan_id`),
  ADD KEY `buku_id` (`buku_id`);

--
-- Indeks untuk tabel `kategori_buku`
--
ALTER TABLE `kategori_buku`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_faktur` (`no_faktur`),
  ADD KEY `penerbit_id` (`penerbit_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama` (`nama`);

--
-- Indeks untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_transaksi` (`no_transaksi`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_buku` (`kode_buku`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `penerbit`
--
ALTER TABLE `penerbit`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kategori_buku`
--
ALTER TABLE `kategori_buku`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `penerbit`
--
ALTER TABLE `penerbit`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD CONSTRAINT `detail_pembelian_ibfk_1` FOREIGN KEY (`pembelian_id`) REFERENCES `pembelian` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pembelian_ibfk_2` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD CONSTRAINT `detail_penjualan_ibfk_1` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_penjualan_ibfk_2` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  ADD CONSTRAINT `pembelian_ibfk_1` FOREIGN KEY (`penerbit_id`) REFERENCES `penerbit` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pembelian_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `buku_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_buku` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
