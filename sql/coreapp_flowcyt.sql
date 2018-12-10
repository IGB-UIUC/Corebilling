-- phpMyAdmin SQL Dump
-- version 4.1.14.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 28, 2015 at 10:28 AM
-- Server version: 5.1.73
-- PHP Version: 5.3.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `coreapp_flowcyt`
--
CREATE DATABASE IF NOT EXISTS `coreapp_flowcyt` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `coreapp_flowcyt`;

-- --------------------------------------------------------

--
-- Table structure for table `access_control`
--

DROP TABLE IF EXISTS `access_control`;
CREATE TABLE IF NOT EXISTS `access_control` (
  `participant_id` int(10) unsigned DEFAULT NULL,
  `resource_type_id` int(11) DEFAULT NULL,
  `resource_id` int(11) NOT NULL,
  `permission` int(11) NOT NULL,
  `participant_type_id` int(11) DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=198 ;

--
-- Dumping data for table `access_control`
--

INSERT INTO `access_control` (`participant_id`, `resource_type_id`, `resource_id`, `permission`, `participant_type_id`, `id`) VALUES
(1, 2, 6, 2, 0, 43),
(1, 2, 1, 2, 0, 52),
(1, 2, 2, 2, 0, 53),
(1, 2, 3, 2, 0, 54),
(1, 2, 4, 2, 0, 70),
(1, 2, 5, 2, 0, 71),
(1, 2, 7, 2, 0, 72),
(1, 2, 8, 2, 0, 73),
(1, 2, 9, 2, 0, 74),
(1, 2, 10, 2, 0, 75),
(1, 2, 11, 2, 0, 76),
(1, 2, 12, 2, 0, 77),
(3, 2, 1, 1, 0, 110),
(3, 2, 2, 1, 0, 111),
(3, 2, 10, 1, 0, 112),
(3, 2, 12, 1, 0, 113),
(2, 2, 2, 2, 0, 171),
(2, 2, 3, 2, 0, 172),
(2, 2, 12, 2, 0, 173),
(2, 2, 1, 1, 0, 175),
(2, 2, 4, 1, 0, 176),
(2, 2, 5, 1, 0, 177),
(2, 2, 6, 1, 0, 178),
(2, 2, 7, 1, 0, 179),
(2, 2, 8, 1, 0, 180),
(2, 2, 9, 1, 0, 181),
(2, 2, 10, 1, 0, 182),
(2, 2, 11, 1, 0, 183),
(3, 2, 3, 1, 0, 185);

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` date DEFAULT NULL,
  `text` text NOT NULL,
  `title` text NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=58 ;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `created`, `text`, `title`, `user_id`) VALUES
(57, '2014-10-31', 'Please use the navigation on the left to view your bill, reserve an instrument on the calendar and edit your billing information.\r\n\r\nFor any questions please contact help@igb.illinois.edu or call us at 333-4854\r\n\r\nThank you', 'Welcome to Core Instrument billing', 787);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `department_name` longtext,
  `description` varchar(45) NOT NULL,
  `department_code` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=234 ;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `department_name`, `description`, `department_code`) VALUES
(136, 'Materials Science & Engineerng', 'Materials Science & Engineerng', '1-KP-919'),
(167, 'Food Science & Human Nutrition', 'Food Science & Human Nutrition', '1-KL-698'),
(168, 'Fellowships', 'Fellowships', '1-KS-683'),
(169, 'Crop Sciences', 'Crop Sciences', '1-KL-802'),
(170, 'Institute for Genomic Biology', 'Institute for Genomic Biology', '1-NE-231'),
(171, 'Entomology', 'Entomology', '1-KV-361'),
(172, 'Veterinary Teaching Hospital', 'Veterinary Teaching Hospital', '1-LC-255'),
(173, 'Animal Sciences', 'Animal Sciences', '1-KL-538'),
(174, 'Plant Biology', 'Plant Biology', '1-KV-377'),
(175, 'Chemical & Biomolecular Engr', 'Chemical & Biomolecular Engr', '1-KV-687'),
(176, 'Molecular & Integrative Physl', 'Molecular & Integrative Physl', '1-KV-604'),
(177, 'Chemistry', 'Chemistry', '1-KV-413'),
(178, 'Bioengineering', 'Bioengineering', '1-KP-343'),
(179, 'Electrical & Computer Eng', 'Electrical & Computer Eng', '1-KP-933'),
(180, 'Biochemistry', 'Biochemistry', '1-KV-438'),
(181, 'Comparative Biosciences', 'Comparative Biosciences', '1-LC-873'),
(182, 'Physics', 'Physics', '1-KP-244'),
(183, 'Geology', 'Geology', '1-KV-655'),
(184, 'Microbiology', 'Microbiology', '1-KV-948'),
(185, 'Beckman Institute', 'Beckman Institute', '1-LH-392'),
(186, 'Veterinary Diagnostic Lab', 'Veterinary Diagnostic Lab', '1-LC-726'),
(187, 'School of Molecular & Cell Bio', 'School of Molecular & Cell Bio', '1-KV-415'),
(188, 'Medicine at UC Administration', 'Medicine at UC Administration', '1-LB-761'),
(189, 'Cell & Developmental Biology', 'Cell & Developmental Biology', '1-KV-584'),
(190, 'Civil & Environmental Eng', 'Civil & Environmental Eng', '1-KP-251'),
(191, 'Mechanical Science & Engineering', 'Mechanical Science & Engineering', '1-KP-917'),
(192, 'Pathobiology', 'Pathobiology', '1-LC-282'),
(193, 'Control - Payroll', 'Control - Payroll', '1-ZZ-109'),
(194, 'Pathology', 'Pathology', '1-LB-552'),
(195, 'Biotechnology Center', 'Biotechnology Center', '1-NE-531'),
(196, 'Micro and Nanotechnology Lab', 'Micro and Nanotechnology Lab', '1-KP-487'),
(197, 'Housing Division', 'Housing Division', '1-NQ-270'),
(198, 'School of Chemical Sciences', 'School of Chemical Sciences', '1-KV-510'),
(199, 'Animal Biology', 'Animal Biology', '1-KV-292'),
(200, 'Intercollegiate Athletics', 'Intercollegiate Athletics', '1-NU-336'),
(201, 'Nutritional Sciences', 'Nutritional Sciences', '1-KL-971'),
(202, 'Natural Res & Env Sci', 'Natural Res & Env Sci', '1-KL-875'),
(203, 'Library', 'Library', '1-LR-668'),
(204, 'Student Financial Aid', 'Student Financial Aid', '1-NB-678'),
(205, 'Undergraduate Admissions', 'Undergraduate Admissions', '1-NB-593'),
(206, 'Anthropology', 'Anthropology', '1-KV-241'),
(207, 'Vet Clinical Medicine', 'Vet Clinical Medicine', '1-LC-598'),
(208, 'Business Administration', 'Business Administration', '1-KM-902'),
(209, 'Engineering Administration', 'Engineering Administration', '1-KP-227'),
(210, 'School of Integrative Biology', 'School of Integrative Biology', '1-KV-383'),
(211, 'Agricultural & Biological Engr', '', '1-KL-741'),
(218, 'Chemical & Biomolecular Engr', '', ''),
(219, 'Computer Science', 'Computer Science', '1-KP-434'),
(220, 'Supercomputing Applications', 'Supercomputing Applications', '1-NE-320'),
(221, 'Psychology', 'Psychology', '1-KV-299'),
(222, 'Internal Medicine', 'Internal Medicine', '1-LB-684'),
(223, 'Vice President for Research', '', '9-AJ-757'),
(224, 'Division of Research Safety', 'Division of Research Safety', '1-NE-877'),
(225, 'Div State Geological Survey', 'Div State Geological Survey', '1-NE-547'),
(226, 'LAS Administration', 'LAS Administration', '1-KV-580'),
(227, 'Engineering IT Shared Services', 'Engineering IT Shared Services', '1-KP-661'),
(228, 'Library Admin', 'Library Admin', '1-LR-540'),
(229, 'State Natural History Survey', 'State Natural History Survey', '1-NE-375'),
(230, 'Economics', 'Economics', ''),
(231, 'test1234', 'test123', ''),
(232, 'Private company', '', ''),
(233, 'Biotech Center', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `device`
--

DROP TABLE IF EXISTS `device`;
CREATE TABLE IF NOT EXISTS `device` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_name` varchar(255) DEFAULT NULL,
  `location` text NOT NULL,
  `description` text NOT NULL,
  `full_device_name` longtext,
  `status_id` int(11) DEFAULT NULL,
  `loggeduser` int(10) NOT NULL,
  `lasttick` datetime NOT NULL,
  `unauthorized` varchar(45) NOT NULL,
  `device_token` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=62 ;

-- --------------------------------------------------------

--
-- Table structure for table `device_rate`
--

DROP TABLE IF EXISTS `device_rate`;
CREATE TABLE IF NOT EXISTS `device_rate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rate` float NOT NULL,
  `device_id` int(10) unsigned DEFAULT NULL,
  `rate_id` int(11) DEFAULT NULL,
  `min_use_time` int(11) NOT NULL,
  `rate_type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=279 ;

--
-- Dumping data for table `device_rate`
--

INSERT INTO `device_rate` (`id`, `rate`, `device_id`, `rate_id`, `min_use_time`, `rate_type_id`) VALUES
(261, 0.0833333, 56, 9, 30, 1),
(262, 0.816667, 57, 9, 30, 1),
(265, 0, 57, 11, 0, 1),
(266, 0, 56, 11, 0, 1),
(267, 0.816667, 58, 9, 30, 1),
(269, 0, 58, 11, 0, 1),
(270, 0.816667, 59, 9, 30, 1),
(272, 0, 59, 11, 0, 1),
(273, 0.666667, 60, 9, 30, 1),
(275, 0, 60, 11, 0, 1),
(276, 0.666667, 61, 9, 30, 1),
(278, 0, 61, 11, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `department_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=250 ;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `group_name`, `description`, `department_id`) VALUES
(1, 'Admins', 'Core Facilities Administrators', 0);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_name` varchar(45) NOT NULL,
  `show_navigation` int(11) DEFAULT NULL,
  `file_name` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `pages` WRITE;

INSERT INTO `pages` (`id`, `page_name`, `show_navigation`, `file_name`)
VALUES
	(1,'Latest News',1,'news.php'),
	(2,'User Billing',1,'user_billing.php'),
	(3,'Edit Users',1,'edit_users.php'),
	(4,'Edit Groups',1,'edit_groups.php'),
	(5,'Edit Departments',1,'edit_departments.php'),
	(6,'Edit Devices',1,'edit_device.php'),
	(7,'Facility Billing',1,'facility_billing.php'),
	(8,'Edit Permissions',1,'edit_permissions.php'),
	(10,'Devices In Use',1,'in_use.php'),
	(11,'Statistics',1,'dev_statistics.php'),
	(12,'Calendar',1,'calendar_fullcalendar.php');

UNLOCK TABLES;

-- --------------------------------------------------------

--
-- Table structure for table `rates`
--

DROP TABLE IF EXISTS `rates`;
CREATE TABLE IF NOT EXISTS `rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rate_name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `rates`
--

