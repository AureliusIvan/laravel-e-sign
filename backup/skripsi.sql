-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 22, 2024 at 08:28 AM
-- Server version: 10.11.8-MariaDB-cll-lve
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u858029431_skripsi`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `uuid`, `user_id`, `nama`, `created_at`, `updated_at`) VALUES
(1, '634e7c5d-e17b-49ee-ab93-1d4afe9451fd', 1, 'Administrator', '2024-08-14 02:36:32', '2024-08-14 02:36:32');

-- --------------------------------------------------------

--
-- Table structure for table `area_penelitian`
--

CREATE TABLE `area_penelitian` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `research_list_id` bigint(20) UNSIGNED NOT NULL,
  `kode_area_penelitian` varchar(20) NOT NULL,
  `keterangan` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `area_penelitian`
--

INSERT INTO `area_penelitian` (`id`, `uuid`, `research_list_id`, `kode_area_penelitian`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 'a3732161-6050-491d-a95a-a506cece84df', 12, 'HW01', 'Printed circuit boards', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(2, 'fa910e58-8876-4541-9b8a-65c0934b30d1', 12, 'HW02', 'Communication hardware, interfaces and storage', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(3, '4d9296ca-f8aa-4c7b-92d2-a3169135bc3b', 12, 'HW03', 'Integrated circuits', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(4, '4fb2348b-958d-4f4e-863a-368b937d13e5', 12, 'HW04', 'Very large scale integration design', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(5, 'a06dbd7b-2350-471f-bb87-ec027bad415e', 12, 'HW05', 'Power and energy', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(6, '3311332e-42a3-4fa3-b07e-216aebe4f200', 12, 'HW06', 'Electronic design automation', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(7, '7b6c8818-cbac-40a0-b1d5-c833bb94e582', 12, 'HW07', 'Hardware validation', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(8, '1253cc61-711b-492a-9979-a632c28c8fa0', 12, 'HW08', 'Hardware test', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(9, '73e473e6-2103-4df4-9c72-0ab28c15bf50', 12, 'HW09', 'Robustness', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(10, '9084674c-b06b-4a96-988e-0aa79ae558cc', 12, 'HW10', 'Emerging technologies', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(11, 'ae58c268-0c40-4e6d-95ee-81a5a67221bf', 1, 'CO01', 'Architectures', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(12, '9a52da1c-ff86-4a09-abc2-e9bc07290149', 1, 'CO02', 'Embedded and cyber-physical systems', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(13, '8f15c786-f853-4ca6-810a-d72d270a94b4', 1, 'CO03', 'Real-time systems', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(14, '423681b0-8d83-432a-9024-1f6a901a364f', 1, 'CO04', 'Dependable and fault-tolerant systems and networks', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(15, '1869d335-579a-4f84-8a09-e11da0e8692b', 3, 'NW01', 'Network architectures', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(16, '852a7db2-49dc-4a52-89d2-f323b1098a4c', 3, 'NW02', 'Network protocols', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(17, '19ecfaca-132a-4a3a-8f6e-fa12ba7cd458', 3, 'NW03', 'Network components', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(18, '4e878e27-318a-4b88-8ee2-8f71aeab09d5', 3, 'NW04', 'Network algorithms', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(19, 'd2c849da-30a6-4e7d-82f8-a9756e88b2f6', 3, 'NW05', 'Network performance evaluation', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(20, '87000f94-55b4-4fc2-bdf1-e62ab8586f9b', 3, 'NW06', 'Network properties', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(21, 'f85be838-09a7-4f95-a8ac-cb1f3bb127d6', 3, 'NW07', 'Network services', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(22, '360adeec-23d1-4408-a95a-5e24f3c5e670', 3, 'NW08', 'Network types', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(23, 'c2d74f1a-7ac6-4fe6-a555-c8e54599e512', 4, 'SE01', 'Software organization and properties', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(24, 'cf1c0666-4e67-41ad-939e-b4146201db4f', 4, 'SE02', 'Software notations and tools', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(25, 'bce1bd3b-d27c-48f3-a49c-bb9b1a77aee2', 4, 'SE03', 'Software creation and management', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(26, 'a2e4b173-4c65-4111-b336-3d640654c223', 5, 'TC01', 'Models of computation', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(27, 'b4e98125-64ec-4f0e-b424-b39dfa6426c1', 5, 'TC02', 'Formal languages and automata theory', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(28, '2d60f8c7-4ece-4cd7-bc1e-e293b152f442', 5, 'TC03', 'Computational complexity and cryptography', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(29, 'a4f7e1cc-30b4-41df-8f12-e0ba3823db59', 5, 'TC04', 'Logic', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(30, 'c74f607b-096a-4a14-9f56-a3e001990e96', 5, 'TC05', 'Design and analysis of algorithms', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(31, '39c06d23-5ad2-4838-abce-650e58684177', 5, 'TC06', 'Randomness, geometry and discrete structures', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(32, '5d713fd1-ce18-44e3-aae3-ea44af4d68f8', 5, 'TC07', 'Theory and algorithms for application domains', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(33, 'f4186b19-df4b-4a04-94da-e1bd78ce8c66', 5, 'TC08', 'Semantics and reasoning', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(34, '68733486-08f4-469b-9eb4-8c6c149a67ed', 6, 'MC01', 'Discrete mathematics', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(35, '97a8403b-e40a-45b7-abb1-6a3d51a74644', 6, 'MC02', 'Probability and statistics', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(36, '8a50a0ac-5fa1-46b6-b01a-b813d1a28720', 6, 'MC03', 'Mathematical software', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(37, '0f1afd56-5aff-431c-8ba9-26f82bbe7d48', 6, 'MC04', 'Information theory', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(38, 'fa2fe17b-79da-4e99-ada2-3ff99001f83c', 6, 'MC05', 'Mathematical analysis', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(39, 'ab446c24-e775-4269-9d9a-80c46d3e8465', 6, 'MC06', 'Continuous mathematics', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(40, '9ed0cffe-67d8-4331-aa2c-bedb528bbd6a', 7, 'IS01', 'Data management systems', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(41, '887d90d8-63ce-4250-8763-fb2cac61a894', 7, 'IS02', 'Information storage systems', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(42, '6dcbe616-e771-4da2-8385-1a78a7aff1c7', 7, 'IS03', 'Information systems applications', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(43, '0b5b11d2-c789-4d8b-8894-c0524b9c52da', 7, 'IS04', 'World Wide Web', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(44, '3cb80b06-62bf-4207-b6af-f79f1bbeecfe', 7, 'IS05', 'Information retrieval', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(45, '45ed9986-e971-49a0-9c36-89c96093920b', 8, 'SP01', 'Cryptography', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(46, '0af13842-8392-4d5a-ac99-34929a97e7b2', 8, 'SP02', 'Formal methods and theory of security', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(47, '0dfff85e-280a-4504-891b-47efb445216c', 8, 'SP03', 'Security services', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(48, '6e1cb9a6-31de-4736-b59a-301f093a89dd', 8, 'SP04', 'Intrusion/anomaly detection and malware mitigation', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(49, '250f7016-15e4-4bd1-bafd-05cbfd030843', 8, 'SP05', 'Security in hardware', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(50, 'ec27ad69-aefa-4d77-ac19-a92c8152ad74', 8, 'SP06', 'Systems security', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(51, '42162b10-53df-4873-aa1d-cba4fa37d10f', 8, 'SP07', 'Network security', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(52, 'e183c268-7097-4d0b-b716-c51bb556ef36', 8, 'SP08', 'Database and storage security', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(53, '11aac34d-691c-487e-a523-3f9cffd571a6', 8, 'SP09', 'Software and application security', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(54, '3092bb25-4602-4f11-bbbd-8dffed5836b9', 8, 'SP10', 'Human and societal aspects of security and privacy', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(55, 'e215df5c-65ef-4510-8f24-2c1a31ba874d', 9, 'HC01', 'Human computer interaction', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(56, '9488aa6a-c8c8-4c64-bee1-e0c02521e531', 9, 'HC02', 'Interaction design', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(57, 'f1527623-8295-4bd2-9b80-fe4d4f0b5050', 9, 'HC03', 'Collaborative and social computing', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(58, '267eb398-803e-4386-95a1-a7ed10fbd1e6', 9, 'HC04', 'Ubiquitous and mobile computing', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(59, '3c3b2256-efb3-421c-b7c7-d97c1e79dba8', 9, 'HC05', 'Visualization', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(60, '003d88b3-3a53-41a0-9a67-0ea987831fb7', 9, 'HC06', 'Accessibility', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(61, '3f579fcf-e26e-4fd1-820f-8ad6fbf77ff0', 10, 'CM01', 'Symbolic and algebraic manipulation', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(62, 'f4a5addd-ca9b-4f2c-91ce-1c9e0ee3a7b6', 10, 'CM02', 'Parallel computing methodologies', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(63, 'a50e6346-8563-45f9-98c1-d04fdeab02f1', 10, 'CM03', 'Artificial intelligence', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(64, 'a13c5f86-092d-4afe-a956-46944d758c25', 10, 'CM04', 'Machine learning', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(65, 'bfb139b9-5ae5-4169-a645-aabff8b93191', 10, 'CM05', 'Modeling and simulation', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(66, 'c78c2b2c-f8bc-4ec3-9751-226c2c823be6', 10, 'CM06', 'Computer graphics', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(67, '80f0bc1a-de08-4417-8adc-feadcc888232', 10, 'CM07', 'Distributed computing methodologies', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(68, '5231f99f-a5eb-41a4-b79e-d850051aebcb', 10, 'CM08', 'Concurrent computing methodologies', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(69, 'cf6a7fea-db7a-4120-b96d-643c3718c545', 11, 'AC01', 'Electronic commerce', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(70, '09f3302b-a169-494a-9c5a-a03ea3b09585', 11, 'AC02', 'Enterprise computing', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(71, 'fd042db5-495e-4fe7-a9ca-19ba3679cdbd', 11, 'AC03', 'Physical sciences and engineering', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(72, 'c4985850-6a73-481e-bf5b-1638c43f0cf7', 11, 'AC04', 'Life and medical sciences', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(73, '71c8b6c7-6b2b-479e-91a7-4d826057b7a3', 11, 'AC05', 'Law, social and behavioral sciences', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(74, '761b32a8-ef04-4365-806b-21472b88e571', 11, 'AC06', 'Computer forensics', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(75, 'dfe38171-d372-4e37-bfa6-ec29bcf28461', 11, 'AC07', 'Arts and humanities', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(76, 'd92e1dcf-0941-4ab7-9f2e-f74f30648f2e', 11, 'AC08', 'Computers in other domains', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(77, '39f0b8d2-6ddd-40e7-a5cb-1fa4515b693d', 11, 'AC09', 'Operations research', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(78, '1f2715ec-f643-46aa-91a0-87e7e93cf0dc', 11, 'AC10', 'Education', '2024-08-18 08:54:05', '2024-08-18 08:54:05'),
(79, '2a4503ff-b0f5-4a2f-a833-b9b72b6d8adf', 11, 'AC11', 'Document management and text processing', '2024-08-18 08:54:05', '2024-08-18 08:54:05');

-- --------------------------------------------------------

--
-- Table structure for table `berita_acara`
--

CREATE TABLE `berita_acara` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `dosen_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal_awal` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `isi_berita` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `berita_acara`
--

INSERT INTO `berita_acara` (`id`, `uuid`, `tahun_ajaran_id`, `program_studi_id`, `dosen_id`, `tanggal_awal`, `tanggal_akhir`, `isi_berita`, `created_at`, `updated_at`) VALUES
(1, 'eac83a7e-85c5-49f8-bd67-26aafcc03373', 1, 1, 1, '' ||
                                                     '2024-08-17', '2024-08-17', 'Isi berita acara pertama', '2024-08-16 02:04:27', '2024-08-16 03:11:59'),
(3, 'bff7c0c5-c5ef-4c54-91ec-7a65b7a5aa60', 1, 1, 1, '2024-08-18', '2024-08-20', 'Cari Pembimbing', '2024-08-17 22:52:17', '2024-08-17 22:52:17');

-- --------------------------------------------------------

--
-- Table structure for table `bimbingan`
--

CREATE TABLE `bimbingan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `mahasiswa_id` bigint(20) UNSIGNED NOT NULL,
  `dosen_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal_bimbingan` date NOT NULL,
  `isi_bimbingan` text NOT NULL,
  `saran` text NOT NULL,
  `status` tinyint(4) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bimbingan`
--

INSERT INTO `bimbingan` (`id`, `uuid`, `tahun_ajaran_id`, `program_studi_id`, `mahasiswa_id`, `dosen_id`, `tanggal_bimbingan`, `isi_bimbingan`, `saran`, `status`, `note`, `created_at`, `updated_at`) VALUES
(3, 'b7add9a5-858e-44f6-82fe-d37c952ed3e5', 1, 1, 1, 1, '2024-08-23', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Assumenda iste ex optio accusantium dicta id vero officiis dignissimos praesentium similique quae reiciendis asperiores repudiandae quasi eum perferendis dolorem doloribus, distinctio culpa omnis laudantium? Incidunt illum fuga magni perferendis architecto itaque excepturi porro, nihil culpa, ullam aut commodi a saepe temporibus!', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Assumenda iste ex optio accusantium dicta id vero officiis dignissimos praesentium similique quae reiciendis asperiores repudiandae quasi eum perferendis dolorem doloribus, distinctio culpa omnis laudantium? Incidunt illum fuga magni perferendis architecto itaque excepturi porro, nihil culpa, ullam aut commodi a saepe temporibus!', 0, 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita mollitia atque consequuntur sunt natus? Repudiandae perspiciatis porro nihil. Explicabo maxime iste facere obcaecati, ullam pariatur numquam eum provident iusto praesentium recusandae ipsum consequatur saepe fugiat nisi placeat ad incidunt magnam cum consectetur? Aspernatur consequuntur dicta molestias pariatur tempore! Vitae, quasi.', '2024-08-22 22:59:30', '2024-08-24 03:46:58');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nid` varchar(20) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id`, `uuid`, `user_id`, `nid`, `nama`, `program_studi_id`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 'b7fc01ab-0c1c-4ee9-bf7d-1b0a795674ea', 2, '000001', 'Dosen 1', 1, 1, '2024-08-14 02:47:01', '2024-08-14 02:47:01'),
(2, '8874f35f-163e-4b05-a8a6-f9c2157ecb66', 3, '000002', 'Dosen 2', 1, 1, '2024-08-14 02:47:01', '2024-08-15 22:24:01'),
(3, '962c841d-3c83-451c-918f-b4530ab27db6', 6, '000003', 'Dosen 3', 1, 1, '2024-08-15 22:23:52', '2024-08-15 22:23:52'),
(4, '59d2418e-6eb9-43a6-bca3-f91e373e73d0', 8, '000004', 'Wirawan Istiono', 1, 1, '2024-08-26 13:08:14', '2024-08-26 13:08:14'),
(5, '8e0484dd-0144-4e41-900e-bfa8c42ba611', 9, '000005', 'Adhi Kusnadi', 1, 1, '2024-08-26 13:09:28', '2024-08-26 13:09:28');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
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
-- Table structure for table `jadwal_sidang`
--

CREATE TABLE `jadwal_sidang` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `laporan_akhir_id` bigint(20) UNSIGNED NOT NULL,
  `mahasiswa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jadwal_sidang` datetime DEFAULT NULL,
  `ruang_sidang` datetime DEFAULT NULL,
  `pembimbing1` bigint(20) UNSIGNED DEFAULT NULL,
  `file_pembimbing1` varchar(510) DEFAULT NULL,
  `file_random_pembimbing1` varchar(510) DEFAULT NULL,
  `keputusan_sidang_pembimbing1` tinyint(4) DEFAULT NULL,
  `berita_acara_pembimbing1` text DEFAULT NULL,
  `pembimbing2` bigint(20) UNSIGNED DEFAULT NULL,
  `file_pembimbing2` varchar(510) DEFAULT NULL,
  `file_random_pembimbing2` varchar(510) DEFAULT NULL,
  `keputusan_sidang_pembimbing2` tinyint(4) DEFAULT NULL,
  `berita_acara_pembimbing2` text DEFAULT NULL,
  `ketua_sidang` bigint(20) UNSIGNED DEFAULT NULL,
  `file_ketua_sidang` varchar(510) DEFAULT NULL,
  `file_random_ketua_sidang` varchar(510) DEFAULT NULL,
  `keputusan_sidang_ketua_sidang` tinyint(4) DEFAULT NULL,
  `berita_acara_ketua_sidang` text DEFAULT NULL,
  `penguji` bigint(20) UNSIGNED DEFAULT NULL,
  `file_penguji` varchar(510) DEFAULT NULL,
  `file_random_penguji` varchar(510) DEFAULT NULL,
  `keputusan_sidang_penguji` tinyint(4) DEFAULT NULL,
  `berita_acara_penguji` text DEFAULT NULL,
  `keputusan_akhir` tinyint(4) DEFAULT NULL,
  `ganti_judul` tinyint(1) DEFAULT NULL,
  `pengumpulan_laporan_dibuka` datetime DEFAULT NULL,
  `pengumpulan_laporan_ditutup` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
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
-- Table structure for table `job_batches`
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
-- Table structure for table `jurnal`
--

CREATE TABLE `jurnal` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jurnal_form`
--

