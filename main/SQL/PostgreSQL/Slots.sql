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

CREATE TABLE segue_slot (
  shortname varchar(50)NOT NULL,
  site_id varchar(50) default NULL,
  "type" varchar(10) NOT NULL default 'personal',
  location_category NOT NULL,
  media_quota int default NULL,
  PRIMARY KEY  (shortname)
);

CREATE INDEX segue_slot_site_id_index ON segue_slot (site_id);
CREATE INDEX segue_slot_location_category_index ON segue_slot (location_category);
-- --------------------------------------------------------

-- 
-- Table structure for table `segue_slot_owner`
-- 

CREATE TABLE segue_slot_owner (
  shortname varchar(50) NOT NULL,
  owner_id varchar(75) NOT NULL,
  removed smallint default '0'
);

ALTER TABLE ONLY segue_slot_owner
	ADD CONSTRAINT segue_slot_owner_shortname_fkey FOREIGN KEY (shortname) REFERENCES "segue_slot"(shortname) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY segue_slot_owner
	ADD CONSTRAINT segue_slot_owner_unique_key UNIQUE (shortname, owner_id);

CREATE INDEX segue_slot_owner_shortname_index ON segue_slot_owner (shortname);
CREATE INDEX segue_slot_owner_owner_id_index ON segue_slot_owner (owner_id);