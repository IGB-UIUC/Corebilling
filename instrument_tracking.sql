-- MySQL dump 10.13  Distrib 5.1.73, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: biotech_instru
-- ------------------------------------------------------
-- Server version	5.1.73-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `access_control`
--

DROP TABLE IF EXISTS `access_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `access_control` (
  `participant_id` int(10) unsigned DEFAULT NULL,
  `resource_type_id` int(11) DEFAULT NULL,
  `resource_id` int(11) NOT NULL,
  `permission` int(11) NOT NULL,
  `participant_type_id` int(11) DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=188 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_control`
--

LOCK TABLES `access_control` WRITE;
/*!40000 ALTER TABLE `access_control` DISABLE KEYS */;
INSERT INTO `access_control` VALUES (1,2,6,2,0,43),(1,2,1,2,0,52),(1,2,2,2,0,53),(1,2,3,2,0,54),(1,2,4,2,0,70),(1,2,5,2,0,71),(1,2,7,2,0,72),(1,2,8,2,0,73),(1,2,9,2,0,74),(1,2,10,2,0,75),(1,2,11,2,0,76),(1,2,12,2,0,77),(3,2,1,1,0,110),(3,2,2,1,0,111),(3,2,10,1,0,112),(3,2,12,1,0,113),(2,2,2,2,0,171),(2,2,3,2,0,172),(2,2,12,2,0,173),(1,2,13,2,0,174),(2,2,1,1,0,175),(2,2,4,1,0,176),(2,2,5,1,0,177),(2,2,6,1,0,178),(2,2,7,1,0,179),(2,2,8,1,0,180),(2,2,9,1,0,181),(2,2,10,1,0,182),(2,2,11,1,0,183),(2,2,13,1,0,184),(3,2,3,1,0,185),(1,1,56,2,0,186),(3,1,56,1,0,187);
/*!40000 ALTER TABLE `access_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` date DEFAULT NULL,
  `text` text NOT NULL,
  `title` text NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles`
--

LOCK TABLES `articles` WRITE;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
INSERT INTO `articles` VALUES (56,'2014-10-24','test','test',9);
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `department_name` longtext,
  `description` varchar(45) NOT NULL,
  `department_code` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=232 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (136,'Materials Science & Engineerng','Materials Science & Engineerng','1-KP-919'),(167,'Food Science & Human Nutrition','Food Science & Human Nutrition','1-KL-698'),(168,'Fellowships','Fellowships','1-KS-683'),(169,'Crop Sciences','Crop Sciences','1-KL-802'),(170,'Institute for Genomic Biology','Institute for Genomic Biology','1-NE-231'),(171,'Entomology','Entomology','1-KV-361'),(172,'Veterinary Teaching Hospital','Veterinary Teaching Hospital','1-LC-255'),(173,'Animal Sciences','Animal Sciences','1-KL-538'),(174,'Plant Biology','Plant Biology','1-KV-377'),(175,'Chemical & Biomolecular Engr','Chemical & Biomolecular Engr','1-KV-687'),(176,'Molecular & Integrative Physl','Molecular & Integrative Physl','1-KV-604'),(177,'Chemistry','Chemistry','1-KV-413'),(178,'Bioengineering','Bioengineering','1-KP-343'),(179,'Electrical & Computer Eng','Electrical & Computer Eng','1-KP-933'),(180,'Biochemistry','Biochemistry','1-KV-438'),(181,'Comparative Biosciences','Comparative Biosciences','1-LC-873'),(182,'Physics','Physics','1-KP-244'),(183,'Geology','Geology','1-KV-655'),(184,'Microbiology','Microbiology','1-KV-948'),(185,'Beckman Institute','Beckman Institute','1-LH-392'),(186,'Veterinary Diagnostic Lab','Veterinary Diagnostic Lab','1-LC-726'),(187,'School of Molecular & Cell Bio','School of Molecular & Cell Bio','1-KV-415'),(188,'Medicine at UC Administration','Medicine at UC Administration','1-LB-761'),(189,'Cell & Developmental Biology','Cell & Developmental Biology','1-KV-584'),(190,'Civil & Environmental Eng','Civil & Environmental Eng','1-KP-251'),(191,'Mechanical Science & Engineering','Mechanical Science & Engineering','1-KP-917'),(192,'Pathobiology','Pathobiology','1-LC-282'),(193,'Control - Payroll','Control - Payroll','1-ZZ-109'),(194,'Pathology','Pathology','1-LB-552'),(195,'Biotechnology Center','Biotechnology Center','1-NE-531'),(196,'Micro and Nanotechnology Lab','Micro and Nanotechnology Lab','1-KP-487'),(197,'Housing Division','Housing Division','1-NQ-270'),(198,'School of Chemical Sciences','School of Chemical Sciences','1-KV-510'),(199,'Animal Biology','Animal Biology','1-KV-292'),(200,'Intercollegiate Athletics','Intercollegiate Athletics','1-NU-336'),(201,'Nutritional Sciences','Nutritional Sciences','1-KL-971'),(202,'Natural Res & Env Sci','Natural Res & Env Sci','1-KL-875'),(203,'Library','Library','1-LR-668'),(204,'Student Financial Aid','Student Financial Aid','1-NB-678'),(205,'Undergraduate Admissions','Undergraduate Admissions','1-NB-593'),(206,'Anthropology','Anthropology','1-KV-241'),(207,'Vet Clinical Medicine','Vet Clinical Medicine','1-LC-598'),(208,'Business Administration','Business Administration','1-KM-902'),(209,'Engineering Administration','Engineering Administration','1-KP-227'),(210,'School of Integrative Biology','School of Integrative Biology','1-KV-383'),(211,'Agricultural & Biological Engr','','1-KL-741'),(218,'Chemical & Biomolecular Engr','',''),(219,'Computer Science','Computer Science','1-KP-434'),(220,'Supercomputing Applications','Supercomputing Applications','1-NE-320'),(221,'Psychology','Psychology','1-KV-299'),(222,'Internal Medicine','Internal Medicine','1-LB-684'),(223,'Vice President for Research','','9-AJ-757'),(224,'Division of Research Safety','Division of Research Safety','1-NE-877'),(225,'Div State Geological Survey','Div State Geological Survey','1-NE-547'),(226,'LAS Administration','LAS Administration','1-KV-580'),(227,'Engineering IT Shared Services','Engineering IT Shared Services','1-KP-661'),(228,'Library Admin','Library Admin','1-LR-540'),(229,'State Natural History Survey','State Natural History Survey','1-NE-375'),(230,'Economics','Economics',''),(231,'test1234','test123','');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device`
--

DROP TABLE IF EXISTS `device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device` (
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
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device`
--

LOCK TABLES `device` WRITE;
/*!40000 ALTER TABLE `device` DISABLE KEYS */;
INSERT INTO `device` VALUES (56,'nevoband-PC','2624','                                                                                                                test                                                                                                ','Nevo Band Computer',1,0,'2014-09-18 10:54:44','','7cc75bb92e472b23d19df73648653e68');
/*!40000 ALTER TABLE `device` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_rate`
--

DROP TABLE IF EXISTS `device_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_rate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rate` float NOT NULL,
  `device_id` int(10) unsigned DEFAULT NULL,
  `rate_id` int(11) DEFAULT NULL,
  `min_use_time` int(11) NOT NULL,
  `rate_type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=262 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_rate`
--

LOCK TABLES `device_rate` WRITE;
/*!40000 ALTER TABLE `device_rate` DISABLE KEYS */;
INSERT INTO `device_rate` VALUES (261,0.0833333,56,9,30,1);
/*!40000 ALTER TABLE `device_rate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `department_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=190 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'Admins','Core Facilities Administrators 1',0);
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_name` varchar(45) NOT NULL,
  `show_navigation` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,'Latest News',1),(2,'User Billing',1),(3,'Edit Users',1),(4,'Edit Groups',1),(5,'Edit Departments',1),(6,'Edit Devices',1),(7,'Facility Billing',1),(8,'Edit Permissions',1),(10,'Devices In Use',1),(11,'Statistics',1),(12,'Calendar',1);
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rate_types`
--

DROP TABLE IF EXISTS `rate_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rate_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rate_type_name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rate_types`
--

LOCK TABLES `rate_types` WRITE;
/*!40000 ALTER TABLE `rate_types` DISABLE KEYS */;
INSERT INTO `rate_types` VALUES (1,'Continuous'),(2,'Monthly');
/*!40000 ALTER TABLE `rate_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rates`
--

DROP TABLE IF EXISTS `rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rate_name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rates`
--

LOCK TABLES `rates` WRITE;
/*!40000 ALTER TABLE `rates` DISABLE KEYS */;
INSERT INTO `rates` VALUES (9,'UofI');
/*!40000 ALTER TABLE `rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation_info`
--

DROP TABLE IF EXISTS `reservation_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservation_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `stop` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` text NOT NULL,
  `training` int(10) unsigned NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35610 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation_info`
--

LOCK TABLES `reservation_info` WRITE;
/*!40000 ALTER TABLE `reservation_info` DISABLE KEYS */;
INSERT INTO `reservation_info` VALUES (35593,56,9,'2014-09-10 10:00:00','2014-09-10 15:30:00','sdfs',0,'2014-09-12 14:12:18'),(35600,56,9,'2014-09-03 01:30:00','2014-09-03 05:30:00','test123',0,'2014-09-16 14:52:20'),(35601,56,9,'2014-09-04 08:00:00','2014-09-04 14:30:00','test123',0,'2014-09-16 15:16:09'),(35602,56,9,'2014-09-12 09:00:00','2014-09-12 13:30:00','test123',0,'2014-09-16 15:16:39'),(35603,56,9,'2014-09-03 10:30:00','2014-09-03 18:00:00','test123',0,'2014-09-17 11:07:49'),(35604,56,9,'2014-09-09 10:30:00','2014-09-09 15:00:00','test123',1,'2014-09-17 11:30:17'),(35605,56,9,'2014-09-11 09:30:00','2014-09-11 14:00:00','test1236',0,'2014-09-17 11:30:43'),(35606,56,9,'2014-09-13 06:00:00','2014-09-13 15:00:00','test123',0,'2014-09-18 10:04:19'),(35609,56,9,'2014-10-08 06:30:00','2014-10-08 12:00:00','test1234',0,'2014-10-30 09:54:19');
/*!40000 ALTER TABLE `reservation_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
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
) ENGINE=InnoDB AUTO_INCREMENT=24365 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session`
--

LOCK TABLES `session` WRITE;
/*!40000 ALTER TABLE `session` DISABLE KEYS */;
INSERT INTO `session` VALUES (24364,9,'2014-09-11 16:19:03','2014-09-18 15:37:44',0,56,10038,0.0833333,'',980,0,1);
/*!40000 ALTER TABLE `session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `status`
--

DROP TABLE IF EXISTS `status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `statusname` varchar(45) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `status`
--

LOCK TABLES `status` WRITE;
/*!40000 ALTER TABLE `status` DISABLE KEYS */;
INSERT INTO `status` VALUES (1,'Online',1),(2,'Repair',1),(3,'Hidden',1),(4,'Offline',1),(5,'Active',2),(6,'Hidden',2),(7,'Disabled',2);
/*!40000 ALTER TABLE `status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_cfop`
--

DROP TABLE IF EXISTS `user_cfop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_cfop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `cfop` varchar(45) NOT NULL,
  `description` text NOT NULL,
  `active` int(11) NOT NULL,
  `default_cfop` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=982 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_cfop`
--

LOCK TABLES `user_cfop` WRITE;
/*!40000 ALTER TABLE `user_cfop` DISABLE KEYS */;
INSERT INTO `user_cfop` VALUES (981,787,'1-000000-000000-000000','',1,1,'2014-10-30 15:34:54'),(980,9,'','',1,0,'2014-09-11 13:49:39'),(979,9,'1-5549191-538029-19111','',1,1,'2014-09-11 10:54:58');
/*!40000 ALTER TABLE `user_cfop` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (1,'Admin'),(2,'Supervisor'),(3,'User');
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
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
) ENGINE=InnoDB AUTO_INCREMENT=791 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (9,'nevoband1','nevoband@igb.uiuc.edu','Nevo','Band',1,0,'igb_rate',1,9,170,5,1,'0000-00-00 00:00:00','93201dfa56d03282dfb5'),(787,'nevoband','nevoband@illinois.edu','Nevo','Band',0,0,'',0,0,211,7,1,'2014-10-30 15:29:04','ca37e09a479b4106d776601f8d726ef6'),(789,'dslater','dslater@illinois.edu','','',0,0,'',0,0,0,7,3,'2014-10-30 15:32:55','5ea4a32af3b1276dd8f1928fbb9e3a3a'),(790,'rsturg','rsturg@illinois.edu','','',0,0,'',0,0,0,7,3,'2014-10-30 15:51:17','3be54dc225b3393c8490f51a574cfa06');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-11-11 15:25:03