CREATE TABLE `jurnal_form` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `judul_form` text NOT NULL,
  `keterangan` text DEFAULT NULL,
  `dibuka` datetime NOT NULL,
  `ditutup` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori_nilai`
--

CREATE TABLE `kategori_nilai` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `kategori` text NOT NULL,
  `persentase` tinyint(4) NOT NULL,
  `user` varchar(30) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori_nilai_detail`
--

CREATE TABLE `kategori_nilai_detail` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `kategori_nilai_id` bigint(20) UNSIGNED NOT NULL,
  `detail_kategori` text NOT NULL,
  `detail_persentase` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kode_penelitian_proposal`
--

CREATE TABLE `kode_penelitian_proposal` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `proposal_skripsi_id` bigint(20) UNSIGNED NOT NULL,
  `area_penelitian_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kode_penelitian_proposal`
--

INSERT INTO `kode_penelitian_proposal` (`id`, `uuid`, `proposal_skripsi_id`, `area_penelitian_id`, `created_at`, `updated_at`) VALUES
(1, 'da53c81c-c593-47af-9104-2c906d3fef73', 5, 56, '2024-08-25 04:27:29', '2024-08-25 04:27:29'),
(2, '7f1d071d-ad98-417e-a894-cf54608a13de', 5, 57, '2024-08-25 04:27:29', '2024-08-25 04:27:29'),
(6, '51cce4cd-08b8-431f-bef5-9607f66256ff', 7, 45, '2024-08-25 04:42:11', '2024-08-25 04:42:11'),
(7, 'c461b6c5-d867-4087-acb4-ec2b8fc16c79', 7, 48, '2024-08-25 04:42:11', '2024-08-25 04:42:11');

-- --------------------------------------------------------

--
-- Table structure for table `laporan_akhir`
--

CREATE TABLE `laporan_akhir` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `revisi_proposal_id` bigint(20) UNSIGNED NOT NULL,
  `mahasiswa_id` bigint(20) UNSIGNED NOT NULL,
  `judul_laporan` text NOT NULL,
  `file_laporan` varchar(510) NOT NULL,
  `file_laporan_random` varchar(510) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `pembimbing1` bigint(20) UNSIGNED DEFAULT NULL,
  `file_pembimbing1` varchar(510) DEFAULT NULL,
  `file_random_pembimbing1` varchar(510) DEFAULT NULL,
  `status_approval_pembimbing1` tinyint(4) DEFAULT NULL,
  `note_pembimbing1` text DEFAULT NULL,
  `tanggal_approval_pembimbing1` date DEFAULT NULL,
  `pembimbing2` bigint(20) UNSIGNED DEFAULT NULL,
  `file_pembimbing2` varchar(510) DEFAULT NULL,
  `file_random_pembimbing2` varchar(510) DEFAULT NULL,
  `status_approval_pembimbing2` tinyint(4) DEFAULT NULL,
  `note_pembimbing2` text DEFAULT NULL,
  `tanggal_approval_pembimbing2` date DEFAULT NULL,
  `file_kaprodi` varchar(510) DEFAULT NULL,
  `file_random_kaprodi` varchar(510) DEFAULT NULL,
  `status_approval_kaprodi` tinyint(4) DEFAULT NULL,
  `note_kaprodi` text DEFAULT NULL,
  `tanggal_approval_kaprodi` date DEFAULT NULL,
  `status_akhir` tinyint(4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laporan_akhir_form`
