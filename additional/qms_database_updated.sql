-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2026 at 10:43 AM
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
-- Database: `qms_database_updated`
--
CREATE DATABASE IF NOT EXISTS `qms_database_updated` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `qms_database_updated`;

-- --------------------------------------------------------

--
-- Table structure for table `allocated_quarters`
--

DROP TABLE IF EXISTS `allocated_quarters`;
CREATE TABLE IF NOT EXISTS `allocated_quarters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nic` varchar(20) NOT NULL,
  `computer_no` varchar(50) NOT NULL,
  `quarter_no` varchar(50) NOT NULL,
  `quarter_name` varchar(100) DEFAULT NULL,
  `quarter_location` varchar(100) NOT NULL,
  `quarter_type` varchar(100) NOT NULL,
  `resident_name` varchar(150) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `workplace` varchar(150) NOT NULL,
  `allocated_date` date NOT NULL,
  `other` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nic` (`nic`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--

DROP TABLE IF EXISTS `applicants`;
CREATE TABLE IF NOT EXISTS `applicants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `designation_score` int(11) NOT NULL DEFAULT 0,
  `applied_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
CREATE TABLE IF NOT EXISTS `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_no` varchar(30) DEFAULT NULL,
  `nic` varchar(20) NOT NULL,
  `computer_no` varchar(50) NOT NULL,
  `quarter_type` varchar(100) NOT NULL,
  `residence_type` enum('single','joint_residence') DEFAULT NULL,
  `form_language` enum('en','single_residence') DEFAULT NULL,
  `application_date` date NOT NULL,
  `boss_status` varchar(20) DEFAULT 'pending',
  `boss_reason` text DEFAULT NULL,
  `file_status` varchar(20) DEFAULT 'pending',
  `file_reason` text DEFAULT NULL,
  `clerk_status` varchar(20) DEFAULT 'pending',
  `clerk_reason` text DEFAULT NULL,
  `final_status` varchar(20) DEFAULT 'pending',
  `final_reason` text DEFAULT NULL,
  `marks` decimal(5,2) DEFAULT NULL,
  `waiting_list_no` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `applied_quarter_number` varchar(50) DEFAULT NULL,
  `applied_quarter_location` varchar(100) DEFAULT NULL,
  `application_date_received` date DEFAULT NULL,
  `application_submitted_at` datetime DEFAULT NULL,
  `application_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `has_applicant_served_other_sub_dept` tinyint(1) DEFAULT 0,
  `has_applicant_service_breaks` tinyint(1) DEFAULT 0,
  `does_applicant_have_children_residing` tinyint(1) DEFAULT 0,
  `does_applicant_have_spouse_residing` tinyint(1) DEFAULT 0,
  `is_applicant_spouse_gov_employee` tinyint(1) DEFAULT 0,
  `is_applicant_currently_renting` tinyint(1) DEFAULT 0,
  `does_applicant_have_disability` tinyint(1) DEFAULT 0,
  `has_applicant_rejected_quarter_before` tinyint(1) DEFAULT 0,
  `has_applicant_applied_before` tinyint(1) DEFAULT 0,
  `is_resubmission` tinyint(1) NOT NULL DEFAULT 0,
  `application_status` enum('draft','submitted','pending_clerk','pending_super','approved','rejected','returned_to_boss','returned_to_file') NOT NULL DEFAULT 'draft',
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `submit_status` varchar(20) DEFAULT 'pending',
  `branch_status` varchar(20) DEFAULT 'pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_no` (`application_no`),
  KEY `nic` (`nic`),
  KEY `idx_applications_nic` (`nic`),
  KEY `idx_applications_status` (`application_status`),
  KEY `idx_applications_quarter_type` (`quarter_type`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `application_no`, `nic`, `computer_no`, `quarter_type`, `residence_type`, `form_language`, `application_date`, `boss_status`, `boss_reason`, `file_status`, `file_reason`, `clerk_status`, `clerk_reason`, `final_status`, `final_reason`, `marks`, `waiting_list_no`, `created_at`, `applied_quarter_number`, `applied_quarter_location`, `application_date_received`, `application_submitted_at`, `application_updated_at`, `has_applicant_served_other_sub_dept`, `has_applicant_service_breaks`, `does_applicant_have_children_residing`, `does_applicant_have_spouse_residing`, `is_applicant_spouse_gov_employee`, `is_applicant_currently_renting`, `does_applicant_have_disability`, `has_applicant_rejected_quarter_before`, `has_applicant_applied_before`, `is_resubmission`, `application_status`, `status`, `submit_status`, `branch_status`) VALUES
