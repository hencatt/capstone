-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2025 at 07:51 AM
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
-- Table structure for table `accounts_tbl`
--

CREATE TABLE `accounts_tbl` (
  `id` int(11) NOT NULL,
  `email` varchar(60) NOT NULL,
  `username` varchar(100) NOT NULL,
  `pass` text NOT NULL,
  `fname` varchar(60) NOT NULL,
  `lname` varchar(60) NOT NULL,
  `position` varchar(50) DEFAULT NULL,
  `department` varchar(100) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts_tbl`
--

INSERT INTO `accounts_tbl` (`id`, `email`, `username`, `pass`, `fname`, `lname`, `position`, `department`, `date_created`, `is_active`) VALUES
(4, 'director@gmail.com', 'director1', '$2y$10$04hgVjOaCFgt/29NY7a5LOsdcRAVLdktAUjhc9SJctfMTY3xYI.cO', 'director', '1', 'Director', '', '2025-04-13 13:20:47', 1),
(5, 'focalperson@gmail.com', 'focalperson1', '$2y$10$8Ovcdh5TjBXADfw.cYj8ieqWxoFOolMZTRi0Ra5oCmdRYJLdqkvk2', 'focal', 'person', 'Focal Person', 'CICT', '2025-04-13 13:20:47', 1),
(6, 'TA@gmail.com', 'TA1', '$2y$10$xPm8HX5m8NrqMu5.Z.3ltuazowcnpkfqI9b3XM2DfE5XG3F1v.1.e', 'Technical', 'Assistant', 'TA', '', '2025-04-15 14:50:18', 0),
(7, 'bronnyjames@gmail.com', '', '$2y$10$B50IiEFlsMi2ADizpFbQRONe3gVZaNzicuyrmG6rPcXNyp3NSp2gu', 'bronny', 'james', NULL, '', '2025-04-15 14:48:55', 0),
(8, 'untiludie@yahoo.com', '', '$2y$10$c8rDbVh8xzi9cDwvzGhSseuIAIuYBkjPD6vN45gnYw4/SzG4g5vWm', 'try', 'try', 'Director', '', '2025-04-15 14:32:01', 0),
(9, 'focal@focal.com', '', '$2y$10$Wpwm.un8XB72bU7X1ANOneeYadPlQtR.lvi.Sks8ZTeO8JJ5VnyZ2', 'a', 'a', 'Focal Person', '', '2025-04-15 14:29:41', 0),
(10, 'please@tryagain.later', 'maximum', '$2y$10$4s1MLIeWNjnNNC2W.KVTK..9VmC.xTf/jzeTbdiQGZxJlmsMfpLP2', 'try', 'again', 'Technical Assistant', '', '2025-04-15 14:45:58', 0);

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `accounts_tbl`
--
ALTER TABLE `accounts_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
