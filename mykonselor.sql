-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 29 Agu 2026 pada 12.48
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
-- Database: `mykonselor`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `articles`
--

CREATE TABLE `articles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `konten` longtext NOT NULL,
  `gambar_sampul` varchar(255) DEFAULT NULL,
  `kategori` varchar(255) DEFAULT NULL,
  `penulis` varchar(255) DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `articles`
--

INSERT INTO `articles` (`id`, `judul`, `slug`, `konten`, `gambar_sampul`, `kategori`, `penulis`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 'Mengenal Perbedaan Stres, Cemas, dan Burnout', 'mengenal-perbedaan-stres-cemas-dan-burnout', 'Stres, kecemasan, dan burnout sering dianggap sama padahal berbeda. Artikel ini membahas ciri khas masing-masing kondisi serta kapan mahasiswa perlu mulai waspada dan mencari bantuan.', NULL, 'Stres', 'Tim MyKonselor', 'published', '2026-08-17 03:58:19', '2026-08-17 03:58:19', '2026-08-17 03:58:19'),
(2, 'Teknik Pernapasan 4-7-8 untuk Meredakan Kecemasan', 'teknik-pernapasan-4-7-8-untuk-meredakan-kecemasan', 'Teknik pernapasan sederhana ini dapat membantu menenangkan sistem saraf dalam hitungan menit. Simak langkah-langkah praktisnya di sini.', NULL, 'Kecemasan', 'Tim MyKonselor', 'published', '2026-08-17 03:58:19', '2026-08-17 03:58:19', '2026-08-17 03:58:19'),
(3, 'Tanda-Tanda Depresi yang Sering Diabaikan Mahasiswa', 'tanda-tanda-depresi-yang-sering-diabaikan-mahasiswa', 'Banyak mahasiswa menganggap kelelahan dan kehilangan motivasi sebagai hal biasa. Artikel ini membahas kapan gejala tersebut perlu mendapat perhatian lebih serius.', NULL, 'Depresi', 'Tim MyKonselor', 'published', '2026-08-17 03:58:19', '2026-08-17 03:58:19', '2026-08-17 03:58:19'),
(4, 'Membangun Rutinitas Self-Care di Tengah Kesibukan Kuliah', 'membangun-rutinitas-self-care-di-tengah-kesibukan-kuliah', 'Self-care tidak harus mahal atau memakan waktu lama. Berikut kebiasaan kecil yang bisa diterapkan sehari-hari untuk menjaga kesehatan mental.', NULL, 'Self-care', 'Tim MyKonselor', 'published', '2026-08-17 03:58:19', '2026-08-17 03:58:19', '2026-08-17 03:58:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `assessments`
--

CREATE TABLE `assessments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('in_progress','completed') NOT NULL DEFAULT 'in_progress',
  `current_step` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `assessments`
--

