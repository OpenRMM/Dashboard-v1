-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 11, 2021 at 12:47 PM
-- Server version: 10.3.29-MariaDB
-- PHP Version: 7.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rmm`
--

-- --------------------------------------------------------

--
-- Table structure for table `commands`
--

CREATE TABLE `commands` (
  `ID` int(11) NOT NULL,
  `ComputerID` varchar(100) NOT NULL,
  `userid` int(100) NOT NULL,
  `command` varchar(500) NOT NULL,
  `arg` varchar(500) NOT NULL,
  `time_sent` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `expire_after` int(5) NOT NULL DEFAULT 5,
  `expire_time` varchar(30) DEFAULT NULL,
  `data_received` text DEFAULT NULL,
  `time_received` varchar(25) DEFAULT NULL,
  `status` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `CompanyID` int(11) NOT NULL,
  `name` varchar(75) CHARACTER SET utf8mb4 NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 NOT NULL,
  `address` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `comments` longtext CHARACTER SET utf8mb4 NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `date_added` varchar(15) CHARACTER SET utf8mb4 NOT NULL,
  `active` int(2) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `computerdata`
--

CREATE TABLE `computerdata` (
  `ID` int(11) NOT NULL,
  `hostname` varchar(50) NOT NULL DEFAULT '',
  `CompanyID` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL DEFAULT '',
  `phone` varchar(12) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `comment` varchar(500) NOT NULL DEFAULT '',
  `active` int(1) NOT NULL DEFAULT 1,
  `computer_type` varchar(25) NOT NULL DEFAULT '',
  `date_added` int(20) DEFAULT NULL,
  `teamviewer` varchar(20) DEFAULT '',
  `show_alerts` int(1) DEFAULT 0,
  `last_update` varchar(20) NOT NULL DEFAULT current_timestamp(),
  `last_command_received` varchar(15) NOT NULL DEFAULT '',
  `online` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `general`
--

CREATE TABLE `general` (
  `ID` int(11) NOT NULL,
  `agent_latest_version` varchar(10) NOT NULL,
  `last_cron` varchar(25) NOT NULL DEFAULT '',
  `sitewideAlert` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `screenshots`
--

CREATE TABLE `screenshots` (
  `ID` int(11) NOT NULL,
  `ComputerID` int(11) NOT NULL,
  `image` mediumblob NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `username` varchar(25) NOT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `phone` varchar(400) NOT NULL DEFAULT '',
  `nicename` varchar(25) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL,
  `accountType` varchar(10) NOT NULL DEFAULT '',
  `notes` varchar(255) NOT NULL DEFAULT '',
  `last_login` varchar(15) NOT NULL DEFAULT '',
  `recents` longtext NOT NULL DEFAULT '',
  `recentedit` longtext NOT NULL DEFAULT '',
  `active` int(2) NOT NULL DEFAULT 1,
  `hex` varchar(200) NOT NULL,
  `alert_settings` varchar(255) NOT NULL DEFAULT '',
  `userActivity` text NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `wmidata`
--

CREATE TABLE `wmidata` (
  `ID` bigint(20) NOT NULL,
  `ComputerID` int(11) DEFAULT NULL,
  `WMI_Name` varchar(50) DEFAULT NULL,
  `WMI_Data` longtext DEFAULT NULL,
  `last_update` varchar(25) NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `commands`
--
ALTER TABLE `commands`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`CompanyID`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `computerdata`
--
ALTER TABLE `computerdata`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `hostname` (`hostname`);

--
-- Indexes for table `general`
--
ALTER TABLE `general`
  ADD UNIQUE KEY `ID` (`ID`);

--
-- Indexes for table `screenshots`
--
ALTER TABLE `screenshots`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `ComputerID` (`ComputerID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `wmidata`
--
ALTER TABLE `wmidata`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Hostname` (`ComputerID`),
  ADD KEY `WMI_Name` (`WMI_Name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `commands`
--
ALTER TABLE `commands`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `CompanyID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `computerdata`
--
ALTER TABLE `computerdata`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `screenshots`
--
ALTER TABLE `screenshots`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wmidata`
--
ALTER TABLE `wmidata`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
