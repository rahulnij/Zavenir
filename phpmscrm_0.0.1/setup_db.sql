DROP DATABASE IF EXISTS `phpmscrm`;

CREATE DATABASE `phpmscrm`;

USE `phpmscrm`;

CREATE TABLE IF NOT EXISTS `tusers` (
  `idUser` int(11) NOT NULL AUTO_INCREMENT,
  `szPermHash` tinytext NOT NULL,
  `szUserName` mediumtext NOT NULL,
  `szEmail` tinytext NOT NULL,
  `szPassword` tinytext NOT NULL,
  `dCreated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dLastLogin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `szUser` tinytext NOT NULL,
  `crmUserId` mediumtext NOT NULL COMMENT 'CRM Id for the user',
  `crmBusinessUnitId` mediumtext NOT NULL,
  `crmOrganizationId` mediumtext NOT NULL,
  PRIMARY KEY (`idUser`),
  KEY `szEmail` (`szEmail`(8))
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Table for users' AUTO_INCREMENT=2 ;
