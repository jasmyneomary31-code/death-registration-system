-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 11, 2026 at 09:10 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `death_registration`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `table_affected` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `action_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`log_id`, `user_id`, `action`, `table_affected`, `record_id`, `action_time`) VALUES
(1, 4, 'CREATE_DECEASED', 'deceased', 1, '2026-07-08 09:22:27'),
(2, 5, 'CREATE_DECEASED', 'deceased', 2, '2026-07-09 07:02:36'),
(3, 5, 'APPROVE_DECEASED', 'deceased', 2, '2026-07-10 07:29:13'),
(4, 5, 'REJECT_DECEASED', 'deceased', 1, '2026-07-10 07:30:44'),
(5, 5, 'ISSUE_CERTIFICATE', 'certificates', 1, '2026-07-10 07:56:54');

-- --------------------------------------------------------

--
-- Table structure for table `causes_of_death`
--

CREATE TABLE `causes_of_death` (
  `cause_id` int(11) NOT NULL,
  `description` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `causes_of_death`
--

INSERT INTO `causes_of_death` (`cause_id`, `description`) VALUES
(3, 'Illness - Cardiac'),
(4, 'Illness - Malaria'),
(1, 'Natural causes'),
(2, 'Road accident'),
(5, 'Unknown');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `certificate_id` int(11) NOT NULL,
  `deceased_id` int(11) NOT NULL,
  `certificate_number` varchar(30) NOT NULL,
  `issued_by` int(11) NOT NULL,
  `issued_date` datetime DEFAULT current_timestamp(),
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`certificate_id`, `deceased_id`, `certificate_number`, `issued_by`, `issued_date`, `file_path`) VALUES
(1, 2, 'DRS-2026-4C057E', 5, '2026-07-10 10:56:54', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `deceased`
--

CREATE TABLE `deceased` (
  `deceased_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `date_of_birth` date NOT NULL,
  `date_of_death` date NOT NULL,
  `place_of_death` varchar(150) NOT NULL,
  `national_id_encrypted` varbinary(255) DEFAULT NULL,
  `national_id_iv` varbinary(16) DEFAULT NULL,
  `cause_id` int(11) NOT NULL,
  `registered_by` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deceased`
--

INSERT INTO `deceased` (`deceased_id`, `full_name`, `gender`, `date_of_birth`, `date_of_death`, `place_of_death`, `national_id_encrypted`, `national_id_iv`, `cause_id`, `registered_by`, `status`, `approved_by`, `approved_at`, `created_at`) VALUES
(1, 'hassan kikombe', 'male', '1999-12-03', '2025-02-12', 'muhimbili hospital', 0x737676483737564137394567377449465335316457773d3d, 0x35438cec1817a05919b29fef95185207, 3, 4, 'rejected', 5, '2026-07-10 10:30:44', '2026-07-08 09:22:27'),
(2, 'asha juma', 'female', '2003-06-05', '2025-12-06', 'home', 0x5a524454706652715a47476d3767344b6f53744343673d3d, 0x4fd498dbb5afceaadb8a1258a97a7490, 1, 5, 'approved', 5, '2026-07-10 10:29:13', '2026-07-09 07:02:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','registrar','hospital_staff') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `password_hash`, `role`, `is_active`, `created_at`) VALUES
(1, 'System Admin', 'admin', '[email protected]', '$2y$10$examplehashvaluereplaceinphp', 'admin', 1, '2026-07-06 07:43:16'),
(2, 'Jasmine Test', 'jasmine1', 'jasmyneomary31@gmail.com', '$2y$10$sw8BU5RQoQMV4UIOq7mXXu0eiT6u4mbAGBFPRcSGoYNbQzGQijr.a', 'hospital_staff', 1, '2026-07-06 08:43:01'),
(3, 'Jasmine omar', 'jassuh01', 'jasmyneomary21@gmail.com', '$2y$10$b6JlvmzRMhKsCcjO.Uad6Oxe.vZ70VRhkQ3xLKkJwZLa/F8nIFxre', 'registrar', 1, '2026-07-06 08:45:44'),
(4, 'salum juma', 'safar', 'jamal3451@gmail.com', '$2y$10$XVf8zqONtaVjae87ZAx3eOxU0StsFNFMPJ3xSkoTGr8potz7.Bpg.', 'hospital_staff', 1, '2026-07-08 04:55:45'),
(5, 'hassan kikombe', 'kikombejr', 'jask@gmail.com', '$2y$10$ckdZYOtfo73bLT/dL9BUd.2fZXm.poEZlLMp6OTt7PD.3YURknZ0a', 'registrar', 1, '2026-07-09 06:58:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `causes_of_death`
--
ALTER TABLE `causes_of_death`
  ADD PRIMARY KEY (`cause_id`),
  ADD UNIQUE KEY `description` (`description`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`certificate_id`),
  ADD UNIQUE KEY `deceased_id` (`deceased_id`),
  ADD UNIQUE KEY `certificate_number` (`certificate_number`),
  ADD KEY `issued_by` (`issued_by`);

--
-- Indexes for table `deceased`
--
ALTER TABLE `deceased`
  ADD PRIMARY KEY (`deceased_id`),
  ADD KEY `cause_id` (`cause_id`),
  ADD KEY `registered_by` (`registered_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `causes_of_death`
--
ALTER TABLE `causes_of_death`
  MODIFY `cause_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `certificate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `deceased`
--
ALTER TABLE `deceased`
  MODIFY `deceased_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`deceased_id`) REFERENCES `deceased` (`deceased_id`),
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`issued_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `deceased`
--
ALTER TABLE `deceased`
  ADD CONSTRAINT `deceased_ibfk_1` FOREIGN KEY (`cause_id`) REFERENCES `causes_of_death` (`cause_id`),
  ADD CONSTRAINT `deceased_ibfk_2` FOREIGN KEY (`registered_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `deceased_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
