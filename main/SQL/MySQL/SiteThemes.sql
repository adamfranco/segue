-- phpMyAdmin SQL Dump
-- version 2.11.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 20, 2008 at 06:37 PM
-- Server version: 5.0.26
-- PHP Version: 5.2.4

--
-- Database: `afranco_segue2_prod`
--

-- --------------------------------------------------------

--
-- Table structure for table `segue_site_theme`
--

CREATE TABLE `segue_site_theme` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_site` varchar(75) character set utf8 collate utf8_bin NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `modify_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `fk_site` (`fk_site`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Themes private to a particular site.';

-- --------------------------------------------------------

--
-- Table structure for table `segue_site_theme_data`
--

CREATE TABLE `segue_site_theme_data` (
  `fk_theme` int(10) unsigned NOT NULL,
  `fk_type` int(10) unsigned NOT NULL,
  `data` text NOT NULL,
  UNIQUE KEY `fk_theme` (`fk_theme`,`fk_type`),
  KEY `fk_theme_2` (`fk_theme`),
  KEY `fk_type` (`fk_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CSS and HTML data for a theme.';

-- --------------------------------------------------------

--
-- Table structure for table `segue_site_theme_data_type`
--

CREATE TABLE `segue_site_theme_data_type` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `data_type` varchar(30) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `type` (`data_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A listing of the data types stored for themes. Block_Standar';

-- --------------------------------------------------------

--
-- Table structure for table `segue_site_theme_image`
--

CREATE TABLE `segue_site_theme_image` (
  `fk_theme` int(10) unsigned NOT NULL,
  `modify_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `mime_type` varchar(50) NOT NULL,
  `path` varchar(200) NOT NULL,
  `size` int(11) NOT NULL,
  `data` mediumblob NOT NULL,
  PRIMARY KEY  (`fk_theme`,`path`),
  KEY `fk_theme` (`fk_theme`),
  KEY `path` (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `segue_site_theme_thumbnail`
--

CREATE TABLE `segue_site_theme_thumbnail` (
  `fk_theme` int(10) unsigned NOT NULL,
  `modify_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `mime_type` varchar(50) NOT NULL,
  `size` int(11) NOT NULL,
  `data` mediumblob NOT NULL,
  PRIMARY KEY  (`fk_theme`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `segue_site_theme`
--
ALTER TABLE `segue_site_theme`
  ADD CONSTRAINT `segue_site_theme_ibfk_1` FOREIGN KEY (`fk_site`) REFERENCES `az2_node` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `segue_site_theme_data`
--
ALTER TABLE `segue_site_theme_data`
  ADD CONSTRAINT `segue_site_theme_data_ibfk_2` FOREIGN KEY (`fk_type`) REFERENCES `segue_site_theme_data_type` (`id`),
  ADD CONSTRAINT `segue_site_theme_data_ibfk_1` FOREIGN KEY (`fk_theme`) REFERENCES `segue_site_theme` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `segue_site_theme_image`
--
ALTER TABLE `segue_site_theme_image`
  ADD CONSTRAINT `segue_site_theme_image_ibfk_1` FOREIGN KEY (`fk_theme`) REFERENCES `segue_site_theme` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `segue_site_theme_thumbnail`
--
ALTER TABLE `segue_site_theme_thumbnail`
  ADD CONSTRAINT `segue_site_theme_thumbnail_ibfk_1` FOREIGN KEY (`fk_theme`) REFERENCES `segue_site_theme` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
