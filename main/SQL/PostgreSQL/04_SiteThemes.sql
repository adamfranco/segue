-- phpMyAdmin SQL Dump
-- version 2.11.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 20, 2008 at 06:38 PM
-- Server version: 5.0.26
-- PHP Version: 5.2.4

--
-- Database: "afranco_segue2_prod"
--

-- --------------------------------------------------------

--
-- Table structure for table "segue_site_theme"
--

CREATE TABLE IF NOT EXISTS "segue_site_theme" (
  "id" int(10) unsigned NOT NULL,
  "fk_site" varchar(75) NOT NULL,
  "display_name" varchar(255) NOT NULL,
  "description" text NOT NULL,
  "modify_timestamp" timestamp with time zone NOT NULL default CURRENT_TIMESTAMP
);

ALTER TABLE ONLY segue_site_theme
	ADD CONSTRAINT segue_site_theme_primary_key PRIMARY KEY (id);
	
CREATE INDEX segue_site_theme_fk_site_index ON segue_site_theme (fk_site);


-- --------------------------------------------------------

--
-- Table structure for table "segue_site_theme_data"
--

CREATE TABLE IF NOT EXISTS "segue_site_theme_data" (
  "fk_theme" int(10) unsigned NOT NULL,
  "fk_type" int(10) unsigned NOT NULL,
  "data" text NOT NULL,
  UNIQUE KEY "fk_theme" ("fk_theme","fk_type"),
  KEY "fk_theme_2" ("fk_theme"),
  KEY "fk_type" ("fk_type")
);

ALTER TABLE ONLY segue_site_theme_data
	ADD CONSTRAINT segue_site_theme_data_unique_key UNIQUE KEY (fk_theme, fk_type);
	
CREATE INDEX segue_site_theme_data_fk_theme_2_index ON segue_site_theme_data (fk_theme);
CREATE INDEX segue_site_theme_data_fk_type_index ON segue_site_theme_data (fk_type);


-- --------------------------------------------------------

--
-- Table structure for table "segue_site_theme_data_type"
--

CREATE TABLE IF NOT EXISTS "segue_site_theme_data_type" (
  "id" int(10) NOT NULL,
  "data_type" varchar(30) NOT NULL
);

ALTER TABLE ONLY segue_site_theme_data_type
	ADD CONSTRAINT segue_site_theme_data_type_primary_key PRIMARY KEY (id);
	
ALTER TABLE ONLY segue_site_theme_data_type
	ADD CONSTRAINT segue_site_theme_data_type_unique_key UNIQUE KEY (data_type);

-- --------------------------------------------------------

--
-- Table structure for table "segue_site_theme_image"
--

CREATE TABLE IF NOT EXISTS "segue_site_theme_image" (
  "fk_theme" int(10) NOT NULL,
  "modify_timestamp" timestamp with time zone NOT NULL default CURRENT_TIMESTAMP,
  "mime_type" varchar(50) NOT NULL,
  "path" varchar(200) NOT NULL,
  "size" int(11) NOT NULL,
  "data" text NOT NULL
);

ALTER TABLE ONLY segue_site_theme_image
	ADD CONSTRAINT segue_site_theme_image_primary_key PRIMARY KEY ("fk_theme", "path");
	
CREATE INDEX segue_site_theme_image_fk_theme_index ON segue_site_theme_image (fk_theme);
CREATE INDEX segue_site_theme_image_path_index ON segue_site_theme_image ("path");

-- --------------------------------------------------------

--
-- Table structure for table "segue_site_theme_thumbnail"
--

CREATE TABLE IF NOT EXISTS "segue_site_theme_thumbnail" (
  "fk_theme" int(10) NOT NULL,
  "modify_timestamp" timestamp with time zone NOT NULL default CURRENT_TIMESTAMP,
  "mime_type" varchar(50) NOT NULL,
  "size" int(11) NOT NULL,
  "data" text NOT NULL
);

ALTER TABLE ONLY segue_site_theme_thumbnail
	ADD CONSTRAINT segue_site_theme_thumbnail_primary_key PRIMARY KEY ("fk_theme");

--
-- Constraints for dumped tables
--

--
-- Constraints for table "segue_site_theme"
--
ALTER TABLE "segue_site_theme"
  ADD CONSTRAINT "segue_site_theme_ibfk_1" FOREIGN KEY ("fk_site") REFERENCES "az2_node" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table "segue_site_theme_data"
--
ALTER TABLE "segue_site_theme_data"
  ADD CONSTRAINT "segue_site_theme_data_ibfk_2" FOREIGN KEY ("fk_type") REFERENCES "segue_site_theme_data_type" ("id"),
  ADD CONSTRAINT "segue_site_theme_data_ibfk_1" FOREIGN KEY ("fk_theme") REFERENCES "segue_site_theme" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table "segue_site_theme_image"
--
ALTER TABLE "segue_site_theme_image"
  ADD CONSTRAINT "segue_site_theme_image_ibfk_1" FOREIGN KEY ("fk_theme") REFERENCES "segue_site_theme" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table "segue_site_theme_thumbnail"
--
ALTER TABLE "segue_site_theme_thumbnail"
  ADD CONSTRAINT "segue_site_theme_thumbnail_ibfk_1" FOREIGN KEY ("fk_theme") REFERENCES "segue_site_theme" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
