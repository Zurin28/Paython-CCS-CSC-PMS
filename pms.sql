-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2024 at 05:11 AM
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
-- Database: `pms`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_periods`
--

CREATE TABLE `academic_periods` (
  `school_year` varchar(9) NOT NULL,
  `semester` enum('1st','2nd','Summer') NOT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `ID` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `MI` varchar(11) NOT NULL,
  `WmsuEmail` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` enum('student','staff','admin') NOT NULL,
  `school_year` varchar(9) NOT NULL,
  `semester` enum('1st','2nd','Summer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `AdminID` int(11) NOT NULL,
  `OrganizationID` varchar(11) NOT NULL,
  `Position` varchar(255) NOT NULL,
  `school_year` varchar(9) NOT NULL,
  `semester` enum('1st','2nd','Summer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `logID` int(11) NOT NULL,
  `EmployeeID` int(11) NOT NULL,
  `ActionType` varchar(255) NOT NULL,
  `EntityType` varchar(255) NOT NULL,
  `OrgName` varchar(255) NOT NULL,
  `Timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `FeeID` int(11) NOT NULL,
  `OrganizationID` varchar(11) NOT NULL,
  `FeeName` varchar(255) NOT NULL,
  `Amount` int(6) NOT NULL,
  `DueDate` date NOT NULL,
  `Description` text NOT NULL,
  `school_year` varchar(9) NOT NULL,
  `semester` enum('1st','2nd','Summer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_creation_requests`
--

CREATE TABLE `fee_creation_requests` (
  `request_id` int(11) NOT NULL,
  `OrganizationID` varchar(11) NOT NULL,
  `fee_id` int(11) NOT NULL,
  `fee_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `description` text NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `submit_date` datetime NOT NULL,
  `status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `school_year` varchar(9) NOT NULL,
  `semester` enum('1st','2nd','Summer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organizations`
--

CREATE TABLE `organizations` (
  `OrganizationID` varchar(11) NOT NULL,
  `school_year` varchar(9) NOT NULL,
  `semester` enum('1st','2nd','Summer') NOT NULL,
  `OrgName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_requests`
--

CREATE TABLE `payment_requests` (
  `paymentID` int(11) NOT NULL,
  `StudentID` int(11) NOT NULL,
  `staffID` int(11) NOT NULL,
  `fee_id` int(11) NOT NULL,
  `DatePaid` date NOT NULL,
  `Status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `school_year` varchar(9) NOT NULL,
  `semester` enum('1st','2nd','Summer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `StudentID` int(11) NOT NULL,
  `staffID` int(11) NOT NULL,
  `OrganizationID` varchar(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `MI` varchar(11) NOT NULL,
  `WmsuEmail` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Course` enum('Computer Science','Information Technology','Associate in Computer Technology','Application Development') NOT NULL,
  `Year` enum('1st','2nd','3rd','4th','Over 4 years') NOT NULL,
  `Section` varchar(255) NOT NULL,
  `school_year` varchar(9) NOT NULL,
  `semester` enum('1st','2nd','Summer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `StudentID` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `MI` varchar(11) NOT NULL,
  `WmsuEmail` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Course` enum('Computer Science','Information Technology','Associate in Computer Technology','Application Development') NOT NULL,
  `Year` enum('1st','2nd','3rd','4th','Over 4 years') NOT NULL,
  `Section` varchar(255) NOT NULL,
  `school_year` varchar(9) NOT NULL,
  `semester` enum('1st','2nd','Summer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_periods`
--
ALTER TABLE `academic_periods`
  ADD PRIMARY KEY (`school_year`,`semester`);

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`),
  ADD KEY `OrganizationID` (`OrganizationID`),
  ADD KEY `school_year` (`school_year`,`semester`);

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`logID`),
  ADD KEY `EmployeeID` (`EmployeeID`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`FeeID`),
  ADD KEY `OrganizationID` (`OrganizationID`),
  ADD KEY `school_year` (`school_year`,`semester`);

--
-- Indexes for table `fee_creation_requests`
--
ALTER TABLE `fee_creation_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `OrganizationID` (`OrganizationID`),
  ADD KEY `fee_id` (`fee_id`),
  ADD KEY `school_year` (`school_year`,`semester`);

--
-- Indexes for table `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`OrganizationID`),
  ADD KEY `school_year` (`school_year`,`semester`);

--
-- Indexes for table `payment_requests`
--
ALTER TABLE `payment_requests`
  ADD PRIMARY KEY (`paymentID`),
  ADD KEY `StudentID` (`StudentID`),
  ADD KEY `staffID` (`staffID`),
  ADD KEY `fee_id` (`fee_id`),
  ADD KEY `school_year` (`school_year`,`semester`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staffID`),
  ADD KEY `OrganizationID` (`OrganizationID`),
  ADD KEY `school_year` (`school_year`,`semester`),
  ADD KEY `StudentID` (`StudentID`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`StudentID`),
  ADD KEY `school_year` (`school_year`,`semester`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `logID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `FeeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_creation_requests`
--
ALTER TABLE `fee_creation_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_requests`
--
ALTER TABLE `payment_requests`
  MODIFY `paymentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staffID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--

ALTER TABLE `account`
  ADD CONSTRAINT `account_ibfk_2` FOREIGN KEY (`school_year`,`semester`) REFERENCES `academic_periods` (`school_year`, `semester`);

ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`OrganizationID`) REFERENCES `organizations` (`OrganizationID`),
  ADD CONSTRAINT `admin_ibfk_2` FOREIGN KEY (`school_year`,`semester`) REFERENCES `academic_periods` (`school_year`, `semester`);

--
-- Constraints for table `fees`
--
ALTER TABLE `fees`
  ADD CONSTRAINT `fees_ibfk_1` FOREIGN KEY (`OrganizationID`) REFERENCES `organizations` (`OrganizationID`),
  ADD CONSTRAINT `fees_ibfk_2` FOREIGN KEY (`school_year`,`semester`) REFERENCES `academic_periods` (`school_year`, `semester`);

--
-- Constraints for table `fee_creation_requests`
--
ALTER TABLE `fee_creation_requests`
  ADD CONSTRAINT `fee_creation_requests_ibfk_1` FOREIGN KEY (`OrganizationID`) REFERENCES `organizations` (`OrganizationID`),
  ADD CONSTRAINT `fee_creation_requests_ibfk_2` FOREIGN KEY (`fee_id`) REFERENCES `fees` (`FeeID`),
  ADD CONSTRAINT `fee_creation_requests_ibfk_3` FOREIGN KEY (`school_year`,`semester`) REFERENCES `academic_periods` (`school_year`, `semester`);

--
-- Constraints for table `organizations`
--
ALTER TABLE `organizations`
  ADD CONSTRAINT `organizations_ibfk_1` FOREIGN KEY (`school_year`,`semester`) REFERENCES `academic_periods` (`school_year`, `semester`);

--
-- Constraints for table `payment_requests`
--
ALTER TABLE `payment_requests`
  ADD CONSTRAINT `payment_requests_ibfk_2` FOREIGN KEY (`staffID`) REFERENCES `staff` (`staffID`),
  ADD CONSTRAINT `payment_requests_ibfk_3` FOREIGN KEY (`fee_id`) REFERENCES `fees` (`FeeID`),
  ADD CONSTRAINT `payment_requests_ibfk_4` FOREIGN KEY (`school_year`,`semester`) REFERENCES `academic_periods` (`school_year`, `semester`);

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`OrganizationID`) REFERENCES `organizations` (`OrganizationID`),
  ADD CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`school_year`,`semester`) REFERENCES `academic_periods` (`school_year`, `semester`),
  ADD CONSTRAINT `staff_ibfk_3` FOREIGN KEY (`StudentID`) REFERENCES `student` (`StudentID`);

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_2` FOREIGN KEY (`school_year`,`semester`) REFERENCES `academic_periods` (`school_year`, `semester`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
