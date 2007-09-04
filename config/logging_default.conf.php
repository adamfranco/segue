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
 * @version $Id: logging_default.conf.php,v 1.2 2007/09/04 18:00:43 adamfranco Exp $
 */
 
 	$configuration = new ConfigurationProperties;
	$configuration->addProperty('database_index', $dbID);
	Services::startManagerAsService("LoggingManager", $context, $configuration);