(10, 'SLR/AOT/2026/0001', '200329012186', '200329012186', 'Row', 'single', 'en', '2026-10-15', 'approved', NULL, 'approved', NULL, 'pending', NULL, 'pending', NULL, NULL, 1, '2026-10-15 06:59:10', 'G-022', 'Maradana', NULL, '2026-10-15 12:34:27', '2026-10-16 13:26:11', 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 'pending_clerk', 'pending', 'success', 'pending'),
(11, 'SLR/AOT/2026/0002', '200329012186', '200329012186', 'Field', 'single', 'en', '2026-10-16', 'approved', NULL, 'rejected', 'Personal file Rejection reason', 'pending', NULL, 'pending', NULL, NULL, 1, '2026-10-16 04:03:36', 'K-014', 'Maradana', NULL, '2026-10-16 10:26:01', '2026-10-16 13:26:37', 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 'returned_to_boss', 'pending', 'success', 'pending'),
(12, 'SLR/AOT/2026/0003', '200329012186', '200329012186', 'Twin', 'single', 'en', '2026-10-16', 'pending', NULL, 'pending', NULL, 'pending', NULL, 'pending', NULL, NULL, 2, '2026-10-16 05:18:27', 'K-014', 'Kandy', NULL, '2026-10-16 10:57:53', '2026-10-16 10:57:53', 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 'submitted', 'pending', 'success', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `children_details`
--

DROP TABLE IF EXISTS `children_details`;
CREATE TABLE IF NOT EXISTS `children_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `child_full_name` varchar(150) NOT NULL,
  `child_gender` enum('female','male') NOT NULL,
  `child_date_of_birth` date NOT NULL,
  `child_civil_status` enum('married','unmarried','divorced','widowed') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_children_app` (`application_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `children_details`
--

INSERT INTO `children_details` (`id`, `application_id`, `child_full_name`, `child_gender`, `child_date_of_birth`, `child_civil_status`) VALUES
(9, 10, 'ABCD', 'male', '2026-10-22', 'married'),
(10, 10, 'hhgjg', 'female', '2026-10-16', 'divorced'),
(13, 11, 'ABCD', 'male', '2026-10-22', 'married'),
(14, 11, 'ABCD', 'female', '2026-10-23', 'widowed'),
(17, 12, 'ABCD', 'male', '2026-10-21', 'married'),
(18, 12, 'hhgjg', 'female', '2026-10-27', 'widowed');

-- --------------------------------------------------------

--
-- Table structure for table `clerk_recommendations`
--

DROP TABLE IF EXISTS `clerk_recommendations`;
CREATE TABLE IF NOT EXISTS `clerk_recommendations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `clerk_is_right_forfeited` tinyint(1) DEFAULT NULL,
  `clerk_forfeit_expiry_date` date DEFAULT NULL,
  `clerk_is_mihindusethpura_recipient` tinyint(1) DEFAULT NULL,
  `clerk_residing_in_other_gov_quarter` tinyint(1) DEFAULT NULL,
  `clerk_other_remarks` text DEFAULT NULL,
  `clerk_cabinet_number` varchar(50) DEFAULT NULL,
  `clerk_recommendation_status` enum('pending','recommended','not_recommended') NOT NULL DEFAULT 'pending',
  `clerk_recommended_at` datetime DEFAULT NULL,
  `clerk_signed_by_nic` varchar(20) DEFAULT NULL,
  `clerk_signed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_clerk_app` (`application_id`),
  KEY `fk_clerk_user` (`clerk_signed_by_nic`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
CREATE TABLE IF NOT EXISTS `complaints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(20) DEFAULT NULL,
  `complaints` text NOT NULL,
  `related_issue` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_complaints_user` (`user_id`),
  KEY `idx_complaints_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `declaration_details`
--

DROP TABLE IF EXISTS `declaration_details`;
CREATE TABLE IF NOT EXISTS `declaration_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `declaration_salary_month` varchar(20) NOT NULL,
  `declaration_salary_year` int(4) NOT NULL,
  `salary_certificate_file_path` varchar(255) DEFAULT NULL,
  `declaration_date` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_declaration_app` (`application_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `declaration_details`
--

INSERT INTO `declaration_details` (`id`, `application_id`, `declaration_salary_month`, `declaration_salary_year`, `salary_certificate_file_path`, `declaration_date`) VALUES
(6, 10, 'May', 2026, 'uploads/salary_certificates/6ad07afb5c1d5.pdf', '2026-10-16'),
(8, 11, 'March', 2026, 'uploads/salary_certificates/6ad1ae619327b.pdf', '2026-10-31'),
(10, 12, 'May', 2026, 'uploads/salary_certificates/6ad1b5d93c472.pdf', '2026-10-30');

-- --------------------------------------------------------

--
-- Table structure for table `designations`
--

DROP TABLE IF EXISTS `designations`;
CREATE TABLE IF NOT EXISTS `designations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `designation_name` varchar(100) NOT NULL,
  `designation_name_si` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `designations`
--

INSERT INTO `designations` (`id`, `designation_name`, `designation_name_si`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Station Master', 'දුම්රිය ස්ථානාධිපති', 1, 1, '2026-09-02 07:47:37'),
(2, 'Assistant Station Master', 'සහාය දුම්රිය ස්ථානාධිපති', 1, 2, '2026-09-02 07:47:37'),
(3, 'Controller', 'පාලක', 1, 3, '2026-09-02 07:47:37'),
(4, 'Assistant Controller', 'සහාය පාලක', 1, 4, '2026-09-02 07:47:37'),
(5, 'Train Guard', 'දුම්රිය ආරක්ෂක', 1, 5, '2026-09-02 07:47:37'),
(6, 'Clerk', 'ලිපිකරු', 1, 6, '2026-09-02 07:47:37'),
(7, 'Senior Clerk', 'ජ්‍යෙෂ්ඨ ලිපිකරු', 1, 7, '2026-09-02 07:47:37'),
(8, 'Superintendent', 'අධිකාරී', 1, 8, '2026-09-02 07:47:37'),
(9, 'Deputy Superintendent', 'නියෝජ්‍ය අධිකාරී', 1, 9, '2026-09-02 07:47:37'),
(10, 'Chief Superintendent', 'ප්‍රධාන අධිකාරී', 1, 10, '2026-09-02 07:47:37'),
(11, 'Engineer', 'ඉංජිනේරු', 1, 11, '2026-09-02 07:47:37'),
(12, 'Assistant Engineer', 'සහාය ඉංජිනේරු', 1, 12, '2026-09-02 07:47:37'),
(13, 'Technical Officer', 'තාක්ෂණික නිලධාරී', 1, 13, '2026-09-02 07:47:37'),
(14, 'Signal Inspector', 'සංඥා පරීක්ෂක', 1, 14, '2026-09-02 07:47:37'),
(15, 'Driver', 'රියදුරු', 1, 15, '2026-09-02 07:47:37'),
(16, 'Fireman', 'ගිනි නිවන භට', 1, 16, '2026-09-02 07:47:37'),
(17, 'Shunter', 'ෂන්ටර්', 1, 17, '2026-09-02 07:47:37'),
(18, 'Ticket Examiner', 'ටිකට් පරීක්ෂක', 1, 18, '2026-09-02 07:47:37'),
(19, 'Commercial Officer', 'වාණිජ නිලධාරී', 1, 19, '2026-09-02 07:47:37'),
(20, 'Accounts Officer', 'ගිණුම් නිලධාරී', 1, 20, '2026-09-02 07:47:37');

-- --------------------------------------------------------

--
-- Table structure for table `disability_medical_details`
--

DROP TABLE IF EXISTS `disability_medical_details`;
CREATE TABLE IF NOT EXISTS `disability_medical_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `disability_or_illness_details` text DEFAULT NULL,
  `medical_certificate_file_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_disability_app` (`application_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disability_medical_details`
--

INSERT INTO `disability_medical_details` (`id`, `application_id`, `disability_or_illness_details`, `medical_certificate_file_path`) VALUES
(6, 10, 'adkjansjdnad', 'uploads/medical_certificates/6ad07afb5ab5e.pdf'),
(8, 11, 'adkjansjdnad', 'uploads/medical_certificates/6ad1ae61927b0.pdf'),
(10, 12, 'adkjansjdnad', 'uploads/medical_certificates/6ad1b5d93b201.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `districts`
--

DROP TABLE IF EXISTS `districts`;
CREATE TABLE IF NOT EXISTS `districts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `district_name` varchar(100) NOT NULL,
  `district_name_si` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `districts`
--

INSERT INTO `districts` (`id`, `district_name`, `district_name_si`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Colombo', 'කොළඹ', 1, 1, '2026-09-02 07:47:38'),
(2, 'Gampaha', 'ගම්පහ', 1, 2, '2026-09-02 07:47:38'),
(3, 'Kalutara', 'කලුතර', 1, 3, '2026-09-02 07:47:38'),
(4, 'Kandy', 'මහනුවර', 1, 4, '2026-09-02 07:47:38'),
(5, 'Matale', 'මාතලේ', 1, 5, '2026-09-02 07:47:38'),
(6, 'Nuwara Eliya', 'නුවර එළිය', 1, 6, '2026-09-02 07:47:38'),
(7, 'Galle', 'ගාල්ල', 1, 7, '2026-09-02 07:47:38'),
(8, 'Matara', 'මාතර', 1, 8, '2026-09-02 07:47:38'),
(9, 'Hambantota', 'හම්බන්තොට', 1, 9, '2026-09-02 07:47:38'),
(10, 'Jaffna', 'යාපනය', 1, 10, '2026-09-02 07:47:38'),
(11, 'Kilinochchi', 'කිලිනොච්චි', 1, 11, '2026-09-02 07:47:38'),
(12, 'Mannar', 'මන්නාරම', 1, 12, '2026-09-02 07:47:38'),
(13, 'Vavuniya', 'වවුනියාව', 1, 13, '2026-09-02 07:47:38'),
(14, 'Mullaitivu', 'මුලතිව්', 1, 14, '2026-09-02 07:47:38'),
(15, 'Batticaloa', 'මඩකලපුව', 1, 15, '2026-09-02 07:47:38'),
(16, 'Ampara', 'අම්පාර', 1, 16, '2026-09-02 07:47:38'),
(17, 'Trincomalee', 'ත්‍රිකුණාමලය', 1, 17, '2026-09-02 07:47:38'),
(18, 'Kurunegala', 'කුරුණෑගල', 1, 18, '2026-09-02 07:47:38'),
(19, 'Puttalam', 'පුත්තලම', 1, 19, '2026-09-02 07:47:38'),
(20, 'Anuradhapura', 'අනුරාධපුර', 1, 20, '2026-09-02 07:47:38'),
(21, 'Polonnaruwa', 'පොලොන්නරුව', 1, 21, '2026-09-02 07:47:38'),
(22, 'Badulla', 'බදුල්ල', 1, 22, '2026-09-02 07:47:38'),
(23, 'Monaragala', 'මොනරාගල', 1, 23, '2026-09-02 07:47:38'),
(24, 'Ratnapura', 'රත්නපුර', 1, 24, '2026-09-02 07:47:38'),
(25, 'Kegalle', 'කෑගල්ල', 1, 25, '2026-09-02 07:47:38');

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_marks`
--

DROP TABLE IF EXISTS `evaluation_marks`;
CREATE TABLE IF NOT EXISTS `evaluation_marks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `mark_waiting_list_duration` int(11) DEFAULT NULL,
  `mark_child_count` int(11) DEFAULT NULL,
  `mark_housing_condition` int(11) DEFAULT NULL,
  `mark_distance_above_30km` int(11) DEFAULT NULL,
  `mark_applicant_serious_illness` int(11) DEFAULT NULL,
  `mark_dependent_serious_illness` int(11) DEFAULT NULL,
  `mark_marital_status` int(11) DEFAULT NULL,
  `mark_tie_breaker_service_years` int(11) DEFAULT NULL,
  `total_evaluation_marks` int(11) DEFAULT NULL,
  `marks_evaluated_at` datetime DEFAULT current_timestamp(),
  `marks_evaluated_by_nic` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_marks_app` (`application_id`),
  KEY `fk_marks_user` (`marks_evaluated_by_nic`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nic` varchar(20) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `success` tinyint(1) DEFAULT 0,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_nic_time` (`nic`,`attempt_time`),
  KEY `idx_ip_time` (`ip_address`,`attempt_time`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `nic`, `ip_address`, `success`, `attempt_time`) VALUES
(61, '200329012186', '::1', 1, '2026-10-14 09:01:28'),
(62, '200329012186', '::1', 1, '2026-10-14 09:02:05'),
(63, '200329012186', '::1', 1, '2026-10-14 09:02:47'),
(64, '200329012186', '::1', 1, '2026-10-14 09:10:05'),
(65, '200329012186', '::1', 1, '2026-10-14 09:13:16'),
(66, '200329012186', '::1', 1, '2026-10-14 09:42:44'),
(67, '199012345678', '::1', 0, '2026-10-14 10:05:24'),
(68, '200329012186', '::1', 1, '2026-10-15 04:58:54'),
(69, '200329012186', '::1', 1, '2026-10-15 05:26:27'),
(70, '200329012186', '::1', 0, '2026-10-15 05:29:48'),
(71, '200329012186', '::1', 1, '2026-10-15 05:29:54'),
(72, '200329012186', '::1', 1, '2026-10-15 05:35:19'),
(73, '200329012186', '::1', 1, '2026-10-15 06:58:55'),
(74, '200329012186', '::1', 1, '2026-10-15 07:09:48'),
(75, '200329012186', '::1', 0, '2026-10-15 09:24:35'),
(76, '200329012186', '::1', 1, '2026-10-15 09:24:43'),
(77, '200329012186', '::1', 1, '2026-10-15 09:30:54'),
(78, '200329012186', '::1', 1, '2026-10-16 04:02:54'),
(79, '200329012186', '::1', 1, '2026-10-16 04:03:29'),
(80, '200329012186', '::1', 1, '2026-10-16 04:08:55'),
(81, '200329012186', '::1', 1, '2026-10-16 04:27:52'),
(82, '200329012186', '::1', 1, '2026-10-16 04:56:50'),
(83, '200329012186', '::1', 1, '2026-10-16 05:17:53'),
(84, '200329012186', '::1', 1, '2026-10-16 05:22:45'),
(85, '200329012186', '::1', 1, '2026-10-16 05:23:48'),
(86, '200329012186', '::1', 1, '2026-10-16 05:43:19'),
(87, '200329012186', '::1', 1, '2026-10-16 08:00:52'),
(88, '200329012186', '::1', 1, '2026-10-16 08:02:01'),
(89, '200329012186', '::1', 1, '2026-10-16 08:02:58'),
(90, '200329012186', '::1', 1, '2026-10-16 08:13:29'),
(91, '200329012186', '::1', 1, '2026-10-16 08:27:18'),
(92, '200329012186', '::1', 1, '2026-10-19 03:22:57'),
(93, '123456789', '::1', 1, '2026-10-19 03:50:23'),
(94, '200329012186', '::1', 1, '2026-10-19 03:50:42');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance`
--

DROP TABLE IF EXISTS `maintenance`;
CREATE TABLE IF NOT EXISTS `maintenance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quarter_number` varchar(50) NOT NULL,
  `quarter_name` varchar(100) DEFAULT NULL,
  `location_name` varchar(100) NOT NULL,
  `quarter_type` varchar(100) NOT NULL,
  `maintenance_status` varchar(50) NOT NULL DEFAULT 'Under Maintenance',
  `last_maintenance_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_maint_quarter` (`quarter_number`),
  KEY `idx_maint_status` (`maintenance_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nic` varchar(20) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `nic` (`nic`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `nic`, `title`, `message`, `is_read`, `created_at`) VALUES
(21, '200329012186', 'Application Received', 'Your quarter application has been received and is under review.', 1, '2026-10-15 07:04:27'),
(22, '200329012186', 'Application Received', 'Your quarter application has been received and is under review.', 0, '2026-10-16 04:08:32'),
(23, '200329012186', 'Application Received', 'Your quarter application has been received and is under review.', 0, '2026-10-16 04:56:01'),
(24, '200329012186', 'Application Received', 'Your quarter application has been received and is under review.', 0, '2026-10-16 05:22:18'),
(25, '200329012186', 'Application Rejected', 'Your application was rejected by the Immediate Boss. Reason: Testing the rejection', 0, '2026-10-16 05:23:04'),
(26, '200329012186', 'Application Received', 'Your quarter application has been received and is under review.', 0, '2026-10-16 05:27:53');

-- --------------------------------------------------------

--
-- Table structure for table `other_sub_departments_served`
--

DROP TABLE IF EXISTS `other_sub_departments_served`;
CREATE TABLE IF NOT EXISTS `other_sub_departments_served` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `sub_department_name` varchar(100) NOT NULL,
  `service_from_date` date NOT NULL,
  `service_to_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_othersub_app` (`application_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permanent_residence_details`
--

DROP TABLE IF EXISTS `permanent_residence_details`;
CREATE TABLE IF NOT EXISTS `permanent_residence_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `permanent_residence_address` text NOT NULL,
  `is_applicant_owner_of_residence` tinyint(1) DEFAULT NULL,
  `owner_relationship_if_not_self` varchar(50) DEFAULT NULL,
  `does_applicant_commute_from_residence` tinyint(1) DEFAULT NULL,
  `permanent_residence_district` varchar(50) DEFAULT NULL,
  `nearest_major_city_name` varchar(100) DEFAULT NULL,
  `distance_to_nearest_city_km` decimal(6,1) DEFAULT NULL,
  `distance_from_branch_post_to_work_km` decimal(6,1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_residence_app` (`application_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permanent_residence_details`
--

INSERT INTO `permanent_residence_details` (`id`, `application_id`, `permanent_residence_address`, `is_applicant_owner_of_residence`, `owner_relationship_if_not_self`, `does_applicant_commute_from_residence`, `permanent_residence_district`, `nearest_major_city_name`, `distance_to_nearest_city_km`, `distance_from_branch_post_to_work_km`) VALUES
(6, 10, 'nasdjasjdnaljd', 1, NULL, 1, 'Kilinochchi', 'aasdada', 53.0, 45.0),
(8, 11, 'nasdjasjdnaljd', 0, 'andbakjsbdad', 1, 'Matara', 'Giriulla', 3.0, 2.0),
(10, 12, 'nasdjasjdnaljd', 0, 'andbakjsbdad', 1, 'Jaffna', 'aasdada', 4.0, 3.0);

-- --------------------------------------------------------

--
-- Table structure for table `personal_file_offices`
--

DROP TABLE IF EXISTS `personal_file_offices`;
CREATE TABLE IF NOT EXISTS `personal_file_offices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `office_name` varchar(100) NOT NULL,
  `office_name_si` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_office_name` (`office_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `personal_file_offices`
--

INSERT INTO `personal_file_offices` (`id`, `office_name`, `office_name_si`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Divisional Office - Colombo', 'කොට්ඨාශ කාර්යාලය - කොළඹ', 1, 1, '2026-10-12 06:38:41'),
(2, 'Divisional Office - Maradana', 'කොට්ඨාශ කාර්යාලය - මරදාන', 1, 2, '2026-10-12 06:38:41'),
(3, 'Divisional Office - Kandy', 'කොට්ඨාශ කාර්යාලය - මහනුවර', 1, 3, '2026-10-12 06:38:41'),
(4, 'Divisional Office - Galle', 'කොට්ඨාශ කාර්යාලය - ගාල්ල', 1, 4, '2026-10-12 06:38:41'),
(5, 'Divisional Office - Anuradhapura', 'කොට්ඨාශ කාර්යාලය - අනුරාධපුර', 1, 5, '2026-10-12 06:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `previous_quarter_details`
--

DROP TABLE IF EXISTS `previous_quarter_details`;
CREATE TABLE IF NOT EXISTS `previous_quarter_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `previous_quarter_number` varchar(50) DEFAULT NULL,
  `previous_quarter_vacated_date` date DEFAULT NULL,
  `previous_quarter_vacate_reason` text DEFAULT NULL,
  `has_applicant_rejected_quarter_before` tinyint(1) DEFAULT NULL,
  `rejected_quarter_details` text DEFAULT NULL,
  `has_applicant_applied_for_quarter_before` tinyint(1) DEFAULT NULL,
  `previous_application_date` date DEFAULT NULL,
  `previous_applied_quarter_type` varchar(50) DEFAULT NULL,
  `previous_applied_quarter_location` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_prevquarters_app` (`application_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `previous_quarter_details`
--

INSERT INTO `previous_quarter_details` (`id`, `application_id`, `previous_quarter_number`, `previous_quarter_vacated_date`, `previous_quarter_vacate_reason`, `has_applicant_rejected_quarter_before`, `rejected_quarter_details`, `has_applicant_applied_for_quarter_before`, `previous_application_date`, `previous_applied_quarter_type`, `previous_applied_quarter_location`) VALUES
(6, 10, 'asdmasmdad', '2026-10-16', 'admakmddada', 1, NULL, 1, '2026-10-17', 'Sub Staff', 'Maradana'),
(8, 11, 'asdmasmdad', '2026-10-24', 'admakmddada', 1, NULL, 1, '2026-10-23', 'Sub Staff', 'Kandy'),
(10, 12, 'asdmasmdad', '2026-10-19', 'admakmddada', 1, NULL, 1, '2026-10-21', 'Couple', 'Maradana');

-- --------------------------------------------------------

--
-- Table structure for table `quarters`
--

DROP TABLE IF EXISTS `quarters`;
CREATE TABLE IF NOT EXISTS `quarters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quarter_number` varchar(50) NOT NULL,
  `quarter_name` varchar(100) DEFAULT NULL,
  `location_name` varchar(100) NOT NULL,
  `quarter_address` varchar(255) DEFAULT NULL,
  `quarter_type` varchar(100) NOT NULL,
  `quarter_division` varchar(100) DEFAULT NULL,
  `quarter_ownership` varchar(100) DEFAULT NULL,
  `quarter_area` varchar(50) DEFAULT NULL,
  `quarter_assessment_rent` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `quarter_status` enum('Available','Occupied','Under Maintenance') NOT NULL DEFAULT 'Available',
  `has_electricity` tinyint(1) NOT NULL DEFAULT 0,
  `has_water` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_quarter_location_number` (`location_name`,`quarter_number`),
  KEY `idx_quarters_type` (`quarter_type`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quarters`
--

INSERT INTO `quarters` (`id`, `quarter_number`, `quarter_name`, `location_name`, `quarter_address`, `quarter_type`, `quarter_division`, `quarter_ownership`, `quarter_area`, `quarter_assessment_rent`, `is_active`, `quarter_status`, `has_electricity`, `has_water`, `sort_order`, `created_at`) VALUES
(1, 'A-10156', 'Bungalows - A-101', 'Colombo Fort', 'Colombo Fort, Sri Lanka', 'Bungalows', 'Way & Works test', 'Railway Department', '800 sq.ft', 3500.00, 1, 'Available', 1, 1, 1, '2026-10-12 06:38:41'),
(2, 'B-205', 'Flats - B-205', 'Maradana', 'Maradana, Sri Lanka', 'Flats', 'Way & Works', 'Railway Department', '800 sq.ft', 3500.00, 1, 'Available', 1, 1, 2, '2026-10-12 06:38:41'),
(3, 'K-014', 'Double - K-014', 'Kandy', 'Kandy, Sri Lanka', 'Double', 'Way & Works', 'Railway Department', '800 sq.ft', 3500.00, 1, 'Available', 1, 1, 3, '2026-10-12 06:38:41'),
(4, 'G-022', 'Single - G-022', 'Galle', 'Galle, Sri Lanka', 'Single', 'Way & Works', 'Railway Department', '800 sq.ft', 3500.00, 1, 'Available', 1, 1, 4, '2026-10-12 06:38:41'),
(5, 'AP-007', 'Row - AP-007', 'Anuradhapura', 'Anuradhapura, Sri Lanka', 'Row', 'Way & Works', 'Railway Department', '800 sq.ft', 3500.00, 1, 'Available', 1, 1, 5, '2026-10-12 06:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `quarter_locations`
--

DROP TABLE IF EXISTS `quarter_locations`;
CREATE TABLE IF NOT EXISTS `quarter_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_name` varchar(100) NOT NULL,
  `location_name_si` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_location_name` (`location_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quarter_locations`
--

INSERT INTO `quarter_locations` (`id`, `location_name`, `location_name_si`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Colombo Fort', 'කොළඹ කොටුව', 1, 1, '2026-10-12 06:38:41'),
(2, 'Maradana', 'මරදාන', 1, 2, '2026-10-12 06:38:41'),
(3, 'Kandy', 'මහනුවර', 1, 3, '2026-10-12 06:38:41'),
(4, 'Galle', 'ගාල්ල', 1, 4, '2026-10-12 06:38:41'),
(5, 'Anuradhapura', 'අනුරාධපුර', 1, 5, '2026-10-12 06:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `quarter_numbers`
--

DROP TABLE IF EXISTS `quarter_numbers`;
CREATE TABLE IF NOT EXISTS `quarter_numbers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quarter_number` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_quarter_number` (`quarter_number`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quarter_numbers`
--

INSERT INTO `quarter_numbers` (`id`, `quarter_number`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'A-101', 1, 1, '2026-10-12 01:08:41'),
(2, 'B-205', 1, 2, '2026-10-12 01:08:41'),
(3, 'K-014', 1, 3, '2026-10-12 01:08:41'),
(4, 'G-022', 1, 4, '2026-10-12 01:08:41'),
(5, 'AP-007', 1, 5, '2026-10-12 01:08:41');

-- --------------------------------------------------------

--
-- Table structure for table `quarter_types`
--

DROP TABLE IF EXISTS `quarter_types`;
CREATE TABLE IF NOT EXISTS `quarter_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(100) NOT NULL,
  `type_name_si` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quarter_types`
--

INSERT INTO `quarter_types` (`id`, `type_name`, `type_name_si`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Bungalows', 'බංගලා', 1, 1, '2026-09-02 07:47:37'),
(2, 'Single', 'තනි', 1, 2, '2026-09-02 07:47:37'),
(3, 'Double', 'ද්විත්ව', 1, 3, '2026-09-02 07:47:37'),
(4, 'Row', 'පේළි', 1, 4, '2026-09-02 07:47:37'),
(5, 'Flats', 'මහල්', 1, 5, '2026-09-02 07:47:37'),
(6, 'Field', 'පිටිය', 1, 6, '2026-09-02 07:47:37'),
(7, 'Couple', 'යුගල', 1, 7, '2026-09-02 07:47:37'),
(8, 'Line', 'රේඛා', 1, 8, '2026-09-02 07:47:37'),
(9, 'Flat', 'මහල', 1, 9, '2026-09-02 07:47:37'),
(10, 'Twin', 'නිවුන්', 1, 10, '2026-09-02 07:47:37'),
(11, 'Gateman', 'ගේට්ටු රකින්නා', 1, 11, '2026-09-02 07:47:37'),
(12, 'Sub Staff', 'උප කාර්ය මණ්ඩලය', 1, 12, '2026-09-02 07:47:37'),
(13, 'Rows', 'පේළි', 1, 13, '2026-09-02 07:47:37'),
(14, 'Gate', 'ගේට්ටුව', 1, 14, '2026-09-02 07:47:37'),
(15, 'Double House', 'ද්විත්ව නිවස', 1, 15, '2026-09-02 07:47:37'),
(16, 'Row House', 'පේළි නිවස', 1, 16, '2026-09-02 07:47:37'),
(17, 'Row(Wa.Di)', 'පේළි(ව.දි.)', 1, 17, '2026-09-02 07:47:37'),
(18, 'Shared', 'හවුල්', 1, 18, '2026-09-02 07:47:37'),
(19, 'Regular', 'සාමාන්‍ය', 1, 19, '2026-09-02 07:47:37'),
(20, 'Room', 'කාමරය', 1, 20, '2026-09-02 07:47:37'),
(21, 'Fields', 'පිටි', 1, 21, '2026-09-02 07:47:37');

-- --------------------------------------------------------

--
-- Table structure for table `rented_house_details`
--

DROP TABLE IF EXISTS `rented_house_details`;
CREATE TABLE IF NOT EXISTS `rented_house_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `rented_house_owner_name` varchar(150) DEFAULT NULL,
  `rented_house_location` varchar(100) DEFAULT NULL,
  `rented_house_address` text DEFAULT NULL,
  `rented_house_occupancy_date` date DEFAULT NULL,
  `rented_house_monthly_rent` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rented_app` (`application_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rented_house_details`
--

INSERT INTO `rented_house_details` (`id`, `application_id`, `rented_house_owner_name`, `rented_house_location`, `rented_house_address`, `rented_house_occupancy_date`, `rented_house_monthly_rent`) VALUES
(6, 10, 'amsndlanda', 'asndnansda', 'amdnalsndla', '2026-10-21', 46465.00),
(8, 11, 'amsndlanda', 'asndnansda', 'amdnalsndla', '2026-10-23', 57657.00),
(10, 12, 'amsndlanda', 'asndnansda', 'amdnalsndla', '2026-10-28', 6476568.00);

-- --------------------------------------------------------

--
-- Table structure for table `respond_to_offer`
--

DROP TABLE IF EXISTS `respond_to_offer`;
CREATE TABLE IF NOT EXISTS `respond_to_offer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nic` varchar(20) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `nic` (`nic`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_account_branches`
--

DROP TABLE IF EXISTS `salary_account_branches`;
CREATE TABLE IF NOT EXISTS `salary_account_branches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_name` varchar(100) NOT NULL,
  `branch_name_si` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_branch_name` (`branch_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salary_account_branches`
--

INSERT INTO `salary_account_branches` (`id`, `branch_name`, `branch_name_si`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Bank of Ceylon - Colombo Fort', 'ලංකා බැංකුව - කොළඹ කොටුව', 1, 1, '2026-10-12 06:38:41'),
(2, 'People\'s Bank - Maradana', 'මහජන බැංකුව - මරදාන', 1, 2, '2026-10-12 06:38:41'),
(3, 'Bank of Ceylon - Kandy City', 'ලංකා බැංකුව - මහනුවර නගර', 1, 3, '2026-10-12 06:38:41'),
(4, 'People\'s Bank - Galle', 'මහජන බැංකුව - ගාල්ල', 1, 4, '2026-10-12 06:38:41'),
(5, 'Bank of Ceylon - Anuradhapura', 'ලංකා බැංකුව - අනුරාධපුර', 1, 5, '2026-10-12 06:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `service_breaks`
--

DROP TABLE IF EXISTS `service_breaks`;
CREATE TABLE IF NOT EXISTS `service_breaks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `break_sub_department` varchar(100) NOT NULL,
  `break_from_date` date NOT NULL,
  `break_to_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_breaks_app` (`application_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `spouse_details`
--

DROP TABLE IF EXISTS `spouse_details`;
CREATE TABLE IF NOT EXISTS `spouse_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `spouse_full_name` varchar(150) NOT NULL,
  `spouse_national_id` varchar(20) NOT NULL,
  `spouse_occupation` varchar(100) DEFAULT NULL,
  `spouse_will_reside_in_quarters` enum('Will Come','Will Not Come') DEFAULT NULL,
  `spouse_department` varchar(100) DEFAULT NULL,
  `spouse_service_number` varchar(30) DEFAULT NULL,
  `spouse_designation` varchar(100) DEFAULT NULL,
  `spouse_institution_head_and_address` varchar(150) DEFAULT NULL,
  `spouse_work_station_and_address` varchar(150) DEFAULT NULL,
  `spouse_annual_salary` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_spouse_app` (`application_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spouse_details`
--

INSERT INTO `spouse_details` (`id`, `application_id`, `spouse_full_name`, `spouse_national_id`, `spouse_occupation`, `spouse_will_reside_in_quarters`, `spouse_department`, `spouse_service_number`, `spouse_designation`, `spouse_institution_head_and_address`, `spouse_work_station_and_address`, `spouse_annual_salary`) VALUES
(6, 10, 'dnlanldnalnsdlasd', '466666666666654', 'wfsfddddddddddddddd', NULL, 'XYZ', '123445678979879', 'ajndansdlnad', 'asndjandjna', 'ajndajndljandna', 500000.00),
(8, 11, 'dnlanldnalnsdlasd', '466666666666654', 'wfsfddddddddddddddd', NULL, 'Food', '12345678', 'ajndansdlnad', 'asndjandjna', 'ajndajndljandna', 986967.00),
(10, 12, 'dnlanldnalnsdlasd', '5798798', 'wfsfddddddddddddddd', NULL, 'ddasfasfc', '12345678', 'ajndansdlnad', 'asndjandjna', 'ajndajndljandna', 9869778.00);

-- --------------------------------------------------------

--
-- Table structure for table `sub_departments`
--

DROP TABLE IF EXISTS `sub_departments`;
CREATE TABLE IF NOT EXISTS `sub_departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dept_name` varchar(100) NOT NULL,
  `dept_name_si` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_departments`
--

INSERT INTO `sub_departments` (`id`, `dept_name`, `dept_name_si`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Operations', 'මෙහෙයුම්', 1, 1, '2026-09-02 07:47:37'),
(2, 'Transportation', 'ප්‍රවාහන', 1, 2, '2026-09-02 07:47:37'),
(3, 'Signal & Telecom', 'සංඥා සහ විදුලි සංදේශ', 1, 3, '2026-09-02 07:47:37'),
(4, 'Track Maintenance', 'මාර්ග නඩත්තු', 1, 4, '2026-09-02 07:47:37'),
(5, 'Rolling Stock', 'රෝලිං ස්ටොක්', 1, 5, '2026-09-02 07:47:37'),
(6, 'Mechanical', 'යාන්ත්‍රික', 1, 6, '2026-09-02 07:47:37'),
(7, 'Electrical', 'විදුලි', 1, 7, '2026-09-02 07:47:37'),
(8, 'Civil Engineering', 'සිවිල් ඉංජිනේරු', 1, 8, '2026-09-02 07:47:37'),
(9, 'Safety', 'ආරක්ෂාව', 1, 9, '2026-09-02 07:47:37'),
(10, 'Commercial', 'වාණිජ', 1, 10, '2026-09-02 07:47:37'),
(11, 'Accounts', 'ගිණුම්', 1, 11, '2026-09-02 07:47:37'),
(12, 'Administration', 'පරිපාලන', 1, 12, '2026-09-02 07:47:37'),
(13, 'Human Resources', 'මානව සම්පත්', 1, 13, '2026-09-02 07:47:37'),
(14, 'Information Technology', 'තොරතුරු තාක්ෂණය', 1, 14, '2026-09-02 07:47:37'),
(15, 'Stores', 'ගබඩා', 1, 15, '2026-09-02 07:47:37');

-- --------------------------------------------------------

--
-- Table structure for table `superintendent_recommendations`
--

DROP TABLE IF EXISTS `superintendent_recommendations`;
CREATE TABLE IF NOT EXISTS `superintendent_recommendations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `super_quarter_type_and_number` varchar(50) DEFAULT NULL,
  `super_essentiality_and_remarks` text DEFAULT NULL,
  `super_approval_date` date DEFAULT NULL,
  `super_recommendation_status` enum('pending','recommended','not_recommended') NOT NULL DEFAULT 'pending',
  `super_recommended_at` datetime DEFAULT NULL,
  `super_signed_by_nic` varchar(20) DEFAULT NULL,
  `super_signed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_super_app` (`application_id`),
  KEY `fk_super_user` (`super_signed_by_nic`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `nic` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `computer_number` varchar(50) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `applicant_initials` varchar(50) DEFAULT NULL,
  `applicant_current_work_station` varchar(100) DEFAULT NULL,
  `applicant_service_number` varchar(30) DEFAULT NULL,
  `applicant_salary_sheet_number` varchar(30) DEFAULT NULL,
  `applicant_date_of_birth` date DEFAULT NULL,
  `applicant_private_address` text DEFAULT NULL,
  `applicant_official_address` text DEFAULT NULL,
  `applicant_civil_status` enum('Married','Unmarried','Divorced','Widowed') DEFAULT NULL,
  `applicant_designation` varchar(100) DEFAULT NULL,
  `applicant_current_sub_department` varchar(100) DEFAULT NULL,
  `applicant_salary_account_branch` varchar(100) DEFAULT NULL,
  `applicant_personal_file_office` varchar(100) DEFAULT NULL,
  `is_applicant_service_confirmed` tinyint(1) DEFAULT 0,
  `applicant_service_confirmed_date` date DEFAULT NULL,
  `applicant_gov_first_appointment_date` date DEFAULT NULL,
  `applicant_railway_first_appointment_date` date DEFAULT NULL,
  `applicant_current_sub_dept_duty_date` date DEFAULT NULL,
  `applicant_current_post_appointment_date` date DEFAULT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'applicant',
  `role_id` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`nic`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`nic`, `name`, `email`, `password`, `computer_number`, `mobile`, `is_active`, `created_at`, `updated_at`, `applicant_initials`, `applicant_current_work_station`, `applicant_service_number`, `applicant_salary_sheet_number`, `applicant_date_of_birth`, `applicant_private_address`, `applicant_official_address`, `applicant_civil_status`, `applicant_designation`, `applicant_current_sub_department`, `applicant_salary_account_branch`, `applicant_personal_file_office`, `is_applicant_service_confirmed`, `applicant_service_confirmed_date`, `applicant_gov_first_appointment_date`, `applicant_railway_first_appointment_date`, `applicant_current_sub_dept_duty_date`, `applicant_current_post_appointment_date`, `role`, `role_id`) VALUES
('123456789', 'Asintha Karunarathne', 'gpjprimal1@gmail.com', '$2y$12$vgSbhpW7C/TINomtFOJWaOEc9u7lH.OYy6BF5i/sgoDL1hZn6ZZTG', '123456789', '0768123100', 1, '2026-10-19 03:50:10', '2026-10-19 03:50:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'applicant', 1),
('200212345', 'Chirana Attygalle', 'chirana@gmail.com', '$2y$12$N4neXfCBnzigURedYeLMn.h5ZKxBv.X2LlF4oshnPUS9GVcvtcrAO', '200212345', '0729812345', 1, '2026-10-19 04:35:16', '2026-10-19 04:35:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'applicant', 3),
('200312345', 'Dilan', 'dilan@gmail.com', '$2y$12$tg.cz9Tf5UsHlm/NPuJ5a.asR1sSlzi6E3C31oTHAY3JEl4DofNly', '200312345', '0781234567', 1, '2026-10-19 04:22:34', '2026-10-19 04:22:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'applicant', 1),
('200329012186', 'Gayan Pradeep Jayawardhana', 'gpjprimal@gmail.com', '$2y$12$2WkXXm6/VcxMYXInYfJmW.ccxoy0sI83HJ7LIE.Z1ssQeaPkPIlAa', '200329012186', '0768102100', 1, '2026-10-14 06:28:58', '2026-10-19 03:22:33', 'aklsndlandlandljandlnad', 'Kandy Station', '2176313123', '12345', '2026-10-19', 'annsjdnaljdna', 'adlknalkdnalnd', 'Married', 'Signal Inspector', 'Commercial', 'People\'s Bank - Maradana', 'Divisional Office - Maradana', 1, '2026-10-25', '2026-10-24', '2026-10-25', '2026-10-27', '2026-10-29', 'applicant', 2);

-- --------------------------------------------------------

--
-- Table structure for table `waiting_list`
--

DROP TABLE IF EXISTS `waiting_list`;
CREATE TABLE IF NOT EXISTS `waiting_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nic` varchar(20) NOT NULL,
  `position` int(11) DEFAULT NULL,
  `quarter_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `applied_date` date DEFAULT NULL,
  `designation_score` int(11) DEFAULT 0,
  `employee_marks` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `nic` (`nic`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `waiting_list`
--

INSERT INTO `waiting_list` (`id`, `nic`, `position`, `quarter_type`, `created_at`, `applied_date`, `designation_score`, `employee_marks`) VALUES
(8, '200329012186', 1, 'Twin', '2026-10-15 05:34:50', '2026-10-15', 0, 0),
(9, '200329012186', 1, 'Row', '2026-10-15 07:04:27', '2026-10-15', 0, 0),
(10, '200329012186', 1, 'Field', '2026-10-16 04:08:32', '2026-10-16', 0, 0),
(11, '200329012186', 2, 'Twin', '2026-10-16 05:22:18', '2026-10-16', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `work_stations`
--

DROP TABLE IF EXISTS `work_stations`;
CREATE TABLE IF NOT EXISTS `work_stations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `station_name` varchar(100) NOT NULL,
  `station_name_si` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_station_name` (`station_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_stations`
--

INSERT INTO `work_stations` (`id`, `station_name`, `station_name_si`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Colombo Fort Station', 'කොළඹ කොටුව දුම්රිය ස්ථානය', 1, 1, '2026-10-12 06:38:41'),
(2, 'Maradana Station', 'මරදාන දුම්රිය ස්ථානය', 1, 2, '2026-10-12 06:38:41'),
(3, 'Kandy Station', 'මහනුවර දුම්රිය ස්ථානය', 1, 3, '2026-10-12 06:38:41'),
(4, 'Galle Station', 'ගාල්ල දුම්රිය ස්ථානය', 1, 4, '2026-10-12 06:38:41'),
(5, 'Anuradhapura Station', 'අනුරාධපුර දුම්රිය ස්ථානය', 1, 5, '2026-10-12 06:38:41');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`nic`) REFERENCES `users` (`nic`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `children_details`
--
ALTER TABLE `children_details`
  ADD CONSTRAINT `fk_children_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `clerk_recommendations`
--
ALTER TABLE `clerk_recommendations`
  ADD CONSTRAINT `fk_clerk_approval_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_clerk_user` FOREIGN KEY (`clerk_signed_by_nic`) REFERENCES `users` (`nic`) ON DELETE SET NULL;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `fk_complaints_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`nic`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `declaration_details`
--
ALTER TABLE `declaration_details`
  ADD CONSTRAINT `fk_declaration_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `disability_medical_details`
--
ALTER TABLE `disability_medical_details`
  ADD CONSTRAINT `fk_disability_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `evaluation_marks`
--
ALTER TABLE `evaluation_marks`
  ADD CONSTRAINT `fk_marks_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_marks_user` FOREIGN KEY (`marks_evaluated_by_nic`) REFERENCES `users` (`nic`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`nic`) REFERENCES `users` (`nic`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `other_sub_departments_served`
--
ALTER TABLE `other_sub_departments_served`
  ADD CONSTRAINT `fk_othersub_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permanent_residence_details`
--
ALTER TABLE `permanent_residence_details`
  ADD CONSTRAINT `fk_residence_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `previous_quarter_details`
--
ALTER TABLE `previous_quarter_details`
  ADD CONSTRAINT `fk_prevquarters_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rented_house_details`
--
ALTER TABLE `rented_house_details`
  ADD CONSTRAINT `fk_rented_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `respond_to_offer`
--
ALTER TABLE `respond_to_offer`
  ADD CONSTRAINT `respond_to_offer_ibfk_1` FOREIGN KEY (`nic`) REFERENCES `users` (`nic`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `service_breaks`
--
ALTER TABLE `service_breaks`
  ADD CONSTRAINT `fk_breaks_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `spouse_details`
--
ALTER TABLE `spouse_details`
  ADD CONSTRAINT `fk_spouse_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `superintendent_recommendations`
--
ALTER TABLE `superintendent_recommendations`
  ADD CONSTRAINT `fk_super_approval_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_super_user` FOREIGN KEY (`super_signed_by_nic`) REFERENCES `users` (`nic`) ON DELETE SET NULL;

--
-- Constraints for table `waiting_list`
--
ALTER TABLE `waiting_list`
  ADD CONSTRAINT `waiting_list_ibfk_1` FOREIGN KEY (`nic`) REFERENCES `users` (`nic`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
