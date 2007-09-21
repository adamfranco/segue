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

require_once(dirname(__FILE__)."/../themes/SimpleThemeWhite/SimpleThemeWhite.class.php");


// :: GUIManager setup ::
	define("LOGO_URL", MYPATH."/themes/SimpleThemeWhite/images/logo.gif");
	
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('database_index', $dbID);
	$configuration->addProperty('database_name', $dbName);
	$configuration->addProperty('default_theme', new SimpleThemeWhite);
	$configuration->addProperty('character_set', 'utf-8');
	$configuration->addProperty('document_type', 'text/html');
	$configuration->addProperty('document_type_definition', '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
	$configuration->addProperty('xmlns', 'http://www.w3.org/1999/xhtml');
	
	
	$arrayOfThemes[] = array("Generic Theme","GenericTheme");
	$arrayOfThemes[] = array("Simple Theme","SimpleTheme");
	$arrayOfThemes[] = array("Simple Lines Theme","SimpleLinesTheme");
	$arrayOfThemes[] = array("Simple Black Theme","SimpleThemeBlack");
	$arrayOfThemes[] = array("Simple Black Theme","SimpleThemeWhite");


	$configuration->addProperty('array_of_default_themes', $arrayOfThemes);
	
	Services::startManagerAsService("GUIManager", $context, $configuration);