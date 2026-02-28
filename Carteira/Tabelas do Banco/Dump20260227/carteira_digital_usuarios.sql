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
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `cpf` char(11) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuarios_email` (`email`),
  UNIQUE KEY `uk_usuarios_cpf` (`cpf`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Eduardo Rocha','eduardo@gmail.com','$2y$10$jR8U2LIM9cEHhF0Uz6O1VePPZnLyStcVF3oCCo4HiuxdV2MLoXXgG',NULL,NULL,NULL,'2026-02-19 23:38:30','2026-02-19 23:38:30',0),(2,'Joao','joao@gmail.com','$2y$10$yN1M6H13HSbZjqoqvcSDseMGIk6YQdLBKX7/qoDoc8JuNvKVElIiy',NULL,NULL,NULL,'2026-02-20 00:48:54','2026-02-20 00:48:54',0),(3,'Eduardo Rocha','eduardofake386@gmail.com','$2y$10$dSIjbI4o6OugckSVF979pukvYF2ECqnG1zq3cQ4IdjIXvIZadVPr6',NULL,NULL,NULL,'2026-02-23 23:22:44','2026-02-23 23:22:44',0),(4,'Jhennifer Cardoso','jhe@gmail.com','$2y$10$9aQF5XmIneXx8ESCLvoiouOTx3sG8PbjoQaIx2TC2eWLbaLXN9M9e',NULL,NULL,NULL,'2026-02-24 00:11:43','2026-02-24 01:56:41',1),(9,'admin','admin@gmail.com','$2y$10$tv7NIwHlYq3HbEw/xcuBkuZf5GTwk/7An0DUm2VkRRa4JnyL0bNge',NULL,NULL,NULL,'2026-02-24 01:57:39','2026-02-24 01:57:39',1),(10,'Felipe Migot','felipe@gmail.com','$2y$10$4AIXqabxvbELF7cnvBR2j.CnGeyTkDPdLqc/K6SKILaRCiA/emvlO',NULL,NULL,NULL,'2026-02-24 22:13:44','2026-02-24 22:13:44',0);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-27 21:11:57
