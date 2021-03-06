# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: bend.encs.vancouver.wsu.edu (MySQL 5.6.22)
# Database: CS420G4
# Generation Time: 2015-02-27 00:20:51 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table Attribute
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Attribute`;

CREATE TABLE `Attribute` (
  `AttributeId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(128) NOT NULL,
  `Abbreviation` varchar(10) NOT NULL,
  PRIMARY KEY (`AttributeId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `Attribute` WRITE;
/*!40000 ALTER TABLE `Attribute` DISABLE KEYS */;

INSERT INTO `Attribute` (`AttributeId`, `Name`, `Abbreviation`)
VALUES
	(1,'Handicap Placard','HC'),
	(2,'Residential Permit','RP'),
	(3,'Employee Permit','EP'),
	(4,'Student Permit','SP'),
	(5,'Carpool Permit','CP'),
	(6,'Other','O');

/*!40000 ALTER TABLE `Attribute` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table Block
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Block`;

CREATE TABLE `Block` (
  `Block` int(10) unsigned NOT NULL,
  `Face` char(1) NOT NULL DEFAULT '',
  `numStalls` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`Block`,`Face`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `Block` WRITE;
/*!40000 ALTER TABLE `Block` DISABLE KEYS */;

INSERT INTO `Block` (`Block`, `Face`, `numStalls`)
VALUES
	(1,'A',5),
	(1,'B',4),
	(1,'C',0),
	(1,'D',0),
	(2,'A',0),
	(2,'B',5),
	(2,'C',0),
	(2,'D',0),
	(3,'A',4),
	(3,'B',0),
	(3,'C',0),
	(3,'D',0),
	(4,'A',0),
	(4,'B',0),
	(4,'C',5),
	(4,'D',0),
	(5,'A',5),
	(5,'B',7),
	(5,'C',5),
	(5,'D',4),
	(6,'A',5),
	(6,'B',9),
	(6,'C',5),
	(6,'D',0),
	(7,'A',0),
	(7,'B',0),
	(7,'C',6),
	(7,'D',0),
	(8,'A',13),
	(8,'B',0),
	(8,'C',0),
	(8,'D',7),
	(9,'A',15),
	(9,'B',3),
	(9,'C',15),
	(9,'D',7),
	(10,'A',14),
	(10,'B',2),
	(10,'C',14),
	(10,'D',6),
	(11,'A',0),
	(11,'B',2),
	(11,'C',15),
	(11,'D',0),
	(12,'A',9),
	(12,'B',0),
	(12,'C',0),
	(12,'D',7),
	(13,'A',0),
	(13,'B',0),
	(13,'C',0),
	(13,'D',3),
	(14,'A',3),
	(14,'B',0),
	(14,'C',14),
	(14,'D',3),
	(15,'A',0),
	(15,'B',0),
	(15,'C',6),
	(15,'D',3),
	(16,'A',3),
	(16,'B',0),
	(16,'C',0),
	(16,'D',0),
	(17,'A',10),
	(17,'B',8),
	(17,'C',8),
	(17,'D',4),
	(18,'A',0),
	(18,'B',0),
	(18,'C',14),
	(18,'D',0),
	(19,'A',6),
	(19,'B',0),
	(19,'C',0),
	(19,'D',0),
	(20,'A',8),
	(20,'B',5),
	(20,'C',0),
	(20,'D',0),
	(21,'A',0),
	(21,'B',0),
	(21,'C',9),
	(21,'D',0),
	(22,'A',0),
	(22,'B',0),
	(22,'C',0),
	(22,'D',0),
	(23,'A',0),
	(23,'B',0),
	(23,'C',0),
	(23,'D',0),
	(24,'A',0),
	(24,'B',0),
	(24,'C',0),
	(24,'D',4);

/*!40000 ALTER TABLE `Block` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table Parking
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Parking`;

CREATE TABLE `Parking` (
  `ParkingId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Plate` varchar(10) NOT NULL,
  `Block` int(10) unsigned NOT NULL,
  `Face` char(1) NOT NULL,
  `Stall` int(2) unsigned DEFAULT NULL,
  `Time` datetime NOT NULL,
  PRIMARY KEY (`ParkingId`),
  KEY `Block` (`Block`),
  CONSTRAINT `parking_ibfk_1` FOREIGN KEY (`Block`) REFERENCES `Block` (`Block`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `Parking` WRITE;
/*!40000 ALTER TABLE `Parking` DISABLE KEYS */;

INSERT INTO `Parking` (`ParkingId`, `Plate`, `Block`, `Face`, `Stall`, `Time`)
VALUES
	(1,'AGP2556',2,'A',0,'2014-11-16 10:32:06');

/*!40000 ALTER TABLE `Parking` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table ParkingAttributes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ParkingAttributes`;

CREATE TABLE `ParkingAttributes` (
  `ParkingId` int(10) unsigned NOT NULL,
  `AttributeId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ParkingId`),
  KEY `PA_Attribute` (`AttributeId`),
  CONSTRAINT `PA_Attribute` FOREIGN KEY (`AttributeId`) REFERENCES `Attribute` (`AttributeId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `PA_Parking` FOREIGN KEY (`ParkingId`) REFERENCES `Parking` (`ParkingId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table Role
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Role`;

CREATE TABLE `Role` (
  `RoleId` int(10) unsigned NOT NULL,
  `Name` varchar(128) NOT NULL,
  PRIMARY KEY (`RoleId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `Role` WRITE;
/*!40000 ALTER TABLE `Role` DISABLE KEYS */;

INSERT INTO `Role` (`RoleId`, `Name`)
VALUES
	(1,'User'),
	(2,'Admin');

/*!40000 ALTER TABLE `Role` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table Session
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Session`;

CREATE TABLE `Session` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserId` int(10) unsigned NOT NULL,
  `SessionToken` binary(64) NOT NULL DEFAULT '\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0',
  `LastSeen` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `SessionToken` (`SessionToken`),
  KEY `UserId` (`UserId`),
  CONSTRAINT `session_ibfk_1` FOREIGN KEY (`UserId`) REFERENCES `User` (`UserId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `Session` WRITE;
/*!40000 ALTER TABLE `Session` DISABLE KEYS */;

INSERT INTO `Session` (`id`, `UserId`, `SessionToken`, `LastSeen`)
VALUES
	(1,1,X'34363330316463326133366530653136333662373164643266303863656336616463366532663837633366653239633135363039613635666464313433393331','2015-02-24 16:01:11');

/*!40000 ALTER TABLE `Session` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table User
# ------------------------------------------------------------

DROP TABLE IF EXISTS `User`;

CREATE TABLE `User` (
  `UserId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `Password` varchar(255) NOT NULL,
  PRIMARY KEY (`UserId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `User` WRITE;
/*!40000 ALTER TABLE `User` DISABLE KEYS */;

INSERT INTO `User` (`UserId`, `Name`, `Password`)
VALUES
	(1,'Test','$2y$10$nkoefdL7b5hJPx/yX2M8keXftTrEGdAsggX9a/JfYXFBPHYY3Bxpi');

/*!40000 ALTER TABLE `User` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table UserRoles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `UserRoles`;

CREATE TABLE `UserRoles` (
  `UserId` int(10) unsigned NOT NULL,
  `RoleId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`UserId`,`RoleId`),
  KEY `UserId` (`UserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `UserRoles` WRITE;
/*!40000 ALTER TABLE `UserRoles` DISABLE KEYS */;

INSERT INTO `UserRoles` (`UserId`, `RoleId`)
VALUES
	(1,1);

/*!40000 ALTER TABLE `UserRoles` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
