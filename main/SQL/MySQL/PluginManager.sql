-- /**
-- @package segue.plugin_manager
--
-- @copyright Copyright &copy; 2005, Middlebury College
-- @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
--
-- @version $Id: PluginManager.sql,v 1.1 2007/09/11 19:10:46 adamfranco Exp $
-- */
-- --------------------------------------------------------

-- 
-- Table structure for table `plugin_manager`
-- 

CREATE TABLE plugin_manager (
  fk_plugin_type int(10) unsigned NOT NULL default '0',
  fk_schema varchar(255) NOT NULL default '0',
  PRIMARY KEY  (fk_plugin_type,fk_schema)
) 
CHARACTER SET utf8
TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `plugin_type`
-- 

CREATE TABLE plugin_type (
  type_id int(10) NOT NULL auto_increment,
  type_domain varchar(255) NOT NULL default '',
  type_authority varchar(255) NOT NULL default '',
  type_keyword varchar(255) NOT NULL default '',
  type_description text,
  type_enabled boolean,
  PRIMARY KEY  (type_id),
  KEY domain (type_domain),
  KEY authority (type_authority),
  KEY keyword (type_keyword)
) 
CHARACTER SET utf8
TYPE=InnoDB;
        