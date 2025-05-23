-- Simple Backup SQL Dump
-- Version 1.0.3
-- https://www.github.com/coderatio/simple-backup/
--
-- Host: localhost:3306
-- Generation Time: May 23, 2025 at 06:04 AM
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
-- Table structure for table `wp_amelia_providers_to_services`
--

DROP TABLE IF EXISTS `wp_amelia_providers_to_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `wp_amelia_providers_to_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `serviceId` int(11) NOT NULL,
  `price` double NOT NULL,
  `minCapacity` int(11) NOT NULL,
  `maxCapacity` int(11) NOT NULL,
  `customPricing` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_amelia_providers_to_services`
--

LOCK TABLES `wp_amelia_providers_to_services` WRITE;
/*!40000 ALTER TABLE `wp_amelia_providers_to_services` DISABLE KEYS */;
SET autocommit=0;
INSERT  IGNORE INTO `wp_amelia_providers_to_services` VALUES (1,1,1,149,1,1,'{\"enabled\":true,\"durations\":{\"5400\":{\"price\":219,\"rules\":[]},\"7200\":{\"price\":289,\"rules\":[]}}}');
/*!40000 ALTER TABLE `wp_amelia_providers_to_services` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;

-- Dumped table `wp_amelia_providers_to_services` with 1 row(s)
--

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on: Fri, 23 May 2025 06:15:04 +0000
