<?php

/**
 * Set up the GUIManager
 *
 * USAGE: Copy this file to gui.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: gui_default.conf.php,v 1.7 2007/09/21 15:49:24 adamfranco Exp $
 */

require_once(HARMONI.'Gui2/GuiManager.class.php');
require_once(MYDIR.'/main/library/Gui2/SiteThemeSource.class.php');
require_once(MYDIR.'/main/modules/view/SiteDispatcher.class.php');


// :: GUIManager setup ::
	define("LOGO_URL", MYPATH."/images/logo.gif");
	
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('database_index', $dbID);
	$configuration->addProperty('database_name', $dbName);
	$configuration->addProperty('default_theme', 'RoundedCorners');
	$configuration->addProperty('character_set', 'utf-8');
	$configuration->addProperty('document_type', 'text/html');
	$configuration->addProperty('document_type_definition', '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
	$configuration->addProperty('xmlns', 'http://www.w3.org/1999/xhtml');
	
	// Theme sources
	$sources = array();
	
	$sources[] = new Segue_Gui2_SiteThemeSource(array('database_index' => $dbID));
	
	// Read-only themes
	$sources[] = array(	'type' => 'directory',
						'path' => MYDIR.'/themes-dist');
	$sources[] = array(	'type' => 'directory',
						'path' => MYDIR.'/themes-local');
	
	$configuration->addProperty('sources', $sources);
	
	$guiMgr = new Harmoni_Gui2_GuiManager;
	$guiMgr->assignConfiguration($configuration);
	$guiMgr->assignOsidContext($context);
	Services::registerObjectAsService("GUIManager", $guiMgr);
	
	$guiMgr->setHead($guiMgr->getHead()."
		
		<link rel='stylesheet' type='text/css' href='".MYPATH."/images/SegueCommon.css' id='SegueCommon'/>
");