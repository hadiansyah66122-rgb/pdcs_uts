-- Database praktikum Pemrograman Database Client Server (UTS)
-- Cara pakai: import lewat phpMyAdmin (tab Import) atau
--   mysql -uroot < database/pdcs.sql
-- Peringatan: tabel jurusan, mahasiswa, dan matakuliah di database pdcs_hadiansyah
-- dibuat ulang, jadi isi lama tabel-tabel itu ikut terhapus.

CREATE DATABASE IF NOT EXISTS `pdcs_hadiansyah` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pdcs_hadiansyah`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `matakuliah`;
DROP TABLE IF EXISTS `mahasiswa`;
DROP TABLE IF EXISTS `jurusan`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------
-- jurusan
-- ---------------------------------------------------------------
CREATE TABLE `jurusan` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `kode_jurusan` VARCHAR(32)  NOT NULL,
  `nama_jurusan` VARCHAR(64)  NOT NULL,
  `fakultas`     VARCHAR(32)  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_jurusan_kode` (`kode_jurusan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------
-- mahasiswa (banyak mahasiswa -> satu jurusan)
-- ---------------------------------------------------------------
CREATE TABLE `mahasiswa` (
  `id`         INT(11)     NOT NULL AUTO_INCREMENT,
  `jurusan_id` INT(11)     NOT NULL,
  `nama`       VARCHAR(32) NOT NULL,
  `nim`        VARCHAR(32) NOT NULL,
  `created_at` DATE        NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mahasiswa_nim` (`nim`),
  KEY `idx_mahasiswa_jurusan` (`jurusan_id`),
  CONSTRAINT `fk_mahasiswa_jurusan` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------
-- matakuliah (banyak matakuliah -> satu jurusan, belum ada CRUD-nya)
-- ---------------------------------------------------------------
CREATE TABLE `matakuliah` (
  `id`         INT(11)     NOT NULL AUTO_INCREMENT,
  `jurusan_id` INT(11)     NOT NULL,
  `kode_mk`    VARCHAR(32) NOT NULL,
  `nama_mk`    VARCHAR(64) NOT NULL,
  `sks`        INT(8)      NOT NULL,
  `semester`   INT(8)      NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_matakuliah_jurusan` (`jurusan_id`),
  CONSTRAINT `fk_matakuliah_jurusan` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------
-- Data dummy
-- ---------------------------------------------------------------
INSERT INTO `jurusan` (`id`, `kode_jurusan`, `nama_jurusan`, `fakultas`) VALUES
  (1, 'IF', 'Informatika',      'Fakultas Ilmu Komputer'),
  (2, 'SK', 'Sistem Komputer',  'Fakultas Ilmu Komputer'),
  (3, 'TE', 'Teknik Elektro',   'Fakultas Teknik'),
  (4, 'MN', 'Manajemen',        'Fakultas Ekonomi'),
  (5, 'AK', 'Akuntansi',        'Fakultas Ekonomi');

INSERT INTO `mahasiswa` (`id`, `jurusan_id`, `nama`, `nim`, `created_at`) VALUES
  (1, 1, 'Bagas Ramadhan',   '2511520001', '2026-10-05'),
  (2, 1, 'Nadia Kusuma',     '2511520002', '2026-10-05'),
  (3, 1, 'Rafi Hidayat',     '2511520003', '2026-10-06'),
  (4, 2, 'Salsabila Putri',  '2511520004', '2026-10-06'),
  (5, 3, 'Yoga Prakoso',     '2511520005', '2026-10-07'),
  (6, 4, 'Melati Anggraeni', '2511520006', '2026-10-07'),
  (7, 2, 'Dimas Wicaksono',  '2511520007', '2026-10-08');

INSERT INTO `matakuliah` (`id`, `jurusan_id`, `kode_mk`, `nama_mk`, `sks`, `semester`) VALUES
  (1, 1, 'IF101', 'Struktur Data',                      3, 2),
  (2, 1, 'IF305', 'Pemrograman Database Client Server', 3, 5),
  (3, 2, 'SK202', 'Sistem Digital',                     3, 3),
  (4, 3, 'TE110', 'Rangkaian Listrik',                  3, 1),
  (5, 5, 'AK120', 'Pengantar Akuntansi',                2, 1),
  (6, 5, 'AK230', 'Akuntansi Biaya',                    3, 3);
