<?php

/**
 * Set up the IdManager as this is required for the ID service
 *
 * USAGE: Copy this file to id.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: logging_default.conf.php,v 1.3 2008/02/26 14:08:11 adamfranco Exp $
 */
 
 	$configuration = new ConfigurationProperties;
	$configuration->addProperty('database_index', $dbID);
	Services::startManagerAsService("LoggingManager", $context, $configuration);
	
/*********************************************************
 * If you wish to add a list of user agents and their uncaught
 * exceptions that should not be logged, uncomment the 
 * lines below and add/remove Exception classes as needed.
 *********************************************************/
// $printer = SegueErrorPrinter::instance();
// $printer->addUserAgentFilter(
// 	"Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)",
// 	array('UnknownActionException'));