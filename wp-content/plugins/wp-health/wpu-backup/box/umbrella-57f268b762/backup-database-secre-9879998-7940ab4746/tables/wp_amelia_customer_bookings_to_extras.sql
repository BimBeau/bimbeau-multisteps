-- Simple Backup SQL Dump
-- Version 1.0.3
-- https://www.github.com/coderatio/simple-backup/
--
-- Host: localhost:3306
-- Generation Time: May 23, 2025 at 06:05 AM
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
-- Table structure for table `wp_amelia_customer_bookings_to_extras`
--

DROP TABLE IF EXISTS `wp_amelia_customer_bookings_to_extras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `wp_amelia_customer_bookings_to_extras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customerBookingId` int(11) NOT NULL,
  `extraId` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` double NOT NULL,
  `aggregatedPrice` tinyint(1) DEFAULT NULL,
  `tax` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bookingExtra` (`customerBookingId`,`extraId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wp_amelia_customer_bookings_to_extras`
--

LOCK TABLES `wp_amelia_customer_bookings_to_extras` WRITE;
/*!40000 ALTER TABLE `wp_amelia_customer_bookings_to_extras` DISABLE KEYS */;
SET autocommit=0;
/*!40000 ALTER TABLE `wp_amelia_customer_bookings_to_extras` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;

-- Dumped table `wp_amelia_customer_bookings_to_extras` with 0 row(s)
--

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on: Fri, 23 May 2025 06:15:05 +0000
