# MySQL-Front 3.2  (Build 14.8)

/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='SYSTEM' */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE */;
/*!40101 SET SQL_MODE='' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES */;
/*!40103 SET SQL_NOTES='ON' */;


# Host: localhost    Database: juice
# ------------------------------------------------------
# Server version 4.1.22-community-nt

DROP DATABASE IF EXISTS `juice`;
CREATE DATABASE `juice` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `juice`;

#
# Table structure for table _user
#

CREATE TABLE `_user` (
  `id` int(11) NOT NULL auto_increment,
  `username` char(255) default NULL,
  `password` char(255) default NULL,
  `email` char(255) default NULL,
  `team` tinyint(1) NOT NULL default '0',
  `createdate` timestamp NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

#
# Dumping data for table _user
#

INSERT INTO `_user` VALUES (1,'braeker','braeker','braeker@gmail.com',1,NULL);

#
# Table structure for table statistics
#

CREATE TABLE `statistics` (
  `id` int(11) NOT NULL auto_increment,
  `testid` int(11) default NULL,
  `userid` int(11) default NULL,
  `useragent` varchar(255) default NULL,
  `platform` varchar(255) default NULL,
  `engine` varchar(255) default NULL,
  `engineversion` varchar(255) default NULL,
  `version` varchar(255) default NULL,
  `result` tinyint(3) default NULL,
  `createdate` timestamp NULL default CURRENT_TIMESTAMP,
  `ip` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Dumping data for table statistics
#

INSERT INTO `statistics` VALUES (1,1,1,'Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-BR; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12','Win32','Mozilla','1.8.1.12','1.2.3',1,'2008-03-13 01:32:56','127.0.0.1');
INSERT INTO `statistics` VALUES (2,1,1,'Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-BR; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12','Win32','Mozilla','1.8.1.12','1.2.3',1,'2008-03-13 01:34:14','127.0.0.1');
INSERT INTO `statistics` VALUES (3,1,1,'Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-BR; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12','Win32','Mozilla','1.8.1.12','1.2.3',2,'2008-03-13 01:34:33','127.0.0.1');
INSERT INTO `statistics` VALUES (4,1,NULL,'Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-BR; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12','Win32','Mozilla','1.8.1.12','1.2.3',1,'2008-03-13 01:43:50','127.0.0.1');
INSERT INTO `statistics` VALUES (5,1,NULL,'Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-BR; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12','Win32','Mozilla','1.8.1.12','1.2.3',1,'2008-03-13 01:47:02','127.0.0.1');
INSERT INTO `statistics` VALUES (6,1,NULL,'Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-BR; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12','Win32','Mozilla','1.8.1.12','1.2.3',3,'2008-03-13 01:48:08','127.0.0.1');
INSERT INTO `statistics` VALUES (7,1,NULL,'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)','Win32','Internet Explorer','7.0','1.2.3',2,'2008-03-13 01:52:18','127.0.0.1');
INSERT INTO `statistics` VALUES (8,1,1,'Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-BR; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12','Win32','Mozilla','1.8.1.12','1.2.3',1,'2008-03-13 01:55:10','127.0.0.1');

#
# Table structure for table tests
#

CREATE TABLE `tests` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `category` varchar(255) default NULL,
  `code` text,
  `template` text,
  `enabled` tinyint(1) default NULL,
  `createdate` timestamp NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Dumping data for table tests
#


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