INSERT INTO `rates` (`id`, `rate_name`) VALUES
(9, 'UofI'),
(11, 'Flow Admin');

-- --------------------------------------------------------

--
-- Table structure for table `rate_types`
--

DROP TABLE IF EXISTS `rate_types`;
CREATE TABLE IF NOT EXISTS `rate_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rate_type_name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `rate_types`
--

INSERT INTO `rate_types` (`id`, `rate_type_name`) VALUES
(1, 'Continuous'),
(2, 'Monthly');

-- --------------------------------------------------------

--
-- Table structure for table `reservation_info`
--

DROP TABLE IF EXISTS `reservation_info`;
CREATE TABLE IF NOT EXISTS `reservation_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `stop` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` text NOT NULL,
  `training` int(10) unsigned NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=35615 ;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
CREATE TABLE IF NOT EXISTS `session` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `stop` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `device_id` int(10) unsigned DEFAULT NULL,
  `elapsed` int(10) unsigned NOT NULL DEFAULT '0',
  `rate` float NOT NULL DEFAULT '0',
  `description` text,
  `cfop_id` int(11) DEFAULT NULL,
  `min_use_time` int(11) NOT NULL,
  `rate_type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26196 ;

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

DROP TABLE IF EXISTS `status`;
CREATE TABLE IF NOT EXISTS `status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `statusname` varchar(45) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`id`, `statusname`, `type`) VALUES
(1, 'Online', 1),
(2, 'Repair', 1),
(3, 'Hidden', 1),
(4, 'Offline', 1),
(5, 'Active', 2),
(6, 'Hidden', 2),
(7, 'Disabled', 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(32) DEFAULT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `first` varchar(45) NOT NULL DEFAULT '',
  `last` varchar(45) NOT NULL DEFAULT '',
  `group_id` int(10) unsigned DEFAULT NULL,
  `grank` int(10) unsigned NOT NULL DEFAULT '0',
  `rate` varchar(45) NOT NULL DEFAULT '',
  `hidden` tinyint(1) DEFAULT '0',
  `rate_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `user_role_id` int(11) DEFAULT NULL,
  `date_added` datetime NOT NULL,
  `secure_key` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=940 ;


CREATE TABLE `user_demographics` (
   `user_id` int(11) unsigned NOT NULL,
   `edu_level` varchar(128) DEFAULT NULL,
   `gender` varchar(64) DEFAULT NULL,
   `underrepresented` varchar(64) DEFAULT NULL,
   PRIMARY KEY (`user_id`),
   CONSTRAINT `user_demographics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_cfop`
--

DROP TABLE IF EXISTS `user_cfop`;
CREATE TABLE IF NOT EXISTS `user_cfop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `cfop` varchar(45) NOT NULL,
  `description` text NOT NULL,
  `active` int(11) NOT NULL,
  `default_cfop` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1134 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE IF NOT EXISTS `user_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Supervisor'),
(3, 'User');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
