-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: carteira_digital
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `carteira_renda_fixa`
--

DROP TABLE IF EXISTS `carteira_renda_fixa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carteira_renda_fixa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `emissor` varchar(150) NOT NULL,
  `tipo_titulo` varchar(50) NOT NULL,
  `indexador` varchar(30) NOT NULL,
  `taxa` decimal(8,4) DEFAULT NULL,
  `forma` enum('POS_FIXADO','PRE_FIXADO') NOT NULL DEFAULT 'POS_FIXADO',
  `valor_investido` decimal(18,2) NOT NULL,
  `data_compra` date NOT NULL,
  `data_vencimento` date DEFAULT NULL,
  `liquidez_diaria` tinyint(1) NOT NULL DEFAULT 0,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_renda_fixa_usuario` (`usuario_id`),
  CONSTRAINT `carteira_renda_fixa_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carteira_renda_fixa`
--

LOCK TABLES `carteira_renda_fixa` WRITE;
/*!40000 ALTER TABLE `carteira_renda_fixa` DISABLE KEYS */;
INSERT INTO `carteira_renda_fixa` VALUES (1,4,'Nubank','CDB','CDI',115.0000,'POS_FIXADO',1351.12,'2026-02-24',NULL,1,'','2026-02-24 00:18:33','2026-02-24 00:18:33'),(5,10,'Nubank','CDB','CDI',100.0000,'POS_FIXADO',1000.00,'2024-01-25',NULL,1,'','2026-02-24 23:38:54','2026-02-24 23:38:54');
/*!40000 ALTER TABLE `carteira_renda_fixa` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-27 21:11:58
