-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 06, 2011 at 03:33 PM
-- Server version: 5.0.41
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `gegn_sneffeldoodle`
--

-- --------------------------------------------------------

--
-- Table structure for table `BoardsToday`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `gegn_sneffeldoodle`.`BoardsToday` AS select `gegn_sneffeldoodle`.`DoodleBoard`.`id` AS `id`,`gegn_sneffeldoodle`.`DoodleBoard`.`name` AS `name`,`gegn_sneffeldoodle`.`DoodleBoard`.`timeCreated` AS `timeCreated`,`gegn_sneffeldoodle`.`DoodleBoard`.`lastAccess` AS `lastAccess`,`gegn_sneffeldoodle`.`DoodleBoard`.`lastSave` AS `lastSave`,`gegn_sneffeldoodle`.`DoodleBoard`.`privacy` AS `privacy`,`gegn_sneffeldoodle`.`DoodleBoard`.`phpCreateSession` AS `phpCreateSession`,`gegn_sneffeldoodle`.`DoodleBoard`.`userId` AS `userId`,`gegn_sneffeldoodle`.`DoodleBoard`.`brandImage` AS `brandImage`,`gegn_sneffeldoodle`.`DoodleBoard`.`backgroundColor` AS `backgroundColor`,`gegn_sneffeldoodle`.`DoodleBoard`.`expireDate` AS `expireDate`,`gegn_sneffeldoodle`.`DoodleBoard`.`views` AS `views`,`gegn_sneffeldoodle`.`DoodleBoard`.`replayLeft` AS `replayLeft` from `gegn_sneffeldoodle`.`DoodleBoard` where (`gegn_sneffeldoodle`.`DoodleBoard`.`timeCreated` > (unix_timestamp() - 86400));

-- --------------------------------------------------------

--
-- Table structure for table `BoardUsers`
--

CREATE TABLE IF NOT EXISTS `BoardUsers` (
  `boardId` int(11) NOT NULL,
  `users` int(11) NOT NULL,
  PRIMARY KEY  (`boardId`)
) ENGINE=MEMORY DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `DoodleBoard`
--

CREATE TABLE IF NOT EXISTS `DoodleBoard` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(90) default NULL,
  `timeCreated` int(11) default NULL,
  `lastAccess` bigint(20) default NULL,
  `lastSave` int(11) NOT NULL default '0',
  `privacy` varchar(15) NOT NULL default 'Public',
  `phpCreateSession` varchar(32) NOT NULL,
  `schoolId` int(11) NOT NULL,
  `brandImage` varchar(90) NOT NULL,
  `backgroundColor` varchar(6) NOT NULL,
  `expireDate` int(11) NOT NULL,
  `views` int(11) NOT NULL default '0',
  `replayLeft` int(11) NOT NULL default '5',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=29350 ;

-- --------------------------------------------------------

--
-- Table structure for table `PopularBoards`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `gegn_sneffeldoodle`.`PopularBoards` AS select count(`gegn_sneffeldoodle`.`Scribble`.`id`) AS `num`,`gegn_sneffeldoodle`.`Scribble`.`DoodleBoardId` AS `DoodleBoardId` from `gegn_sneffeldoodle`.`Scribble` where 1 group by `gegn_sneffeldoodle`.`Scribble`.`DoodleBoardId`;

-- --------------------------------------------------------

--
-- Table structure for table `School`
--

