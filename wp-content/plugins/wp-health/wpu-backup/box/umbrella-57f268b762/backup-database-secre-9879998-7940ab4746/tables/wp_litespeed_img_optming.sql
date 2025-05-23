-- Simple Backup SQL Dump
-- Version 1.0.3
-- https://www.github.com/coderatio/simple-backup/
--
-- Host: localhost:3306
-- Generation Time: May 23, 2025 at 05:07 AM
-- MYSQL Server Version: 10.11.10-MariaDB
-- PHP Version: 8.2.27
-- Developer: Josiah O. Yahaya
-- Copyright: Coderatio


--
-- Database: `u161683415_secretdecoj11`
-- Total Tables: 1
--

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `wp_litespeed_img_optming`
--

DROP TABLE IF EXISTS `wp_litespeed_img_optming`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `wp_litespeed_img_optming` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `optm_status` tinyint(4) NOT NULL DEFAULT 0,
  `src` varchar(1000) NOT NULL DEFAULT '',
  `server_info` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `optm_status` (`optm_status`),
  KEY `src` (`src`(191))
) ENGINE=InnoDB AUTO_INCREMENT=15124 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_litespeed_img_optming`
--

LOCK TABLES `wp_litespeed_img_optming` WRITE;
/*!40000 ALTER TABLE `wp_litespeed_img_optming` DISABLE KEYS */;
SET autocommit=0;
INSERT  IGNORE INTO `wp_litespeed_img_optming` VALUES (4831,8945,3,'2024/05/shutterstock_2282305023-400x400.jpg',''),(4853,8948,3,'2024/06/pexels-annpoan-5849392-100x100.jpg',''),(5062,6132,3,'2024/02/enlarge_le-secret-deco-instagram__3_-864x1536.jpg',''),(5153,5518,3,'2024/01/dounia-conseils-rdvdeco-600x400.jpg',''),(6467,9682,3,'2024/12/shutterstock_2415719487-700x500.jpg',''),(6694,9720,3,'2024/12/IMG_4136-scaled-apres-1024x674.jpg','');
/*!40000 ALTER TABLE `wp_litespeed_img_optming` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;

-- Dumped table `wp_litespeed_img_optming` with 6 row(s)
--

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on: Fri, 23 May 2025 05:41:07 +0000
