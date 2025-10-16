CREATE DATABASE  IF NOT EXISTS `projeto_qr_code` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `projeto_qr_code`;
-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: projeto_qr_code
-- ------------------------------------------------------
-- Server version	8.0.42

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
-- Table structure for table `agendamentos`
--

DROP TABLE IF EXISTS `agendamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agendamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `data_agendamento` date NOT NULL,
  `hora_agendamento` time NOT NULL,
  `status` enum('AGENDADO','CONFIRMADO','REALIZADO','CANCELADO') COLLATE utf8mb4_unicode_ci DEFAULT 'AGENDADO',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agendamentos`
--

LOCK TABLES `agendamentos` WRITE;
/*!40000 ALTER TABLE `agendamentos` DISABLE KEYS */;
INSERT INTO `agendamentos` VALUES (4,4,'2025-10-15','17:13:00','REALIZADO',NULL,'2025-10-14 17:13:07','2025-10-14 17:40:53'),(5,4,'2025-10-16','17:46:00','REALIZADO',NULL,'2025-10-14 17:46:10','2025-10-14 18:21:27'),(7,7,'2025-10-16','15:30:00','AGENDADO',NULL,'2025-10-14 22:33:08','2025-10-14 22:33:08');
/*!40000 ALTER TABLE `agendamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracoes`
--

DROP TABLE IF EXISTS `configuracoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chave` (`chave`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracoes`
--

LOCK TABLES `configuracoes` WRITE;
/*!40000 ALTER TABLE `configuracoes` DISABLE KEYS */;
INSERT INTO `configuracoes` VALUES (1,'idade_maxima_doador','65');
/*!40000 ALTER TABLE `configuracoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doadores`
--

DROP TABLE IF EXISTS `doadores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doadores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `apto_para_doacao` tinyint DEFAULT '0',
  `faltas` int DEFAULT '0',
  `bloqueado` tinyint DEFAULT '0',
  `motivo_bloqueio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ultima_doacao` date DEFAULT NULL,
  `tipo_sanguineo` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rh` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `altura` decimal(4,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `doadores_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doadores`
--

LOCK TABLES `doadores` WRITE;
/*!40000 ALTER TABLE `doadores` DISABLE KEYS */;
INSERT INTO `doadores` VALUES (4,4,1,0,0,NULL,'2025-10-16',NULL,NULL,NULL,NULL),(6,7,1,0,0,NULL,NULL,'O+','',89.00,1.85);
/*!40000 ALTER TABLE `doadores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perguntas`
--

DROP TABLE IF EXISTS `perguntas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perguntas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `texto_pergunta` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_resposta` enum('SIM_NAO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SIM_NAO',
  `resposta_inapta` enum('SIM','NAO','NENHUMA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SIM',
  `ordem` int DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perguntas`
--

LOCK TABLES `perguntas` WRITE;
/*!40000 ALTER TABLE `perguntas` DISABLE KEYS */;
INSERT INTO `perguntas` VALUES (1,'Você ja teve cancer?','SIM_NAO','SIM',0,1,'2025-09-08 15:27:30','2025-09-08 15:27:58'),(2,'Você ja teve alguma doença cardiaca?','SIM_NAO','SIM',0,1,'2025-09-08 15:27:31','2025-09-08 15:28:16'),(3,'Você ja teve alguma doença pulmonar?','SIM_NAO','SIM',0,1,'2025-09-08 15:27:31','2025-09-08 15:28:36'),(4,'Você ja teve alguma doença renal?','SIM_NAO','SIM',0,1,'2025-09-08 15:27:31','2025-09-08 15:29:00'),(5,'Você ja fez alguma cirurgia?','SIM_NAO','SIM',0,1,'2025-09-08 15:27:31','2025-09-08 15:29:16'),(6,'Você ja fez uso de drogas ilicitas?','SIM_NAO','SIM',0,1,'2025-09-08 15:27:31','2025-09-08 15:29:32'),(7,'Você ja sofreu algum traumatismo craniano?','SIM_NAO','SIM',0,1,'2025-09-08 15:27:31','2025-09-08 15:29:51'),(8,'Você ja teve alguma convulsao?','SIM_NAO','SIM',0,1,'2025-09-08 15:27:31','2025-09-08 15:30:05'),(9,'Você ja teve ou tem alguma doença que possa ser transmitida pelo sangue?','SIM_NAO','SIM',0,1,'2025-09-08 15:27:31','2025-09-08 16:18:42'),(10,'Teve alguma doença infecciosa nos ultimos 30 dias?','SIM_NAO','SIM',0,1,'2025-09-08 15:27:31','2025-09-08 16:19:09'),(11,'Teve contato com alguem que teve alguma doença infecciosa nos últimos 30 dias?','SIM_NAO','SIM',0,1,'2025-09-08 15:27:31','2025-09-08 16:19:51'),(12,'Realizou alguma endoscopia ou colonoscopia nos últimos 6 meses?','SIM_NAO','SIM',0,1,'2025-09-08 15:27:31','2025-09-08 16:20:22'),(13,'Realizou alguma vacina recentemente?','SIM_NAO','SIM',0,1,'2025-09-08 15:27:31','2025-09-08 16:20:42');
/*!40000 ALTER TABLE `perguntas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perguntas_questionario`
--

DROP TABLE IF EXISTS `perguntas_questionario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perguntas_questionario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `texto` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordem` int NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perguntas_questionario`
--

LOCK TABLES `perguntas_questionario` WRITE;
/*!40000 ALTER TABLE `perguntas_questionario` DISABLE KEYS */;
INSERT INTO `perguntas_questionario` VALUES (1,'Você teve febre nas últimas 72 horas?',1,1,'2025-10-09 21:54:13'),(2,'Você apresentou sintomas gripais recentemente?',2,1,'2025-10-09 21:54:13'),(3,'Teve contato com pessoas diagnosticadas com doenças transmissíveis?',3,1,'2025-10-09 21:54:13'),(4,'Fez procedimentos médicos invasivos nos últimos 6 meses?',4,1,'2025-10-09 21:54:13'),(5,'Está em uso de medicamentos que possam interferir na doação?',5,1,'2025-10-09 21:54:13');
/*!40000 ALTER TABLE `perguntas_questionario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questionario_config`
--

DROP TABLE IF EXISTS `questionario_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questionario_config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pergunta` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resposta_correta` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questionario_config`
--

LOCK TABLES `questionario_config` WRITE;
/*!40000 ALTER TABLE `questionario_config` DISABLE KEYS */;
INSERT INTO `questionario_config` VALUES (21,'Você está em boas condições de saúde?',1),(22,'Dormiu pelo menos 6 horas nas últimas 24h?',1),(23,'Está alimentado?',1),(24,'Está gripado, resfriado ou com febre?',0),(25,'Fez cirurgia nos últimos 12 meses?',0),(26,'Fez tatuagem ou piercing nos últimos 12 meses?',0),(27,'Teve contato com pessoa com hepatite?',0),(28,'Usou drogas ilícitas?',0),(29,'Está gestante ou amamentando?',0),(30,'Teve comportamento de risco para doenças sexualmente transmissíveis?',0);
/*!40000 ALTER TABLE `questionario_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questionarios`
--

DROP TABLE IF EXISTS `questionarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questionarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `pergunta_1` tinyint(1) NOT NULL,
  `pergunta_2` tinyint(1) NOT NULL,
  `pergunta_3` tinyint(1) NOT NULL,
  `pergunta_4` tinyint(1) NOT NULL,
  `pergunta_5` tinyint(1) NOT NULL,
  `pergunta_6` tinyint(1) NOT NULL,
  `pergunta_7` tinyint(1) NOT NULL,
  `pergunta_8` tinyint(1) NOT NULL,
  `pergunta_9` tinyint(1) NOT NULL,
  `pergunta_10` tinyint(1) NOT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `aprovado` tinyint(1) NOT NULL,
  `data_preenchimento` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `questionarios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questionarios`
--

LOCK TABLES `questionarios` WRITE;
/*!40000 ALTER TABLE `questionarios` DISABLE KEYS */;
INSERT INTO `questionarios` VALUES (24,4,1,1,1,0,0,0,0,0,0,0,'',1,'2025-10-14 17:38:43'),(26,4,1,1,1,0,0,0,0,0,0,0,'',1,'2025-10-14 17:46:37'),(32,7,1,1,1,0,0,0,0,0,0,0,'',1,'2025-10-14 22:32:08');
/*!40000 ALTER TABLE `questionarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `respostas_usuario`
--

DROP TABLE IF EXISTS `respostas_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `respostas_usuario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `questionario_id` int NOT NULL,
  `pergunta_id` int NOT NULL,
  `resposta_dada` tinyint(1) NOT NULL COMMENT '1 para Sim, 0 para Não',
  PRIMARY KEY (`id`),
  KEY `questionario_id` (`questionario_id`),
  KEY `pergunta_id` (`pergunta_id`),
  CONSTRAINT `respostas_usuario_ibfk_1` FOREIGN KEY (`questionario_id`) REFERENCES `questionarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `respostas_usuario_ibfk_2` FOREIGN KEY (`pergunta_id`) REFERENCES `perguntas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `respostas_usuario`
--

LOCK TABLES `respostas_usuario` WRITE;
/*!40000 ALTER TABLE `respostas_usuario` DISABLE KEYS */;
/*!40000 ALTER TABLE `respostas_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipos_sanguineos`
--

DROP TABLE IF EXISTS `tipos_sanguineos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_sanguineos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rh` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_sanguineos`
--

LOCK TABLES `tipos_sanguineos` WRITE;
/*!40000 ALTER TABLE `tipos_sanguineos` DISABLE KEYS */;
INSERT INTO `tipos_sanguineos` VALUES (1,'A','+'),(2,'A','-'),(3,'B','+'),(4,'B','-'),(5,'AB','+'),(6,'AB','-'),(7,'O','+'),(8,'O','-');
/*!40000 ALTER TABLE `tipos_sanguineos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_nascimento` date NOT NULL,
  `cpf` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `perfil` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'usuario',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivel_acesso` enum('usuario','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'usuario',
  `ativo` tinyint(1) DEFAULT '1',
  `must_change_password` tinyint(1) DEFAULT '0',
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ultimo_acesso` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (3,'EDUARDO JOSE DA SILVA','1980-07-22','291.564.138-23','11995295547','admin','edu11995295547@gmail.com','$2y$10$84rUga9glMqWk0cR8zsAI.O1TuSTgYV1Ofn7xQZOTkThLHkSXy6Ou','admin',1,0,'2025-09-14 14:29:08','2025-10-14 19:54:50',NULL),(4,'jonatas henrique silva','2009-06-29','587.915.818-77','(11) 97846-4806','usuario','jonatashenriquesilva2020@gmail.com','$2y$10$o.U0YuIbj7nx8APeASotFuXMUPvxpT.UaxZTc6qxAg8A.eimCRGYm','usuario',1,0,'2025-09-28 18:40:31','2025-10-14 16:54:57',NULL),(6,'IEDU BASILIO DA SILVA','2007-05-22','513.449.528-95','(11) 95404-6646','usuario','a@a','$2y$10$qq38jkv.h//Pt9oHi5RBXutZ4lg3ciMnE5YvdDacVUJ808UjmndN.','usuario',1,0,'2025-10-09 21:49:16','2025-10-13 22:20:52',NULL),(7,'Matheus Henrique','2002-09-02','449.517.578-50','(11) 94887-0380','usuario','mhenrique081@gmail.com','$2y$10$jCfmb2rqFtOd7RBPPr5iH.KidxEvJr5VcfTp4NJrx9Ko8RSW9C22e','usuario',1,0,'2025-10-13 23:11:30','2025-10-13 23:11:30',NULL);
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

-- Dump completed on 2025-10-14 20:59:24
