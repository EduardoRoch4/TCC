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
-- Table structure for table `ativos`
--

DROP TABLE IF EXISTS `ativos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ativos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `tipo_id` tinyint(4) NOT NULL,
  `preco_atual` decimal(18,4) DEFAULT 0.0000,
  `variacao_dia` decimal(8,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ativos_codigo` (`codigo`),
  KEY `idx_ativos_tipo` (`tipo_id`),
  CONSTRAINT `fk_ativos_tipos_ativo` FOREIGN KEY (`tipo_id`) REFERENCES `tipos_ativo` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ativos`
--

LOCK TABLES `ativos` WRITE;
/*!40000 ALTER TABLE `ativos` DISABLE KEYS */;
INSERT INTO `ativos` VALUES (1,'PETR4','Petroleo Brasileiro SA Pfd',1,37.8100,1.67,'2026-02-20 00:35:19'),(2,'VALE3','Vale S.A.',1,84.0900,0.20,'2026-02-20 00:35:19'),(3,'ITUB4','Itau Unibanco Holding SA Pfd',1,48.5500,1.17,'2026-02-20 00:35:19'),(4,'BBAS3','Banco do Brasil S.A.',1,26.4600,2.48,'2026-02-20 00:35:19'),(5,'WEGE3','WEG SA',1,51.3700,-3.78,'2026-02-20 00:35:19'),(6,'TAEE11','Transmissora Alianca De Energia Eletrica S.A. Unit',2,44.5600,2.01,'2026-02-20 00:35:19'),(7,'KLBN11','Klabin SA Ctf de Deposito de Acoes Cons of 1 Sh + 4 Pfd Shs',2,20.1700,-0.20,'2026-02-20 00:35:19'),(8,'IVVB11','iShares S&P 500 Fundo de Investimento em Cotas de Fundo de Indice - Investimento no Exterior',2,402.7000,-0.63,'2026-02-20 00:35:19'),(9,'HGLG11','Patria Log - Fundo de Investimento Imobiliario - Responsabilidade Limitada Cotas',2,157.0000,-0.31,'2026-02-20 00:35:19'),(10,'XPML11','XP Malls Fundo Investimento Imobiliario Investor',2,111.4900,0.40,'2026-02-20 00:35:19'),(11,'AAPL34','Apple Inc. Shs Unsponsored Brazilian Depository Receipt Repr 0.05 Sh',4,67.8900,-1.61,'2026-02-20 00:35:19'),(12,'MSFT34','Microsoft Corp Shs Unsponsored Brazilian Depository Receipt Repr 0.04167 Sh',4,86.6100,-1.13,'2026-02-20 00:35:19');
/*!40000 ALTER TABLE `ativos` ENABLE KEYS */;
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
