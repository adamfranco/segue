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
 * @version $Id: gui_default.conf.php,v 1.3 2007/01/08 21:02:32 adamfranco Exp $
 */

require_once(dirname(__FILE__)."/../themes/SimpleTheme/SimpleTheme.class.php");


// :: GUIManager setup ::
	define("LOGO_URL", MYPATH."/themes/SimpleTheme/images/logo.gif");
	
	$configuration =& new ConfigurationProperties;
	$configuration->addProperty('database_index', $dbID);
	$configuration->addProperty('database_name', $dbName);
	$configuration->addProperty('default_theme', new SimpleTheme);
	$configuration->addProperty('character_set', $arg0 = 'utf-8');
	$configuration->addProperty('document_type', $arg1 = 'text/html');
	$configuration->addProperty('document_type_definition', $arg2 = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
	
	$arrayOfThemes[] = array("Generic Theme","GenericTheme");
	$arrayOfThemes[] = array("Simple Theme","SimpleTheme");
	$arrayOfThemes[] = array("Simple Lines Theme","SimpleLinesTheme");
	$arrayOfThemes[] = array("Simple Black Theme","SimpleThemeBlack");


	$configuration->addProperty('array_of_default_themes', $arrayOfThemes);
	
	unset($arg0, $arg1, $arg2);
	Services::startManagerAsService("GUIManager", $context, $configuration);