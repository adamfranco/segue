-- phpMyAdmin SQL Dump
-- version 2.6.3-pl1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Mar 20, 2008 at 04:27 PM
-- Server version: 5.0.37
-- PHP Version: 5.2.3
-- 
-- Database: `afranco_segue2_prod`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `segue1_id_map`
-- Mapping between segue1 and segue2 ids for auto-redirects.
-- 

CREATE TABLE segue1_id_map (
  segue1_slot_name varchar(50) NOT NULL,
  segue1_id varchar(50) NOT NULL,
  segue2_slot_name varchar(50) NOT NULL,
  segue2_id varchar(170) NOT NULL,
  PRIMARY KEY  (segue1_id)
);

ALTER TABLE ONLY segue1_id_map
	ADD CONSTRAINT segue1_id_map_old_id_unique_key UNIQUE (segue1_slot_name, segue1_id);
ALTER TABLE ONLY segue1_id_map
	ADD CONSTRAINT segue1_id_map_new_id_unique_key UNIQUE (segue2_slot_name, segue2_id);

CREATE INDEX segue1_id_map_segue2_id_index ON segue1_id_map (segue2_id);