CREATE TABLE IF NOT EXISTS `School` (
  `id` int(11) NOT NULL auto_increment,
  `domain` varchar(35) NOT NULL,
  `schoolName` varchar(160) NOT NULL,
  `contactName` varchar(90) NOT NULL,
  `contactEmail` varchar(160) NOT NULL,
  `contactPassword` varchar(120) NOT NULL,
  `bgColor` varchar(6) NOT NULL,
  `bgImg` varchar(90) NOT NULL,
  `createDate` int(11) NOT NULL,
  `expireDate` int(11) NOT NULL default '-1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `domain` (`domain`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=62 ;

-- --------------------------------------------------------

--
-- Table structure for table `Scribble`
--

CREATE TABLE IF NOT EXISTS `Scribble` (
  `id` int(11) NOT NULL auto_increment,
  `DoodleBoardId` int(11) NOT NULL,
  `xCoords` blob NOT NULL,
  `yCoords` blob NOT NULL,
  `type` varchar(10) NOT NULL,
  `color` varchar(11) NOT NULL,
  `metaData` varchar(200) NOT NULL,
  `sizeX` int(11) NOT NULL,
  `sizeY` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `undoName` varchar(25) NOT NULL,
  `timeCreated` bigint(20) NOT NULL,
  `serverTimeCreated` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `DoodelBoard` (`DoodleBoardId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=515576 ;

-- --------------------------------------------------------

--
-- Table structure for table `ScribbleTypes`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `gegn_sneffeldoodle`.`ScribbleTypes` AS select `gegn_sneffeldoodle`.`Scribble`.`type` AS `type`,count(`gegn_sneffeldoodle`.`Scribble`.`type`) AS `count(type)` from `gegn_sneffeldoodle`.`Scribble` group by `gegn_sneffeldoodle`.`Scribble`.`type`;

-- --------------------------------------------------------

--
-- Table structure for table `Session`
--

CREATE TABLE IF NOT EXISTS `Session` (
  `flexSessionId` varchar(40) NOT NULL,
  `DoodleBoardId` int(11) NOT NULL,
  `lastAccess` int(11) NOT NULL,
  `screenName` varchar(90) NOT NULL,
  `color` varchar(11) NOT NULL,
  PRIMARY KEY  (`flexSessionId`),
  KEY `SessionBoard` (`DoodleBoardId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `TempBoard`
--

CREATE TABLE IF NOT EXISTS `TempBoard` (
  `id` int(11) NOT NULL auto_increment,
  `sessionId` varchar(32) NOT NULL,
  `timeCreated` int(11) NOT NULL,
  `name` varchar(90) default NULL,
  `brandImage` varchar(90) default NULL,
  `backgroundColor` varchar(6) default NULL,
  `height` int(11) default NULL,
  `width` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MEMORY  DEFAULT CHARSET=latin1 AUTO_INCREMENT=49 ;

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE IF NOT EXISTS `User` (
  `id` int(11) NOT NULL auto_increment,
  `email` varchar(120) NOT NULL,
  `name` varchar(165) NOT NULL,
  `password` varchar(120) NOT NULL,
  `credit` int(11) NOT NULL default '60',
  `signupDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `lastLogin` timestamp NULL default NULL,
  `emailUpdates` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=43 ;

-- --------------------------------------------------------

--
-- Table structure for table `WhiteboardSession`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `gegn_sneffeldoodle`.`WhiteboardSession` AS select count(`gegn_sneffeldoodle`.`Session`.`flexSessionId`) AS `numUsers`,from_unixtime(max(`gegn_sneffeldoodle`.`Session`.`lastAccess`)) AS `lastAccess`,from_unixtime(min(`gegn_sneffeldoodle`.`Session`.`lastAccess`)) AS `firstAccess`,`gegn_sneffeldoodle`.`Session`.`DoodleBoardId` AS `DoodleBoardId`,(select `gegn_sneffeldoodle`.`DoodleBoard`.`privacy` AS `privacy` from `gegn_sneffeldoodle`.`DoodleBoard` where (`gegn_sneffeldoodle`.`DoodleBoard`.`id` = `gegn_sneffeldoodle`.`Session`.`DoodleBoardId`)) AS `privacy` from `gegn_sneffeldoodle`.`Session` where 1 group by `gegn_sneffeldoodle`.`Session`.`DoodleBoardId`;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Scribble`
--
ALTER TABLE `Scribble`
  ADD CONSTRAINT `DoodelBoard` FOREIGN KEY (`DoodleBoardId`) REFERENCES `DoodleBoard` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Session`
--
ALTER TABLE `Session`
  ADD CONSTRAINT `SessionBoard` FOREIGN KEY (`DoodleBoardId`) REFERENCES `DoodleBoard` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

