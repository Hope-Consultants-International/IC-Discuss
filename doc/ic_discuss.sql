-- phpMyAdmin SQL Dump
-- version 4.2.12deb2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 01, 2015 at 11:36 AM
-- Server version: 5.5.43-0+deb8u1
-- PHP Version: 5.6.7-1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ic_discuss`
--

-- --------------------------------------------------------

--
-- Table structure for table `db_version`
--

CREATE TABLE IF NOT EXISTS `db_version` (
  `version` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
`GroupId` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `issues`
--

CREATE TABLE IF NOT EXISTS `issues` (
`IssueId` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `statements`
--

CREATE TABLE IF NOT EXISTS `statements` (
`StatementId` int(11) NOT NULL,
  `GroupId` int(11) NOT NULL,
  `IssueId` int(11) NOT NULL,
  `SummaryId` int(11) DEFAULT NULL,
  `Statement` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `summaries`
--

CREATE TABLE IF NOT EXISTS `summaries` (
`SummaryId` int(11) NOT NULL,
  `IssueId` int(11) NOT NULL,
  `Summary` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
 ADD PRIMARY KEY (`GroupId`), ADD UNIQUE KEY `Name` (`Name`);

--
-- Indexes for table `issues`
--
ALTER TABLE `issues`
 ADD PRIMARY KEY (`IssueId`), ADD UNIQUE KEY `Title` (`Title`);

--
-- Indexes for table `statements`
--
ALTER TABLE `statements`
 ADD PRIMARY KEY (`StatementId`), ADD KEY `GroupId` (`GroupId`), ADD KEY `IssueId` (`IssueId`), ADD KEY `SummaryId` (`SummaryId`);

--
-- Indexes for table `summaries`
--
ALTER TABLE `summaries`
 ADD PRIMARY KEY (`SummaryId`), ADD KEY `IssueId` (`IssueId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
MODIFY `GroupId` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `issues`
--
ALTER TABLE `issues`
MODIFY `IssueId` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `statements`
--
ALTER TABLE `statements`
MODIFY `StatementId` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `summaries`
--
ALTER TABLE `summaries`
MODIFY `SummaryId` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `statements`
--
ALTER TABLE `statements`
ADD CONSTRAINT `statements_ibfk_3` FOREIGN KEY (`SummaryId`) REFERENCES `summaries` (`SummaryId`) ON DELETE SET NULL,
ADD CONSTRAINT `statements_ibfk_1` FOREIGN KEY (`GroupId`) REFERENCES `groups` (`GroupId`) ON DELETE CASCADE,
ADD CONSTRAINT `statements_ibfk_2` FOREIGN KEY (`IssueId`) REFERENCES `issues` (`IssueId`) ON DELETE CASCADE;

--
-- Constraints for table `summaries`
--
ALTER TABLE `summaries`
ADD CONSTRAINT `summaries_ibfk_1` FOREIGN KEY (`IssueId`) REFERENCES `issues` (`IssueId`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
