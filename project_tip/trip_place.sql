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
-- Table structure for table `trip_place`
--

CREATE TABLE `trip_place` (
  `id_trip_place` int(11) NOT NULL,
  `id_account` int(11) NOT NULL,
  `id_place` int(11) NOT NULL,
  `order_no` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `trip_place`
--
ALTER TABLE `trip_place`
  ADD PRIMARY KEY (`id_trip_place`),
  ADD UNIQUE KEY `uniq_account_place` (`id_account`,`id_place`),
  ADD KEY `id_place` (`id_place`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `trip_place`
--
ALTER TABLE `trip_place`
  MODIFY `id_trip_place` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `trip_place`
--
ALTER TABLE `trip_place`
  ADD CONSTRAINT `trip_place_ibfk_1` FOREIGN KEY (`id_account`) REFERENCES `account` (`id_account`) ON DELETE CASCADE,
  ADD CONSTRAINT `trip_place_ibfk_2` FOREIGN KEY (`id_place`) REFERENCES `place` (`id_place`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
