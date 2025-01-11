-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 21, 2024 at 02:47 AM
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
-- Database: `brew+flex`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `check_in_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `member_id`, `first_name`, `last_name`, `check_in_date`, `created_at`, `updated_at`) VALUES
(67, 40, 'Kennethhh', 'Sayan', '2024-12-18 00:58:44', '2024-12-09 06:20:00', '2024-12-17 16:58:44'),
(68, 41, 'Kenneth angel', 'Sayan', '2024-12-18 19:38:52', '2024-12-09 07:38:25', '2024-12-18 11:38:52'),
(69, 47, 'Awdwadwa', 'Dwadwaw', '2024-12-16 00:01:57', '2024-12-12 18:01:03', '2024-12-15 16:01:57'),
(70, 46, 'Kennethh', 'Sayan', '2024-12-15 19:00:23', '2024-12-12 18:15:42', '2024-12-15 11:00:23'),
(71, 48, 'Angelo', 'Sayan', '2024-12-18 20:04:06', '2024-12-12 18:19:27', '2024-12-18 12:04:06'),
(72, 50, 'Kenneth uya', 'Dwadwa', '2024-12-16 00:07:49', '2024-12-12 19:57:32', '2024-12-15 16:07:49'),
(73, 52, 'Dadadasdad', 'Aw', '2024-12-16 09:01:24', '2024-12-12 20:13:37', '2024-12-16 01:01:24'),
(74, 43, 'Jehu', 'Syd', '2024-12-18 19:40:21', '2024-12-12 20:22:40', '2024-12-18 11:40:21'),
(75, 44, 'Van dung', 'Pulvera', '2024-12-18 19:54:26', '2024-12-12 20:24:30', '2024-12-18 11:54:26'),
(76, 45, 'Ken', 'Dong', '2024-12-15 19:00:13', '2024-12-12 20:26:01', '2024-12-15 11:00:13'),
(77, 49, 'Awawawawa', 'Wawawdawsd', '2024-12-15 20:24:36', '2024-12-15 12:24:36', '2024-12-15 12:24:36');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `logs_id` int(11) NOT NULL,
  `attendance_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `check_in_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_logs`
--

