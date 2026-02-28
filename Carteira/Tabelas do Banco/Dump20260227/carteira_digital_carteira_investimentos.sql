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
-- Table structure for table `carteira_investimentos`
--

DROP TABLE IF EXISTS `carteira_investimentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carteira_investimentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `ativo_id` int(11) NOT NULL,
  `quantidade` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `preco_medio` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `data_ultima_atualizacao` date NOT NULL,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_carteira_usuario_ativo` (`usuario_id`,`ativo_id`),
  UNIQUE KEY `unique_usuario_ativo` (`usuario_id`,`ativo_id`),
  KEY `fk_carteira_ativo` (`ativo_id`),
  CONSTRAINT `fk_carteira_ativo` FOREIGN KEY (`ativo_id`) REFERENCES `ativos` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_carteira_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_quantidade_positiva` CHECK (`quantidade` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carteira_investimentos`
--

LOCK TABLES `carteira_investimentos` WRITE;
/*!40000 ALTER TABLE `carteira_investimentos` DISABLE KEYS */;
INSERT INTO `carteira_investimentos` VALUES (1,1,4,1.000000,26.4600,'2026-02-20','','2026-02-20 00:36:52','2026-02-20 00:36:52'),(2,1,3,1.000000,48.5500,'2026-02-20','','2026-02-20 00:37:08','2026-02-20 00:37:08'),(4,2,1,2.000000,37.8100,'2026-02-20','','2026-02-20 00:53:12','2026-02-20 00:53:12'),(6,2,8,102.000000,402.7000,'2026-02-20','teste','2026-02-20 00:55:12','2026-02-20 00:59:34'),(8,3,7,2.000000,20.1700,'2026-02-24','','2026-02-23 23:22:57','2026-02-23 23:34:25'),(11,2,3,3.000000,48.5500,'2026-02-22','teste','2026-02-23 23:53:19','2026-02-23 23:53:19'),(12,3,3,5.000000,48.5500,'2026-02-24','','2026-02-23 23:53:48','2026-02-23 23:53:48'),(13,4,10,1.000000,111.4900,'2026-02-24','','2026-02-24 00:14:32','2026-02-24 00:43:20'),(15,4,9,8.000000,157.0000,'2026-02-24','','2026-02-24 00:44:44','2026-02-24 01:43:34'),(16,4,8,6.000000,402.7000,'2026-02-24','','2026-02-24 01:42:30','2026-02-24 01:42:46'),(18,10,4,3.000000,26.4600,'2026-02-25','','2026-02-24 23:39:25','2026-02-24 23:39:37'),(20,10,11,3.000000,67.8900,'2026-02-25','','2026-02-24 23:40:54','2026-02-24 23:40:54');
/*!40000 ALTER TABLE `carteira_investimentos` ENABLE KEYS */;
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
