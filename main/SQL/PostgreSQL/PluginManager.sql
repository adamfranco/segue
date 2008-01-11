-- /**
-- @package segue.plugin_manager
--
-- @copyright Copyright &copy; 2005, Middlebury College
-- @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
--
-- @version $Id: PluginManager.sql,v 1.3 2008/01/11 20:28:33 adamfranco Exp $
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


-- --------------------------------------------------------

-- 
-- Table structure for table `segue_plugin_version`
-- 

CREATE TABLE segue_plugin_version (
  version_id SERIAL NOT NULL,
  node_id varchar(170) NOT NULL,
  tstamp timestamp with time zone NOT NULL default CURRENT_TIMESTAMP,
  comment varchar(255) NOT NULL,
  agent_id varchar(170) NOT NULL,
  version_xml text NOT NULL,
)

ALTER TABLE ONLY segue_plugin_version
	ADD CONSTRAINT segue_plugin_version_primary_key PRIMARY KEY (version_id);

CREATE INDEX segue_plugin_version_node_id_index ON segue_plugin_version (node_id);