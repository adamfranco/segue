-- phpMyAdmin SQL Dump
-- version 2.6.3-pl1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jul 30, 2007 at 01:33 PM
-- Server version: 5.0.37
-- PHP Version: 4.4.2
-- 
-- Database: `afranco_segue2`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `segue_slot`
-- 

CREATE TABLE `segue_slot` (
  `shortname` varchar(50) collate utf8_bin NOT NULL,
  `site_id` varchar(50) collate utf8_bin NOT NULL,
  `type` enum('personal','course','custom') collate utf8_bin NOT NULL default 'personal',
  `location_category` enum('main','community') collate utf8_bin NOT NULL,
  PRIMARY KEY  (`shortname`),
  KEY `site_id` (`site_id`),
  KEY `location_category` (`location_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `segue_slot_owner`
-- 

CREATE TABLE segue_slot_owner (
  shortname varchar(50) collate utf8_bin NOT NULL,
  owner_id varchar(75) collate utf8_bin NOT NULL,
  removed int(1) default 0,
  KEY shortname (shortname),
  KEY owner_id (owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
