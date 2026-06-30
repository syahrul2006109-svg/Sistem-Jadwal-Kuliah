-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 28 Jun 2026 pada 18.18
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
-- Database: `sistem jadwal kuliah`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `dosen`
--

CREATE TABLE `dosen` (
  `id_dosen` int(11) NOT NULL,
  `nama_dosen` varchar(100) NOT NULL,
  `email_dosen` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `dosen`
--

INSERT INTO `dosen` (`id_dosen`, `nama_dosen`, `email_dosen`, `password`) VALUES
(1, 'Heri', 'Heri@gmail.com', '123'),
(2, 'Satya', 'Satya@gmail.com', '123'),
(3, 'Muri', 'Muri@gmail.com', '123'),
(4, 'Merto', 'Merto@gmail.com', '123'),
(5, 'jeri', 'Jeri@gmail.com', '123'),
(6, 'Eri', 'Eri@gmail.com', '123'),
(7, 'Ahmad', 'Ahmad@gmail.com', '123'),
(8, 'Fahri', 'Fahri@gmail.com', '123'),
(9, 'Fadli', 'Fadli@gmail.com', '123'),
(10, 'Geril', 'Geril@gmail.com', '123');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_kuliah`
--

CREATE TABLE `jadwal_kuliah` (
  `id_jadwal` int(11) NOT NULL,
  `id_matkul` int(11) NOT NULL,
  `id_dosen` int(11) NOT NULL,
  `id_ruang` int(11) NOT NULL,
  `gedung` varchar(100) NOT NULL,
  `hari` varchar(20) NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jadwal_kuliah`
--

INSERT INTO `jadwal_kuliah` (`id_jadwal`, `id_matkul`, `id_dosen`, `id_ruang`, `gedung`, `hari`, `waktu_mulai`, `waktu_selesai`) VALUES
(10, 3, 2, 1, 'Lantai 2', 'Senin', '07:30:00', '09:10:00'),
(11, 3, 2, 2, 'Lantai 2', 'Selasa', '07:30:00', '09:10:00'),
(12, 10, 1, 2, 'Lantai 2', 'Senin', '07:30:00', '09:10:00'),
(13, 3, 1, 2, 'Lantai 2', 'Senin', '09:15:00', '10:55:00'),
(14, 7, 3, 1, 'Lantai 2', 'Senin', '09:15:00', '10:55:00'),
(15, 7, 3, 4, 'Lantai 3', 'Senin', '11:00:00', '12:40:00'),
(16, 4, 4, 1, 'Lantai 2', 'Selasa', '09:15:00', '10:55:00'),
(17, 4, 4, 2, 'Lantai 2', 'Senin', '11:00:00', '12:40:00'),
(18, 4, 4, 1, 'Lantai 2', 'Selasa', '07:30:00', '09:10:00'),
(19, 1, 5, 1, 'Lantai 2', 'Senin', '14:00:00', '15:40:00'),
(20, 1, 5, 2, 'Lantai 2', 'Senin', '15:45:00', '17:25:00'),
(21, 1, 5, 4, 'Lantai 3', 'Senin', '07:30:00', '09:10:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `nim` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mahasiswa`
--

INSERT INTO `mahasiswa` (`nim`, `nama`, `email`, `password`) VALUES
('24011057', 'Stevan', 'Stevan@gmail.com', '123'),
('241011044', 'Muhammad Farhan', 'MhmdFrhnxy@gmail.com', '118'),
('241011099', 'Muhammad Harianto', 'Harianto123@gmail.com', '12345'),
('241011111', 'Jeremia', 'Jeremia@gmail.com', '120'),
('241011119', 'Fiqri Sinrang', 'Fiqri@gmail.com', '112'),
('241011124', 'AL-Fatih ', 'FtihSyf@gmail.com', '116'),
('241011125', 'Syahrul Ramadhan', 'Syahrul@gmail.com', '111'),
('241011127', 'Ferdii', 'Ferdian@gmail.com', '117'),
('241011128', 'Ahmad Naufal ', 'AhmdNafl@gmail.com', '115'),
('241011130', 'Syafiq Muttaqin', 'SyfqMutqin@gmail.com', '114'),
('241011135', 'Ahmad Fauzan Mustamin', 'ahmdfauznmustmin@gmail.com', '113');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mata_kuliah`
--

CREATE TABLE `mata_kuliah` (
  `id_matkul` int(11) NOT NULL,
  `nama_matkul` varchar(100) NOT NULL,
  `sks` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mata_kuliah`
--

INSERT INTO `mata_kuliah` (`id_matkul`, `nama_matkul`, `sks`) VALUES
(1, 'Programan Web', 3),
(2, 'Rekayasa Perangkat Lunak ', 3),
(3, 'Bhs Indonesia', 2),
(4, 'Struktur Data', 3),
(5, 'Probabilitas dan Statistika', 3),
(6, 'Desain Analisis Algoritma', 3),
(7, 'Pengolahan Citra Digital', 3),
(8, 'Keamanan Data Informasi', 3),
(9, 'Riset Teknologi Informasi', 3),
(10, 'Kecerdasan Buatan', 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengingat`
--

CREATE TABLE `pengingat` (
  `id_pengingat` int(11) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `waktu_pengingat` datetime DEFAULT NULL,
  `pesan` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Struktur dari tabel `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Struktur dari tabel `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Struktur dari tabel `ruangan`
--

CREATE TABLE `ruangan` (
  `id_ruang` int(11) NOT NULL,
  `nama_ruang` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `gedung` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data untuk tabel `ruangan`
--

INSERT INTO `ruangan` (`id_ruang`, `nama_ruang`, `gedung`) VALUES
(1, 'LT201', 'laboratorium-ITH'),
(2, 'LT202', 'laboratorium-ITH'),
(3, 'LT203', 'laboratorium-ITH'),
(4, 'LT301', 'laboratorium-ITH'),
(5, 'LT302', 'laboratorium-ITH'),
(6, 'LT303', 'laboratorium-ITH'),
(7, 'LT401', 'laboratorium-ITH'),
(8, 'LT402', 'laboratorium-ITH');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id_dosen`),
  ADD UNIQUE KEY `email_dosen` (`email_dosen`);

--
-- Indeks untuk tabel `jadwal_kuliah`
--
ALTER TABLE `jadwal_kuliah`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_matkul` (`id_matkul`),
  ADD KEY `id_dosen` (`id_dosen`),
  ADD KEY `id_ruang` (`id_ruang`);

--
-- Indeks untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`nim`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD PRIMARY KEY (`id_matkul`);

--
-- Indeks untuk tabel `pengingat`
--
ALTER TABLE `pengingat`
  ADD PRIMARY KEY (`id_pengingat`),
  ADD KEY `id_jadwal` (`id_jadwal`);

--
-- Indeks untuk tabel `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indeks untuk tabel `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indeks untuk tabel `ruangan`
--
ALTER TABLE `ruangan`
  ADD PRIMARY KEY (`id_ruang`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id_dosen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `jadwal_kuliah`
--
ALTER TABLE `jadwal_kuliah`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  MODIFY `id_matkul` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `pengingat`
--
ALTER TABLE `pengingat`
  MODIFY `id_pengingat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ruangan`
--
ALTER TABLE `ruangan`
  MODIFY `id_ruang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `jadwal_kuliah`
--
ALTER TABLE `jadwal_kuliah`
  ADD CONSTRAINT `fk_jadwal_dosen` FOREIGN KEY (`id_dosen`) REFERENCES `dosen` (`id_dosen`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_jadwal_matkul` FOREIGN KEY (`id_matkul`) REFERENCES `mata_kuliah` (`id_matkul`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_jadwal_ruangan` FOREIGN KEY (`id_ruang`) REFERENCES `ruangan` (`id_ruang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengingat`
--
ALTER TABLE `pengingat`
  ADD CONSTRAINT `pengingat_ibfk_1` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal_kuliah` (`id_jadwal`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
