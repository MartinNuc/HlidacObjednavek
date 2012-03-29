-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.5.8


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema hlidacobjednavek
--

CREATE DATABASE IF NOT EXISTS hlidacobjednavek;
USE hlidacobjednavek;

--
-- Definition of table `automat_kontakt`
--

DROP TABLE IF EXISTS `automat_kontakt`;
CREATE TABLE `automat_kontakt` (
  `id_automat_kontakt` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_automat` int(10) unsigned NOT NULL DEFAULT '0',
  `id_kontakt` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_automat_kontakt`),
  KEY `vazba_automat` (`id_automat`),
  KEY `vazba_kontakt` (`id_kontakt`),
  CONSTRAINT `vazba_automat` FOREIGN KEY (`id_automat`) REFERENCES `automaty` (`id_automat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `vazba_kontakt` FOREIGN KEY (`id_kontakt`) REFERENCES `kontakty` (`id_kontakt`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Definition of table `automaty`
--

DROP TABLE IF EXISTS `automaty`;
CREATE TABLE `automaty` (
  `id_automat` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adresa` varchar(128) DEFAULT NULL,
  `id_zakaznik` int(10) unsigned DEFAULT NULL,
  `bmb` varchar(45) NOT NULL DEFAULT '',
  `layout` varchar(45) DEFAULT NULL,
  `nazev` varchar(64) NOT NULL DEFAULT '',
  `id_oblast` int(10) unsigned NOT NULL DEFAULT '0',
  `vyrobni_cislo` varchar(45) NOT NULL,
  `umisteni` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id_automat`),
  KEY `automat_oblast` (`id_oblast`) USING BTREE,
  KEY `automat_zakazni` (`id_zakaznik`) USING BTREE,
  CONSTRAINT `automat_zakaznik` FOREIGN KEY (`id_zakaznik`) REFERENCES `zakaznici` (`id_zakaznik`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Definition of table `dph`
--

DROP TABLE IF EXISTS `dph`;
CREATE TABLE `dph` (
  `id_dph` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dph` int(10) unsigned NOT NULL DEFAULT '20',
  PRIMARY KEY (`id_dph`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `dph`
--
INSERT INTO `dph` VALUES  (1,10),
 (2,20),
 (3,14);

--
-- Definition of table `kategorie`
--

DROP TABLE IF EXISTS `kategorie`;
CREATE TABLE `kategorie` (
  `id_kategorie` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nazev` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_kategorie`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `kategorie`
--
INSERT INTO `kategorie` VALUES  (1,'Káva'),
 (2,'Mléko'),
 (3,'Čokoláda'),
 (4,'Čaje'),
 (5,'Kelímky a víčka'),
 (6,'Cukr a mandle'),
 (7,'Milano');

--
-- Definition of table `kontakty`
--

DROP TABLE IF EXISTS `kontakty`;
CREATE TABLE `kontakty` (
  `id_kontakt` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jmeno` varchar(45) NOT NULL DEFAULT '',
  `telefon` varchar(128) NOT NULL,
  `poznamka` varchar(128) NOT NULL DEFAULT '',
  `email` varchar(45) NOT NULL,
  PRIMARY KEY (`id_kontakt`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8;

--
-- Definition of table `obchodni_zastupci`
--

DROP TABLE IF EXISTS `obchodni_zastupci`;
CREATE TABLE `obchodni_zastupci` (
  `id_obchodni_zastupce` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jmeno` varchar(45) NOT NULL,
  `telefon` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  PRIMARY KEY (`id_obchodni_zastupce`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `obchodni_zastupci`
--
INSERT INTO `obchodni_zastupci` VALUES  (0,'Nepřiřazeno','','');

--
-- Definition of table `objednavky`
--

DROP TABLE IF EXISTS `objednavky`;
CREATE TABLE `objednavky` (
  `id_objednavka` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_zakaznik` int(10) unsigned NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `poznamka` varchar(128) NOT NULL DEFAULT '',
  `id_oblast` int(10) unsigned NOT NULL,
  `cena_bez_dph` float DEFAULT NULL,
  `cena_s_dph` float DEFAULT NULL,
  `kod` varchar(45) NOT NULL DEFAULT '',
  `hledani_bmb` varchar(45) NOT NULL,
  `hledani_vyrobni_cislo` varchar(45) NOT NULL,
  `body` float NOT NULL,
  PRIMARY KEY (`id_objednavka`),
  KEY `objednavka_zakaznik` (`id_zakaznik`) USING BTREE,
  KEY `objednavka_oblast` (`id_oblast`),
  CONSTRAINT `objednavka_oblast` FOREIGN KEY (`id_oblast`) REFERENCES `oblasti` (`id_oblast`),
  CONSTRAINT `objednavka_zakaznik` FOREIGN KEY (`id_zakaznik`) REFERENCES `zakaznici` (`id_zakaznik`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Definition of table `oblasti`
--

DROP TABLE IF EXISTS `oblasti`;
CREATE TABLE `oblasti` (
  `id_oblast` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nazev` varchar(45) NOT NULL DEFAULT '',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `id_obchodni_zastupce` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_oblast`),
  KEY `oblast_obchodni_zastupce` (`id_obchodni_zastupce`),
  CONSTRAINT `oblast_obchodni_zastupce` FOREIGN KEY (`id_obchodni_zastupce`) REFERENCES `obchodni_zastupci` (`id_obchodni_zastupce`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `oblasti`
--
INSERT INTO `oblasti` VALUES  (0,'SKLAD',0,0);

--
-- Definition of table `smlouvy`
--

DROP TABLE IF EXISTS `smlouvy`;
CREATE TABLE `smlouvy` (
  `id_smlouva` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `minimalni_odber` varchar(45) NOT NULL DEFAULT '',
  `od` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `do` datetime DEFAULT NULL,
  `zpusob_platby` varchar(45) NOT NULL DEFAULT '',
  `id_zakaznik` int(10) unsigned NOT NULL DEFAULT '0',
  `cislo_smlouvy` varchar(45) NOT NULL,
  PRIMARY KEY (`id_smlouva`),
  KEY `smlouvy_zakaznik` (`id_zakaznik`) USING BTREE,
  CONSTRAINT `smlouvy_zakaznik` FOREIGN KEY (`id_zakaznik`) REFERENCES `zakaznici` (`id_zakaznik`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Definition of table `technici`
--

DROP TABLE IF EXISTS `technici`;
CREATE TABLE `technici` (
  `id_technik` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jmeno` varchar(45) NOT NULL DEFAULT '',
  `prijmeni` varchar(45) NOT NULL DEFAULT '',
  `id_oblast` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_technik`),
  KEY `oblasti` (`id_oblast`) USING BTREE,
  CONSTRAINT `technici_oblasti` FOREIGN KEY (`id_oblast`) REFERENCES `oblasti` (`id_oblast`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Definition of table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id_user` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL DEFAULT '',
  `password` varchar(45) NOT NULL DEFAULT '',
  `disabled` tinyint(1) DEFAULT NULL,
  `role` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--
INSERT INTO `users` VALUES  (0,'admin','2ae94cb3ac228bfddadfb70e3f8daadb',NULL,'Administrátor');

--
-- Definition of table `zakaznici`
--

DROP TABLE IF EXISTS `zakaznici`;
CREATE TABLE `zakaznici` (
  `id_zakaznik` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nazev` varchar(45) NOT NULL DEFAULT '',
  `adresa` varchar(128) NOT NULL DEFAULT '',
  `email` varchar(45) NOT NULL DEFAULT '',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `telefon` varchar(45) NOT NULL,
  `osobni_zakaznik` tinyint(1) NOT NULL DEFAULT '0',
  `ico` varchar(16) NOT NULL,
  PRIMARY KEY (`id_zakaznik`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `zakaznici`
--
INSERT INTO `zakaznici` VALUES  (0,'SKLAD','','',0,'',0,'');

--
-- Definition of table `zakaznici_zbozi`
--

DROP TABLE IF EXISTS `zakaznici_zbozi`;
CREATE TABLE `zakaznici_zbozi` (
  `id_zakaznici_zbozi` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_zakaznik` int(10) unsigned NOT NULL DEFAULT '0',
  `id_zbozi` int(10) unsigned NOT NULL DEFAULT '0',
  `ve_smlouve` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_zakaznici_zbozi`),
  KEY `vazba_zakazni` (`id_zakaznik`),
  KEY `vazba_zboz` (`id_zbozi`),
  CONSTRAINT `vazba_zakazni` FOREIGN KEY (`id_zakaznik`) REFERENCES `zakaznici` (`id_zakaznik`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `vazba_zboz` FOREIGN KEY (`id_zbozi`) REFERENCES `zbozi` (`id_zbozi`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Definition of table `zbozi`
--

DROP TABLE IF EXISTS `zbozi`;
CREATE TABLE `zbozi` (
  `id_zbozi` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `zkratka` varchar(45) NOT NULL DEFAULT '',
  `nazev` varchar(45) NOT NULL DEFAULT '',
  `id_dph` int(10) unsigned NOT NULL DEFAULT '0',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `id_kategorie` int(10) unsigned NOT NULL DEFAULT '0',
  `prodejni_cena` float unsigned NOT NULL,
  `nakupni_cena` float unsigned NOT NULL,
  `nestle` tinyint(1) NOT NULL DEFAULT '0',
  `skladem` tinyint(4) NOT NULL DEFAULT '0',
  `body` float NOT NULL,
  PRIMARY KEY (`id_zbozi`),
  KEY `zbozi_dph` (`id_dph`) USING BTREE,
  KEY `zbozi_kategorie` (`id_kategorie`),
  CONSTRAINT `zbozi_dph` FOREIGN KEY (`id_dph`) REFERENCES `dph` (`id_dph`),
  CONSTRAINT `zbozi_kategorie` FOREIGN KEY (`id_kategorie`) REFERENCES `kategorie` (`id_kategorie`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Definition of table `zbozi_objednavky`
--

DROP TABLE IF EXISTS `zbozi_objednavky`;
CREATE TABLE `zbozi_objednavky` (
  `id_zbozi_objednavka` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_objednavka` int(10) unsigned NOT NULL DEFAULT '0',
  `id_zbozi` int(10) unsigned NOT NULL DEFAULT '0',
  `pocet` float unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_zbozi_objednavka`),
  KEY `objednavka` (`id_objednavka`) USING BTREE,
  KEY `vazba_zbozi` (`id_zbozi`),
  CONSTRAINT `vazba_objednavka` FOREIGN KEY (`id_objednavka`) REFERENCES `objednavky` (`id_objednavka`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `vazba_zbozi` FOREIGN KEY (`id_zbozi`) REFERENCES `zbozi` (`id_zbozi`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