--

CREATE TABLE `laporan_akhir_form` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `judul_form` text NOT NULL,
  `keterangan` text DEFAULT NULL,
  `dibuka` datetime NOT NULL,
  `ditutup` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `angkatan` smallint(6) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status_aktif_skripsi` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `uuid`, `user_id`, `nim`, `nama`, `program_studi_id`, `angkatan`, `tahun_ajaran_id`, `status_aktif_skripsi`, `created_at`, `updated_at`) VALUES
(1, 'f607a5db-10be-458c-8088-a0b0155d3078', 4, '00000000001', 'Mahasiswa 1', 1, 2020, 1, 1, '2024-08-14 02:47:02', '2024-08-14 05:29:35'),
(2, 'a3f6a8fe-87b9-490e-a414-b9fd180e9438', 5, '00000000002', 'Mahasiswa 2', 1, 2020, 1, 1, '2024-08-14 02:47:02', '2024-08-14 05:29:35'),
(3, '4b27eb7c-b112-45a1-8579-169fb8f6a5b5', 7, '00000000003', 'Mahasiswa 3', 1, 2020, NULL, 0, '2024-08-25 20:06:50', '2024-08-25 20:06:50');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_08_09_035930_create_admins_table', 1),
(5, '2024_08_09_040143_create_dosens_table', 1),
(6, '2024_08_09_040329_create_program_studis_table', 1),
(7, '2024_08_09_041714_create_tahun_ajarans_table', 1),
(8, '2024_08_09_042534_create_mahasiswas_table', 1),
(9, '2024_08_10_021851_create_pengaturans_table', 1),
(10, '2024_08_10_033244_create_pengaturan_details_table', 1),
(11, '2024_08_10_072329_create_berita_acaras_table', 1),
(12, '2024_08_10_073115_create_bimbingans_table', 1),
(13, '2024_08_10_075028_create_research_lists_table', 1),
(14, '2024_08_10_075513_create_research_dosens_table', 1),
(15, '2024_08_10_093629_create_permintaan_mahasiswa_forms_table', 1),
(16, '2024_08_10_094030_create_permintaan_mahasiswas_table', 1),
(17, '2024_08_10_101948_create_proposal_skripsi_forms_table', 1),
(18, '2024_08_10_102649_create_proposal_skripsis_table', 1),
(19, '2024_08_10_143521_create_revisi_proposal_forms_table', 1),
(20, '2024_08_10_150912_create_revisi_proposals_table', 1),
(21, '2024_08_10_152810_create_laporan_akhir_forms_table', 1),
(22, '2024_08_10_153517_create_laporan_akhirs_table', 1),
(23, '2024_08_10_155730_create_jadwal_sidangs_table', 1),
(24, '2024_08_11_022324_create_kategori_nilais_table', 1),
(25, '2024_08_11_022859_create_kategori_nilai_details_table', 1),
(26, '2024_08_11_035419_create_nilai_akhirs_table', 1),
(27, '2024_08_11_041327_create_nilai_sidangs_table', 1),
(28, '2024_08_11_072104_create_revisi_laporans_table', 1),
(29, '2024_08_10_102648_create_area_penelitians_table', 2),
(30, '2024_08_16_144039_create_pembimbing_mahasiswas_table', 2),
(31, '2024_08_17_022227_create_jurnal_forms_table', 2),
(32, '2024_08_17_022343_create_poster_forms_table', 2),
(33, '2024_08_17_023400_create_source_code_forms_table', 2),
(34, '2024_08_17_024652_create_jurnals_table', 2),
(35, '2024_08_17_024910_create_posters_table', 2),
(36, '2024_08_17_024916_create_source_codes_table', 2),
(37, '2024_08_25_100502_create_kode_penelitian_proposals_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `nilai_akhir`
--

CREATE TABLE `nilai_akhir` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `jadwal_sidang_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mahasiswa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pembimbing1` bigint(20) UNSIGNED DEFAULT NULL,
  `nilai_pembimbing1` double DEFAULT NULL,
  `pembimbing2` bigint(20) UNSIGNED DEFAULT NULL,
  `nilai_pembimbing2` double DEFAULT NULL,
  `total_nilai_pembimbing` double DEFAULT NULL,
  `penguji` bigint(20) UNSIGNED DEFAULT NULL,
  `nilai_penguji` double DEFAULT NULL,
  `ketua_sidang` bigint(20) UNSIGNED DEFAULT NULL,
  `nilai_ketua_sidang` double DEFAULT NULL,
  `nilai_akhir` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nilai_sidang`
