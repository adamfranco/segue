-- /**
-- @package segue.plugin_manager
--
-- @copyright Copyright &copy; 2005, Middlebury College
-- @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
--
-- @version $Id: PluginManager.sql,v 1.2 2007/09/13 16:09:41 adamfranco Exp $
-- */
-- --------------------------------------------------------

-- 
-- Table structure for table `plugin_type`
-- 

CREATE TABLE plugin_type (
  type_id SERIAL NOT NULL,
  type_domain varchar(255) NOT NULL,
  type_authority varchar(255) NOT NULL,
  type_keyword varchar(255) NOT NULL,
  type_description text,
  type_enabled smallint,
  PRIMARY KEY  (type_id)
);

ALTER TABLE ONLY plugin_type
	ADD CONSTRAINT plugin_type_unique UNIQUE (type_domain, type_authority, type_keyword);

-- --------------------------------------------------------

-- 
-- Table structure for table `plugin_manager`
-- 

CREATE TABLE plugin_manager (
  fk_plugin_type int NOT NULL,
  fk_schema varchar(255) NOT NULL,
  PRIMARY KEY  (fk_plugin_type,fk_schema)
);

ALTER TABLE ONLY plugin_manager
	ADD CONSTRAINT plugin_manager_fk_plugin_type_fkey FOREIGN KEY (fk_plugin_type) REFERENCES "plugin_type"(type_id) ON UPDATE CASCADE ON DELETE RESTRICT;
        