INSERT INTO `attendance_logs` (`logs_id`, `attendance_id`, `member_id`, `first_name`, `last_name`, `check_in_date`, `created_at`) VALUES
(86, 67, 40, 'Kenneth', 'Sayan', '2024-12-09 14:20:00', '2024-12-10 06:21:11'),
(87, 67, 40, 'Kennethhh', 'Sayan', '2024-12-10 14:21:11', '2024-12-09 06:21:36'),
(88, 67, 40, 'Kennethhh', 'Sayan', '2024-12-09 14:21:36', '2024-12-11 06:23:41'),
(89, 67, 40, 'Kennethhh', 'Sayan', '2024-12-11 14:23:41', '2025-11-24 06:36:37'),
(90, 67, 40, 'Kennethhh', 'Sayan', '2025-11-24 14:36:37', '2024-12-09 17:22:08'),
(91, 68, 41, 'Kenneth angel', 'Sayan', '2024-12-09 15:38:25', '2024-12-09 17:49:56'),
(92, 67, 40, 'Kennethhh', 'Sayan', '2024-12-10 01:22:08', '2024-12-10 18:17:50'),
(93, 68, 41, 'Kenneth angel', 'Sayan', '2024-12-10 01:49:56', '2024-12-10 18:18:01'),
(94, 68, 41, 'Kenneth angel', 'Sayan', '2024-12-11 02:18:01', '2024-12-09 18:18:36'),
(95, 67, 40, 'Kennethhh', 'Sayan', '2024-12-11 02:17:50', '2024-12-09 18:19:03'),
(96, 68, 41, 'Kenneth angel', 'Sayan', '2024-12-10 02:18:36', '2024-12-10 18:22:35'),
(97, 68, 41, 'Kenneth angel', 'Sayan', '2024-12-11 02:22:35', '2024-12-09 18:30:41'),
(98, 67, 40, 'Kennethhh', 'Sayan', '2024-12-10 02:19:03', '2024-12-10 19:58:51'),
(99, 68, 41, 'Kenneth angel', 'Sayan', '2024-12-10 02:30:41', '2024-12-10 20:07:49'),
(100, 67, 40, 'Kennethhh', 'Sayan', '2024-12-11 03:58:51', '2024-12-12 08:04:28'),
(101, 67, 40, 'Kennethhh', 'Sayan', '2024-12-12 16:04:28', '2024-12-12 17:56:17'),
(102, 68, 41, 'Kenneth angel', 'Sayan', '2024-12-11 04:07:49', '2024-12-12 18:00:45'),
(103, 69, 47, 'Awdwadwa', 'Dwadwaw', '2024-12-13 02:01:03', '2024-12-15 06:54:21'),
(104, 67, 40, 'Kennethhh', 'Sayan', '2024-12-13 01:56:17', '2024-12-15 10:42:38'),
(105, 68, 41, 'Kenneth angel', 'Sayan', '2024-12-13 02:00:45', '2024-12-15 10:59:56'),
(106, 76, 45, 'Ken', 'Dong', '2024-12-13 04:26:01', '2024-12-15 11:00:13'),
(107, 70, 46, 'Kennethh', 'Sayan', '2024-12-13 02:15:42', '2024-12-15 11:00:23'),
(108, 72, 50, 'Kenneth uya', 'Dwadwa', '2024-12-13 03:57:32', '2024-12-15 11:00:32'),
(109, 75, 44, 'Van dung', 'Pulvera', '2024-12-13 04:24:30', '2024-12-15 15:54:45'),
(110, 69, 47, 'Awdwadwa', 'Dwadwaw', '2024-12-15 14:54:21', '2024-12-15 16:01:57'),
(111, 75, 44, 'Van dung', 'Pulvera', '2024-12-15 23:54:45', '2024-12-15 16:03:54'),
(112, 72, 50, 'Kenneth uya', 'Dwadwa', '2024-12-15 19:00:32', '2024-12-15 16:07:49'),
(113, 67, 40, 'Kennethhh', 'Sayan', '2024-12-15 18:42:38', '2024-12-15 16:07:55'),
(114, 68, 41, 'Kenneth angel', 'Sayan', '2024-12-15 18:59:56', '2024-12-15 16:10:20'),
(115, 73, 52, 'Dadadasdad', 'Aw', '2024-12-13 04:13:37', '2024-12-16 01:01:24'),
(116, 67, 40, 'Kennethhh', 'Sayan', '2024-12-16 00:07:55', '2024-12-17 16:58:44'),
(117, 68, 41, 'Kenneth angel', 'Sayan', '2024-12-16 00:10:20', '2024-12-18 11:38:52'),
(118, 74, 43, 'Jehu', 'Syd', '2024-12-13 04:22:40', '2024-12-18 11:40:21'),
(119, 75, 44, 'Van dung', 'Pulvera', '2024-12-16 00:03:54', '2024-12-18 11:54:26'),
(120, 71, 48, 'Angelo', 'Sayan', '2024-12-13 02:19:27', '2024-12-18 12:04:06');

-- --------------------------------------------------------

--
-- Table structure for table `coaches`
--

