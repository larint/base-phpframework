-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 04, 2023 at 09:34 AM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 7.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `basedb`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password_display` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_super` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`id`, `name`, `email`, `email_verified_at`, `password_display`, `password`, `is_super`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'User 1', 'admin@gmail.com', NULL, '123456', '$2y$10$0fKAlqXXlcMtzluvuIp3AefuNCsS.GgjfHomdgB6bJt9R6pqVgLIq', 1, NULL, '2023-01-13 10:55:04', '2023-01-13 10:55:04', NULL),
(2, 'dung', 'dung@gmail.com', NULL, 'ađ', '$2y$10$wmije0hg4rKLxHu4rCPvK.PD9UsMZQ88Tb27.F3Rrocwp0RNboCXW', 0, NULL, '2023-01-13 10:55:04', '2023-01-13 10:55:04', NULL),
(3, 'dung 22', 'staff2@gmail.com', NULL, '123456', '$2y$10$GKeKUoaN2UNU5Qs7JyAGvesZNLhyUx5k8ih7.ept.Hhrfxrcawh/O', 0, NULL, '2023-01-04 10:55:04', '2023-01-13 10:55:04', NULL),
(4, 'dung 22', 'staff3@gmail.com', NULL, '123456', '$2y$10$RF78Ir3IcWtIqL8zBV14wuJUcAN/jxqej6nbNVkPjCXlzHIFcHGRO', 0, NULL, '2023-01-05 10:55:04', '2023-01-13 10:55:04', NULL),
(5, 'dung 22', 'staff4@gmail.com', NULL, '123456', '$2y$10$PjMvp621X8PJlikFGtOE.uh2OTeH7NsTYG/5m9tgvbR2zw6vPXmt.', 0, NULL, '2023-01-12 10:55:04', '2023-01-13 10:55:04', NULL),
(6, 'dung', 'dun23g@gmail.com', NULL, '12312312', '12312321', 2, NULL, NULL, NULL, NULL),
(18, 'dung', 'quangdungg@gmail.com', NULL, '12312312', '12312312', 2, NULL, NULL, NULL, NULL),
(19, 'dung', 'quangdungg23@gmail.com', NULL, '12312312', '12312312', 2, NULL, NULL, NULL, NULL),
(20, 'dung', 'quangdung90@gmail.com', NULL, '12312312', '12312312', 2, NULL, NULL, NULL, NULL),
(21, 'asd', 'acs@gmail.com', NULL, '12312312', '12312312', 2, NULL, NULL, NULL, NULL),
(24, 'asd', 'acsa@gmail.com', NULL, '12312312', '12312312', 2, NULL, NULL, NULL, '2023-04-03 16:01:33'),
(25, 'dung', 'quangdussng90@gmail.com', NULL, '12312312', '12312312', 2, NULL, '2023-04-03 15:39:40', '2023-04-03 15:39:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `account_role`
--

CREATE TABLE `account_role` (
  `id` int(10) UNSIGNED NOT NULL,
  `account_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `account_role`
--

INSERT INTO `account_role` (`id`, `account_id`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL),
(14, 4, 4, NULL, NULL),
(15, 5, 5, NULL, NULL),
(16, 2, 2, NULL, NULL),
(17, 3, 3, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin', NULL, '2019-12-15 16:20:35', '2019-12-15 16:20:35'),
(2, 'officer', 'officer', NULL, '2019-12-15 16:20:35', '2019-12-15 16:20:35'),
(3, 'hirabayashi', 'hirabayashi', NULL, '2019-12-15 16:20:35', '2019-12-15 16:20:35'),
(4, 'health', 'health', NULL, '2019-12-15 16:20:35', '2019-12-15 16:20:35'),
(5, 'reception', 'reception', NULL, '2019-12-15 16:20:35', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_email_unique` (`email`);

--
-- Indexes for table `account_role`
--
ALTER TABLE `account_role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `account_role`
--
ALTER TABLE `account_role`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
