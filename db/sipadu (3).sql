-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Jan 2025 pada 04.57
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sipadu`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id_pengaduan` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `id_petugas` int(11) DEFAULT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `bukti` varchar(255) DEFAULT NULL,
  `laporan_petugas` text DEFAULT NULL,
  `status` enum('Baru','Diproses','Selesai','Ditolak','Revisi') NOT NULL,
  `keteranganRevisi` varchar(500) DEFAULT NULL,
  `tanggal_selesai` timestamp NULL DEFAULT NULL,
  `tanggal_pengaduan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaduan`
--

INSERT INTO `pengaduan` (`id_pengaduan`, `id`, `id_petugas`, `judul`, `deskripsi`, `bukti`, `laporan_petugas`, `status`, `tanggal_selesai`, `tanggal_pengaduan`) VALUES
(5, 91, NULL, 'Luas Tanah Tidak Sesuai', 'aaaaaa', 'Luas Tanah Tidak Sesuai.pdf', NULL, 'Baru', NULL, '2025-01-23 03:05:46'),
(6, 92, NULL, 'lAPAR', 'WKWK', 'lAPAR.pdf', NULL, 'Baru', NULL, '2025-01-23 03:23:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Petugas','Masyarakat') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `role`) VALUES
(89, 'adm', '$2y$10$lKz/L7JbEFobGTJ8j0MQDuJoWH3gxRV3cTjcxBf5tp2Ov1qJJTyeC', 'Admin'),
(90, 'ptgs', '$2y$10$d5TIZaxd6Yfw4lHRXRCGiOCnDG.ebNWGo9SF7csNUACG7KmjdlICC', 'Petugas'),
(91, 'msykt', '$2y$10$xheOe3HxU4aBOFdezuZ84O3QLuz1Vfo6wX4Yj4dOfZ3zSN3OYq7G.', 'Masyarakat'),
(92, 'eki', '$2y$10$8E3FXCkat4blrKowIBKLy.TAxfChYTXR24.1FOssjFbCO02tiEe32', 'Masyarakat');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD PRIMARY KEY (`id_pengaduan`),
  ADD KEY `id` (`id`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  MODIFY `id_pengaduan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD CONSTRAINT `pengaduan_ibfk_1` FOREIGN KEY (`id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