CREATE TABLE `coaches` (
  `coach_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `contact_number` varchar(11) NOT NULL,
  `expertise` varchar(50) NOT NULL,
  `gender` enum('Male','Female','Others') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coaches`
--

INSERT INTO `coaches` (`coach_id`, `first_name`, `last_name`, `contact_number`, `expertise`, `gender`, `created_at`, `updated_at`) VALUES
(1, 'Kennetha', 'Sayan', '09123456789', 'Strength Training', 'Male', '2024-11-21 17:52:52', '2024-12-17 19:03:25'),
(2, 'ashley', 'Pulvera', '09123456789', 'Boxing', 'Male', '2024-11-21 20:06:46', '2024-11-21 20:06:46'),
(3, 'htrhsdfsd', 'addasdwa', '09279001620', 'Power Lifting', 'Male', '2024-12-16 13:42:38', '2024-12-16 13:42:38'),
(4, 'hxfsdfsdf', 'sdfse', '09279001620', 'Boxing', 'Male', '2024-12-16 13:43:13', '2024-12-16 13:43:13'),
(5, 'dfdwszddszdsaasd', 'afgregtrgr', '09279001620', 'Bodybuilding', 'Male', '2024-12-16 13:43:26', '2024-12-16 13:43:26'),
(6, 'jhgjghsdffsd', 'fewswewqwqe', '09279001620', 'Bodybuilding', 'Male', '2024-12-16 13:43:39', '2024-12-16 13:43:39'),
(7, 'jfghgdfgdf', 'Sayan', '09279001620', 'Bodybuilding', 'Male', '2024-12-16 13:43:52', '2024-12-16 13:43:52'),
(8, 'dwaafdszfdxger', 'grdgregerg', '09279001620', 'Power Lifting', 'Male', '2024-12-16 13:44:03', '2024-12-16 13:44:03'),
(9, 'hfthfgsadads', 'gfdfgf', '09279001620', 'Power Lifting', 'Male', '2024-12-16 15:24:04', '2024-12-16 15:35:02'),
(10, 'zxcsadsa', 'dasdwa', '09279001620', 'Bodybuilding', 'Male', '2024-12-16 15:24:33', '2024-12-16 15:24:33'),
(11, 'ashleydsadwa', 'pulveraddsa', '09279001620', 'Power Lifting', 'Female', '2024-12-16 15:25:14', '2024-12-16 15:25:14');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `date_acquired` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `type`, `quantity`, `price`, `date_acquired`) VALUES
(1, '111', 'Supplement & Drinks', 80823, 1.00, '2024-12-03'),
(2, 'ASH', 'Apparel', 11111098, 11.00, '0000-00-00'),
(3, 'Kenneth Sayan', 'Gym Equipment', 12200, 111.00, '2024-12-11'),
(4, 'Way', 'Supplement & Drinks', 11111073, 10000.00, '0000-00-00'),
(5, 'dwadwadwa', 'Accessories', 1076, 111.00, '0000-00-00'),
(6, 'Kenneth', 'Gym Equipment', 312321, 312321.00, '2024-12-15'),
(7, 'dwadwa', 'Supplement & Drinks', 13123, 321321.00, '0000-00-00'),
(8, 'Kenneth', 'Supplement & Drinks', 312312, 321321.00, '0000-00-00'),
(9, 'Kenneth', 'Supplement & Drinks', 321321, 321321.00, '0000-00-00');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `member_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `birthday` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_no` varchar(15) NOT NULL,
  `country` varchar(50) NOT NULL,
  `zipcode` varchar(10) NOT NULL,
  `municipality` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `date_enrolled` date NOT NULL,
  `expiration_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('active','expired','pending') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT 'default-profile.png',
  `generated_code` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`member_id`, `first_name`, `last_name`, `gender`, `birthday`, `email`, `contact_no`, `country`, `zipcode`, `municipality`, `city`, `date_enrolled`, `expiration_date`, `amount`, `status`, `created_at`, `profile_picture`, `generated_code`) VALUES
(40, 'Kennethhh', 'Sayan', 'male', '2003-05-20', 'sayankennethangel@gmail.com', '09123213213', 'PHILIPPINES', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-11-24', '2025-11-24', 300.00, 'active', '2024-11-24 07:10:08', 'uploads/profile_6742d1506da770.37149849_kenneth.jpg', 'TBNHaoxE6W'),
(41, 'Kenneth angel', 'Sayan', 'male', '2003-05-20', 'sayankennethangel@gmail.com', '09279001123', 'BELARUS', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-11-24', '2025-11-24', 300.00, 'active', '2024-11-24 07:11:45', 'uploads/profile_6742d1b13b3708.71495235_kenneth.jpg', 'nF91gI6zXm'),
(42, 'Ash', 'Sy', 'male', '2024-12-03', 'pulvera.ashley123@gmail.com', '09263456788', 'BANGLADESH', '9101', 'KAUSWAGAN', 'CAGAYAN DE ORO', '2024-12-03', '2025-12-03', 300.00, 'active', '2024-12-03 08:11:43', 'uploads/profile_674ebd3fec3d56.78905269.png', '9Z38BnrL17'),
(43, 'Jehu', 'Syd', 'male', '2024-12-03', 'kennethsayan@gmail.com', '09263456788', 'AZERBAIJAN', '9101', 'KAUSWAGAN', 'CAGAYAN DE ORO', '2024-12-03', '2025-12-03', 300.00, 'active', '2024-12-03 08:12:35', 'uploads/profile_674ebd733b5747.85123971.png', 'qKyVNMyHXE'),
(44, 'Van dung', 'Pulvera', 'female', '2024-12-03', 'kennethsayan@gmail.com', '09263456788', 'BARBADOS', '9102', 'KAUSWAGAN', 'CAGAYAN DE ORO', '2024-12-03', '2025-12-03', 300.00, 'active', '2024-12-03 08:13:50', 'uploads/profile_674ebdbe773926.10938314_print qr.png', 'Zrz2j5BHKM'),
(45, 'Ken', 'Dong', 'male', '2024-12-03', 'kennethsayan@gmail.com', '09263456788', 'BARBADOS', '9102', 'KAUSWAGAN', 'CAGAYAN DE ORO', '2024-12-03', '2025-12-03', 300.00, 'active', '2024-12-03 08:16:28', 'uploads/profile_674ebe5c069792.24657631.png', '3zrBuyjp7B'),
(46, 'Kennethh', 'Sayan', 'male', '2024-12-03', 'pulvera.ashley123@gmail.com', '09263456788', 'BANGLADESH', '9101', 'KAUSWAGAN', 'CAGAYAN DE ORO', '2024-12-03', '2025-12-03', 300.00, 'active', '2024-12-03 08:17:03', 'uploads/profile_674ebe7f5f6747.23233436_print qr.png', 'z25dQDbQx1'),
(47, 'Awdwadwa', 'Dwadwaw', 'male', '2003-05-20', 'sayankennethangel@gmail.com', '09123213213', 'BANGLADESH', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-13', '2025-12-13', 300.00, 'active', '2024-12-12 17:21:12', 'default-profile.png', 'fAXFGbhEUs'),
(48, 'Angelo', 'Sayan', 'male', '2003-05-20', 'sayankennethangel@gmail.com', '09123213213', 'BARBADOS', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-13', '2025-12-13', 300.00, 'active', '2024-12-12 17:30:23', 'default-profile.png', 'dS7yQoMuKR'),
(49, 'Awawawawa', 'Wawawdawsd', 'male', '3211-05-20', 'dwadwadwadwadwa@gmail.com', '09123213213', 'BARBADOS', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-13', '2025-12-13', 300.00, 'active', '2024-12-12 17:30:58', 'default-profile.png', 'lZJELypwwC'),
(50, 'Kenneth uya', 'Dwadwa', 'male', '3122-03-12', 'sayankennethangel@gmail.com', '09123213213', 'BARBADOS', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-13', '2025-12-13', 300.00, 'active', '2024-12-12 17:31:22', 'default-profile.png', 'yNcQqfaaQr'),
(51, 'Dwadwadwadwaa', 'Awawdsad', 'male', '3121-03-12', 'sayankennethangel@gmail.com', '09123213213', 'BELGIUM', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-13', '2025-12-13', 300.00, 'active', '2024-12-12 17:31:47', 'default-profile.png', '7lyzvDO2Kk'),
(52, 'Dadadasdad', 'Aw', 'male', '0000-00-00', 'sayankennethangel@gmail.com', '09123213213', 'BANGLADESH', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-13', '2025-12-13', 300.00, 'active', '2024-12-12 17:39:25', 'default-profile.png', '7l3EMjDo42'),
(53, 'Sayan', 'Ang', 'male', '2024-12-18', 'dwadwadwadwadwa@gmail.com', '09123213213', 'BAHRAIN', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-15', '2025-12-15', 300.00, 'active', '2024-12-15 06:37:17', 'default-profile.png', '9roXJAKLPj'),
(54, 'Jadsadasdghawg', 'Adwajsgsdfg', 'male', '2024-12-15', 'sayankennethangel@gmail.com', '09123213213', 'BANGLADESH', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-15', '2025-12-15', 300.00, 'active', '2024-12-15 06:44:45', 'default-profile.png', 'a9RvPEoVor'),
(55, 'Hcghdfgds', 'Fsdazxczxsdaff', 'male', '5121-03-12', 'sayankennethangel@gmail.com', '09123213213', 'BANGLADESH', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-15', '2025-12-15', 300.00, 'active', '2024-12-15 06:45:28', 'default-profile.png', 'sm6L5v6hM2'),
(56, 'Vjmvf', 'Vbghjkghdsaa', 'male', '3121-05-12', 'sayankennethangel@gmail.com', '09123213213', 'BANGLADESH', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-15', '2025-12-15', 300.00, 'active', '2024-12-15 06:45:50', 'default-profile.png', 'XRuppid4el'),
(57, 'Jhgjghrqw', 'Adwaasdwa', 'male', '3121-03-12', 'sayankennethangel@gmail.com', '09123213213', 'BARBADOS', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-15', '2025-12-15', 300.00, 'active', '2024-12-15 06:46:11', 'default-profile.png', 'KCCHwR94MH'),
(58, 'Dfgfdxfcdfad', 'Cfzasasdwaq', 'male', '3232-03-15', 'sayankennethangel@gmail.com', '09123213213', 'BAHAMAS', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-15', '2025-12-15', 300.00, 'active', '2024-12-15 06:46:53', 'default-profile.png', 'HLy2gbIZZR'),
(59, 'Gfdgdfgerger', 'Gergdfsdf', 'male', '0000-00-00', 'sayankennethangel@gmail.com', '09123213213', 'BARBADOS', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-16', '2025-12-16', 300.00, 'active', '2024-12-15 15:25:34', 'default-profile.png', 'h2XnHtsrbe'),
(60, 'Dwadwadhtht', 'Dwadwadwa', 'male', '2024-12-18', 'sayankennethangel@gmail.com', '09123213213', 'BELGIUM', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-18', '2025-12-18', 300.00, 'active', '2024-12-17 19:14:25', 'default-profile.png', 'Q2v3Kax64d'),
(61, 'Dwadwadhtht', 'Dwadwadwa', 'male', '2024-12-18', 'sayankennethangel@gmail.com', '09123213213', 'BELGIUM', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-18', '2025-12-18', 300.00, 'active', '2024-12-17 19:17:28', 'default-profile.png', 'zFzftYENtc'),
(62, 'Hjtyfdasdaw', 'Fderwtwerw', 'male', '3211-05-21', 'sayankennethangel@gmail.com', '09123213213', 'BELARUS', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-18', '2025-12-18', 300.00, 'active', '2024-12-17 19:23:28', 'default-profile.png', 'jGEKcaRcpL'),
(63, 'Jtyjtyjyt', 'Tyjgdew', 'female', '3121-03-12', 'sayankennethangel@gmail.com', '09123213213', 'BARBADOS', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-18', '2025-12-18', 300.00, 'active', '2024-12-17 19:25:45', 'default-profile.png', 'shM8g55P9Q'),
(64, 'Jtyjtyjyt', 'Tyjgdew', 'female', '3121-03-12', 'sayankennethangel@gmail.com', '09123213213', 'BARBADOS', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-18', '2025-12-18', 300.00, 'active', '2024-12-17 19:26:03', 'default-profile.png', 'fLOT0CWruU'),
(65, 'Jhtrqwaw', 'Dwqdwqdqwdqw', 'male', '3121-03-12', 'dwadwadwadwadwa@gmail.com', '09123213213', 'BELARUS', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-18', '2025-12-18', 300.00, 'active', '2024-12-17 19:28:35', 'default-profile.png', 'HgSE8NXv3u'),
(66, 'Zxcxzcxz', 'Czxcxzcxz', 'male', '3121-03-12', 'sayankennethangel@gmail.com', '09123213213', 'BELGIUM', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-18', '2025-12-18', 300.00, 'active', '2024-12-17 19:29:24', 'default-profile.png', 'j7mcIAxBNT'),
(67, 'Dwadhjtywfe', 'Dawawgfdgdf', 'male', '3121-03-12', 'sayankennethangel@gmail.com', '09123213213', 'BELGIUM', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-18', '2025-12-18', 300.00, 'active', '2024-12-17 19:35:42', 'default-profile.png', '2dFkcycd66'),
(68, 'Kenneth uya', 'Kennethaaa', 'male', '3121-03-12', 'sayankennethangel@gmail.com', '09123213213', 'BELGIUM', '9000', 'MISAMIS ORIENTAL', 'CDO', '2024-12-18', '2025-12-18', 300.00, 'active', '2024-12-17 19:45:20', 'default-profile.png', 'qkG2jZeZKi');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `coaching_payment_date` date DEFAULT NULL,
  `monthly_plan_payment_date` date DEFAULT NULL,
  `monthly_plan_expiration_date` date DEFAULT NULL,
  `membership_renewal_payment_date` date DEFAULT NULL,
  `membership_expiration_date` date DEFAULT NULL,
  `locker_payment_date` date DEFAULT NULL,
  `locker_expiration_date` date DEFAULT NULL,
  `coach_id` int(11) DEFAULT NULL,
  `coaching_amount` decimal(10,2) DEFAULT 0.00,
  `monthly_amount` decimal(10,2) DEFAULT 0.00,
  `renewal_amount` decimal(10,2) DEFAULT 0.00,
  `locker_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `member_id`, `coaching_payment_date`, `monthly_plan_payment_date`, `monthly_plan_expiration_date`, `membership_renewal_payment_date`, `membership_expiration_date`, `locker_payment_date`, `locker_expiration_date`, `coach_id`, `coaching_amount`, `monthly_amount`, `renewal_amount`, `locker_amount`, `total_amount`) VALUES
