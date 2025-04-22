-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 22, 2025 at 02:34 AM
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
-- Database: `teacher_dashboard`
--

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `invite_hash` varchar(100) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `name`, `description`, `invite_hash`, `teacher_id`, `created_at`, `updated_at`) VALUES
(1, 'CSC 5272', 'cybersecutiry', 'NMC7LG', 1, NULL, NULL),
(2, 'CSC 1100', 'freshman', 'J2GZYV', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `class_groups`
--

CREATE TABLE `class_groups` (
  `id` int(11) NOT NULL,
  `group_set_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `leader_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_members`
--

CREATE TABLE `class_members` (
  `user_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `role_in_class` varchar(50) DEFAULT NULL,
  `joined_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_members`
--

INSERT INTO `class_members` (`user_id`, `class_id`, `role_in_class`, `joined_at`, `updated_at`) VALUES
(2, 1, NULL, NULL, NULL),
(3, 1, NULL, NULL, NULL),
(4, 1, NULL, NULL, NULL),
(4, 2, 'student', '2025-04-18 14:00:18', '2025-04-18 14:00:18');

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

CREATE TABLE `group_members` (
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `joined_at` datetime DEFAULT NULL,
  `is_pending` tinyint(1) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_members`
--

INSERT INTO `group_members` (`user_id`, `group_id`, `joined_at`, `is_pending`, `updated_at`) VALUES
(2, 2, NULL, 0, NULL),
(3, 2, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `group_sets`
--

CREATE TABLE `group_sets` (
  `id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `allow_self_signup` tinyint(1) DEFAULT NULL,
  `require_approval` tinyint(1) DEFAULT NULL,
  `require_teacher_approval` tinyint(1) DEFAULT NULL,
  `require_leader_approval` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_sets`
--

INSERT INTO `group_sets` (`id`, `class_id`, `name`, `allow_self_signup`, `require_approval`, `require_teacher_approval`, `require_leader_approval`, `created_at`, `updated_at`) VALUES
(1, 2, 'group 1', 1, 0, 0, 0, NULL, NULL),
(2, 1, 'Group 2', 1, 0, 0, 0, NULL, NULL),
(3, 1, 'Group 3', 1, 0, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rewards`
--

CREATE TABLE `rewards` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cost` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rewards`
--

INSERT INTO `rewards` (`id`, `name`, `description`, `cost`, `created_at`, `updated_at`) VALUES
(1, '5 Points Extra Credit', 'Applied to any assignment of choice and can be stacked to ultimately “round up” final grades.', 50, '2025-03-13 00:00:00', '2025-03-13 00:00:00'),
(2, 'Final Exam Cheat Sheet', 'Must be pre-approved and presented at the time of the exam.', 500, '2025-03-13 00:00:00', '2025-03-13 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `reward_assignments`
--

CREATE TABLE `reward_assignments` (
  `id` int(11) NOT NULL,
  `reward_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `hashed_password` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `balance` int(11) DEFAULT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `hashed_password`, `role`, `status`, `created_at`, `updated_at`, `balance`, `school_name`, `security_question`, `security_answer`) VALUES
(1, 'Hadi', 'Nasser', 'hadinasser@wayne.edu', '$2y$10$R7lAKlXigetj0El472nXVOJ4Cm9gGz7r/LkBra0raH3ra9lcjX8J.', 'Teacher', NULL, NULL, NULL, NULL, 'school3', 'birth-city', '$2y$10$0TtEaBD5Pr3UGquPTL18YOIZbGrUPQvD5NVO/kOFlgNx6QBmKBmny'),
(2, 'Yasmin', 'Shah', 'hh4891@wayne.edu', '$2y$10$AkJz/v/11sbYggDFJkws.uXIPnglYr3Fak8fUgpr5aP5letF7j.uW', 'Student', NULL, NULL, NULL, NULL, 'school3', 'birth-city', '$2y$10$lDJmDFfyaXn8ei4ubOdQu.UqMYxyca.YRWQIuZVRmAjekYV7D6uYm'),
(3, 'Ian', 'Width', 'ian@gmail.com', '$2y$10$u4gNWG45CzuqtSvmHX/o4.zvUoP6TxmXf6RKhmbBTWm2D88TgftAa', 'Student', NULL, NULL, NULL, NULL, 'school3', 'birth-city', '$2y$10$yIXp53BzglJX0KFZ.0/zleV6YReoC8KziO2hkSTuana58QuOOWspK'),
(4, 'Cait', 'Cherian', 'catycate123@gmail.com', '$2y$10$QOVNh.8qNsVniP/fqSQLiuhmKM5DtoS60abBERJtDQJ3PL9TXttsu', 'Student', NULL, NULL, NULL, NULL, 'school3', 'birth-city', '$2y$10$eSUVBVEJR5VAbtWIgaNa2OMFOMI00YIQNo9nftixEQGNiNhYP/T02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `class_groups`
--
ALTER TABLE `class_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_set_id` (`group_set_id`),
  ADD KEY `leader_id` (`leader_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `class_members`
--
ALTER TABLE `class_members`
  ADD PRIMARY KEY (`user_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`user_id`,`group_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `group_sets`
--
ALTER TABLE `group_sets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reward_assignments`
--
ALTER TABLE `reward_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reward_id` (`reward_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `class_groups`
--
ALTER TABLE `class_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `group_sets`
--
ALTER TABLE `group_sets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reward_assignments`
--
ALTER TABLE `reward_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `class_groups`
--
ALTER TABLE `class_groups`
  ADD CONSTRAINT `class_groups_ibfk_1` FOREIGN KEY (`group_set_id`) REFERENCES `group_sets` (`id`),
  ADD CONSTRAINT `class_groups_ibfk_2` FOREIGN KEY (`leader_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `class_groups_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

--
-- Constraints for table `class_members`
--
ALTER TABLE `class_members`
  ADD CONSTRAINT `class_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_members_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `group_members`
--
ALTER TABLE `group_members`
  ADD CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_members_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `group_sets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `group_sets`
--
ALTER TABLE `group_sets`
  ADD CONSTRAINT `group_sets_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reward_assignments`
--
ALTER TABLE `reward_assignments`
  ADD CONSTRAINT `reward_assignments_ibfk_1` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`id`),
  ADD CONSTRAINT `reward_assignments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reward_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
