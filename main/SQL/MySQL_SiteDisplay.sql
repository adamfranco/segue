-- phpMyAdmin SQL Dump
-- version 2.6.3-pl1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jul 27, 2007 at 10:57 AM
-- Server version: 5.0.37
-- PHP Version: 4.4.2
-- 
-- Database: `afranco_segue2`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `segue_site_alias`
-- 

CREATE TABLE segue_site_alias (
  site_id varchar(75) collate utf8_bin NOT NULL,
  alias varchar(75) collate utf8_bin NOT NULL,
  UNIQUE KEY site_id_2 (site_id,alias),
  KEY alias (alias),
  KEY site_id (site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `segue_site_owner`
-- 

CREATE TABLE segue_site_owner (
  owner_id varchar(75) collate utf8_bin NOT NULL,
  site_id varchar(75) collate utf8_bin NOT NULL,
  KEY owner_id (owner_id),
  KEY site_id (site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
