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
 * @version $Id: id_default.conf.php,v 1.1 2006/01/13 18:30:22 adamfranco Exp $
 */
 
 	$configuration =& new ConfigurationProperties;
	$configuration->addProperty('database_index', $dbID);
	$configuration->addProperty('database_name', $dbName);
	$configuration->addProperty('id_prefix', $arg0 = 'dev_id-');
	unset($arg0);
	Services::startManagerAsService("IdManager", $context, $configuration);