--

CREATE TABLE `nilai_sidang` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `mahasiswa_id` bigint(20) UNSIGNED NOT NULL,
  `dosen_id` bigint(20) UNSIGNED NOT NULL,
  `nilai_akhir_id` bigint(20) UNSIGNED NOT NULL,
  `kategori_nilai_detail_id` bigint(20) UNSIGNED NOT NULL,
  `nilai` double NOT NULL,
  `is_editable` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembimbing_mahasiswa`
--

CREATE TABLE `pembimbing_mahasiswa` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `mahasiswa` bigint(20) UNSIGNED DEFAULT NULL,
  `pembimbing1` bigint(20) UNSIGNED DEFAULT NULL,
  `pembimbing2` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pembimbing_mahasiswa`
--

INSERT INTO `pembimbing_mahasiswa` (`id`, `uuid`, `tahun_ajaran_id`, `program_studi_id`, `mahasiswa`, `pembimbing1`, `pembimbing2`, `created_at`, `updated_at`) VALUES
(1, '64445647-a6a1-43af-9003-f4c433b15923', 1, 1, 1, 1, NULL, '2024-08-21 23:50:24', '2024-08-26 08:28:13'),
(2, 'a7850a46-ec1b-4c52-a644-6847ace920fc', 1, 1, 2, 3, NULL, '2024-08-26 08:26:00', '2024-08-26 08:33:57');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `penamaan_proposal` tinyint(1) DEFAULT NULL,
  `penamaan_revisi_proposal` tinyint(1) DEFAULT NULL,
  `penamaan_laporan` tinyint(1) DEFAULT NULL,
  `penamaan_revisi_laporan` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `uuid`, `tahun_ajaran_id`, `program_studi_id`, `penamaan_proposal`, `penamaan_revisi_proposal`, `penamaan_laporan`, `penamaan_revisi_laporan`, `created_at`, `updated_at`) VALUES
(1, 'b458439d-c94f-44ae-b653-aa61cd6eefd2', 1, 1, 1, 1, 1, 1, '2024-08-15 20:55:15', '2024-08-15 20:55:15');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan_detail`
--

CREATE TABLE `pengaturan_detail` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `pengaturan_id` bigint(20) UNSIGNED NOT NULL,
  `kuota_pembimbing_pertama` smallint(6) DEFAULT NULL,
  `kuota_pembimbing_kedua` smallint(6) DEFAULT NULL,
  `minimum_jumlah_bimbingan` smallint(6) DEFAULT NULL,
  `minimum_jumlah_bimbingan_kedua` smallint(6) DEFAULT NULL,
  `tahun_rti_tersedia_sampai` smallint(6) DEFAULT NULL,
  `semester_rti_tersedia_sampai` varchar(10) DEFAULT NULL,
  `tahun_proposal_tersedia_sampai` smallint(6) DEFAULT NULL,
  `semester_proposal_tersedia_sampai` varchar(10) DEFAULT NULL,
  `penamaan_proposal` varchar(255) NOT NULL,
  `penamaan_revisi_proposal` varchar(255) DEFAULT NULL,
  `penamaan_laporan` varchar(255) DEFAULT NULL,
  `penamaan_revisi_laporan` varchar(255) DEFAULT NULL,
  `jumlah_setuju_proposal` smallint(6) DEFAULT NULL,
  `jumlah_setuju_sidang_satupembimbing` smallint(6) DEFAULT NULL,
  `jumlah_setuju_sidang_duapembimbing` smallint(6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengaturan_detail`
--

INSERT INTO `pengaturan_detail` (`id`, `uuid`, `pengaturan_id`, `kuota_pembimbing_pertama`, `kuota_pembimbing_kedua`, `minimum_jumlah_bimbingan`, `minimum_jumlah_bimbingan_kedua`, `tahun_rti_tersedia_sampai`, `semester_rti_tersedia_sampai`, `tahun_proposal_tersedia_sampai`, `semester_proposal_tersedia_sampai`, `penamaan_proposal`, `penamaan_revisi_proposal`, `penamaan_laporan`, `penamaan_revisi_laporan`, `jumlah_setuju_proposal`, `jumlah_setuju_sidang_satupembimbing`, `jumlah_setuju_sidang_duapembimbing`, `created_at`, `updated_at`) VALUES
(1, '34f51962-3e64-4edf-b536-7599328e6e9b', 1, 10, 10, 8, 6, 2024, 'genap', 2025, 'genap', 'nim_nama_judul', 'nim_nama_judul', 'nim_nama_judul', 'nim_nama_judul', 3, 3, 4, '2024-08-15 20:55:15', '2024-08-15 20:55:15');

-- --------------------------------------------------------

--
-- Table structure for table `permintaan_mahasiswa`
--

CREATE TABLE `permintaan_mahasiswa` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `permintaan_mahasiswa_form_id` bigint(20) UNSIGNED NOT NULL,
  `mahasiswa_id` bigint(20) UNSIGNED NOT NULL,
  `dosen_id` bigint(20) UNSIGNED NOT NULL,
  `research_list_id` bigint(20) UNSIGNED NOT NULL,
  `is_rti` tinyint(4) DEFAULT NULL,
  `is_uploaded` tinyint(4) DEFAULT NULL,
  `file_pendukung` varchar(510) DEFAULT NULL,
  `file_pendukung_random` varchar(255) DEFAULT NULL,
  `note_mahasiswa` text DEFAULT NULL,
  `status_pembimbing` tinyint(4) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `note_dosen` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permintaan_mahasiswa`
--

INSERT INTO `permintaan_mahasiswa` (`id`, `uuid`, `permintaan_mahasiswa_form_id`, `mahasiswa_id`, `dosen_id`, `research_list_id`, `is_rti`, `is_uploaded`, `file_pendukung`, `file_pendukung_random`, `note_mahasiswa`, `status_pembimbing`, `status`, `note_dosen`, `created_at`, `updated_at`) VALUES
(1, '0ca5ee2e-d982-4a25-b6b5-897a5578a811', 1, 1, 1, 1, 1, 0, 'file_proposal.pdf', '20240819140553_IwD8oLhVwwjHiPs6G2X6yievXCP9JeIOQ020G0Em.pdf', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Laudantium in modi eius consequuntur omnis placeat recusandae cum reiciendis corporis ipsum unde a, quae aliquam minus et voluptas vitae minima consequatur fuga. Debitis dolorum, odit itaque fuga delectus vitae id, adipisci commodi perspiciatis quis dignissimos accusamus quisquam sunt ullam magni asperiores laborum quos iure expedita! Magni saepe, tenetur quam iusto ea enim ipsam a dolorum eveniet! Sed eos tempora deserunt distinctio, nostrum modi nam est consequuntur? Optio, dolorem quo. Voluptates officia repellat nostrum dolorum, voluptate aliquid ut, dolorem doloribus expedita nam praesentium dolore blanditiis impedit pariatur. Aperiam repellat consequatur natus saepe dolorum perspiciatis incidunt unde tempora consequuntur explicabo laborum aliquid, excepturi reiciendis nemo assumenda molestias, nesciunt odio sunt eum. Deleniti blanditiis inventore ipsa maxime error id labore iure, libero facere animi ut quisquam et debitis sit velit aliquid! Reiciendis quas sapiente sequi deleniti mollitia libero aliquam ab magni veritatis nesciunt provident obcaecati similique accusantium, doloribus id eos laborum quaerat sint totam iure vel dolorem. Expedita nisi harum id inventore doloremque magnam, iste eligendi voluptas quia illo consequatur cum repellat modi excepturi. Dolorem reprehenderit nulla quibusdam neque in atque assumenda, laudantium, culpa aperiam nihil ipsa libero earum, excepturi eligendi saepe! Sit, consequuntur!', 1, 1, 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Laudantium in modi eius consequuntur omnis placeat recusandae cum reiciendis corporis ipsum unde a, quae aliquam minus et voluptas vitae minima consequatur fuga. Debitis dolorum, odit itaque fuga delectus vitae id, adipisci commodi perspiciatis quis dignissimos accusamus quisquam sunt ullam magni asperiores laborum quos iure expedita!', '2024-08-19 07:05:53', '2024-08-21 23:50:24');

-- --------------------------------------------------------

--
-- Table structure for table `permintaan_mahasiswa_form`
--

CREATE TABLE `permintaan_mahasiswa_form` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `judul_form` text NOT NULL,
  `keterangan` text DEFAULT NULL,
  `dibuka` datetime NOT NULL,
  `ditutup` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permintaan_mahasiswa_form`
