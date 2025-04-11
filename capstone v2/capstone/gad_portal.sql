-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2025 at 06:35 PM
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
-- Database: `gad_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts_info`
--

CREATE TABLE `accounts_info` (
  `id` int(11) NOT NULL,
  `fname` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL,
  `birthday` date NOT NULL,
  `marital_status` varchar(100) NOT NULL,
  `sex` enum('Male','Female') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `gender` varchar(100) NOT NULL,
  `priority_status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `accounts_tbl`
--

CREATE TABLE `accounts_tbl` (
  `id` int(11) NOT NULL,
  `email` varchar(60) NOT NULL,
  `pass` text NOT NULL,
  `fname` varchar(60) NOT NULL,
  `lname` varchar(60) NOT NULL,
  `position` varchar(50) DEFAULT NULL,
  `department` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts_tbl`
--

INSERT INTO `accounts_tbl` (`id`, `email`, `pass`, `fname`, `lname`, `position`, `department`) VALUES
(1, 'ivankyle@gmail.com', '$2y$10$bO3.oGY/k652OUWUhlUfo.L7kALg1Li7EOP.7y2/B3QTZQ5u3NxSe', 'ivan kyle', 'samaniego', 'Director', 'Sumacab'),
(2, 'ivan@gmail.com', '$2y$10$Kvf7HrEB3uYqTSA6R.qZM.1sf0RADDKR8oI8xcvoz/VFV83XNxs0W', 'ivan', 'samaniego', 'focal person', ''),
(3, 'bronnyjames@gmail.com', '$2y$10$n5TiH44hA1SM2HMyTdJ6kePIZkxqQ8kjvUHG1NzXpTCq.MuN6mnYa', 'bronny', 'james', NULL, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts_info`
--
ALTER TABLE `accounts_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `accounts_tbl`
--
ALTER TABLE `accounts_tbl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts_info`
--
ALTER TABLE `accounts_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `accounts_tbl`
--
ALTER TABLE `accounts_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