(60, 42, NULL, NULL, NULL, NULL, NULL, '2024-12-20', '0000-00-00', NULL, 0.00, 0.00, 0.00, 1600.00, 1600.00),
(61, 47, NULL, NULL, NULL, NULL, NULL, '2024-12-20', '0000-00-00', NULL, 0.00, 0.00, 0.00, 1600.00, 1600.00),
(62, 46, NULL, NULL, NULL, NULL, NULL, '2024-12-20', '0000-00-00', NULL, 0.00, 0.00, 0.00, 1600.00, 1600.00);

-- --------------------------------------------------------

--
-- Table structure for table `pos_logs`
--

CREATE TABLE `pos_logs` (
  `log_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `id` int(11) DEFAULT NULL,
  `coach_id` int(11) DEFAULT NULL,
  `date` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`items`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pos_logs`
--

INSERT INTO `pos_logs` (`log_id`, `member_id`, `id`, `coach_id`, `date`, `total_amount`, `items`, `created_at`) VALUES
(114, 43, NULL, NULL, '2024-12-11 10:20:37', 333.00, '[{\"name\":\"Kenneth Sayan\",\"quantity\":3}]', '2024-12-11 02:20:37'),
(115, 42, NULL, NULL, '2024-12-11 10:21:06', 3.00, '[{\"name\":\"111\",\"quantity\":3}]', '2024-12-11 02:21:06'),
(116, 44, NULL, NULL, '2024-12-11 10:21:19', 9.00, '[{\"name\":\"111\",\"quantity\":9}]', '2024-12-11 02:21:19'),
(117, NULL, NULL, 2, '2024-12-11 10:21:39', 333.00, '[{\"name\":\"dwadwadwa\",\"quantity\":3}]', '2024-12-11 02:21:39'),
(118, 42, NULL, NULL, '2024-12-11 11:28:03', 10012.00, '[{\"name\":\"111\",\"quantity\":1},{\"name\":\"ASH\",\"quantity\":1},{\"name\":\"Way\",\"quantity\":1}]', '2024-12-11 03:28:03'),
(119, 41, NULL, NULL, '2024-12-11 18:00:55', 3.00, '[{\"name\":\"111\",\"quantity\":3}]', '2024-12-11 10:00:55'),
(120, 42, NULL, NULL, '2024-12-11 18:03:46', 1443.00, '[{\"name\":\"dwadwadwa\",\"quantity\":13}]', '2024-12-11 10:03:46'),
(121, 41, NULL, NULL, '2024-12-11 18:03:54', 1110.00, '[{\"name\":\"dwadwadwa\",\"quantity\":10}]', '2024-12-11 10:03:54'),
(122, 45, NULL, NULL, '2024-12-11 18:16:59', 3.00, '[{\"name\":\"111\",\"quantity\":3}]', '2024-12-11 10:16:59'),
(123, NULL, 20, NULL, '2024-12-15 00:14:54', 10234.00, '[{\"name\":\"dwadwadwa\",\"quantity\":1},{\"name\":\"Way\",\"quantity\":1},{\"name\":\"Kenneth Sayan\",\"quantity\":1},{\"name\":\"ASH\",\"quantity\":1},{\"name\":\"111\",\"quantity\":1}]', '2024-12-14 16:14:54'),
(124, 40, NULL, NULL, '2024-12-15 15:49:25', 11.00, '[{\"name\":\"ASH\",\"quantity\":1}]', '2024-12-15 07:49:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_no` varchar(15) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `usertype` enum('admin','staff') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT 'default-profile.png',
  `otp_code` int(11) DEFAULT NULL,
  `otp_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `contact_no`, `gender`, `email`, `usertype`, `created_at`, `profile_picture`, `otp_code`, `otp_expiration`) VALUES
(13, 'admin3', '$2y$10$otj2F2W3I/adVVOOHc72quzrztpwhgXPHxq0I8zPwdwrnec.0y2gO', '09123456789', 'male', 'sayankennethangel@gmail.com', 'admin', '2024-11-06 12:19:15', 'uploads/profile.jpg', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `walkins`
--

CREATE TABLE `walkins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `join_date` date NOT NULL,
  `walkin_type` enum('basic','premium') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `walkins`
--

INSERT INTO `walkins` (`id`, `name`, `lastname`, `contact_number`, `gender`, `join_date`, `walkin_type`, `amount`, `created_at`) VALUES
(18, 'Kenneth', 'Pulvera', '09279001620', 'female', '2024-12-06', 'premium', 270.00, '2024-12-05 13:13:46'),
(19, 'Kenneth', 'Sayan', '09279001620', 'male', '2024-12-07', 'premium', 270.00, '2024-12-06 17:35:49'),
(20, 'dwadwa', 'dwadwadwa', '09279001620', 'female', '2024-12-07', 'premium', 270.00, '2024-12-06 17:37:16'),
(21, 'ashley', 'pogoso', '09279001620', 'female', '2024-12-27', 'basic', 70.00, '2024-12-06 17:40:11'),
(22, 'dwadwa', 'dwadwadwa', '31221312312', 'male', '2024-12-12', 'premium', 270.00, '2024-12-06 17:45:28'),
(23, 'pogoso', 'pulvera', '09123456789', 'female', '2024-12-26', 'basic', 70.00, '2024-12-06 17:45:55'),
(24, 'lobot', 'dako', '09279001620', 'female', '2024-12-17', 'basic', 70.00, '2024-12-06 17:48:43'),
(25, 'dwadwadwa', 'dwadawdwa', '09279001620', 'male', '2025-01-08', 'basic', 70.00, '2024-12-06 17:53:09'),
(26, 'dwadwad', 'wadwadwa', '09279001620', 'male', '2024-12-14', 'premium', 270.00, '2024-12-12 20:25:11'),
(27, 'yuri', 'albert', '09123151231', 'male', '2024-12-13', 'premium', 270.00, '2024-12-12 21:24:52'),
(28, 'Kennethadws', 'Sayansas', '09279001620', 'male', '2024-12-14', 'basic', 70.00, '2024-12-12 21:25:14'),
(29, 'asdqeqjyjyht', 'dwadwadaas', '09471289371', 'male', '2024-12-13', 'basic', 70.00, '2024-12-12 21:26:16'),
(30, 'dawdwadaswadgg', 'wadwadwadaasdwa', '09279001620', 'female', '2024-12-13', 'basic', 70.00, '2024-12-12 21:29:54'),
(31, 'jafdszfasdgdas', 'fjzfjazfjas', '31221312312', 'female', '2024-12-20', 'premium', 270.00, '2024-12-12 21:35:01'),
(32, 'dawgdwagdwahtj', 'dadwadwa', '09279001620', 'female', '2024-12-17', 'premium', 270.00, '2024-12-12 21:43:07');

-- --------------------------------------------------------

--
-- Table structure for table `walkins_logs`
--

CREATE TABLE `walkins_logs` (
  `log_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `join_date` date NOT NULL,
  `walkin_type` enum('basic','premium') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `action` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `walkins_logs`
--

INSERT INTO `walkins_logs` (`log_id`, `id`, `name`, `lastname`, `contact_number`, `gender`, `join_date`, `walkin_type`, `amount`, `action`, `created_at`) VALUES
(64, 24, 'lobot', 'dako', '09279001620', 'female', '2024-12-17', 'basic', 70.00, 'Updated', '2024-12-17 12:05:14'),
(65, 32, 'dawgdwagdwahtj', 'dadwadwa', '09279001620', 'male', '2024-12-17', 'premium', 270.00, 'Updated', '2024-12-17 12:05:42'),
(66, 32, 'dawgdwagdwahtj', 'dadwadwa', '09279001620', 'female', '2024-12-21', 'basic', 70.00, 'Updated', '2024-12-17 12:06:06'),
(67, 32, 'dawgdwagdwahtj', 'dadwadwa', '09279001620', 'male', '2024-12-27', 'premium', 270.00, 'Updated', '2024-12-17 12:29:49'),
(68, 32, 'dawgdwagdwahtj', 'dadwadwa', '09279001620', 'male', '2024-12-26', 'basic', 70.00, 'Updated', '2024-12-17 14:32:08'),
(69, 32, 'dawgdwagdwahtj', 'dadwadwa', '09279001620', 'female', '2024-12-17', 'premium', 270.00, 'Updated', '2024-12-17 14:38:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`logs_id`),
  ADD KEY `attendance_id` (`attendance_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `coaches`
--
ALTER TABLE `coaches`
  ADD PRIMARY KEY (`coach_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`member_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `pos_logs`
--
ALTER TABLE `pos_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `id` (`id`),
  ADD KEY `coach_id` (`coach_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `walkins`
--
ALTER TABLE `walkins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `walkins_logs`
--
ALTER TABLE `walkins_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `logs_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `coaches`
--
ALTER TABLE `coaches`
  MODIFY `coach_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `pos_logs`
--
ALTER TABLE `pos_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `walkins`
--
ALTER TABLE `walkins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `walkins_logs`
--
ALTER TABLE `walkins_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`attendance_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_logs_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE;

--
-- Constraints for table `pos_logs`
--
ALTER TABLE `pos_logs`
  ADD CONSTRAINT `pos_logs_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pos_logs_ibfk_2` FOREIGN KEY (`id`) REFERENCES `walkins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pos_logs_ibfk_3` FOREIGN KEY (`coach_id`) REFERENCES `coaches` (`coach_id`) ON DELETE SET NULL;

--
-- Constraints for table `walkins_logs`
--
ALTER TABLE `walkins_logs`
  ADD CONSTRAINT `walkins_logs_ibfk_1` FOREIGN KEY (`id`) REFERENCES `walkins` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