--

INSERT INTO `permintaan_mahasiswa_form` (`id`, `uuid`, `tahun_ajaran_id`, `program_studi_id`, `judul_form`, `keterangan`, `dibuka`, `ditutup`, `created_at`, `updated_at`) VALUES
(1, '08bb9597-e5aa-48a4-8cd8-0fd18341a62a', 1, 1, 'Cari Dosen Pembimbing', 'Silahkan cari dosen pembimbing sesuai dengan penelitian yang anda buat.', '2024-08-17 00:00:00', '2024-08-23 23:59:59', '2024-08-17 22:52:17', '2024-08-21 22:59:58');

-- --------------------------------------------------------

--
-- Table structure for table `posters`
--

CREATE TABLE `posters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `poster_form`
--

CREATE TABLE `poster_form` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `judul_form` text NOT NULL,
  `keterangan` text DEFAULT NULL,
  `dibuka` datetime NOT NULL,
  `ditutup` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program_studi`
--

CREATE TABLE `program_studi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `program_studi` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `program_studi`
--

INSERT INTO `program_studi` (`id`, `uuid`, `program_studi`, `created_at`, `updated_at`) VALUES
(1, '159fbbab-54ce-4401-aad2-21dd117d2ea5', 'Informatika', '2024-08-14 02:36:32', '2024-08-14 02:36:32');

-- --------------------------------------------------------

--
-- Table structure for table `proposal_skripsi`
--

CREATE TABLE `proposal_skripsi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `proposal_skripsi_form_id` bigint(20) UNSIGNED NOT NULL,
  `mahasiswa_id` bigint(20) UNSIGNED NOT NULL,
  `judul_proposal` text NOT NULL,
  `file_proposal` varchar(510) NOT NULL,
  `file_proposal_random` varchar(510) NOT NULL,
  `file_proposal_mime` varchar(100) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `penilai1` bigint(20) UNSIGNED DEFAULT NULL,
  `file_penilai1` varchar(510) DEFAULT NULL,
  `file_random_penilai1` varchar(510) DEFAULT NULL,
  `file_penilai1_mime` varchar(100) NOT NULL,
  `status_approval_penilai1` tinyint(4) DEFAULT NULL,
  `tanggal_approval_penilai1` date DEFAULT NULL,
  `penilai2` bigint(20) UNSIGNED DEFAULT NULL,
  `file_penilai2` varchar(510) DEFAULT NULL,
  `file_random_penilai2` varchar(510) DEFAULT NULL,
  `file_penilai2_mime` varchar(100) NOT NULL,
  `status_approval_penilai2` tinyint(4) DEFAULT NULL,
  `tanggal_approval_penilai2` date DEFAULT NULL,
  `penilai3` bigint(20) UNSIGNED DEFAULT NULL,
  `file_penilai3` varchar(510) DEFAULT NULL,
  `file_random_penilai3` varchar(510) DEFAULT NULL,
  `file_penilai3_mime` varchar(100) NOT NULL,
  `status_approval_penilai3` tinyint(4) DEFAULT NULL,
  `tanggal_approval_penilai3` date DEFAULT NULL,
  `status_akhir` tinyint(4) DEFAULT NULL,
  `available_at` varchar(20) DEFAULT NULL,
  `available_until` varchar(20) DEFAULT NULL,
  `is_expired` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `proposal_skripsi`
--

