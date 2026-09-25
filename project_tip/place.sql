-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 25, 2026 at 10:07 AM
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
-- Database: `programming_world`
--

-- --------------------------------------------------------

--
-- Table structure for table `place`
--

CREATE TABLE `place` (
  `id_place` int(11) NOT NULL,
  `place_key` varchar(50) NOT NULL,
  `name_place` varchar(150) NOT NULL,
  `location_place` varchar(150) NOT NULL,
  `category_place` varchar(50) DEFAULT NULL,
  `lat_place` decimal(10,7) NOT NULL,
  `lng_place` decimal(10,7) NOT NULL,
  `image_place` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `place`
--

INSERT INTO `place` (`id_place`, `place_key`, `name_place`, `location_place`, `category_place`, `lat_place`, `lng_place`, `image_place`, `created_at`) VALUES
(1, 'thi-lo-su', 'น้ำตกทีลอซู', 'อ.อุ้มผาง', 'น้ำตก', 15.7003000, 98.6294000, 'images/dest-thilosu.jpg', '2026-09-23 05:58:59'),
(2, 'doi-musoe', 'ดอยมูเซอ', 'อ.เมืองตาก', 'ทะเลหมอก', 16.8300000, 98.7500000, 'images/dest-doimusoe.jpg', '2026-09-23 05:58:59'),
(3, 'bhumibol-dam', 'เขื่อนภูมิพล', 'อ.สามเงา', 'ธรรมชาติ', 17.2400000, 98.9700000, 'images/dest-bhumibol.jpg', '2026-09-23 05:58:59'),
(4, 'mae-sot-market', 'ตลาดริมเมย', 'อ.แม่สอด', 'ชายแดน', 16.6989000, 98.5347000, 'images/dest-maesot.jpg', '2026-09-23 05:58:59'),
(5, 'lan-sang', 'อุทยานแห่งชาติลานสาง', 'อ.เมืองตาก', 'ธรรมชาติ', 16.8500000, 98.8500000, 'images/dest-lansang.jpg', '2026-09-23 05:58:59'),
(6, 'taksin-maharat', 'อุทยานแห่งชาติตากสินมหาราช', 'อ.บ้านตาก', 'ธรรมชาติ', 17.0000000, 98.8300000, 'images/dest-taksinmaharat.jpg', '2026-09-23 05:58:59'),
(7, 'wat-borommathat', 'วัดพระบรมธาตุ บ้านตาก', 'อ.บ้านตาก', 'วัฒนธรรม', 17.0200000, 99.0700000, 'images/dest-watborommathat.jpg', '2026-09-23 05:58:59'),
(8, 'friendship-bridge', 'สะพานมิตรภาพไทย–เมียนมา', 'อ.แม่สอด', 'ชายแดน', 16.7000000, 98.5300000, 'images/dest-friendshipbridge.jpg', '2026-09-23 05:58:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `place`
--
ALTER TABLE `place`
  ADD PRIMARY KEY (`id_place`),
  ADD UNIQUE KEY `place_key` (`place_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `place`
--
ALTER TABLE `place`
  MODIFY `id_place` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