INSERT INTO `assessments` (`id`, `user_id`, `status`, `current_step`, `started_at`, `completed_at`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 2, 'completed', 21, '2026-08-17 04:14:56', '2026-08-17 04:17:37', NULL, '2026-08-17 04:14:56', '2026-08-17 04:17:37'),
(2, 2, 'completed', 21, '2026-08-17 05:43:08', '2026-08-17 05:44:52', NULL, '2026-08-17 05:43:08', '2026-08-17 05:44:52'),
(3, 2, 'in_progress', 1, '2026-08-19 10:35:18', NULL, NULL, '2026-08-19 10:35:18', '2026-08-20 06:17:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `assessment_answers`
--

CREATE TABLE `assessment_answers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `assessment_id` bigint(20) UNSIGNED NOT NULL,
  `symptom_id` bigint(20) UNSIGNED NOT NULL,
  `answer_value` tinyint(3) UNSIGNED NOT NULL,
  `cf_user` decimal(4,3) NOT NULL,
  `dass_score` tinyint(3) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `assessment_answers`
--

INSERT INTO `assessment_answers` (`id`, `assessment_id`, `symptom_id`, `answer_value`, `cf_user`, `dass_score`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 0.400, 1, '2026-08-17 04:15:11', '2026-08-17 04:15:11'),
(2, 1, 2, 0, 0.000, 0, '2026-08-17 04:15:21', '2026-08-17 04:15:21'),
(3, 1, 3, 3, 1.000, 3, '2026-08-17 04:15:28', '2026-08-17 04:15:28'),
(4, 1, 4, 1, 0.400, 1, '2026-08-17 04:15:33', '2026-08-17 04:15:33'),
(5, 1, 5, 2, 0.800, 2, '2026-08-17 04:15:41', '2026-08-17 04:15:41'),
(6, 1, 6, 2, 0.800, 2, '2026-08-17 04:15:47', '2026-08-17 04:15:47'),
(7, 1, 7, 0, 0.000, 0, '2026-08-17 04:15:52', '2026-08-17 04:15:52'),
(8, 1, 8, 3, 1.000, 3, '2026-08-17 04:15:59', '2026-08-17 04:15:59'),
(9, 1, 9, 3, 1.000, 3, '2026-08-17 04:16:08', '2026-08-17 04:16:08'),
(10, 1, 10, 2, 0.800, 2, '2026-08-17 04:16:16', '2026-08-17 04:16:16'),
(11, 1, 11, 3, 1.000, 3, '2026-08-17 04:16:22', '2026-08-17 04:16:22'),
(12, 1, 12, 2, 0.800, 2, '2026-08-17 04:16:26', '2026-08-17 04:16:26'),
(13, 1, 13, 1, 0.400, 1, '2026-08-17 04:16:30', '2026-08-17 04:16:30'),
(14, 1, 14, 3, 1.000, 3, '2026-08-17 04:16:38', '2026-08-17 04:16:38'),
(15, 1, 15, 1, 0.400, 1, '2026-08-17 04:16:45', '2026-08-17 04:16:45'),
(16, 1, 16, 1, 0.400, 1, '2026-08-17 04:16:51', '2026-08-17 04:16:51'),
(17, 1, 17, 0, 0.000, 0, '2026-08-17 04:16:58', '2026-08-17 04:16:58'),
(18, 1, 18, 2, 0.800, 2, '2026-08-17 04:17:03', '2026-08-17 04:17:03'),
(19, 1, 19, 1, 0.400, 1, '2026-08-17 04:17:10', '2026-08-17 04:17:10'),
(20, 1, 20, 2, 0.800, 2, '2026-08-17 04:17:20', '2026-08-17 04:17:20'),
(21, 1, 21, 0, 0.000, 0, '2026-08-17 04:17:23', '2026-08-17 04:17:23'),
(22, 2, 1, 0, 0.000, 0, '2026-08-17 05:43:30', '2026-08-17 05:43:30'),
(23, 2, 2, 0, 0.000, 0, '2026-08-17 05:43:36', '2026-08-17 05:43:36'),
(24, 2, 3, 0, 0.000, 0, '2026-08-17 05:43:39', '2026-08-17 05:43:39'),
(25, 2, 4, 1, 0.400, 1, '2026-08-17 05:43:43', '2026-08-17 05:43:43'),
(26, 2, 5, 1, 0.400, 1, '2026-08-17 05:43:47', '2026-08-17 05:43:47'),
(27, 2, 6, 0, 0.000, 0, '2026-08-17 05:43:50', '2026-08-17 05:43:50'),
(28, 2, 7, 1, 0.400, 1, '2026-08-17 05:43:54', '2026-08-17 05:43:54'),
(29, 2, 8, 0, 0.000, 0, '2026-08-17 05:43:57', '2026-08-17 05:43:57'),
(30, 2, 9, 1, 0.400, 1, '2026-08-17 05:44:01', '2026-08-17 05:44:01'),
(31, 2, 10, 0, 0.000, 0, '2026-08-17 05:44:05', '2026-08-17 05:44:05'),
(32, 2, 11, 1, 0.400, 1, '2026-08-17 05:44:08', '2026-08-17 05:44:08'),
(33, 2, 12, 0, 0.000, 0, '2026-08-17 05:44:15', '2026-08-17 05:44:15'),
(34, 2, 13, 1, 0.400, 1, '2026-08-17 05:44:18', '2026-08-17 05:44:18'),
(35, 2, 14, 0, 0.000, 0, '2026-08-17 05:44:22', '2026-08-17 05:44:22'),
(36, 2, 15, 2, 0.800, 2, '2026-08-17 05:44:25', '2026-08-17 05:44:25'),
(37, 2, 16, 0, 0.000, 0, '2026-08-17 05:44:28', '2026-08-17 05:44:28'),
(38, 2, 17, 1, 0.400, 1, '2026-08-17 05:44:32', '2026-08-17 05:44:32'),
(39, 2, 18, 2, 0.800, 2, '2026-08-17 05:44:36', '2026-08-17 05:44:36'),
(40, 2, 19, 3, 1.000, 3, '2026-08-17 05:44:39', '2026-08-17 05:44:39'),
(41, 2, 20, 1, 0.400, 1, '2026-08-17 05:44:42', '2026-08-17 05:44:42'),
(42, 2, 21, 0, 0.000, 0, '2026-08-17 05:44:46', '2026-08-17 05:44:46'),
(43, 3, 1, 0, 0.000, 0, '2026-08-19 10:35:25', '2026-08-20 06:16:57'),
(44, 3, 2, 3, 1.000, 3, '2026-08-19 10:35:29', '2026-08-20 06:17:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `assessment_results`
--

CREATE TABLE `assessment_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `assessment_id` bigint(20) UNSIGNED NOT NULL,
  `stress_cf` decimal(5,3) NOT NULL DEFAULT 0.000,
  `anxiety_cf` decimal(5,3) NOT NULL DEFAULT 0.000,
  `depression_cf` decimal(5,3) NOT NULL DEFAULT 0.000,
  `stress_score` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `anxiety_score` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `depression_score` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `stress_severity` enum('normal','ringan','sedang','berat') NOT NULL,
  `anxiety_severity` enum('normal','ringan','sedang','berat') NOT NULL,
  `depression_severity` enum('normal','ringan','sedang','berat') NOT NULL,
  `highest_severity` enum('normal','ringan','sedang','berat') NOT NULL,
  `calculation_version` varchar(20) NOT NULL DEFAULT '1.0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `assessment_results`
--

INSERT INTO `assessment_results` (`id`, `assessment_id`, `stress_cf`, `anxiety_cf`, `depression_cf`, `stress_score`, `anxiety_score`, `depression_score`, `stress_severity`, `anxiety_severity`, `depression_severity`, `highest_severity`, `calculation_version`, `created_at`, `updated_at`) VALUES
(1, 1, 1.000, 1.000, 1.000, 32, 16, 18, 'berat', 'berat', 'sedang', 'berat', '1.0', '2026-08-17 04:17:37', '2026-08-17 04:17:37'),
(2, 2, 0.880, 1.000, 0.400, 6, 18, 6, 'normal', 'berat', 'normal', 'berat', '1.0', '2026-08-17 05:44:52', '2026-08-17 05:44:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0', 'i:1;', 1786965627),
('laravel-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0:timer', 'i:1786965627;', 1786965627);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `diseases`
--

CREATE TABLE `diseases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `cluster_key` varchar(20) NOT NULL,
  `keterangan_singkat` text NOT NULL,
  `panduan_normal_ringan` text NOT NULL,
  `panduan_sedang` text NOT NULL,
  `panduan_berat` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `diseases`
--

INSERT INTO `diseases` (`id`, `kode`, `nama`, `cluster_key`, `keterangan_singkat`, `panduan_normal_ringan`, `panduan_sedang`, `panduan_berat`, `created_at`, `updated_at`) VALUES
(1, 'P01', 'Stres', 'stress', 'Respons tubuh dan pikiran terhadap tekanan atau tuntutan, misalnya beban akademik, tenggat tugas, atau penyesuaian rutinitas perkuliahan.', 'Coba terapkan manajemen waktu sederhana (misalnya teknik Pomodoro), sisihkan waktu istirahat di antara jadwal kuliah, dan batasi asupan kafein berlebih. Jaga pola tidur tetap teratur.', 'Pada tahap ini, tips mandiri saja belum cukup. Disarankan menjadwalkan sesi konseling untuk mendiskusikan sumber tekanan yang kamu alami bersama konselor, agar penanganannya lebih terarah.', 'Kondisi yang kamu alami tampaknya cukup berat, dan itu bukan sesuatu yang perlu kamu hadapi sendirian. Sangat dianjurkan untuk segera menghubungi layanan bantuan profesional melalui halaman rujukan di bawah ini.', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(2, 'P02', 'Kecemasan', 'anxiety', 'Perasaan khawatir, tegang, atau gelisah yang berlebihan, kadang disertai gejala fisik seperti jantung berdebar atau sulit bernapas.', 'Coba latihan pernapasan dalam (misalnya teknik 4-7-8) saat rasa cemas muncul, dan tuliskan kekhawatiranmu di jurnal untuk membantu memilah mana yang benar-benar perlu dikhawatirkan.', 'Tips relaksasi mandiri kemungkinan belum cukup untuk membantumu saat ini. Pertimbangkan untuk menjadwalkan sesi konseling agar kecemasan yang kamu rasakan bisa digali dan ditangani lebih tepat.', 'Apa yang kamu rasakan tampaknya berat, dan wajar untuk merasa kewalahan. Kami sangat menganjurkan kamu segera menghubungi layanan bantuan profesional melalui halaman rujukan di bawah ini.', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(3, 'P03', 'Depresi', 'depression', 'Penurunan suasana hati yang menetap, kehilangan minat terhadap aktivitas yang biasanya disukai, dan perasaan tidak bersemangat dalam menjalani keseharian.', 'Coba jaga rutinitas harian tetap konsisten (jam tidur, jam makan), catat hal-hal kecil yang membuatmu bersyukur setiap hari, dan usahakan tetap terhubung dengan teman atau keluarga.', 'Pada tahap ini, penting untuk tidak hanya mengandalkan usaha mandiri. Disarankan menjadwalkan sesi konseling agar kondisi yang kamu alami dapat didampingi secara lebih tepat oleh profesional.', 'Terima kasih sudah jujur mengenali kondisimu — itu langkah yang tidak mudah. Kondisi ini butuh pendampingan profesional segera, dan kamu tidak sendirian. Silakan hubungi layanan bantuan melalui halaman rujukan di bawah ini.', '2026-08-17 03:58:17', '2026-08-17 03:58:17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `disease_symptom`
--

CREATE TABLE `disease_symptom` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `disease_id` bigint(20) UNSIGNED NOT NULL,
  `symptom_id` bigint(20) UNSIGNED NOT NULL,
  `rule_code` varchar(10) DEFAULT NULL,
  `mb` decimal(4,3) NOT NULL,
  `md` decimal(4,3) NOT NULL,
  `cf_pakar` decimal(5,3) NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `disease_symptom`
--

INSERT INTO `disease_symptom` (`id`, `disease_id`, `symptom_id`, `rule_code`, `mb`, `md`, `cf_pakar`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'R01', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(2, 1, 6, 'R02', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(3, 1, 8, 'R03', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(4, 1, 11, 'R04', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(5, 1, 12, 'R05', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(6, 1, 14, 'R06', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(7, 1, 18, 'R07', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(8, 2, 2, 'R01', 0.000, 1.000, -1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(9, 2, 4, 'R02', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(10, 2, 7, 'R03', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(11, 2, 9, 'R04', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(12, 2, 15, 'R05', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(13, 2, 19, 'R06', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(14, 2, 20, 'R07', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(15, 3, 3, 'R01', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(16, 3, 5, 'R02', 0.000, 1.000, -1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(17, 3, 10, 'R03', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(18, 3, 13, 'R04', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(19, 3, 16, 'R05', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(20, 3, 17, 'R06', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:19', '2026-08-17 03:58:19'),
(21, 3, 21, 'R07', 1.000, 0.000, 1.000, NULL, '2026-08-17 03:58:19', '2026-08-17 03:58:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_01_01_000001_add_profile_fields_to_users_table', 1),
(5, '2025_01_01_000002_create_diseases_table', 1),
(6, '2025_01_01_000003_create_symptoms_table', 1),
(7, '2025_01_01_000004_create_disease_symptom_table', 1),
(8, '2025_01_01_000005_create_rule_change_logs_table', 1),
(9, '2025_01_01_000006_create_assessments_table', 1),
(10, '2025_01_01_000007_create_assessment_answers_table', 1),
(11, '2025_01_01_000008_create_assessment_results_table', 1),
(12, '2025_01_01_000009_create_articles_table', 1),
(13, '2025_01_01_000010_create_referral_requests_table', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `referral_requests`
--

CREATE TABLE `referral_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `assessment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','dihubungi','selesai') NOT NULL DEFAULT 'pending',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `referral_requests`
--

INSERT INTO `referral_requests` (`id`, `user_id`, `assessment_id`, `status`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'pending', 'saya sangat butuh konseling tolong segera jadwalkan', '2026-08-17 04:19:28', '2026-08-17 04:19:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rule_change_logs`
--

CREATE TABLE `rule_change_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `disease_symptom_id` bigint(20) UNSIGNED NOT NULL,
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `old_mb` decimal(4,3) DEFAULT NULL,
  `old_md` decimal(4,3) DEFAULT NULL,
  `new_mb` decimal(4,3) NOT NULL,
  `new_md` decimal(4,3) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('rWQ0A4Tly2TN7G6za15GLoYi0N1AHdfp8TdYupDU', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiZ09SN01ETUV2OTFscnhZYTFSaTlOTTNGckpUREwyZXRLM1RUbkp5SiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQzOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXNzZXNzbWVudC8yL3JlZmVycmFsIjtzOjU6InJvdXRlIjtzOjE5OiJhc3Nlc3NtZW50LnJlZmVycmFsIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mjt9', 1788000073),
('SRCtZuvMxKefSXHnKKO9FDrtO564fyr1KP7SlxUo', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiU2pvQ0twdUEzMENmRWFLUGhkYzdKckZWOWZJTkxWZnpmNDBrVTBCRyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7fQ==', 1787232437);

-- --------------------------------------------------------

--
-- Struktur dari tabel `symptoms`
--

CREATE TABLE `symptoms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode` varchar(10) NOT NULL,
  `deskripsi` text NOT NULL,
  `kategori` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `symptoms`
--

INSERT INTO `symptoms` (`id`, `kode`, `deskripsi`, `kategori`, `created_at`, `updated_at`) VALUES
(1, 'G01', 'Saya merasa sulit untuk menenangkan diri.', 'stress', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(2, 'G02', 'Saya menyadari mulut saya terasa kering.', 'anxiety', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(3, 'G03', 'Saya sama sekali tidak bisa merasakan perasaan positif.', 'depression', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(4, 'G04', 'Saya mengalami kesulitan bernapas (napas cepat/sesak padahal tidak beraktivitas berat).', 'anxiety', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(5, 'G05', 'Saya merasa sulit untuk mulai bersemangat mengerjakan sesuatu.', 'depression', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(6, 'G06', 'Saya cenderung bereaksi berlebihan terhadap suatu situasi.', 'stress', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(7, 'G07', 'Saya merasakan tangan saya gemetar.', 'anxiety', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(8, 'G08', 'Saya merasa banyak menghabiskan energi karena merasa gelisah/cemas.', 'stress', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(9, 'G09', 'Saya khawatir akan situasi di mana saya bisa panik dan mempermalukan diri sendiri.', 'anxiety', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(10, 'G10', 'Saya merasa tidak ada lagi hal yang bisa saya nantikan.', 'depression', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(11, 'G11', 'Saya menyadari diri saya mudah merasa gelisah.', 'stress', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(12, 'G12', 'Saya merasa sulit untuk bersantai.', 'stress', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(13, 'G13', 'Saya merasa sedih dan murung.', 'depression', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(14, 'G14', 'Saya tidak sabar/tersinggung ketika ada hal yang menghambat saya menyelesaikan pekerjaan.', 'stress', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(15, 'G15', 'Saya merasa hampir panik.', 'anxiety', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(16, 'G16', 'Saya tidak bisa merasa antusias terhadap hal apa pun.', 'depression', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(17, 'G17', 'Saya merasa diri saya kurang berharga sebagai seseorang.', 'depression', '2026-08-17 03:58:17', '2026-08-17 03:58:17'),
(18, 'G18', 'Saya merasa mudah tersinggung akhir-akhir ini.', 'stress', '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(19, 'G19', 'Saya menyadari detak jantung saya terasa jelas padahal tidak sedang beraktivitas fisik.', 'anxiety', '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(20, 'G20', 'Saya merasa takut tanpa alasan yang jelas.', 'anxiety', '2026-08-17 03:58:18', '2026-08-17 03:58:18'),
(21, 'G21', 'Saya merasa hidup ini terasa tidak berarti.', 'depression', '2026-08-17 03:58:18', '2026-08-17 03:58:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nim` varchar(12) NOT NULL,
  `no_telp` text DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `fakultas` varchar(255) DEFAULT NULL,
  `program_studi` varchar(255) DEFAULT NULL,
  `role` enum('mahasiswa','admin') NOT NULL DEFAULT 'mahasiswa',
  `theme_preference` enum('light','dark') NOT NULL DEFAULT 'light',
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nim`, `no_telp`, `name`, `email`, `fakultas`, `program_studi`, `role`, `theme_preference`, `status`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '000000000000', 'eyJpdiI6Ik90MFVPZkczRmhaVVNaZkpoci80QWc9PSIsInZhbHVlIjoicTArQTJVUGdEYWZKS1hpZE80akJvdz09IiwibWFjIjoiZmI2M2NjOWIxZjNlYTE0YzM0OWY5Y2FjNjkyNzdjNWMzM2U0MDcxNDJkNjk3MzI4ZTAyNTNiYTQxNWI4Y2MwYSIsInRhZyI6IiJ9', 'Admin MyKonselor', 'admin@mykonselor.test', NULL, NULL, 'admin', 'light', 'aktif', '2026-08-17 03:58:15', '$2y$12$AHwm9d4A9zsPFeBBnnoF6ug.H.7INXmJOpaAWH/fvmYucC2jb0o0K', NULL, '2026-08-17 03:58:15', '2026-08-17 03:58:15', NULL),
(2, '123456789012', 'eyJpdiI6ImcxWkkwTlVKZ1ppMXYza3R5WjdJN0E9PSIsInZhbHVlIjoiZFRRaHAvbjQ0V2VBbEt5WStnOCs1UT09IiwibWFjIjoiZjJlOTJiZmZjMDZjMGU2MjMyM2ZlOWRkOGM5ZTBiZjQ5NjViMjJiODAzMjkwOTllNWM0Y2Q3Y2VlMWY5MDdkYiIsInRhZyI6IiJ9', 'Mahasiswa Contoh', 'mahasiswa@mykonselor.test', 'Ilmu Komputer', 'Sistem Informasi', 'mahasiswa', 'light', 'aktif', '2026-08-17 03:58:16', '$2y$12$ZDJW97.hqEBtxar4XUFd2e6l2VcGELAS7Y8.V3pC3C/L2OvCE9D02', NULL, '2026-08-17 03:58:16', '2026-08-17 05:50:08', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `articles_slug_unique` (`slug`);

--
-- Indeks untuk tabel `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assessments_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `assessment_answers`
--
ALTER TABLE `assessment_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assessment_answers_assessment_id_symptom_id_unique` (`assessment_id`,`symptom_id`),
  ADD KEY `assessment_answers_symptom_id_foreign` (`symptom_id`);

--
-- Indeks untuk tabel `assessment_results`
--
ALTER TABLE `assessment_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assessment_results_assessment_id_foreign` (`assessment_id`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `diseases`
--
ALTER TABLE `diseases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `diseases_kode_unique` (`kode`),
  ADD UNIQUE KEY `diseases_cluster_key_unique` (`cluster_key`);

--
-- Indeks untuk tabel `disease_symptom`
--
ALTER TABLE `disease_symptom`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `disease_symptom_disease_id_symptom_id_unique` (`disease_id`,`symptom_id`),
  ADD KEY `disease_symptom_symptom_id_foreign` (`symptom_id`),
  ADD KEY `disease_symptom_updated_by_foreign` (`updated_by`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `referral_requests`
--
ALTER TABLE `referral_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referral_requests_user_id_foreign` (`user_id`),
  ADD KEY `referral_requests_assessment_id_foreign` (`assessment_id`);

--
-- Indeks untuk tabel `rule_change_logs`
--
ALTER TABLE `rule_change_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rule_change_logs_disease_symptom_id_foreign` (`disease_symptom_id`),
  ADD KEY `rule_change_logs_changed_by_foreign` (`changed_by`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `symptoms`
--
ALTER TABLE `symptoms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `symptoms_kode_unique` (`kode`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_nim_unique` (`nim`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `articles`
--
ALTER TABLE `articles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `assessments`
--
ALTER TABLE `assessments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `assessment_answers`
--
ALTER TABLE `assessment_answers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT untuk tabel `assessment_results`
--
ALTER TABLE `assessment_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `diseases`
--
ALTER TABLE `diseases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `disease_symptom`
--
ALTER TABLE `disease_symptom`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `referral_requests`
--
ALTER TABLE `referral_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `rule_change_logs`
--
ALTER TABLE `rule_change_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `symptoms`
--
ALTER TABLE `symptoms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `assessments`
--
ALTER TABLE `assessments`
  ADD CONSTRAINT `assessments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `assessment_answers`
--
ALTER TABLE `assessment_answers`
  ADD CONSTRAINT `assessment_answers_assessment_id_foreign` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessment_answers_symptom_id_foreign` FOREIGN KEY (`symptom_id`) REFERENCES `symptoms` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `assessment_results`
--
ALTER TABLE `assessment_results`
  ADD CONSTRAINT `assessment_results_assessment_id_foreign` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `disease_symptom`
--
ALTER TABLE `disease_symptom`
  ADD CONSTRAINT `disease_symptom_disease_id_foreign` FOREIGN KEY (`disease_id`) REFERENCES `diseases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disease_symptom_symptom_id_foreign` FOREIGN KEY (`symptom_id`) REFERENCES `symptoms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disease_symptom_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `referral_requests`
--
ALTER TABLE `referral_requests`
  ADD CONSTRAINT `referral_requests_assessment_id_foreign` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `referral_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `rule_change_logs`
--
ALTER TABLE `rule_change_logs`
  ADD CONSTRAINT `rule_change_logs_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rule_change_logs_disease_symptom_id_foreign` FOREIGN KEY (`disease_symptom_id`) REFERENCES `disease_symptom` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