INSERT INTO `proposal_skripsi` (`id`, `uuid`, `proposal_skripsi_form_id`, `mahasiswa_id`, `judul_proposal`, `file_proposal`, `file_proposal_random`, `file_proposal_mime`, `status`, `penilai1`, `file_penilai1`, `file_random_penilai1`, `file_penilai1_mime`, `status_approval_penilai1`, `tanggal_approval_penilai1`, `penilai2`, `file_penilai2`, `file_random_penilai2`, `file_penilai2_mime`, `status_approval_penilai2`, `tanggal_approval_penilai2`, `penilai3`, `file_penilai3`, `file_random_penilai3`, `file_penilai3_mime`, `status_approval_penilai3`, `tanggal_approval_penilai3`, `status_akhir`, `available_at`, `available_until`, `is_expired`, `created_at`, `updated_at`) VALUES
(5, '50597274-2407-4814-9a92-83395cc03953', 1, 1, 'Judul Skripsi Tentang Human Centered Computing', '00000000001_Mahasiswa1_JudulProposalSkripsiTentang', '20240825112729_lSh6UVrD65lb1z9YUTrnlOCJekIwUIilwrKHnHnu.pdf', 'application/pdf', 1, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2024-08-25 04:27:29', '2024-08-25 04:27:29'),
(7, 'e2362952-ddfb-4725-81a0-183999cd8e96', 1, 2, 'Judul Skripsi Tentang Keamanan Komputer', '00000000002_Mahasiswa2_ProposalSkripsi', '20240825114211_M1LJEAP4yhRJIwau59CMy5PGX6TVcF2eWiFrgJuW.pdf', 'application/pdf', 1, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2024-08-25 04:42:11', '2024-08-25 04:42:11');

-- --------------------------------------------------------

--
-- Table structure for table `proposal_skripsi_form`
--

CREATE TABLE `proposal_skripsi_form` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `judul_form` text NOT NULL,
  `keterangan` text DEFAULT NULL,
  `dibuka` datetime NOT NULL,
  `ditutup` datetime NOT NULL,
  `deadline_penilaian` datetime DEFAULT NULL,
  `publish_dosen` tinyint(4) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `proposal_skripsi_form`
--

INSERT INTO `proposal_skripsi_form` (`id`, `uuid`, `tahun_ajaran_id`, `program_studi_id`, `judul_form`, `keterangan`, `dibuka`, `ditutup`, `deadline_penilaian`, `publish_dosen`, `created_at`, `updated_at`) VALUES
(1, '08414b34-275c-4aa1-85a6-2b2fed2719e2', 1, 1, 'Pengumpulan Skripsi Batch I', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsam esse impedit asperiores blanditiis sint fugit, suscipit sequi? Officiis eius odit tempore praesentium consectetur magni odio explicabo iure officia, soluta porro accusantium. Veritatis perspiciatis, obcaecati in, animi cupiditate pariatur ullam impedit reiciendis dicta delectus saepe odit voluptatem id est labore eos.', '2024-08-24 00:00:00', '2024-08-31 23:59:59', '2024-09-07 23:59:59', 0, '2024-08-24 07:19:45', '2024-08-24 07:19:45'),
(2, 'a130d60b-2092-4fbc-8064-c5e3275919b9', 1, 1, 'Pengumpulan Skripsi Batch II', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Vero quibusdam iure distinctio voluptates. Hic alias quidem, odio, aut, animi laboriosam labore odit saepe eveniet distinctio quam minus numquam totam magni quia optio vero molestias suscipit sapiente. Vero, alias! Quae quis non maiores deserunt! Veritatis velit, praesentium atque saepe cum deserunt?', '2024-09-02 00:00:00', '2024-09-09 23:59:59', '2024-09-16 23:59:59', 0, '2024-08-25 00:35:08', '2024-08-25 00:35:08');

-- --------------------------------------------------------

--
-- Table structure for table `research_dosen`
--

CREATE TABLE `research_dosen` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `dosen_id` bigint(20) UNSIGNED NOT NULL,
  `research_list_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `research_dosen`
--

INSERT INTO `research_dosen` (`id`, `uuid`, `program_studi_id`, `dosen_id`, `research_list_id`, `created_at`, `updated_at`) VALUES
(6, '523ba6bc-aa8d-4c46-af47-6e46dd8b95d1', 1, 1, 1, '2024-08-17 06:59:24', '2024-08-17 06:59:24'),
(7, 'd8f385d4-58d8-4739-8c00-fdd70b6bdc45', 1, 1, 5, '2024-08-17 06:59:24', '2024-08-17 06:59:24'),
(8, 'd43581a7-a588-4721-b193-82eca5d644b8', 1, 1, 9, '2024-08-17 06:59:24', '2024-08-17 06:59:24');

-- --------------------------------------------------------

--
-- Table structure for table `research_list`
--

CREATE TABLE `research_list` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `topik_penelitian` varchar(255) NOT NULL,
  `kode_penelitian` varchar(10) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `research_list`
--

INSERT INTO `research_list` (`id`, `uuid`, `program_studi_id`, `topik_penelitian`, `kode_penelitian`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, '560121e4-9479-4155-9894-b197ad4f920c', 1, 'Computer Organization', 'CO', NULL, '2024-08-16 20:55:24', '2024-08-16 20:55:24'),
(3, '40bfb64d-2157-46b7-81cb-3196ca58bb19', 1, 'Network', 'NW', NULL, '2024-08-16 21:27:33', '2024-08-18 06:22:40'),
(4, '7b67b879-33e3-40d2-b734-40067ad09858', 1, 'Software and it\'s Engineering', 'SE', NULL, '2024-08-16 21:28:08', '2024-08-16 21:28:08'),
(5, '04e49375-929b-458a-8053-4fd9ad345ad1', 1, 'Theory of Computation', 'TC', NULL, '2024-08-16 21:28:28', '2024-08-16 21:28:28'),
(6, '13af96b9-e0d4-4978-9807-6f244f300a0b', 1, 'Mathematics of Computing', 'MC', NULL, '2024-08-16 21:28:42', '2024-08-16 21:28:42'),
(7, 'c4d23652-d258-44d8-8dd5-a152c661d3e6', 1, 'Informatics System', 'IS', NULL, '2024-08-16 21:28:55', '2024-08-16 21:28:55'),
(8, '8cc939c7-77f0-4421-b57c-23cfdd635661', 1, 'Security and Privacy', 'SP', NULL, '2024-08-16 21:29:11', '2024-08-16 21:29:11'),
(9, '3b80534b-e202-45b4-a844-2f539c476adc', 1, 'Human Centered Computing', 'HC', NULL, '2024-08-16 21:29:25', '2024-08-16 21:29:25'),
(10, '598a81f3-8108-4c7f-8204-93ce7e459171', 1, 'Computing Methodologies', 'CM', NULL, '2024-08-16 21:29:36', '2024-08-16 21:29:36'),
(11, 'a39526a4-bb00-444a-bfe2-623e3dbef4d8', 1, 'Applied Computer', 'AC', NULL, '2024-08-16 21:29:48', '2024-08-16 21:29:48'),
(12, '4e839170-ac15-4384-b560-cb62efd6a9c6', 1, 'Hardware', 'HW', NULL, '2024-08-16 21:44:01', '2024-08-16 21:44:01');

-- --------------------------------------------------------

--
-- Table structure for table `revisi_laporan`
--

CREATE TABLE `revisi_laporan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `jadwal_sidang_id` bigint(20) UNSIGNED NOT NULL,
  `laporan_akhir_id` bigint(20) UNSIGNED NOT NULL,
  `judul_revisi_laporan` text DEFAULT NULL,
  `file_revisi_laporan` varchar(510) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `penguji` bigint(20) UNSIGNED DEFAULT NULL,
  `file_penguji` varchar(510) DEFAULT NULL,
  `file_random_penguji` varchar(510) DEFAULT NULL,
  `status_approval_penguji` tinyint(4) DEFAULT NULL,
  `note_penguji` text DEFAULT NULL,
  `tanggal_approval_penguji` date DEFAULT NULL,
  `ketua_sidang` bigint(20) UNSIGNED DEFAULT NULL,
  `file_ketua_sidang` varchar(510) DEFAULT NULL,
  `file_random_ketua_sidang` varchar(510) DEFAULT NULL,
  `status_approval_ketua_sidang` tinyint(4) DEFAULT NULL,
  `note_ketua_sidang` text DEFAULT NULL,
  `tanggal_approval_ketua_sidang` date DEFAULT NULL,
  `pembimbing1` bigint(20) UNSIGNED DEFAULT NULL,
  `file_pembimbing1` varchar(510) DEFAULT NULL,
  `file_random_pembimbing1` varchar(510) DEFAULT NULL,
  `status_approval_pembimbing1` tinyint(4) DEFAULT NULL,
  `note_pembimbing1` text DEFAULT NULL,
  `tanggal_approval_pembimbing1` date DEFAULT NULL,
  `pembimbing2` bigint(20) UNSIGNED DEFAULT NULL,
  `file_pembimbing2` varchar(510) DEFAULT NULL,
  `file_random_pembimbing2` varchar(510) DEFAULT NULL,
  `status_approval_pembimbing2` tinyint(4) DEFAULT NULL,
  `note_pembimbing2` text DEFAULT NULL,
  `tanggal_approval_pembimbing2` date DEFAULT NULL,
  `file_kaprodi` varchar(510) DEFAULT NULL,
  `file_random_kaprodi` varchar(510) DEFAULT NULL,
  `status_approval_kaprodi` tinyint(4) DEFAULT NULL,
  `note_kaprodi` text DEFAULT NULL,
  `tanggal_approval_kaprodi` date DEFAULT NULL,
  `status_akhir` tinyint(4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `revisi_proposal`
--

CREATE TABLE `revisi_proposal` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `proposal_skripsi_id` bigint(20) UNSIGNED NOT NULL,
  `revisi_proposal_form_id` bigint(20) UNSIGNED NOT NULL,
  `mahasiswa_id` bigint(20) UNSIGNED NOT NULL,
  `judul_revisi_proposal` text NOT NULL,
  `file_revisi_proposal` varchar(510) NOT NULL,
  `file_revisi_proposal_random` varchar(510) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `penilai1` bigint(20) UNSIGNED DEFAULT NULL,
  `file_revisi_penilai1` varchar(510) DEFAULT NULL,
  `file_revisi_random_penilai1` varchar(510) DEFAULT NULL,
  `status_revisi_approval_penilai1` tinyint(4) DEFAULT NULL,
  `note_revisi_penilai1` text DEFAULT NULL,
  `tanggal_approval_revisi_penilai1` date DEFAULT NULL,
  `penilai2` bigint(20) UNSIGNED DEFAULT NULL,
  `file_revisi_penilai2` varchar(510) DEFAULT NULL,
  `file_revisi_random_penilai2` varchar(510) DEFAULT NULL,
  `status_revisi_approval_penilai2` tinyint(4) DEFAULT NULL,
  `note_revisi_penilai2` text DEFAULT NULL,
  `tanggal_approval_revisi_penilai2` date DEFAULT NULL,
  `penilai3` bigint(20) UNSIGNED DEFAULT NULL,
  `file_revisi_penilai3` varchar(510) DEFAULT NULL,
  `file_revisi_random_penilai3` varchar(510) DEFAULT NULL,
  `status_revisi_approval_penilai3` tinyint(4) DEFAULT NULL,
  `note_revisi_penilai3` text DEFAULT NULL,
  `tanggal_approval_revisi_penilai3` date DEFAULT NULL,
  `status_akhir` tinyint(4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `revisi_proposal_form`
--

CREATE TABLE `revisi_proposal_form` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `judul_form` text NOT NULL,
  `keterangan` text DEFAULT NULL,
  `dibuka` datetime NOT NULL,
  `ditutup` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
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
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('8PdFLelAI33jfOc1n6KLjsCRESb2MSrN9yad81e9', NULL, '104.166.80.254', 'Mozilla/5.0 (X11; Linux i686; rv:109.0) Gecko/20100101 Firefox/120.0', 'ZXlKcGRpSTZJbXQzVEdGUFkwc3pLMnRtUkdaQk5EVnphQ3N4UzFFOVBTSXNJblpoYkhWbElqb2lha1pEZVhFNFFqZEdWRzR3U1VkUmNHdFpRM0ppTjIwd1EyWTRORkZJTUUweVlXSnNXV0Z4VTFBMWEyUnpjM0ZrV0hOcFVYbFZhWEZSYlUxalZ5OVdjVWdyZUc1TUt6bHpiblIxVVRab1NuQk9hRms0ZDBORFJYbExTVll2WVN0R1duUkhMM0J3VjFGRlNWTXZNVXhVVG1oM1ZsSlViV2hrVWpWWWIxRmFibXhVWm5VclowRjRRbGxoTDNkdWMyNDROM2hWY0dWMFlrRXdhRE51YjNORFkwMXdObG96UTFBd1UyUkhTMU5zTVhkRFdrcG5TelEzZUcwd1RsaHNUSEZCTm1rNU5FSm9VRmQxVTNkM1R6RlNWMWRDY0hJMFJFZGhlbTVIYTJwcVQybDVkRGh2TUU1MVNHWnpWQ3N5Ulhwd2FGVkxaV2RhTDJaRU0wVkZaVmgzVTBGRVUxaHhaemxtUVU1R2VFWXhkekJvZFRaUWFWRTlQU0lzSW0xaFl5STZJalZpTnpNMk5qSXpOVEExWmpreVpURTBOak0xTkdJNE5EWXdZekJtTVRBM1pUWmtNR1ZqT0RGbU1UQTJOVFl5TjJKaU16UXlNREF6T0daa1pqSmlZVFVpTENKMFlXY2lPaUlpZlE9PQ==', 1724823488),
('EesqQfsBrmEyn8eRZOjyomDs4eL0ErvLqQGxX9BC', NULL, '149.108.40.96', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'ZXlKcGRpSTZJbkJaWlVnMVp6ZFNhRXRWTTJ0Q1IzVnJObWx3YlhjOVBTSXNJblpoYkhWbElqb2ljazFvYmtsU1QxSlBLMlJuYm5sVVowRldMME52VmsxVlNHSk5aVWhXYlRSd1lYQnhVRFpOUnpsc1NuUlVXREJCZFZJMFlXTkhVRXN6TUU5a1VHTlBUbWw0VDNWb1JXaGxRbGgwWXpCME5UQmxjamN6YlVVMWMzVjFVa1owZG13NGNISTRUV2hIZG1RMVFqQklWelVyVVhwNWNtUlRWV1JqZFZwVll6UmllVE5JUWxoeVNrTlRaMjFyT0d3d2FVMTVaMWRqZFhSUFQwWkxPWGhNYjJRd1kwY3JZazFOZDNkcU4zTmhaVGd3Tmt4bVZFcFVWMGxMU1ZwMlpEYzFLekpSWmxweVREQXpMMFl5Y0VrNVlUQnVkWFJYU2tndk1FRlVjMWR3THpObk1XWXdlbVF4YXpad2VXZHBaVXBFZFRGU1lqZHFTV1kwVEVkbGMyNUhaelJHWWxGMVlpOVJaV1J6V25weVZUaE5WRkJVUkc1dFJWRTlQU0lzSW0xaFl5STZJalZtWm1WbFlUWTFZek13TUdSbFpqSTVNR1k0TWpZMFlUUmxOamxpWkdNeE1ETXhZakZoTjJKaE1UZzJZakZqTjJFek1EUmpNelExWmpnM1pqWXpNR0VpTENKMFlXY2lPaUlpZlE9PQ==', 1724820396),
('nzQRkhE0bmbCCF44cZzBWa5mls73CN9RBMGOJtZr', NULL, '149.108.40.96', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'ZXlKcGRpSTZJbTlqWTA1VFUwYzFVa1JhV0ZCMmJtUmlXbUpSTUZFOVBTSXNJblpoYkhWbElqb2lORlJaVTFkSVZFazJNakJEY2xaSmJERktMMFoxUTFsV00wMWlhR0p5TkZwcE5XYzFTM28yWVZSRFJqbFFZbTlsTmsxR1FucENZeTlNZUdoSVZWbEphR1pZUlRCeVdFNXpibmhUZDFCb1VVUTJWWHB6VUdsWE5ubDNPRUV3Y2s0eVkySlpSSEo1VkZWclIwTm1SRXRWTkhabFJHSnRkRU0wVkc1d1ZFTXZNMEk0VVhJck5ERlBTWEZpYldVMlVFdG5ZakJpU3pCMFFYUm9VWGcwU1ZZM1RFWk9aVFl2UTNCV1lpdG9OamhSWW0xR2VHRXZOWGR4V1dOb1RGcFhSelJvTjJaTlRsQmtlazl0TlZRcmVYRnBWWE5xVW5waFZtTm5jRTFSTjBzNVFqUkxhbGx1WlhKdVQwRnlRemRSSzBKVFpVVTBORzF1YjFKd1VHUkdlakZVWkZWWGQxTkhkM2czTjA5cFExRXhaVFZaTVVGbEwzYzlQU0lzSW0xaFl5STZJamRtWWpNd1pqZ3lOMkV4WkRRME16QmlOamhrT0RJNVpEbGxaVGM1TW1WbE1XTmhaVGRrTkRBNFpqVmxOR1EyTURVM09UUTVNREUzTldVell6SmlaakFpTENKMFlXY2lPaUlpZlE9PQ==', 1724824093);

-- --------------------------------------------------------

--
-- Table structure for table `source_codes`
--

CREATE TABLE `source_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `source_code_form`
--

CREATE TABLE `source_code_form` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tahun_ajaran_id` bigint(20) UNSIGNED NOT NULL,
  `program_studi_id` bigint(20) UNSIGNED NOT NULL,
  `judul_form` text NOT NULL,
  `keterangan` text DEFAULT NULL,
  `dibuka` datetime NOT NULL,
  `ditutup` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tahun_ajaran`
--

CREATE TABLE `tahun_ajaran` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tahun` smallint(6) NOT NULL,
  `semester` varchar(15) NOT NULL,
  `status_aktif` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tahun_ajaran`
--

INSERT INTO `tahun_ajaran` (`id`, `uuid`, `tahun`, `semester`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, '57e3cfac-e5fd-4abf-95d5-767df02a1dd4', 2024, 'genap', 1, '2024-08-14 02:36:32', '2024-08-15 21:04:46'),
(2, 'c5ad26ac-8a86-4941-b3bd-0899a97c2d22', 2024, 'ganjil', 0, '2024-08-14 02:36:32', '2024-08-15 21:04:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'ec825ec7-bd10-45dc-a351-15769e4055b3', 'admin@umn.ac.id', NULL, '$2y$12$NKpBe83xyT/xxChSaNtJ3uaA6lkxBZSWjWskE.BeYPqG5W4iMAgDq', 'admin', NULL, '2024-08-14 02:36:32', '2024-08-14 02:36:32'),
(2, 'd56be875-34f6-42fe-a739-ac2f62b167a3', 'dosen1@umn.ac.id', NULL, '$2y$12$wGsIwA1KK/k9hARMr0FOquSyoDzs.W74NnDyVVqEi3dculRR9jLmm', 'dosen', NULL, '2024-08-14 02:47:01', '2024-08-15 06:27:15'),
(3, '6c8b590d-674a-467b-8eea-23a24675b504', 'dosen2@umn.ac.id', NULL, '$2y$12$5t0/tYJOkhjp4JxIFD7Fu.bkK2LVl73pEV3TGfi7ayjFn75rCZwPW', 'sekprodi', NULL, '2024-08-14 02:47:01', '2024-08-15 22:24:26'),
(4, '48f47bb1-d66a-4a5c-968d-b1cdcf62d28a', 'mahasiswa1@student.umn.ac.id', NULL, '$2y$12$XtsNq25Jz89oW5DH/Hre7eiOyAiq6BI.s3DpNKz6eC5T6WqADwngy', 'mahasiswa', NULL, '2024-08-14 02:47:02', '2024-08-14 02:47:02'),
(5, '53b0ed26-b3f8-4666-a094-e64a87352bc6', 'mahasiswa2@student.umn.ac.id', NULL, '$2y$12$oMDY1S7KN5kIsHcItlEpN.JWGfLA/90nbEmdBr5Bfe4IpghCzbcwu', 'mahasiswa', NULL, '2024-08-14 02:47:02', '2024-08-14 02:47:02'),
(6, '86058eb0-1e9c-4cb7-a11e-e2df1b8c581b', 'dosen3@umn.ac.id', NULL, '$2y$12$ASiWnqmN703YrtmJ8zGRde6J.hWA62Elh35ESx.MGD.Yi/wBmOeZq', 'dosen', NULL, '2024-08-15 22:23:52', '2024-08-15 22:24:19'),
(7, 'f404004d-6179-4876-b488-63e9574f322d', 'zerocyberfive@gmail.com', NULL, '$2y$12$weO90TxgacdMnLgmOW7Oz.Ld5jTaL7hj7L1ZJqID3bXRN7wykL.xe', 'mahasiswa', 'rF7zL4DxT6IQB0618HD81w6n1TEkji49KX1GBiNZZItETvVqV6iHrgSdZUIZ', '2024-08-25 20:06:50', '2024-08-27 19:29:53'),
(8, '12519e43-0252-4eaa-afbe-b085bd8f3298', 'wirawan.istiono@umn.ac.id', NULL, '$2y$12$6o4fWY6efK80hpMwdr.fU.03KCDPLyJXcnyH5x6tkAnV8pV2mmnKG', 'dosen', NULL, '2024-08-26 13:08:14', '2024-08-26 13:08:14'),
(9, '49aff30c-6e9b-48ab-93cf-19d6c3b96ca8', 'adhi.kusnadi@umn.ac.id', NULL, '$2y$12$14UUWcBfCe/qv/FoI9qKl.47UuigJBEnu2Wm2voseAfXsizRCLOqq', 'kaprodi', NULL, '2024-08-26 13:09:28', '2024-08-26 13:09:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_uuid_unique` (`uuid`);

--
-- Indexes for table `area_penelitian`
--
ALTER TABLE `area_penelitian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `area_penelitian_uuid_unique` (`uuid`);

--
-- Indexes for table `berita_acara`
--
ALTER TABLE `berita_acara`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `berita_acara_uuid_unique` (`uuid`);

--
-- Indexes for table `bimbingan`
--
ALTER TABLE `bimbingan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bimbingan_uuid_unique` (`uuid`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dosen_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `dosen_nid_unique` (`nid`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jadwal_sidang`
--
ALTER TABLE `jadwal_sidang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jadwal_sidang_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jurnal`
--
ALTER TABLE `jurnal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jurnal_form`
--
ALTER TABLE `jurnal_form`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jurnal_form_uuid_unique` (`uuid`);

--
-- Indexes for table `kategori_nilai`
--
ALTER TABLE `kategori_nilai`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kategori_nilai_uuid_unique` (`uuid`);

--
-- Indexes for table `kategori_nilai_detail`
--
ALTER TABLE `kategori_nilai_detail`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kategori_nilai_detail_uuid_unique` (`uuid`);

--
-- Indexes for table `kode_penelitian_proposal`
--
ALTER TABLE `kode_penelitian_proposal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_penelitian_proposal_uuid_unique` (`uuid`);

--
-- Indexes for table `laporan_akhir`
--
ALTER TABLE `laporan_akhir`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `laporan_akhir_uuid_unique` (`uuid`);

--
-- Indexes for table `laporan_akhir_form`
--
ALTER TABLE `laporan_akhir_form`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `laporan_akhir_form_uuid_unique` (`uuid`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mahasiswa_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `mahasiswa_nim_unique` (`nim`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nilai_akhir`
--
ALTER TABLE `nilai_akhir`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nilai_akhir_uuid_unique` (`uuid`);

--
-- Indexes for table `nilai_sidang`
--
ALTER TABLE `nilai_sidang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nilai_sidang_uuid_unique` (`uuid`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pembimbing_mahasiswa`
--
ALTER TABLE `pembimbing_mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pembimbing_mahasiswa_uuid_unique` (`uuid`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pengaturan_uuid_unique` (`uuid`);

--
-- Indexes for table `pengaturan_detail`
--
ALTER TABLE `pengaturan_detail`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pengaturan_detail_uuid_unique` (`uuid`);

--
-- Indexes for table `permintaan_mahasiswa`
--
ALTER TABLE `permintaan_mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permintaan_mahasiswa_uuid_unique` (`uuid`);

--
-- Indexes for table `permintaan_mahasiswa_form`
--
ALTER TABLE `permintaan_mahasiswa_form`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permintaan_mahasiswa_form_uuid_unique` (`uuid`);

--
-- Indexes for table `posters`
--
ALTER TABLE `posters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `poster_form`
--
ALTER TABLE `poster_form`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `poster_form_uuid_unique` (`uuid`);

--
-- Indexes for table `program_studi`
--
ALTER TABLE `program_studi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `program_studi_uuid_unique` (`uuid`);

--
-- Indexes for table `proposal_skripsi`
--
ALTER TABLE `proposal_skripsi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `proposal_skripsi_uuid_unique` (`uuid`);

--
-- Indexes for table `proposal_skripsi_form`
--
ALTER TABLE `proposal_skripsi_form`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `proposal_skripsi_form_uuid_unique` (`uuid`);

--
-- Indexes for table `research_dosen`
--
ALTER TABLE `research_dosen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `research_dosen_uuid_unique` (`uuid`);

--
-- Indexes for table `research_list`
--
ALTER TABLE `research_list`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `research_list_uuid_unique` (`uuid`);

--
-- Indexes for table `revisi_laporan`
--
ALTER TABLE `revisi_laporan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `revisi_laporan_uuid_unique` (`uuid`);

--
-- Indexes for table `revisi_proposal`
--
ALTER TABLE `revisi_proposal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `revisi_proposal_uuid_unique` (`uuid`);

--
-- Indexes for table `revisi_proposal_form`
--
ALTER TABLE `revisi_proposal_form`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `revisi_proposal_form_uuid_unique` (`uuid`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `source_codes`
--
ALTER TABLE `source_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `source_code_form`
--
ALTER TABLE `source_code_form`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `source_code_form_uuid_unique` (`uuid`);

--
-- Indexes for table `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tahun_ajaran_uuid_unique` (`uuid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `area_penelitian`
--
ALTER TABLE `area_penelitian`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `berita_acara`
--
ALTER TABLE `berita_acara`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bimbingan`
--
ALTER TABLE `bimbingan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal_sidang`
--
ALTER TABLE `jadwal_sidang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jurnal`
--
ALTER TABLE `jurnal`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jurnal_form`
--
ALTER TABLE `jurnal_form`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori_nilai`
--
ALTER TABLE `kategori_nilai`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori_nilai_detail`
--
ALTER TABLE `kategori_nilai_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kode_penelitian_proposal`
--
ALTER TABLE `kode_penelitian_proposal`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `laporan_akhir`
--
ALTER TABLE `laporan_akhir`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laporan_akhir_form`
--
ALTER TABLE `laporan_akhir_form`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `nilai_akhir`
--
ALTER TABLE `nilai_akhir`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nilai_sidang`
--
ALTER TABLE `nilai_sidang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembimbing_mahasiswa`
--
ALTER TABLE `pembimbing_mahasiswa`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pengaturan_detail`
--
ALTER TABLE `pengaturan_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permintaan_mahasiswa`
--
ALTER TABLE `permintaan_mahasiswa`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permintaan_mahasiswa_form`
--
ALTER TABLE `permintaan_mahasiswa_form`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `posters`
--
ALTER TABLE `posters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `poster_form`
--
ALTER TABLE `poster_form`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program_studi`
--
ALTER TABLE `program_studi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `proposal_skripsi`
--
ALTER TABLE `proposal_skripsi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `proposal_skripsi_form`
--
ALTER TABLE `proposal_skripsi_form`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `research_dosen`
--
ALTER TABLE `research_dosen`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `research_list`
--
ALTER TABLE `research_list`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `revisi_laporan`
--
ALTER TABLE `revisi_laporan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `revisi_proposal`
--
ALTER TABLE `revisi_proposal`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `revisi_proposal_form`
--
ALTER TABLE `revisi_proposal_form`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `source_codes`
--
ALTER TABLE `source_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `source_code_form`
--
ALTER TABLE `source_code_form`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
